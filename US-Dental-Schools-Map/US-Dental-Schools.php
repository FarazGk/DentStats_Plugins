<?php
/*
Plugin Name: US Dental Schools
Description: A plugin to display a map of U.S. Dental Schools.
Version: 1.0
Author: Premium Vortex
Author URI: https://premiumvortex.com/
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin paths
define('US_DENTAL_SCHOOLS_PATH', plugin_dir_path(__FILE__));
define('US_DENTAL_SCHOOLS_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once US_DENTAL_SCHOOLS_PATH . 'includes/shortcodes.php';

// Enqueue styles and scripts
function us_dental_schools_enqueue_scripts() {
    wp_enqueue_style('us-dental-schools-style', US_DENTAL_SCHOOLS_URL . 'includes/style.css');
    wp_enqueue_script('us-dental-schools-script', US_DENTAL_SCHOOLS_URL . 'includes/script.js', array('jquery'), null, true);
    wp_localize_script('us-dental-schools-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'us_dental_schools_enqueue_scripts');

// Handle AJAX request
function fetch_location_data() {
    global $wpdb;
    $location_id = intval($_POST['location_id']);
    
    // Explicitly set the table name
    $table_name = 'j1rn_us_dental_schools_data';
    $data = $wpdb->get_row($wpdb->prepare("SELECT name, city, state, website, `phone_number` AS phone_number, email FROM $table_name WHERE id = %d", $location_id), ARRAY_A);
    
    if ($data) {
        echo json_encode($data);
    } else {
        // Additional debugging information
        $error_message = array(
            'error' => 'Location not found',
            'location_id' => $location_id,
            'query' => $wpdb->last_query,
            'db_error' => $wpdb->last_error
        );
        echo json_encode($error_message);
    }
    wp_die();
}
add_action('wp_ajax_fetch_location_data', 'fetch_location_data');
add_action('wp_ajax_nopriv_fetch_location_data', 'fetch_location_data');
