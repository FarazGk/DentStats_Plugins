<?php

if (!defined('ABSPATH')) {
    exit;
}

// Add shortcode to display user files
add_shortcode('user_files', 'display_user_files');

function display_user_files() {
    if (!is_user_logged_in()) {
        return '<p class="user-files-message">You need to be logged in to view your files.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'user_interview_evaluation_file_access';

    $files = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY timestamp DESC",
        $user_id
    ));

    if (empty($files)) {
        return '<p class="user-files-message">You do not have any files available for download.</p>';
    }

    $output = '<table class="user-files-table">';
    $output .= '<thead><tr><th>Evaluation</th><th>Download</th></tr></thead><tbody>';
    foreach ($files as $file) {
        $output .= '<tr>';
        $output .= '<td class="user-files-description">Evaluation Generated on ' . date('Y-m-d H:i:s', strtotime($file->timestamp)) . '</td>';
        $output .= '<td><a href="' . esc_url($file->file_url) . '" class="user-files-button" target="_blank">Evaluation Report</a></td>';
        $output .= '</tr>';
    }
    $output .= '</tbody></table>';

    return $output;
}
?>
