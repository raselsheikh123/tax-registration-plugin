<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Register Main Admin Menu
|--------------------------------------------------------------------------
*/

function crs_register_admin_menu()
{

    add_menu_page(
        'Client Registration Dashboard', // Page title
        'Client Registration',           // Menu title
        'manage_options',                // Capability
        'crs-dashboard',                 // Menu slug
        'crs_render_dashboard_page',     // Callback function
        'dashicons-groups',              // Icon
        25                               // Position
    );
    // Add default CPT list under our menu
    add_submenu_page(
        'crs-dashboard',
        'All Clients',
        'All Clients',
        'manage_options',
        'edit.php?post_type=client_registration'
    );

}
add_action('admin_menu', 'crs_register_admin_menu');

/*
|--------------------------------------------------------------------------
| Render Dashboard Page
|--------------------------------------------------------------------------
*/

function crs_render_dashboard_page()
{

    // Total Submissions
    $total_clients = wp_count_posts('client_registration')->publish;

    // Online Clients
    $new_clients = new WP_Query(array(
        'post_type' => 'client_registration',
        'meta_query' => array(
            array(
                'key' => 'crs_client_type',
                'value' => 'New Client',
                'compare' => '='
            )
        )
    ));
    $new_client = $new_clients->found_posts;

    // In Office Clients
    $existing_clients = new WP_Query(array(
        'post_type' => 'client_registration',
        'meta_query' => array(
            array(
                'key' => 'crs_client_type',
                'value' => 'Existing Client',
                'compare' => '='
            )
        )
    ));
    $existing_client = $existing_clients->found_posts;

    ?>

    <div class="wrap">
        <h1>Client Registration Dashboard</h1>

        <div style="
            background:#fff;
            border-left:4px solid #2271b1;
            padding:15px 20px;
            margin:20px 0;
            box-shadow:0 1px 1px rgba(0,0,0,0.04);
        ">
            <strong>Form Shortcode:</strong>
            <code style="font-size:14px;">[crs_client_form]</code>
            <p style="margin:8px 0 0;">
                Copy and paste this shortcode into any page to display the client intake form.
            </p>
        </div>

        <div style="display:flex; gap:20px; margin-top:20px;">

            <div style="background:#002b66; padding:20px; border:1px solid #ddd;border-radius: 10px;">
                <h2 style="color: #fff;">Total Submissions</h2>
                <p style="font-size: 24px;
                        margin-bottom: 0;
                        margin-top: 2px;
                        display: inline-flex;
                        background: #135e96;
                        color: #fff;
                        padding: 8px 10px;
                        border-radius: 9px;
                        justify-content: center;
                        align-items: center;"><?php echo esc_html($total_clients); ?></p>
            </div>

            <div style="background:#002b66; padding:20px; border:1px solid #ddd;border-radius: 10px;">
                <h2 style="color: #fff;">New Clients</h2>
                <p style="font-size: 24px;
                        margin-bottom: 0;
                        margin-top: 2px;
                        display: inline-flex;
                        background: #135e96;
                        color: #fff;
                        padding: 8px 10px;
                        border-radius: 9px;
                        justify-content: center;
                        align-items: center;"><?php echo esc_html($new_client); ?></p>
            </div>

            <div style="background:#002b66; padding:20px; border:1px solid #ddd;border-radius: 10px;">
                <h2 style="color: #fff;">Existing Clients</h2>
                <p style="font-size: 24px;
                        margin-bottom: 0;
                        margin-top: 2px;
                        display: inline-flex;
                        background: #135e96;
                        color: #fff;
                        padding: 8px 10px;
                        border-radius: 9px;
                        justify-content: center;
                        align-items: center;"><?php echo esc_html($existing_client); ?></p>
            </div>

        </div>

    </div>

    <hr style="margin:40px 0;">

    <h2>All Clients</h2>
    <form method="get" style="margin-bottom:20px;">

        <input type="hidden" name="page" value="crs-dashboard">

        <select name="filter_type">
            <option value="">Customer Type</option>
            <option value="New Client" <?php selected($_GET['filter_type'] ?? '', 'New Client'); ?>>New Client</option>
            <option value="Existing Client" <?php selected($_GET['filter_type'] ?? '', 'Existing Client'); ?>>Existing
                Client</option>
        </select>


        <button type="submit" class="button">Filter</button>

        <a href="<?php echo esc_url(add_query_arg(array_merge($_GET, array('export_csv' => '1')))); ?>"
            class="button button-primary">
            Export Excel
        </a>

    </form>


    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Customer Type</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Action</th>
                <th>Print</th>

            </tr>
        </thead>
        <tbody>

            <?php
            $meta_query = array();

            if (!empty($_GET['filter_type'])) {
                $meta_query[] = array(
                    'key' => 'crs_client_type',
                    'value' => sanitize_text_field($_GET['filter_type']),
                    'compare' => '='
                );
            }

            $args = array(
                'post_type' => 'client_registration',
                'posts_per_page' => -1,
            );

            if (!empty($meta_query)) {
                $args['meta_query'] = $meta_query;
            }

            $clients = new WP_Query($args);


            if ($clients->have_posts()):
                while ($clients->have_posts()):
                    $clients->the_post();

                    $post_id = get_the_ID();

                    $phone = get_post_meta($post_id, 'crs_phone', true);
                    $email = get_post_meta($post_id, 'crs_email', true);

                    $client_type = get_post_meta($post_id, 'crs_client_type', true);

                    ?>

                    <tr>
                        <td><?php echo esc_html(get_the_title()); ?></td>
                        <td><?php echo esc_html($client_type); ?></td>
                        <td><?php echo esc_html($phone); ?></td>
                        <td><?php echo esc_html($email); ?></td>
                        <td>
                            <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>"
                                class="button button-small">
                                Edit
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=crs-dashboard&crs_print=1&post_id=' . $post_id), 'crs_print_client_' . $post_id, 'crs_print_nonce')); ?>"
                                class="button button-small" target="_blank" title="Print Client Details">
                                <span class="dashicons dashicons-printer" style="margin-top: 3px;"></span> Print
                            </a>
                        </td>
                    </tr>

                    <?php
                endwhile;
                wp_reset_postdata();
            else:
                ?>
                <tr>
                    <td colspan="7">No clients found.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>


    <?php
}

/*
|--------------------------------------------------------------------------
| Handle CSV Export Proper Way
|--------------------------------------------------------------------------
*/

function crs_handle_csv_export()
{

    // Only run in admin
    if (!is_admin()) {
        return;
    }

    // Only on our dashboard page
    if (!isset($_GET['page']) || $_GET['page'] !== 'crs-dashboard') {
        return;
    }

    // Only if export requested
    if (!isset($_GET['export_csv']) || $_GET['export_csv'] != '1') {
        return;
    }

    // Security: only admin
    if (!current_user_can('manage_options')) {
        return;
    }

    $tax_query = array('relation' => 'AND');

    if (!empty($_GET['filter_type'])) {
        $tax_query[] = array(
            'taxonomy' => 'customer_type',
            'field' => 'name',
            'terms' => sanitize_text_field($_GET['filter_type']),
        );
    }

    if (!empty($_GET['filter_year'])) {
        $tax_query[] = array(
            'taxonomy' => 'filing_year',
            'field' => 'name',
            'terms' => sanitize_text_field($_GET['filter_year']),
        );
    }

    $args = array(
        'post_type' => 'client_registration',
        'posts_per_page' => -1,
    );

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    $clients = new WP_Query($args);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="clients-export.csv"');

    $output = fopen('php://output', 'w');

    /*
    |--------------------------------------------------------------------------
    | CSV HEADER ROW
    |--------------------------------------------------------------------------
    */

    $headers = array(
        'Full Name',
        'SSN',
        'DOB',
        'Occupation',
        'Phone',
        'Email',
        'Address',
        'Bank Account',
        'Bank Routing',
        'Filing Status',
        'Customer Type',

        'Spouse Name',
        'Spouse SSN',
        'Spouse DOB',
        'Spouse Email',
        'Spouse Occupation',

        'Self Employed',
        'Overtime',
        'College Tuition',
        'Student Loans',
        'Own Home',
        'New Vehicle',
        'Social Security',
        'Retirement Withdrawal',
        'Sold / Exchange',
        'Unemployment / Leave',
        'Insurance Provider',

        'Document URL',
        'Document URLs',
    );

    // Add Dependent Columns (Always 6)
    for ($i = 1; $i <= 6; $i++) {
        $headers[] = "Dep{$i} Name";
        $headers[] = "Dep{$i} SSN";
        $headers[] = "Dep{$i} DOB";
        $headers[] = "Dep{$i} Relationship";
        $headers[] = "Dep{$i} Lived > 6 months";
        $headers[] = "Dep{$i} Childcare Paid";
    }

    fputcsv($output, $headers);

    /*
    |--------------------------------------------------------------------------
    | CSV ROWS
    |--------------------------------------------------------------------------
    */

    if ($clients->have_posts()) {

        while ($clients->have_posts()) {
            $clients->the_post();
            $post_id = get_the_ID();

            // Core
            $ssn = get_post_meta($post_id, 'crs_ssn', true);
            $dob = get_post_meta($post_id, 'crs_dob', true);
            $occ = get_post_meta($post_id, 'crs_occupation', true);
            $phone = get_post_meta($post_id, 'crs_phone', true);
            $email = get_post_meta($post_id, 'crs_email', true);
            $address = get_post_meta($post_id, 'crs_address', true);
            $bankAcc = get_post_meta($post_id, 'crs_bank_account', true);
            $bankRou = get_post_meta($post_id, 'crs_bank_routing', true);
            $filing = get_post_meta($post_id, 'crs_filing_status', true);
            $client_type = get_post_meta($post_id, 'crs_client_type', true);


            // Spouse
            $spouse_name = get_post_meta($post_id, 'crs_spouse_name', true);
            $spouse_ssn = get_post_meta($post_id, 'crs_spouse_ssn', true);
            $spouse_dob = get_post_meta($post_id, 'crs_spouse_dob', true);
            $spouse_email = get_post_meta($post_id, 'crs_spouse_email', true);
            $spouse_occ = get_post_meta($post_id, 'crs_spouse_occupation', true);

            // Questionnaire
            $questionnaire = get_post_meta($post_id, 'crs_questionnaire', true);
            if (!is_array($questionnaire)) {
                $questionnaire = array();
            }

            // Dependents
            $dependents = get_post_meta($post_id, 'crs_dependents', true);
            if (!is_array($dependents)) {
                $dependents = array();
            }

            $row = array(
                get_the_title(),
                $ssn,
                $dob,
                $occ,
                $phone,
                $email,
                $address,
                $bankAcc,
                $bankRou,
                $filing,
                $client_type,

                $spouse_name,
                $spouse_ssn,
                $spouse_dob,
                $spouse_email,
                $spouse_occ,

                $questionnaire['selfEmployed'] ?? '',
                $questionnaire['overtime'] ?? '',
                $questionnaire['collegeTuition'] ?? '',
                $questionnaire['studentLoans'] ?? '',
                $questionnaire['ownHome'] ?? '',
                $questionnaire['newVehicle'] ?? '',
                $questionnaire['socialSecurity'] ?? '',
                $questionnaire['retirementWithdraw'] ?? '',
                $questionnaire['sellExchangeType'] ?? '',
                $questionnaire['payType'] ?? '',
                $questionnaire['insuranceProvider'] ?? '',

                get_post_meta($post_id, 'crs_document_url', true),
                implode(', ', (array) get_post_meta($post_id, 'crs_document_urls', true)),
            );

            // Flatten 6 Dependents
            for ($i = 0; $i < 6; $i++) {

                if (isset($dependents[$i])) {
                    $row[] = $dependents[$i]['name'] ?? '';
                    $row[] = $dependents[$i]['ssn'] ?? '';
                    $row[] = $dependents[$i]['dob'] ?? '';
                    $row[] = $dependents[$i]['relationship'] ?? '';
                    $row[] = $dependents[$i]['lived'] ?? '';
                    $row[] = $dependents[$i]['childcare'] ?? '';
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                }
            }

            fputcsv($output, $row);
        }

        wp_reset_postdata();
    }

    fclose($output);
    exit;
}

add_action('admin_init', 'crs_handle_csv_export');

