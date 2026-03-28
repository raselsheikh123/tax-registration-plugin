<?php

/**
 * Plugin Name: Client Registration System
 * Description: Custom client registration and admin management system.
 * Version: 5.0
 * Author: Rasel
 * Author URI: https://alterdiv.com/
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/*
|--------------------------------------------------------------------------
| Define Plugin Constants
|--------------------------------------------------------------------------
*/

define('CRS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CRS_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| Include Required Files
|--------------------------------------------------------------------------
*/

require_once CRS_PLUGIN_PATH . 'includes/admin-metabox.php';

require_once CRS_PLUGIN_PATH . 'includes/cpt.php';
require_once CRS_PLUGIN_PATH . 'includes/taxonomy.php';
require_once CRS_PLUGIN_PATH . 'includes/ajax-handler.php';
require_once CRS_PLUGIN_PATH . 'includes/metabox.php';
require_once CRS_PLUGIN_PATH . 'includes/admin-dashboard.php';

/*
|--------------------------------------------------------------------------
| Enqueue Frontend Assets
|--------------------------------------------------------------------------
*/

function crs_enqueue_assets()
{

    wp_enqueue_style(
        'crs-form-style',
        CRS_PLUGIN_URL . 'assets/css/form.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'crs-form-script',
        CRS_PLUGIN_URL . 'assets/js/form.js',
        array('jquery'),
        '1.0',
        true
    );

    // Pass AJAX URL + Nonce to JS
    $thank_you_page = get_page_by_path('thank-you');

    $thank_you_url = $thank_you_page ? get_permalink($thank_you_page->ID) : home_url('/');
    wp_localize_script(
        'crs-form-script',
        'crs_ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crs_nonce'),
            'thank_you_url' => $thank_you_url,
        )
    );
}

add_action('wp_enqueue_scripts', 'crs_enqueue_assets');

/*
|--------------------------------------------------------------------------
| Register Shortcode
|--------------------------------------------------------------------------
*/

function crs_new_client_form_shortcode()
{
    ob_start();
    include CRS_PLUGIN_PATH . 'templates/new-client-form.php';
    return ob_get_clean();
}

add_shortcode('crs_client_form', 'crs_new_client_form_shortcode');

/*
|--------------------------------------------------------------------------
| Google Sheet Settings Menu
|--------------------------------------------------------------------------
*/

function crs_register_google_menu()
{

    add_submenu_page(
        'crs-dashboard',
        'Google Sheet Sync',
        'Google Sheet Sync',
        'manage_options',
        'crs-google-sync',
        'crs_render_google_settings'
    );
}
add_action('admin_menu', 'crs_register_google_menu');

function crs_render_google_settings()
{

    if (isset($_POST['crs_webhook_url'])) {
        update_option('crs_webhook_url', esc_url_raw($_POST['crs_webhook_url']));
        echo '<div class="updated"><p>Settings Saved.</p></div>';
    }

    $webhook = get_option('crs_webhook_url', '');
    ?>

    <div class="wrap">
        <h1>Google Sheet Sync</h1>

        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Webhook URL</th>
                    <td>
                        <input type="text" name="crs_webhook_url" value="<?php echo esc_attr($webhook); ?>"
                            style="width:500px;">
                        <p class="description">
                            Paste your Google Apps Script Web App URL here.
                        </p>
                    </td>
                </tr>
            </table>

            <p>
                <input type="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>

        <hr>

        <h2>Manual Test</h2>

        <form method="post">
            <input type="number" name="crs_test_post_id" placeholder="Post ID">
            <input type="submit" name="crs_manual_send" class="button" value="Send to Google Sheet">
        </form>

    </div>

    <?php
}

add_action('admin_init', 'crs_manual_google_send');

function crs_manual_google_send()
{

    if (!isset($_POST['crs_manual_send']))
        return;
    if (!current_user_can('manage_options'))
        return;

    $post_id = intval($_POST['crs_test_post_id']);

    if ($post_id) {
        crs_send_to_google($post_id);
    }
}

function crs_send_to_google($post_id)
{

    $webhook = get_option('crs_webhook_url');
    if (!$webhook)
        return;

    $questionnaire = get_post_meta($post_id, 'crs_questionnaire', true);
    if (!is_array($questionnaire))
        $questionnaire = array();

    $dependents = get_post_meta($post_id, 'crs_dependents', true);
    if (!is_array($dependents))
        $dependents = array();

    $data = array(
        'post_id' => $post_id,
        'client_type' => get_post_meta($post_id, 'crs_client_type', true),
        'full_name' => get_the_title($post_id),
        'ssn' => get_post_meta($post_id, 'crs_ssn', true),
        'dob' => get_post_meta($post_id, 'crs_dob', true),
        'occupation' => get_post_meta($post_id, 'crs_occupation', true),
        'phone' => get_post_meta($post_id, 'crs_phone', true),
        'email' => get_post_meta($post_id, 'crs_email', true),
        'address' => get_post_meta($post_id, 'crs_address', true),
        'bank_account' => get_post_meta($post_id, 'crs_bank_account', true),
        'bank_routing' => get_post_meta($post_id, 'crs_bank_routing', true),
        'filing_status' => get_post_meta($post_id, 'crs_filing_status', true),
        'spouse_name' => get_post_meta($post_id, 'crs_spouse_name', true),
        'spouse_ssn' => get_post_meta($post_id, 'crs_spouse_ssn', true),
        'spouse_dob' => get_post_meta($post_id, 'crs_spouse_dob', true),
        'spouse_email' => get_post_meta($post_id, 'crs_spouse_email', true),
        'spouse_occ' => get_post_meta($post_id, 'crs_spouse_occupation', true),
        'document_urls' => get_post_meta($post_id, 'crs_document_urls', true),
    );

    // Add questionnaire fields flat
    foreach ($questionnaire as $key => $value) {
        $data['q_' . $key] = $value;
    }

    // Add dependents flat (max 6)
    for ($i = 0; $i < 6; $i++) {

        $index = $i + 1;

        $dep = $dependents[$i] ?? array();

        $data["dep{$index}_name"] = $dep['name'] ?? '';
        $data["dep{$index}_ssn"] = $dep['ssn'] ?? '';
        $data["dep{$index}_dob"] = $dep['dob'] ?? '';
        $data["dep{$index}_rel"] = $dep['relationship'] ?? '';
        $data["dep{$index}_live"] = $dep['lived'] ?? '';
        $data["dep{$index}_care"] = $dep['childcare'] ?? '';
    }

    wp_remote_post($webhook, array(
        'method' => 'POST',
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));
}

add_action('init', 'crs_handle_file_download');

function crs_handle_file_download()
{

    if (isset($_GET['crs_download']) && $_GET['crs_download'] == '1') {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied. Only administrators can access this file.');
        }

        $post_id = intval($_GET['post_id']);
        $index = intval($_GET['index']);

        $urls = get_post_meta($post_id, 'crs_document_urls', true);
        if (is_array($urls) && isset($urls[$index])) {
            $url = $urls[$index];
            $file_path = str_replace(home_url('/'), ABSPATH, $url);

            if (file_exists($file_path)) {
                $mime_type = mime_content_type($file_path);
                header('Content-Type: ' . $mime_type);
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                readfile($file_path);
                exit;
            }
        }
        wp_die('File not found.');
    }
}
