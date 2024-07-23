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
    $location_id = intval($_POST['location_id']);
    // Here you would fetch the location data from your database
    $data = array(
        'name' => 'Example School',
        'city' => 'Example City',
        'state' => 'Example State',
        'website' => 'http://example.com',
        'email' => 'info@example.com',
        'phone_number' => '123-456-7890',
        'scores' => 'Example Scores',
        'gpa_min' => '3.0'
    );
    echo json_encode($data);
    wp_die();
}
add_action('wp_ajax_fetch_location_data', 'fetch_location_data');
add_action('wp_ajax_nopriv_fetch_location_data', 'fetch_location_data');
