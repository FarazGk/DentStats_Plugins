<?php
// Set user tokens to zero upon registration
function add_user_tokens_on_registration($user_id) {
    add_user_meta($user_id, 'user_tokens', 0, true);
}
add_action('user_register', 'add_user_tokens_on_registration');

// Ensure user tokens meta exists upon login
add_action('wp_login', function($user_login, $user) {
    if (!get_user_meta($user->ID, 'user_tokens', true)) {
        add_user_meta($user->ID, 'user_tokens', 0, true);
    }
}, 10, 2);

// Ensure user tokens meta exists upon login
add_action('wp_login', function($user_login, $user) {
    if (!get_user_meta($user->ID, 'user_tokens', true)) {
        add_user_meta($user->ID, 'user_tokens', 0, true);
    }
}, 10, 2);

// Show tokens in the user profile
function show_user_tokens_in_profile($user) {
    $user_tokens = get_user_meta($user->ID, 'user_tokens', true);
    ?>
    <h3>User Tokens</h3>
    <table class="form-table">
        <tr>
            <th><label for="user_tokens">Tokens</label></th>
            <td>
                <input type="text" name="user_tokens" id="user_tokens" value="<?php echo esc_attr($user_tokens); ?>" class="regular-text" readonly /><br />
                <span class="description">This is the number of tokens the user has.</span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'show_user_tokens_in_profile');
add_action('edit_user_profile', 'show_user_tokens_in_profile');

// Function to display user tokens
function display_user_tokens() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $user_tokens = get_user_meta($user_id, 'user_tokens', true);
        return esc_html($user_tokens);
    } else {
        return "You need to be logged in to view your tokens.";
    }
}

// Register the shortcode
function register_user_tokens_shortcode() {
    add_shortcode('user_tokens', 'display_user_tokens');
}
add_action('init', 'register_user_tokens_shortcode');
?>
