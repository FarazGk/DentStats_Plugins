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
    // Enqueue the stylesheet
    wp_enqueue_style(
        'us-dental-schools-style',
        US_DENTAL_SCHOOLS_URL . 'includes/style.css'
    );

    // Enqueue the script
    wp_enqueue_script(
        'us-dental-schools-script',
        US_DENTAL_SCHOOLS_URL . 'includes/script.js',
        array('jquery'),
        null,
        true
    );

    // Localize the script with new data
    wp_localize_script(
        'us-dental-schools-script',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('us_dental_schools_nonce') // Updated nonce action name
        )
    );
}
add_action('wp_enqueue_scripts', 'us_dental_schools_enqueue_scripts');

// Handle AJAX request for location data
function fetch_location_data() {
    // Verify nonce for security
    check_ajax_referer('us_dental_schools_nonce', 'nonce');

    global $wpdb;

    // Get and sanitize the location ID
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;

    if (!$location_id) {
        wp_send_json_error('Invalid location ID.');
    }

    // Log all of $_POST to check what is being passed
    error_log('$_POST contents: ' . print_r($_POST, true));

    // Get the requested fields from the caller and ensure it's an array
    $requested_fields = isset($_POST['fields']) ? $_POST['fields'] : array();

    // Log the requested fields for debugging
    error_log('Requested fields: ' . print_r($requested_fields, true));

    // Define allowed fields to prevent SQL injection
    $allowed_fields = array(
        'id',
        'name',
        'short_name',
        'city',
        'state',
        'website',
        'email',
        'phone_number',
        'deadline',
        'letters_of_evaluation',
        'letters_of_evaluation_count',
        'supplemental_app',
        'shadowing',
        'shadow_hours_minimum',
        'application_fee',
        'community_college_credits_accepted',
        'resident_tuition',
        'non-resident_tuition',
        'additional_fees',
        'aa',
        'dat',
        'pat',
        'ts',
        'min_gpa',
        'min_gpa(science)',
        'avg_gpa',
        'avg_gpa(science)',
        'accepted_percentage',
        'class_size',
        'total_enrollment',
        'grading_system',
        'student_ranking',
        'dual_admission',
        'other_degrees_offered',
        'housing_offered',
        'students_per_chair',
        'state_resident_required',
        'non_resident_spots',
        'prerequisite_courses_per_semester_hours'
    );

    // Sanitize and validate the requested fields
    $requested_fields = array_map('sanitize_text_field', $requested_fields);
    $fields_to_select = array_intersect($allowed_fields, $requested_fields);

    // Log the fields to select for debugging
    error_log('Fields to select: ' . print_r($fields_to_select, true));

    if (empty($fields_to_select)) {
        // Default fields if none are specified or invalid fields are requested
        $fields_to_select = array('id', 'name', 'city', 'state', 'website', 'email', 'phone_number');
    }

    // Build the SELECT clause
    $fields_sql = implode(', ', array_map(function($field) {
        return "`$field`";
    }, $fields_to_select));

    // Log the SQL query for debugging
    error_log('SQL Query: ' . $fields_sql);

    // Explicitly set the table name
    $table_name = $wpdb->prefix . 'us_dental_schools_data'; // Use $wpdb->prefix for table name

    // Prepare and execute the query
    $query = $wpdb->prepare("SELECT $fields_sql FROM $table_name WHERE id = %d", $location_id);
    $data = $wpdb->get_row($query, ARRAY_A);

    // Log the data fetched for debugging
    error_log('Fetched data: ' . print_r($data, true));

    if ($data) {
        // Sanitize output data
        array_walk_recursive($data, function (&$value) {
            $value = sanitize_text_field($value);
        });
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No data found for location ID ' . $location_id);
    }

    wp_die();
}

add_action('wp_ajax_fetch_location_data', 'fetch_location_data');
add_action('wp_ajax_nopriv_fetch_location_data', 'fetch_location_data');

