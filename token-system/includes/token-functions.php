<?php
// Increase tokens on specific product purchase
add_action('woocommerce_order_status_completed', 'increase_user_tokens', 10, 1);
function increase_user_tokens($order_id) {
    $order = wc_get_order($order_id);
    $settings = get_option('token_system_settings');
    $product_tokens = isset($settings['token_system_products']) ? $settings['token_system_products'] : [];

    custom_log("Order ID: " . $order_id); // Log order ID

    foreach ($order->get_items() as $item) {
        custom_log("Product ID: " . $item->get_product_id()); // Log product ID in the order
        foreach ($product_tokens as $index => $tokens) {
            custom_log("Checking against Product ID: " . $tokens['id']); // Log product ID being checked
            if ($item->get_product_id() == (int)$tokens['id']) {
                $user_id = $order->get_user_id();
                $user_info = get_userdata($user_id);
                $username = $user_info->user_login;
                $current_tokens = get_user_meta($user_id, 'user_tokens', true);
                custom_log("Current Tokens for User ID $user_id (Username: $username): $current_tokens"); // Log current tokens

                // Ensure tokens are being updated
                $new_token_count = $current_tokens + (int)$tokens['tokens'];
                update_user_meta($user_id, 'user_tokens', $new_token_count);
                custom_log("Updated Tokens for User ID $user_id (Username: $username): $new_token_count"); // Log updated tokens

                // Update usage count
                $settings['token_system_products'][$index]['usage_count']++;
                update_option('token_system_settings', $settings);
                custom_log("Updated usage count for Product ID: " . $tokens['id']); // Log updated usage count

                // Log the usage
                log_token_usage($user_id, 'product', $tokens['id']);
            }
        }
    }
}

// Decrease tokens on Forminator form submission
add_action('forminator_custom_form_submit_before_set_fields', 'decrease_user_tokens', 10, 3);
function decrease_user_tokens($entry, $form_id, $field_data_array) {
    $settings = get_option('token_system_settings');
    $form_tokens = isset($settings['token_system_forms']) ? $settings['token_system_forms'] : [];

    custom_log("Form ID: " . $form_id); // Log form ID

    foreach ($form_tokens as $index => $tokens) {
        custom_log("Checking against Form ID: " . $tokens['id']); // Log form ID being checked
        if ($form_id == (int)$tokens['id']) {
            $user_id = get_current_user_id();
            $user_info = get_userdata($user_id);
            $username = $user_info->user_login;
            $current_tokens = get_user_meta($user_id, 'user_tokens', true);
            custom_log("Current Tokens for User ID $user_id (Username: $username): $current_tokens"); // Log current tokens

            // if ($current_tokens >= (int)$tokens['tokens']) {
            // Ensure tokens are being updated
            $new_token_count = $current_tokens - (int)$tokens['tokens'];
            update_user_meta($user_id, 'user_tokens', $new_token_count);
            custom_log("Updated Tokens for User ID $user_id (Username: $username): $new_token_count"); // Log updated tokens

            // Update usage count
            $settings['token_system_forms'][$index]['usage_count']++;
            update_option('token_system_settings', $settings);
            custom_log("Updated usage count for Form ID: " . $tokens['id']); // Log updated usage count

            // Log the usage
            log_token_usage($user_id, 'form', $tokens['id']);
        }
    }
}


?>
