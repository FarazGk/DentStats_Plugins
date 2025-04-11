<?php
/*
Plugin Name: Dental School Interview Chance Evaluator
Description: Evaluates the chance of getting an interview from US Dental Schools based on user input.
Version: 2.0
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
    ds_custom_log("ds_interview_activate() called. Activating plugin.");

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
            AA float DEFAULT NULL,
            DAT float DEFAULT NULL,
            PAT float DEFAULT NULL,
            TS float DEFAULT NULL,
            score_mode varchar(20) NOT NULL,
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
    
    ds_custom_log("Plugin activated.");
}

function ds_calculate_and_store_averages($update_existing = false) {
    global $wpdb;
    ds_custom_log("ds_calculate_and_store_averages() called.");
    
    // Retrieve data from the custom table.
    $schools_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}us_dental_schools_data");
    
    // Initialize accumulators for common fields.
    $common_sums = [
        'shadow_hours_minimum' => 0,
        'gpa'                  => 0,
        'science_gpa'          => 0,
    ];
    $common_counts = [
        'shadow_hours_minimum' => 0,
        'gpa'                  => 0,
        'science_gpa'          => 0,
    ];
    
    // Initialize accumulators for 2-digit test score fields.
    $sum_2digit = [
        'AA'  => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS'  => 0,
    ];
    $count_2digit = [
        'AA'  => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS'  => 0,
    ];
    
    // Initialize accumulators for 3-digit test score fields.
    $sum_3digit = [
        'AA'  => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS'  => 0,
    ];
    $count_3digit = [
        'AA'  => 0,
        'DAT' => 0,
        'PAT' => 0,
        'TS'  => 0,
    ];
    
    // Loop through each school record.
    foreach ($schools_data as $school) {
        // Process common fields.
        if (isset($school->shadow_hours_minimum) && $school->shadow_hours_minimum !== '') {
            $common_sums['shadow_hours_minimum'] += (float)$school->shadow_hours_minimum;
            $common_counts['shadow_hours_minimum']++;
        }
        if (isset($school->min_gpa) && $school->min_gpa !== '') {
            $common_sums['gpa'] += (float)$school->min_gpa;
            $common_counts['gpa']++;
        }
        if (isset($school->avg_gpascience) && $school->avg_gpascience !== '') {
            $common_sums['science_gpa'] += (float)$school->avg_gpascience;
            $common_counts['science_gpa']++;
        }
        
        // Process 2-digit scores.
        if (isset($school->aa) && $school->aa !== '') {
            $sum_2digit['AA'] += (float)$school->aa;
            $count_2digit['AA']++;
        }
        if (isset($school->dat) && $school->dat !== '') {
            $sum_2digit['DAT'] += (float)$school->dat;
            $count_2digit['DAT']++;
        }
        if (isset($school->pat) && $school->pat !== '') {
            $sum_2digit['PAT'] += (float)$school->pat;
            $count_2digit['PAT']++;
        }
        if (isset($school->ts) && $school->ts !== '') {
            $sum_2digit['TS'] += (float)$school->ts;
            $count_2digit['TS']++;
        }
        
        // Process 3-digit scores.
        if (isset($school->aa_3dscore) && $school->aa_3dscore !== '') {
            $sum_3digit['AA'] += (float)$school->aa_3dscore;
            $count_3digit['AA']++;
        }
        if (isset($school->dat_3dscore) && $school->dat_3dscore !== '') {
            $sum_3digit['DAT'] += (float)$school->dat_3dscore;
            $count_3digit['DAT']++;
        }
        if (isset($school->pat_3dscore) && $school->pat_3dscore !== '') {
            $sum_3digit['PAT'] += (float)$school->pat_3dscore;
            $count_3digit['PAT']++;
        }
        if (isset($school->ts_3dscore) && $school->ts_3dscore !== '') {
            $sum_3digit['TS'] += (float)$school->ts_3dscore;
            $count_3digit['TS']++;
        }
    }

    // Calculate averages for common fields.
    $common_avgs = [];
    foreach ($common_sums as $key => $sum) {
        $common_avgs[$key] = ($common_counts[$key] > 0) ? $sum / $common_counts[$key] : null;
    }
    
    // Calculate averages for 2-digit scores.
    $avg_2digit = [];
    foreach ($sum_2digit as $key => $sum) {
        $avg_2digit[$key] = ($count_2digit[$key] > 0) ? $sum / $count_2digit[$key] : null;
    }
    
    // Calculate averages for 3-digit scores.
    $avg_3digit = [];
    foreach ($sum_3digit as $key => $sum) {
        $avg_3digit[$key] = ($count_3digit[$key] > 0) ? $sum / $count_3digit[$key] : null;
    }
    
    // Prepare data rows for each score scale.
    $data_averages = [
        '2_digit_score' => array_merge($common_avgs, $avg_2digit, ['score_mode' => '2_digit_score']),
        '3_digit_score' => array_merge($common_avgs, $avg_3digit, ['score_mode' => '3_digit_score']),
    ];
    
    $table_name = $wpdb->prefix . 'dental_school_averages';
    
    // Insert or update the averages in the database and log each result.
    foreach ($data_averages as $scale => $data) {
        $existing_record = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE score_mode = %s LIMIT 1", $scale));
        if ($existing_record) {
            $wpdb->update($table_name, $data, ['id' => $existing_record]);
        } else {
            $wpdb->insert($table_name, $data);
        }
        ds_custom_log("Averages stored for {$scale}: " . print_r($data, true));
    }
}


function ds_interview_deactivate() {
    global $wpdb;
    ds_custom_log("ds_interview_deactivate() called. Deactivating plugin.");
    
    // List of tables to drop (if needed).
    $tables_to_drop = [
        // $wpdb->prefix . 'user_interview_chance_log',
        // $wpdb->prefix . 'user_interview_evaluation_file_access',
        $wpdb->prefix . 'dental_school_averages'
    ];
    
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    ds_custom_log("Plugin deactivated.");
}


?>
