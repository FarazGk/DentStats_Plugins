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
            shadow_hours_minimum float DEFAULT NULL,
            gpa float DEFAULT NULL,
            science_gpa float DEFAULT NULL,
            AA float DEFAULT NULL,
            DAT float DEFAULT NULL,
            PAT float DEFAULT NULL,
            TS float DEFAULT NULL,
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

    // Check and create/update dental_school_averages table
    $table_name = $wpdb->prefix . 'dental_school_averages';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            shadow_hours_minimum float DEFAULT NULL,
            gpa float DEFAULT NULL,
            science_gpa float DEFAULT NULL,
            DAT float DEFAULT NULL,
            AA float DEFAULT NULL,
            PAT float DEFAULT NULL,
            TS float DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Insert initial averages
        ds_calculate_and_store_averages();
    } else {
        // Update existing averages
        ds_calculate_and_store_averages(true);
    }
}

function ds_calculate_and_store_averages() {
    global $wpdb;

    // Get data from the custom table
    $schools_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}us_dental_schools_data");

    // Initialize averages and counters for all categories
    $averages = [
        'shadow_hours_minimum' => 0,
        'gpa' => 0,
        'science_gpa' => 0,
        'AA' => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS' => 0
    ];
    $counts = [
        'shadow_hours_minimum' => 0,
        'gpa' => 0,
        'science_gpa' => 0,
        'AA' => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS' => 0
    ];

    // Calculate sums and counts for each category
    foreach ($schools_data as $school) {
        // Shadow Hours Minimum
        if (!is_null($school->shadow_hours_minimum) && $school->shadow_hours_minimum !== '') {
            $averages['shadow_hours_minimum'] += (float)$school->shadow_hours_minimum;
            $counts['shadow_hours_minimum']++;
        }
        // GPA
        if (!is_null($school->min_gpa) && $school->min_gpa !== '') {
            $averages['gpa'] += (float)$school->min_gpa;
            $counts['gpa']++;
        }
        // Science GPA
        if (!is_null($school->avg_gpascience) && $school->avg_gpascience !== '') {
            $averages['science_gpa'] += (float)$school->avg_gpascience;
            $counts['science_gpa']++;
        }
        // AA
        if (!is_null($school->aa) && $school->aa !== '') {
            $averages['AA'] += (float)$school->aa;
            $counts['AA']++;
        }
        // DAT
        if (!is_null($school->dat) && $school->dat !== '') {
            $averages['DAT'] += (float)$school->dat;
            $counts['DAT']++;
        }
        // PAT
        if (!is_null($school->pat) && $school->pat !== '') {
            $averages['PAT'] += (float)$school->pat;
            $counts['PAT']++;
        }
        // TS
        if (!is_null($school->ts) && $school->ts !== '') {
            $averages['TS'] += (float)$school->ts;
            $counts['TS']++;
        }
    }

    // Avoid division by zero and calculate final averages
    foreach ($averages as $key => &$value) {
        if ($counts[$key] > 0) {
            $value = $value / $counts[$key];
        } else {
            $value = null; // Set to null if no valid data is available
        }
    }

    // Prepare data for database insertion/update
    $data = [
        'shadow_hours_minimum' => $averages['shadow_hours_minimum'],
        'gpa' => $averages['gpa'],
        'science_gpa' => $averages['science_gpa'],
        'AA' => $averages['AA'],
        'DAT' => $averages['DAT'],
        'PAT' => $averages['PAT'],
        'TS' => $averages['TS']
    ];

    $table_name = $wpdb->prefix . 'dental_school_averages';

    // Check if a record exists
    $existing_record = $wpdb->get_var("SELECT id FROM $table_name LIMIT 1");

    if ($existing_record) {
        // Update existing record
        $wpdb->update($table_name, $data, ['id' => $existing_record]);
    } else {
        // Insert new record
        $wpdb->insert($table_name, $data);
    }
}




function ds_interview_deactivate() {
    global $wpdb;

    // List of tables to drop
    $tables_to_drop = [
        // $wpdb->prefix . 'user_interview_chance_log',
        // $wpdb->prefix . 'user_interview_evaluation_file_access',
        $wpdb->prefix . 'dental_school_averages'
    ];

    // Drop each table
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}

?>
