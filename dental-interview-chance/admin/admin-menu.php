<?php

if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu item
function ds_add_admin_menu() {
    add_menu_page(
        __('Interview Logs', 'dental-interview-chance'),
        __('Interview Logs', 'dental-interview-chance'),
        'manage_options',
        'interview-logs',
        'ds_admin_logs_page',
        'dashicons-list-view',
        25
    );
}
add_action('admin_menu', 'ds_add_admin_menu');
?>
