<?php
/*
Plugin Name: Token System for Form Access
Description: Adds token management for form access in WooCommerce and Forminator.
Version: 1.0
Author: Premium Vortex
Author URI: https://premiumvortex.com/
*/

// Ensure the file is not accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the admin settings page
include_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';

// Include the token management functions
include_once plugin_dir_path( __FILE__ ) . 'includes/token-functions.php';

// Include the logging functions
include_once plugin_dir_path( __FILE__ ) . 'includes/log-functions.php';

// Include the user token functions
include_once plugin_dir_path( __FILE__ ) . 'includes/user-token-functions.php';

// Function to create custom table for logs
function create_token_system_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'token_system_logs';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            type varchar(20) NOT NULL,
            log_id varchar(60) NOT NULL,
            timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'create_token_system_logs_table');

