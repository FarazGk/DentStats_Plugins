<?php
/*
Plugin Name: DAT Score Converter
Description: A plugin to convert and lookup DAT scores from uploaded database tables.
Version: 1.0
Author: Premium Vortex
Author URI: https://premiumvortex.com/
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin paths
define('DAT_SCORE_CONVERTER_PATH', plugin_dir_path(__FILE__));
define('DAT_SCORE_CONVERTER_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once DAT_SCORE_CONVERTER_PATH . 'includes/admin-interface.php';
require_once DAT_SCORE_CONVERTER_PATH . 'includes/lookup-functions.php';
require_once DAT_SCORE_CONVERTER_PATH . 'includes/shortcodes.php';

// Enqueue styles and scripts
function dat_score_converter_enqueue_scripts() {
    // Enqueue the stylesheet
    wp_enqueue_style(
        'dat-score-converter-style',
        DAT_SCORE_CONVERTER_URL . 'assets/style.css'
    );

    // Enqueue the script
    wp_enqueue_script(
        'dat-score-converter-script',
        DAT_SCORE_CONVERTER_URL . 'assets/script.js',
        array('jquery'),
        null,
        true
    );

    // Localize the script with AJAX data
    wp_localize_script(
        'dat-score-converter-script',
        'dat_ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dat_score_converter_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'dat_score_converter_enqueue_scripts');

// Handle AJAX request for score lookup
function dat_score_lookup() {
    // Verify nonce for security
    check_ajax_referer('dat_score_converter_nonce', 'nonce');

    global $wpdb;

    // Get and sanitize the input values
    $table_name = isset($_POST['table_name']) ? sanitize_text_field($_POST['table_name']) : '';
    $lookup_value = isset($_POST['lookup_value']) ? sanitize_text_field($_POST['lookup_value']) : '';
    $lookup_column = isset($_POST['lookup_column']) ? sanitize_text_field($_POST['lookup_column']) : '';

    if (empty($table_name) || empty($lookup_value) || empty($lookup_column)) {
        wp_send_json_error('Missing required parameters.');
    }

    // Validate table name (prevent SQL injection) - allow hyphens and dots
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
        wp_send_json_error('Invalid table name: ' . $table_name);
    }

    // Validate column name (prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $lookup_column)) {
        wp_send_json_error('Invalid column name: ' . $lookup_column);
    }

    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if (!$table_exists) {
        wp_send_json_error('Table does not exist.');
    }

    // Get table columns for validation
    $columns = $wpdb->get_col("SHOW COLUMNS FROM `" . esc_sql($table_name) . "`");
    if (!in_array($lookup_column, $columns)) {
        wp_send_json_error('Column does not exist in table.');
    }

    // Perform the lookup
    $query = $wpdb->prepare("SELECT * FROM `" . esc_sql($table_name) . "` WHERE `" . esc_sql($lookup_column) . "` = %s", $lookup_value);
    $data = $wpdb->get_row($query, ARRAY_A);

    if ($data) {
        // Sanitize output data
        array_walk_recursive($data, function (&$value) {
            $value = sanitize_text_field($value);
        });
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No data found for the specified lookup value.');
    }

    wp_die();
}

add_action('wp_ajax_dat_score_lookup', 'dat_score_lookup');
add_action('wp_ajax_nopriv_dat_score_lookup', 'dat_score_lookup');

// Handle AJAX request for table columns
function dat_get_table_columns() {
    // Verify nonce for security
    check_ajax_referer('dat_score_converter_nonce', 'nonce');

    global $wpdb;

    $table_name = isset($_POST['table_name']) ? sanitize_text_field($_POST['table_name']) : '';
    
    // Debug logging
    error_log('DAT Score Converter - Table name received: ' . $table_name);

    if (empty($table_name)) {
        wp_send_json_error('Table name is required.');
    }

    // Validate table name (prevent SQL injection) - allow hyphens and dots
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
        wp_send_json_error('Invalid table name: ' . $table_name);
    }

    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    error_log('DAT Score Converter - Table exists check: ' . ($table_exists ? 'YES' : 'NO'));
    if (!$table_exists) {
        wp_send_json_error('Table does not exist: ' . $table_name);
    }

    // Get table columns
    $columns = $wpdb->get_col("SHOW COLUMNS FROM `" . esc_sql($table_name) . "`");
    error_log('DAT Score Converter - Columns found: ' . print_r($columns, true));
    
    if ($columns) {
        wp_send_json_success($columns);
    } else {
        wp_send_json_error('Could not retrieve table columns.');
    }

    wp_die();
}

add_action('wp_ajax_dat_get_table_columns', 'dat_get_table_columns');
add_action('wp_ajax_nopriv_dat_get_table_columns', 'dat_get_table_columns');

// Handle AJAX request for saving configuration
function dat_save_configuration() {
    // Verify nonce for security
    check_ajax_referer('dat_score_converter_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    $table_name = isset($_POST['table_name']) ? sanitize_text_field($_POST['table_name']) : '';
    $lookup_column = isset($_POST['lookup_column']) ? sanitize_text_field($_POST['lookup_column']) : '';

    if (empty($table_name) || empty($lookup_column)) {
        wp_send_json_error('Table name and lookup column are required.');
    }

    // Validate table name (prevent SQL injection) - allow hyphens and dots
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
        wp_send_json_error('Invalid table name: ' . $table_name);
    }

    // Validate column name (prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $lookup_column)) {
        wp_send_json_error('Invalid column name: ' . $lookup_column);
    }

    // Save the configuration
    update_option('dat_score_converter_table', $table_name);
    update_option('dat_score_converter_lookup_column', $lookup_column);

    wp_send_json_success('Configuration saved successfully.');
}

add_action('wp_ajax_dat_save_configuration', 'dat_save_configuration');

// Handle AJAX request for score conversion
function dat_score_convert() {
    // Verify nonce for security
    check_ajax_referer('dat_score_converter_nonce', 'nonce');

    global $wpdb;

    // Get and sanitize the input values
    $table_name = isset($_POST['table_name']) ? sanitize_text_field($_POST['table_name']) : '';
    $lookup_column = isset($_POST['lookup_column']) ? sanitize_text_field($_POST['lookup_column']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $input_value = isset($_POST['input_value']) ? sanitize_text_field($_POST['input_value']) : '';
    $direction = isset($_POST['direction']) ? sanitize_text_field($_POST['direction']) : '';

    if (empty($table_name) || empty($lookup_column) || empty($category) || empty($input_value) || empty($direction)) {
        wp_send_json_error('Missing required parameters.');
    }

    // Validate table name (prevent SQL injection) - allow hyphens and dots
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
        wp_send_json_error('Invalid table name: ' . $table_name);
    }

    // Validate column names (prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $lookup_column)) {
        wp_send_json_error('Invalid lookup column name: ' . $lookup_column);
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $category)) {
        wp_send_json_error('Invalid category column name: ' . $category);
    }

    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if (!$table_exists) {
        wp_send_json_error('Table does not exist.');
    }

    // Get table columns for validation
    $columns = $wpdb->get_col("SHOW COLUMNS FROM `" . esc_sql($table_name) . "`");
    
    // Case-insensitive column matching
    $lookup_column_actual = null;
    $category_column_actual = null;
    
    foreach ($columns as $col) {
        if (strtolower($col) === strtolower($lookup_column)) {
            $lookup_column_actual = $col;
        }
        if (strtolower($col) === strtolower($category)) {
            $category_column_actual = $col;
        }
    }

    if (!$lookup_column_actual) {
        wp_send_json_error('Lookup column does not exist in table.');
    }

    if (!$category_column_actual) {
        wp_send_json_error('Category column does not exist in table.');
    }

    // Perform the conversion based on direction
    if ($direction === 'old_to_new') {
        // Lookup old score in lookup_column, return new score from category column
        $query = $wpdb->prepare(
            "SELECT `" . esc_sql($category_column_actual) . "` FROM `" . esc_sql($table_name) . "` WHERE `" . esc_sql($lookup_column_actual) . "` = %s LIMIT 1",
            $input_value
        );
        $result = $wpdb->get_var($query);
    } else if ($direction === 'new_to_old') {
        // Lookup new score in category column, return old score from lookup_column
        // First try exact match
        $query = $wpdb->prepare(
            "SELECT `" . esc_sql($lookup_column_actual) . "` FROM `" . esc_sql($table_name) . "` WHERE `" . esc_sql($category_column_actual) . "` = %s LIMIT 1",
            $input_value
        );
        $result = $wpdb->get_var($query);
        
        // If no exact match, find closest value
        if ($result === null) {
            $input_num = floatval($input_value);
            $query = "SELECT `" . esc_sql($lookup_column_actual) . "`, `" . esc_sql($category_column_actual) . "` FROM `" . esc_sql($table_name) . "` WHERE `" . esc_sql($category_column_actual) . "` IS NOT NULL AND `" . esc_sql($category_column_actual) . "` != ''";
            $all_rows = $wpdb->get_results($query, ARRAY_A);
            
            if ($all_rows) {
                $closest_value = null;
                $min_diff = PHP_INT_MAX;
                
                foreach ($all_rows as $row) {
                    $category_value = floatval($row[$category_column_actual]);
                    if (!is_nan($category_value)) {
                        $diff = abs($category_value - $input_num);
                        if ($diff < $min_diff) {
                            $min_diff = $diff;
                            $closest_value = $row[$lookup_column_actual];
                        }
                    }
                }
                
                $result = $closest_value;
            }
        }
    } else {
        wp_send_json_error('Invalid conversion direction.');
    }

    if ($result !== null) {
        wp_send_json_success(sanitize_text_field($result));
    } else {
        wp_send_json_error('No conversion found for the specified value.');
    }

    wp_die();
}

add_action('wp_ajax_dat_score_convert', 'dat_score_convert');
add_action('wp_ajax_nopriv_dat_score_convert', 'dat_score_convert');

// Handle AJAX request to load all table data for caching
function dat_score_load_all_data() {
    // Verify nonce for security
    check_ajax_referer('dat_score_converter_nonce', 'nonce');

    global $wpdb;

    // Get and sanitize the input values
    $table_name = isset($_POST['table_name']) ? sanitize_text_field($_POST['table_name']) : '';
    $lookup_column = isset($_POST['lookup_column']) ? sanitize_text_field($_POST['lookup_column']) : '';

    if (empty($table_name) || empty($lookup_column)) {
        wp_send_json_error('Missing required parameters.');
    }

    // Validate table name (prevent SQL injection) - allow hyphens and dots
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
        wp_send_json_error('Invalid table name: ' . $table_name);
    }

    // Validate column name (prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $lookup_column)) {
        wp_send_json_error('Invalid lookup column name: ' . $lookup_column);
    }

    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if (!$table_exists) {
        wp_send_json_error('Table does not exist.');
    }

    // Get all data from the table
    $query = "SELECT * FROM `" . esc_sql($table_name) . "`";
    $data = $wpdb->get_results($query, ARRAY_A);

    if ($data) {
        // Sanitize output data
        array_walk_recursive($data, function (&$value) {
            $value = sanitize_text_field($value);
        });
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No data found in table.');
    }

    wp_die();
}

add_action('wp_ajax_dat_score_load_all_data', 'dat_score_load_all_data');
add_action('wp_ajax_nopriv_dat_score_load_all_data', 'dat_score_load_all_data');

// Initialize admin interface
function dat_score_converter_init() {
    if (is_admin()) {
        new DAT_Score_Converter_Admin();
    }
}
add_action('init', 'dat_score_converter_init');
