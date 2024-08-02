<?php

if (!defined('ABSPATH')) {
    exit;
}

// Custom logging function
function ds_custom_log($message) {
    $log_dir = DS_INTERVIEW_PLUGIN_DIR . 'logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/interview-chance.log';
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "[{$timestamp}] {$message}\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Log user action
function ds_log_user_action($user_id, $action, $details = '', $form_data = []) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_interview_chance_log';
    $user_info = get_userdata($user_id);
    $username = $user_info->user_login;
    $timestamp = current_time('mysql');

    $form_data_json = json_encode($form_data);

    $wpdb->insert(
        $table_name,
        array(
            'user_id'   => $user_id,
            'username'  => $username,
            'action'    => $action,
            'details'   => $details,
            'form_data' => $form_data_json,
            'timestamp' => $timestamp,
        )
    );

    // Log to file
    $log_message = "User ID: $user_id, Username: $username, Action: $action, Details: $details, Timestamp: $timestamp\n";
    $log_message .= "Form Data: $form_data_json\n";
    ds_custom_log($log_message);
}
?>
