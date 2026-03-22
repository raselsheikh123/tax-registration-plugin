<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Handle Client Form Submission (AJAX)
|--------------------------------------------------------------------------
*/

function crs_handle_client_submission() {

    // 1️⃣ Verify nonce
    check_ajax_referer( 'crs_nonce', 'nonce' );



    // 2️⃣ Sanitize basic fields
    $full_name = sanitize_text_field( $_POST['fullName'] ?? '' );
    $email     = sanitize_email( $_POST['email'] ?? '' );
    $phone     = sanitize_text_field( $_POST['phone'] ?? '' );

    $client_category = sanitize_text_field( $_POST['client_category'] ?? '' );

    // 3️⃣ Create CPT post
    $post_id = wp_insert_post( array(
        'post_title'  => $full_name,
        'post_type'   => 'client_registration',
        'post_status' => 'publish',
    ) );

    // Check if post was created
    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error();
    }

    // 4️⃣ Save meta fields
    update_post_meta( $post_id, 'crs_email', $email );
    update_post_meta( $post_id, 'crs_phone', $phone );

    update_post_meta( $post_id, 'crs_ssn', sanitize_text_field( $_POST['ssn'] ?? '' ) );
    update_post_meta( $post_id, 'crs_dob', sanitize_text_field( $_POST['dob'] ?? '' ) );
    update_post_meta( $post_id, 'crs_occupation', sanitize_text_field( $_POST['occupation'] ?? '' ) );
    update_post_meta( $post_id, 'crs_address', sanitize_textarea_field( $_POST['address'] ?? '' ) );
    update_post_meta( $post_id, 'crs_bank_account', sanitize_text_field( $_POST['bankAccount'] ?? '' ) );
    update_post_meta( $post_id, 'crs_bank_routing', sanitize_text_field( $_POST['bankRouting'] ?? '' ) );
    update_post_meta( $post_id, 'crs_filing_status', sanitize_text_field( $_POST['filingStatus'] ?? '' ) );
    update_post_meta( $post_id, 'crs_client_type', $client_category );

    // ============================
    // Save Spouse Info (if exists)
    // ============================

    update_post_meta( $post_id, 'crs_spouse_name', sanitize_text_field( $_POST['spouseName'] ?? '' ) );
    update_post_meta( $post_id, 'crs_spouse_ssn', sanitize_text_field( $_POST['spouseSSN'] ?? '' ) );
    update_post_meta( $post_id, 'crs_spouse_dob', sanitize_text_field( $_POST['spouseDOB'] ?? '' ) );
    update_post_meta( $post_id, 'crs_spouse_email', sanitize_email( $_POST['spouseEmail'] ?? '' ) );
    update_post_meta( $post_id, 'crs_spouse_occupation', sanitize_text_field( $_POST['spouseOccupation'] ?? '' ) );

    // ============================
    // Save Questionnaire
    // ============================

    $questionnaire_keys = array(
        'selfEmployed',
        'overtime',
        'collegeTuition',
        'studentLoans',
        'ownHome',
        'newVehicle',
        'socialSecurity',
        'retirementWithdraw',
        'sellExchangeType',
        'payType',
        'insuranceProvider'
    );

    $questionnaire_data = array();

    foreach ( $questionnaire_keys as $key ) {
        if ( isset($_POST[$key]) ) {
            $questionnaire_data[$key] = sanitize_text_field( $_POST[$key] );
        }
    }

    if ( ! empty($questionnaire_data) ) {
        update_post_meta( $post_id, 'crs_questionnaire', $questionnaire_data );
    }

    // ============================
    // Save Dependents
    // ============================

    $dependents = array();

    for ( $i = 1; $i <= 6; $i++ ) {

        if ( ! empty($_POST["depName_$i"]) ) {

            $dependents[] = array(
                'name'         => sanitize_text_field( $_POST["depName_$i"] ?? '' ),
                'ssn'          => sanitize_text_field( $_POST["depSSN_$i"] ?? '' ),
                'dob'          => sanitize_text_field( $_POST["depDOB_$i"] ?? '' ),
                'relationship' => sanitize_text_field( $_POST["depRel_$i"] ?? '' ),
                'lived'        => sanitize_text_field( $_POST["depLive_$i"] ?? '' ),
                'childcare'    => sanitize_text_field( $_POST["depCare_$i"] ?? '' ),
            );

        }
    }

    if ( ! empty($dependents) ) {
        update_post_meta( $post_id, 'crs_dependents', $dependents );
    }

    // ============================
    // Handle Document Upload
    // ============================

    $document_urls = array();

    if ( isset($_FILES['client_document']) && !empty($_FILES['client_document']['name'][0]) ) {
        // Handle multiple file uploads
        $files = $_FILES['client_document'];

        foreach ($files['name'] as $key => $name) {
            if (empty($name)) continue;

            $file = array(
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error'    => $files['error'][$key],
                'size'     => $files['size'][$key],
            );

            // Check file size (5MB)
            if ( $file['size'] > 5 * 1024 * 1024 ) {
                wp_send_json_error( array( 'message' => 'File ' . $name . ' exceeds 5MB limit.' ) );
            }

            // Check file type
            $allowed_types = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png' );
            $file_ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
            if ( ! in_array( $file_ext, $allowed_types ) ) {
                wp_send_json_error( array( 'message' => 'Invalid file type for ' . $name . '. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.' ) );
            }

            // Upload file
            $upload_overrides = array( 'test_form' => false );
            $uploaded_file = wp_handle_upload( $file, $upload_overrides );

            if ( isset( $uploaded_file['error'] ) ) {
                wp_send_json_error( array( 'message' => 'File upload failed for ' . $name . ': ' . $uploaded_file['error'] ) );
            }

            $document_urls[] = $uploaded_file['url'];
        }

        update_post_meta( $post_id, 'crs_document_urls', $document_urls );
    }


    // 5️⃣ Assign taxonomies
    crs_send_to_google($post_id);
    wp_send_json_success();

}

/*
|--------------------------------------------------------------------------
| Register AJAX Hooks
|--------------------------------------------------------------------------
*/

// For logged-in users
add_action( 'wp_ajax_crs_submit_client', 'crs_handle_client_submission' );

// For non-logged-in users
add_action( 'wp_ajax_nopriv_crs_submit_client', 'crs_handle_client_submission' );
