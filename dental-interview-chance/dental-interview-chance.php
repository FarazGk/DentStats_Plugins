<?php
/*
Plugin Name: Dental School Interview Chance Evaluator
Description: Evaluates the chance of getting an interview from US Dental Schools based on user input.
Version: 1.0
Author: Premium Vortex
Author URI: https://premiumvortex.com/
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DS_INTERVIEW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DS_INTERVIEW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/form-handler.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/pdf-generator.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/custom-tcpdf.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/logger.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/user-file-access.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'admin/admin-logs-page.php';
require_once DS_INTERVIEW_PLUGIN_DIR . 'admin/admin-menu.php';

// Enqueue plugin styles
function ds_interview_enqueue_styles() {
    wp_enqueue_style('ds-interview-styles', DS_INTERVIEW_PLUGIN_URL . 'assets/css/styles.css');
}
add_action('wp_enqueue_scripts', 'ds_interview_enqueue_styles');

// Activation and Deactivation hooks
register_activation_hook(__FILE__, 'ds_interview_activate');
register_deactivation_hook(__FILE__, 'ds_interview_deactivate');

function ds_interview_activate() {
    global $wpdb;

    // Create user_interview_chance_log table if it doesn't exist
    $table_name = $wpdb->prefix . 'user_interview_chance_log';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            action varchar(255) NOT NULL,
            details text,
            form_data text,
            timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create user_interview_evaluation_file_access table if it doesn't exist
    $table_name = $wpdb->prefix . 'user_interview_evaluation_file_access';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            file_url varchar(255) NOT NULL,
            timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function ds_interview_deactivate() {
    // Deactivation code here...
}
?>
