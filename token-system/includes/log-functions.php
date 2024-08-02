<?php
// Custom logging function
function custom_log($message) {
    $log_dir = plugin_dir_path(__FILE__) . '../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/token-system.log';
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Log token usage
function log_token_usage($user_id, $type, $id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'token_system_logs';
    $user_info = get_userdata($user_id);
    $username = $user_info->user_login;

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'username' => $username,
            'type' => $type,
            'log_id' => $id,
            'timestamp' => current_time('mysql')
        )
    );

    // Log the usage to the file as well
    $log_message = "User ID: $user_id, Username: $username, Type: $type, ID: $id, Timestamp: " . current_time('mysql') . "\n";
    $log_message .= "----------------------------------------------------------------\n";
    custom_log($log_message);
}
?>
