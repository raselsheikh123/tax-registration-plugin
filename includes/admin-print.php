<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'crs_handle_print_client');

function crs_handle_print_client() {
    if (!isset($_GET['crs_print']) || $_GET['crs_print'] !== '1' || empty($_GET['post_id'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Access Denied');
    }

    $post_id = intval($_GET['post_id']);

    if (!isset($_GET['crs_print_nonce']) || !wp_verify_nonce($_GET['crs_print_nonce'], 'crs_print_client_' . $post_id)) {
        wp_die('Security check failed. Invalid or expired token.');
    }

    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'client_registration') {
        wp_die('Invalid Client.');
    }

    // Core
    $ssn     = get_post_meta($post_id, 'crs_ssn', true);
    $dob     = get_post_meta($post_id, 'crs_dob', true);
    $occ     = get_post_meta($post_id, 'crs_occupation', true);
    $phone   = get_post_meta($post_id, 'crs_phone', true);
    $email   = get_post_meta($post_id, 'crs_email', true);
    $address = get_post_meta($post_id, 'crs_address', true);
    $bankAcc = get_post_meta($post_id, 'crs_bank_account', true);
    $bankRou = get_post_meta($post_id, 'crs_bank_routing', true);
    $referral = get_post_meta($post_id, 'crs_referral', true);
    $filing  = get_post_meta($post_id, 'crs_filing_status', true);
    $client_type = get_post_meta($post_id, 'crs_client_type', true);

    // Spouse
    $spouse_name  = get_post_meta($post_id, 'crs_spouse_name', true);
    $spouse_ssn   = get_post_meta($post_id, 'crs_spouse_ssn', true);
    $spouse_dob   = get_post_meta($post_id, 'crs_spouse_dob', true);
    $spouse_email = get_post_meta($post_id, 'crs_spouse_email', true);
    $spouse_occ   = get_post_meta($post_id, 'crs_spouse_occupation', true);

    // Questionnaire
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
    if (!is_array($questionnaire)) $questionnaire = array();

    // Dependents
    $dependents = get_post_meta($post_id, 'crs_dependents', true);
    if (!is_array($dependents)) $dependents = array();

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Print Client - <?php echo esc_html($post->post_title); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
                background: #f4f6f8;
            }
            .print-container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 40px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                border-bottom: 2px solid #2271b1;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .header h1 {
                margin: 0;
                color: #2271b1;
                font-size: 28px;
            }
            .header p {
                margin: 5px 0 0;
                color: #555;
                font-size: 16px;
            }
            h2 {
                color: #2271b1;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
                margin-top: 30px;
                font-size: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                page-break-inside: avoid;
            }
            th, td {
                padding: 10px 15px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            th {
                width: 40%;
                background: #f9f9f9;
                color: #555;
                font-weight: bold;
            }
            td {
                width: 60%;
            }
            .dependents-wrapper {
                margin-bottom: 20px;
            }
            .dependent-card {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                background: #fafafa;
                page-break-inside: avoid;
            }
            .dependent-card h4 {
                margin-top: 0;
                margin-bottom: 10px;
                color: #444;
            }
            @media print {
                body {
                    background: #fff;
                    padding: 0;
                }
                .print-container {
                    box-shadow: none;
                    max-width: 100%;
                    padding: 0;
                    margin: 0;
                }
                .no-print {
                    display: none !important;
                }
            }
            .print-button {
                display: block;
                width: 150px;
                margin: 0 auto 20px;
                text-align: center;
                background: #2271b1;
                color: white;
                text-decoration: none;
                padding: 10px 15px;
                border-radius: 4px;
                font-weight: bold;
                cursor: pointer;
                border: none;
            }
            .print-button:hover {
                background: #135e96;
            }
        </style>
    </head>
    <body onload="window.print()">

        <button class="print-button no-print" onclick="window.print()">Print Document</button>

        <div class="print-container">
            <div class="header">
                <h1>Client Information Summary</h1>
                <p><strong>Client Name:</strong> <?php echo esc_html($post->post_title); ?></p>
                <p><strong>Client Type:</strong> <?php echo esc_html($client_type); ?></p>
            </div>

            <h2>Personal Information</h2>
            <table>
                <tr><th>SSN</th><td><?php echo esc_html($ssn); ?></td></tr>
                <tr><th>DOB</th><td><?php echo esc_html($dob); ?></td></tr>
                <tr><th>Occupation</th><td><?php echo esc_html($occ); ?></td></tr>
                <tr><th>Phone</th><td><?php echo esc_html($phone); ?></td></tr>
                <tr><th>Email</th><td><?php echo esc_html($email); ?></td></tr>
                <tr><th>Address</th><td><?php echo nl2br(esc_html($address)); ?></td></tr>
                <tr><th>Bank Account</th><td><?php echo esc_html($bankAcc); ?></td></tr>
                <tr><th>Bank Routing</th><td><?php echo esc_html($bankRou); ?></td></tr>
                <?php if (!empty($referral)) : ?>
                    <tr><th>Referred By</th><td><?php echo esc_html($referral); ?></td></tr>
                <?php endif; ?>
                <tr><th>Filing Status</th><td><?php echo esc_html($filing); ?></td></tr>
            </table>

            <h2>Spouse Information</h2>
            <table>
                <tr><th>Name</th><td><?php echo esc_html($spouse_name); ?></td></tr>
                <tr><th>SSN</th><td><?php echo esc_html($spouse_ssn); ?></td></tr>
                <tr><th>DOB</th><td><?php echo esc_html($spouse_dob); ?></td></tr>
                <tr><th>Email</th><td><?php echo esc_html($spouse_email); ?></td></tr>
                <tr><th>Occupation</th><td><?php echo esc_html($spouse_occ); ?></td></tr>
            </table>

            <h2>Questionnaire</h2>
            <table>
                <?php foreach ($questions as $key => $label): ?>
                    <tr>
                        <th><?php echo esc_html($label); ?></th>
                        <td><?php echo esc_html($questionnaire[$key] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2>Dependents</h2>
            <?php if (!empty($dependents)): ?>
                <div class="dependents-wrapper">
                    <?php foreach ($dependents as $index => $dep): ?>
                        <div class="dependent-card">
                            <h4>Dependent <?php echo $index + 1; ?></h4>
                            <table>
                                <tr><th>Name</th><td><?php echo esc_html($dep['name'] ?? ''); ?></td></tr>
                                <tr><th>SSN</th><td><?php echo esc_html($dep['ssn'] ?? ''); ?></td></tr>
                                <tr><th>Date of Birth</th><td><?php echo esc_html($dep['dob'] ?? ''); ?></td></tr>
                                <tr><th>Relationship</th><td><?php echo esc_html($dep['relationship'] ?? ''); ?></td></tr>
                                <?php if (!empty($dep['notes'])) : ?>
                                    <tr><th>Notes / Updates</th><td><?php echo esc_html($dep['notes'] ?? ''); ?></td></tr>
                                <?php endif; ?>
                                <tr><th>Lived > 6 months</th><td><?php echo esc_html($dep['lived'] ?? ''); ?></td></tr>
                                <tr><th>Childcare Paid</th><td><?php echo esc_html($dep['childcare'] ?? ''); ?></td></tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No dependents added.</p>
            <?php endif; ?>

        </div>
    </body>
    </html>
    <?php
    exit;
}
