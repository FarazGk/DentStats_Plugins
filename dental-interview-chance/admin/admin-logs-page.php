<?php

if (!defined('ABSPATH')) {
    exit;
}

function ds_admin_logs_page() {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get current page number
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $logs_per_page = 20;
    $offset = ($paged - 1) * $logs_per_page;

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_interview_chance_log';

    // Fetch total number of logs
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Fetch logs for the current page
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d", $logs_per_page, $offset));

    // Calculate total pages
    $total_pages = ceil($total_logs / $logs_per_page);

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Interview Chance Logs', 'dental-interview-chance'); ?></h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('User ID', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('Username', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('Action', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('Details', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('Form Data', 'dental-interview-chance'); ?></th>
                    <th><?php esc_html_e('Timestamp', 'dental-interview-chance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->id); ?></td>
                            <td><?php echo esc_html($log->user_id); ?></td>
                            <td><?php echo esc_html($log->username); ?></td>
                            <td><?php echo esc_html($log->action); ?></td>
                            <td><?php echo esc_html($log->details); ?></td>
                            <td><?php echo esc_html($log->form_data); ?></td>
                            <td><?php echo esc_html($log->timestamp); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7"><?php esc_html_e('No logs found.', 'dental-interview-chance'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        // Display pagination if needed
        if ($total_pages > 1) {
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $paged
            ));

            echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
        }
        ?>
    </div>
    <?php
}
?>
