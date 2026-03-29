<?php


if (!defined('ABSPATH')) {
    exit;
}

/*
|------------------------------------------------------------------
| Register Client Info Metabox
|------------------------------------------------------------------
*/

function crs_register_client_metabox()
{

    add_meta_box(
        'crs_client_details',
        'Client Information',
        'crs_render_client_metabox',
        'client_registration',
        'normal',
        'high'
    );

}

add_action('add_meta_boxes', 'crs_register_client_metabox');

/*
|------------------------------------------------------------------
| Render Metabox Content
|------------------------------------------------------------------
*/

function crs_render_client_metabox( $post ) {

    $post_id = $post->ID;

    // Nonce for security
    wp_nonce_field( 'crs_save_meta', 'crs_meta_nonce' );

    // Core
    $ssn     = get_post_meta($post_id, 'crs_ssn', true);
    $dob     = get_post_meta($post_id, 'crs_dob', true);
    $occ     = get_post_meta($post_id, 'crs_occupation', true);
    $phone   = get_post_meta($post_id, 'crs_phone', true);
    $email   = get_post_meta($post_id, 'crs_email', true);
    $address = get_post_meta($post_id, 'crs_address', true);
    $bankAcc = get_post_meta($post_id, 'crs_bank_account', true);
    $bankRou = get_post_meta($post_id, 'crs_bank_routing', true);
    $filing  = get_post_meta($post_id, 'crs_filing_status', true);
    $client_type = get_post_meta($post_id, 'crs_client_type', true);

    // Spouse
    $spouse_name  = get_post_meta($post_id, 'crs_spouse_name', true);
    $spouse_ssn   = get_post_meta($post_id, 'crs_spouse_ssn', true);
    $spouse_dob   = get_post_meta($post_id, 'crs_spouse_dob', true);
    $spouse_email = get_post_meta($post_id, 'crs_spouse_email', true);
    $spouse_occ   = get_post_meta($post_id, 'crs_spouse_occupation', true);
    ?>

    <style>
        .crs-input { width:100%; }
        .crs-table { width:100%; border-collapse:collapse; }
        .crs-table th, .crs-table td { padding:8px; border:1px solid #ddd; }
        .crs-table th { width:30%; background:#f7f7f7; text-align:left; }
    </style>

    <h2>Client Settings</h2>
    <table class="crs-table">
        <tr>
            <th>Client Type</th>
            <td>
                <select name="crs_client_type" class="crs-input">
                    <option value="New Client" <?php selected($client_type,'New Client'); ?>>New Client</option>
                    <option value="Existing Client" <?php selected($client_type,'Existing Client'); ?>>Existing Client</option>
                </select>
            </td>
        </tr>
    </table>

    <h2>Personal Information</h2>
    <table class="crs-table">
        <tr><th>SSN</th><td><input type="text" name="crs_ssn" value="<?php echo esc_attr($ssn); ?>" class="crs-input"></td></tr>
        <tr><th>DOB</th><td><input type="text" name="crs_dob" value="<?php echo esc_attr($dob); ?>" class="crs-input"></td></tr>
        <tr><th>Occupation</th><td><input type="text" name="crs_occupation" value="<?php echo esc_attr($occ); ?>" class="crs-input"></td></tr>
        <tr><th>Phone</th><td><input type="text" name="crs_phone" value="<?php echo esc_attr($phone); ?>" class="crs-input"></td></tr>
        <tr><th>Email</th><td><input type="email" name="crs_email" value="<?php echo esc_attr($email); ?>" class="crs-input"></td></tr>
        <tr><th>Address</th><td><textarea name="crs_address" class="crs-input"><?php echo esc_textarea($address); ?></textarea></td></tr>
        <tr><th>Bank Account</th><td><input type="text" name="crs_bank_account" value="<?php echo esc_attr($bankAcc); ?>" class="crs-input"></td></tr>
        <tr><th>Bank Routing</th><td><input type="text" name="crs_bank_routing" value="<?php echo esc_attr($bankRou); ?>" class="crs-input"></td></tr>
        <tr><th>Filing Status</th><td><input type="text" name="crs_filing_status" value="<?php echo esc_attr($filing); ?>" class="crs-input"></td></tr>
    </table>

    <h2>Spouse Information</h2>
    <table class="crs-table">
        <tr><th>Name</th><td><input type="text" name="crs_spouse_name" value="<?php echo esc_attr($spouse_name); ?>" class="crs-input"></td></tr>
        <tr><th>SSN</th><td><input type="text" name="crs_spouse_ssn" value="<?php echo esc_attr($spouse_ssn); ?>" class="crs-input"></td></tr>
        <tr><th>DOB</th><td><input type="text" name="crs_spouse_dob" value="<?php echo esc_attr($spouse_dob); ?>" class="crs-input"></td></tr>
        <tr><th>Email</th><td><input type="email" name="crs_spouse_email" value="<?php echo esc_attr($spouse_email); ?>" class="crs-input"></td></tr>
        <tr><th>Occupation</th><td><input type="text" name="crs_spouse_occupation" value="<?php echo esc_attr($spouse_occ); ?>" class="crs-input"></td></tr>
    </table>

    <h2>Questionnaire</h2>
    <table class="crs-table">
        <?php
        $questions = array(
                'selfEmployed'       => 'Self Employed / 1099',
                'overtime'           => 'Worked Overtime',
                'collegeTuition'     => 'Paid College Tuition',
                'studentLoans'       => 'Student Loan Payments',
                'ownHome'            => 'Own Home',
                'newVehicle'         => 'Purchased New Vehicle',
                'socialSecurity'     => 'Receiving Social Security',
                'retirementWithdraw' => 'Retirement Withdrawal',
                'sellExchangeType'   => 'Sold or Exchanged',
                'payType'            => 'Unemployment / Leave',
                'insuranceProvider'  => 'Health Insurance Provider',
        );

        $questionnaire = get_post_meta($post_id, 'crs_questionnaire', true);

        if ( ! is_array($questionnaire) ) {
            $questionnaire = array();
        }

        foreach ( $questions as $key => $label ) :

            $value = $questionnaire[$key] ?? '';
            ?>
            <tr>
                <th><?php echo esc_html($label); ?></th>
                <td>
                    <input type="text" name="crs_questionnaire[<?php echo esc_attr($key); ?>]"
                           value="<?php echo esc_attr($value); ?>"
                           class="crs-input">
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

    <h2>Dependents</h2>

    <?php
    $dependents = get_post_meta($post_id, 'crs_dependents', true);

    if ( ! is_array($dependents) ) {
        $dependents = array();
    }

    if ( empty($dependents) ) :
        echo '<p>No dependents added.</p>';
    else :
        foreach ( $dependents as $index => $dep ) :
            ?>

            <h4>Dependent <?php echo $index + 1; ?></h4>

            <table class="crs-table">
                <tr>
                    <th>Name</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][name]"
                               value="<?php echo esc_attr($dep['name'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>

                <tr>
                    <th>SSN</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][ssn]"
                               value="<?php echo esc_attr($dep['ssn'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>

                <tr>
                    <th>Date of Birth</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][dob]"
                               value="<?php echo esc_attr($dep['dob'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>

                <tr>
                    <th>Relationship</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][relationship]"
                               value="<?php echo esc_attr($dep['relationship'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>

                <tr>
                    <th>Lived > 6 months</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][lived]"
                               value="<?php echo esc_attr($dep['lived'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>

                <tr>
                    <th>Childcare Paid</th>
                    <td>
                        <input type="text"
                               name="crs_dependents[<?php echo $index; ?>][childcare]"
                               value="<?php echo esc_attr($dep['childcare'] ?? ''); ?>"
                               class="crs-input">
                    </td>
                </tr>
            </table>

        <?php
        endforeach;
    endif;
    ?>

    <h2>Document</h2>
    <table class="crs-table">
        <?php
        $document_urls = get_post_meta($post_id, 'crs_document_urls', true);
        if (is_array($document_urls) && !empty($document_urls)) {
            echo '<tr><th>Document URLs</th><td><ul>';
            foreach ($document_urls as $key => $url) {
                $download_url = add_query_arg(array('crs_download' => '1', 'post_id' => $post_id, 'index' => $key), home_url('/'));
                echo '<li><a href="' . esc_url($download_url) . '" target="_blank">' . esc_html(basename($url)) . '</a></li>';
            }
            echo '</ul></td></tr>';
        }

        ?>
    </table>

    <div style="margin-top:20px; padding-top:10px; border-top:1px solid #ddd;">
        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=crs-dashboard&crs_print=1&post_id=' . $post_id), 'crs_print_client_' . $post_id, 'crs_print_nonce')); ?>" class="button button-primary button-large" target="_blank">
            <span class="dashicons dashicons-printer" style="margin-top:4px;"></span> Print Client Data
        </a>
    </div>

    <?php
}

function crs_save_client_meta( $post_id ) {

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! isset($_POST['crs_meta_nonce']) || ! wp_verify_nonce($_POST['crs_meta_nonce'],'crs_save_meta') ) return;
    if ( get_post_type($post_id) !== 'client_registration' ) return;
    if ( ! current_user_can('edit_post',$post_id) ) return;

    $fields = array(
            'crs_client_type',
            'crs_ssn',
            'crs_dob',
            'crs_occupation',
            'crs_phone',
            'crs_email',
            'crs_address',
            'crs_bank_account',
            'crs_bank_routing',
            'crs_filing_status',
            'crs_spouse_name',
            'crs_spouse_ssn',
            'crs_spouse_dob',
            'crs_spouse_email',
            'crs_spouse_occupation',
    );

    foreach ( $fields as $field ) {

        if ( array_key_exists($field, $_POST) ) {

            if ( $field === 'crs_address' ) {
                update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
            } else {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }

        }
    }

    // Save Questionnaire safely
    if ( isset($_POST['crs_questionnaire']) && is_array($_POST['crs_questionnaire']) ) {

        $clean = array();

        foreach ( $_POST['crs_questionnaire'] as $key => $value ) {
            $clean[$key] = sanitize_text_field($value);
        }

        delete_post_meta($post_id, 'crs_questionnaire');
        update_post_meta($post_id, 'crs_questionnaire', $clean);
    }

    // Save Dependents safely
    if ( isset($_POST['crs_dependents']) && is_array($_POST['crs_dependents']) ) {

        $clean_dependents = array();

        foreach ( $_POST['crs_dependents'] as $index => $dep ) {

            $clean_dependents[$index] = array(
                    'name'         => sanitize_text_field($dep['name'] ?? ''),
                    'ssn'          => sanitize_text_field($dep['ssn'] ?? ''),
                    'dob'          => sanitize_text_field($dep['dob'] ?? ''),
                    'relationship' => sanitize_text_field($dep['relationship'] ?? ''),
                    'lived'        => sanitize_text_field($dep['lived'] ?? ''),
                    'childcare'    => sanitize_text_field($dep['childcare'] ?? ''),
            );
        }

        delete_post_meta($post_id, 'crs_dependents');
        update_post_meta($post_id, 'crs_dependents', $clean_dependents);
    }

    // Save Document fields
    $document_fields = array(
            'crs_document_url',
    );

    foreach ( $document_fields as $doc_field ) {

        if ( array_key_exists($doc_field, $_POST) ) {
            update_post_meta($post_id, $doc_field, sanitize_text_field($_POST[$doc_field]));
        }
    }
}
add_action('save_post','crs_save_client_meta');