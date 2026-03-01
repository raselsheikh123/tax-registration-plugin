<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function crs_register_client_cpt() {

    $args = array(
        'label'               => 'Client Registrations',
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => false, // We will create custom admin menu later
        'capability_type'     => 'post',
        'supports'            => array( 'title' , 'custom-fields'),
        'has_archive'         => false,
        'rewrite'             => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-id',
    );

    register_post_type( 'client_registration', $args );
}

add_action( 'init', 'crs_register_client_cpt' );
