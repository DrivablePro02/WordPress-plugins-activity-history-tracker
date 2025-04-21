<?php
/**
 * PHPUnit bootstrap file
 */

// First, we need to load the composer autoloader so we can use WP Mock
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Now call the bootstrap method of WP Mock
\WP_Mock::bootstrap();

/**
 * Define WordPress functions that we need to mock
 */
if (!function_exists('add_action')) {
    function add_action() {
        \WP_Mock::onAction(...func_get_args());
    }
}

if (!function_exists('add_filter')) {
    function add_filter() {
        \WP_Mock::onFilter(...func_get_args());
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page() {
        return \WP_Mock::userFunction('add_submenu_page', ['args' => func_get_args()]);
    }
}

if (!function_exists('get_plugin_data')) {
    function get_plugin_data() {
        return \WP_Mock::userFunction('get_plugin_data', ['args' => func_get_args()]);
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return 'plugin-activity-history/plugin-activity-history.php';
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return \WP_Mock::userFunction('current_user_can', ['args' => [$capability]]);
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return \WP_Mock::userFunction('is_admin');
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return \WP_Mock::userFunction('get_current_user_id');
    }
}

if (!function_exists('current_time')) {
    function current_time($type = 'mysql') {
        return \WP_Mock::userFunction('current_time', ['args' => [$type]]);
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return \WP_Mock::userFunction('get_option', ['args' => [$option, $default]]);
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        return \WP_Mock::userFunction('update_option', ['args' => [$option, $value, $autoload]]);
    }
}

if (!function_exists('get_transient')) {
    function get_transient($transient) {
        return \WP_Mock::userFunction('get_transient', ['args' => [$transient]]);
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        return \WP_Mock::userFunction('set_transient', ['args' => [$transient, $value, $expiration]]);
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        return \WP_Mock::userFunction('delete_transient', ['args' => [$transient]]);
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = array()) {
        return \WP_Mock::userFunction('wp_die', ['args' => [$message, $title, $args]]);
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = "_wpnonce", $referer = true, $echo = true) {
        return \WP_Mock::userFunction('wp_nonce_field', ['args' => [$action, $name, $referer, $echo]]);
    }
}

if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action = -1, $query_arg = '_wpnonce') {
        return \WP_Mock::userFunction('check_admin_referer', ['args' => [$action, $query_arg]]);
    }
}

if (!function_exists('paginate_links')) {
    function paginate_links($args = '') {
        return \WP_Mock::userFunction('paginate_links', ['args' => [$args]]);
    }
}

if (!function_exists('get_userdata')) {
    function get_userdata($user_id) {
        return \WP_Mock::userFunction('get_userdata', ['args' => [$user_id]]);
    }
}

if (!function_exists('get_date_from_gmt')) {
    function get_date_from_gmt($string, $format = 'Y-m-d H:i:s') {
        return \WP_Mock::userFunction('get_date_from_gmt', ['args' => [$string, $format]]);
    }
}

if (!function_exists('settings_errors')) {
    function settings_errors($setting = '', $sanitize = false, $hide_on_cap = '') {
        return \WP_Mock::userFunction('settings_errors', ['args' => [$setting, $sanitize, $hide_on_cap]]);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return \WP_Mock::userFunction('sanitize_text_field', ['args' => [$str]]);
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return \WP_Mock::userFunction('esc_html__', ['args' => [$text, $domain]]);
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return \WP_Mock::userFunction('esc_attr__', ['args' => [$text, $domain]]);
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text) {
        return \WP_Mock::userFunction('esc_js', ['args' => [$text]]);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return \WP_Mock::userFunction('esc_html', ['args' => [$text]]);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return \WP_Mock::userFunction('esc_attr', ['args' => [$text]]);
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return \WP_Mock::userFunction('__', ['args' => [$text, $domain]]);
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg() {
        return \WP_Mock::userFunction('add_query_arg', ['args' => func_get_args()]);
    }
}

if (!function_exists('error_log')) {
    function error_log($message, $message_type = 0, $destination = null, $additional_headers = null) {
        return \WP_Mock::userFunction('error_log', ['args' => [$message, $message_type, $destination, $additional_headers]]);
    }
}

// Define WordPress constants
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', '/path/to/wp-content/plugins');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', '/path/to/wordpress/');
}