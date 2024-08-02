<?php
// Add admin menu
add_action('admin_menu', 'token_system_admin_menu');
function token_system_admin_menu() {
    add_menu_page(
        'Token System Settings',
        'Token System',
        'manage_options',
        'token-system',
        'token_system_settings_page',
        'dashicons-tickets-alt',
        20
    );
    
    // Add Logs submenu
    add_submenu_page(
        'token-system',
        'Token System Logs',
        'Logs',
        'manage_options',
        'token-system-logs',
        'token_system_logs_page'
    );
    
    // Add Users and Tokens submenu
    add_submenu_page(
        'token-system',
        'Users and Tokens',
        'Users and Tokens',
        'manage_options',
        'token-system-users',
        'token_system_users_page'
    );
}

// Create settings page
function token_system_settings_page() {
    ?>
    <div class="wrap">
        <h1>Token System Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('token_system_settings_group');
            do_settings_sections('token-system');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'token_system_register_settings');
function token_system_register_settings() {
    register_setting('token_system_settings_group', 'token_system_settings');

    add_settings_section(
        'token_system_main_section',
        'Main Settings',
        'token_system_main_section_callback',
        'token-system'
    );

    add_settings_field(
        'token_system_products',
        'Products and Tokens',
        'token_system_products_callback',
        'token-system',
        'token_system_main_section'
    );

    add_settings_field(
        'token_system_forms',
        'Forms and Tokens',
        'token_system_forms_callback',
        'token-system',
        'token_system_main_section'
    );
}

function token_system_main_section_callback() {
    echo '<p>Main settings for the Token System.</p>';
}

function token_system_products_callback() {
    $settings = get_option('token_system_settings');
    $product_tokens = isset($settings['token_system_products']) ? $settings['token_system_products'] : [];
    ?>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Tokens</th>
                <th>Usage Count</th>
            </tr>
        </thead>
        <tbody id="product_tokens_table_body">
            <?php foreach ($product_tokens as $index => $tokens): ?>
                <tr>
                    <td><input type="text" name="token_system_settings[token_system_products][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($tokens['id']); ?>" /></td>
                    <td><input type="number" name="token_system_settings[token_system_products][<?php echo esc_attr($index); ?>][tokens]" value="<?php echo esc_attr($tokens['tokens']); ?>" /></td>
                    <td><?php echo esc_attr($tokens['usage_count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" id="add_product_token_row">Add Product Token</button>
    <?php
}

function token_system_forms_callback() {
    $settings = get_option('token_system_settings');
    $form_tokens = isset($settings['token_system_forms']) ? $settings['token_system_forms'] : [];
    ?>
    <table>
        <thead>
            <tr>
                <th>Form ID</th>
                <th>Tokens</th>
                <th>Usage Count</th>
            </tr>
        </thead>
        <tbody id="form_tokens_table_body">
            <?php foreach ($form_tokens as $index => $tokens): ?>
                <tr>
                    <td><input type="text" name="token_system_settings[token_system_forms][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($tokens['id']); ?>" /></td>
                    <td><input type="number" name="token_system_settings[token_system_forms][<?php echo esc_attr($index); ?>][tokens]" value="<?php echo esc_attr($tokens['tokens']); ?>" /></td>
                    <td><?php echo esc_attr($tokens['usage_count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" id="add_form_token_row">Add Form Token</button>
    <?php
}

// Create logs page
// Create logs page
function token_system_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'token_system_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    $product_logs = array_filter($logs, function($log) {
        return $log['type'] === 'product';
    });
    $form_logs = array_filter($logs, function($log) {
        return $log['type'] === 'form';
    });
    ?>
    <div class="wrap">
        <h1>Token System Logs</h1>
        <h2>Product Purchases</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Product ID</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($product_logs)) : ?>
                    <tr>
                        <td colspan="3">No logs found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($product_logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log['user_id']); ?></td>
                            <td><?php echo esc_html($log['log_id']); ?></td>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Form Submissions</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Form ID</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($form_logs)) : ?>
                    <tr>
                        <td colspan="3">No logs found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($form_logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log['user_id']); ?></td>
                            <td><?php echo esc_html($log['log_id']); ?></td>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}


// Create users and tokens page
function token_system_users_page() {
    $users = get_users();
    ?>
    <div class="wrap">
        <h1>Users and Tokens</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Tokens</th>
                    <th>Logs</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(get_user_meta($user->ID, 'user_tokens', true)); ?></td>
                            <td><a href="<?php echo admin_url('admin.php?page=token-system-user-logs&user_id=' . $user->ID); ?>">View Logs</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Create user logs page
function token_system_user_logs_page() {
    if (!isset($_GET['user_id'])) {
        echo '<div class="wrap"><h1>User Logs</h1><p>No user selected.</p></div>';
        return;
    }

    $user_id = intval($_GET['user_id']);
    $logs = get_option('token_system_logs', []);
    $user_logs = array_filter($logs, function($log) use ($user_id) {
        return $log['user_id'] == $user_id;
    });

    ?>
    <div class="wrap">
        <h1>User Logs for User ID: <?php echo esc_html($user_id); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($user_logs)) : ?>
                    <tr>
                        <td colspan="3">No logs found for this user.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($user_logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log['type']); ?></td>
                            <td><?php echo esc_html($log['id']); ?></td>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Hook for user logs page
add_action('admin_menu', function() {
    add_submenu_page(
        null, // This makes it a hidden page
        'User Logs',
        'User Logs',
        'manage_options',
        'token-system-user-logs',
        'token_system_user_logs_page'
    );
});

// JavaScript to add new rows for products and forms
add_action('admin_footer', 'token_system_admin_js');
function token_system_admin_js() {
    ?>
    <script type="text/javascript">
        document.getElementById('add_product_token_row').addEventListener('click', function() {
            var tableBody = document.getElementById('product_tokens_table_body');
            var rowCount = tableBody.rows.length;
            var row = tableBody.insertRow(rowCount);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var index = rowCount + Math.floor(Math.random() * 1000); // Ensure unique index for new rows
            cell1.innerHTML = '<input type="text" name="token_system_settings[token_system_products][' + index + '][id]" value="" />';
            cell2.innerHTML = '<input type="number" name="token_system_settings[token_system_products][' + index + '][tokens]" value="" />';
            cell3.innerHTML = '0';
        });

        document.getElementById('add_form_token_row').addEventListener('click', function() {
            var tableBody = document.getElementById('form_tokens_table_body');
            var rowCount = tableBody.rows.length;
            var row = tableBody.insertRow(rowCount);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var index = rowCount + Math.floor(Math.random() * 1000); // Ensure unique index for new rows
            cell1.innerHTML = '<input type="text" name="token_system_settings[token_system_forms][' + index + '][id]" value="" />';
            cell2.innerHTML = '<input type="number" name="token_system_settings[token_system_forms][' + index + '][tokens]" value="" />';
            cell3.innerHTML = '0';
        });
    </script>
    <?php
}
?>
