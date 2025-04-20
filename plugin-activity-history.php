<?php
/**
 * Plugin Name: Plugin Activity History
 * Plugin URI: https://yahyadev.online/
 * Description: A comprehensive suite of enhancements for WooCommerce, providing advanced delivery tracking and management features.
 * Version: 1.0.0
 * Author: Yahya Eddaqqaq
 * Author URI: https://yahyadev.online/
 * Text Domain: PAS
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 *
 * @package PAS
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PAH;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('PAS_VERSION', '1.0.0');
define('PAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PAS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PAS_MIN_WC_VERSION', '6.0');
define('PAS_MIN_PHP_VERSION', '7.4');
define('PAS_MIN_WP_VERSION', '5.0');
define('TRANSLATION_DOMAIN', 'PAS');
define('PAS_SNIPPET_SETTINGS_OPTION', 'PAS_enhancer_snippet_settings');
define('PAS_GENERAL_SETTINGS_OPTION', 'PAS_general_settings');


class PAH_Plugin_Activity_Tracker {
	/**
	 * Constants
	 */
	const MAX_LOGS = 1000;
	const CACHE_DURATION = 7200; // 2 hours
	const ITEMS_PER_PAGE = 20;
	const TEXT_DOMAIN = 'plugin-activity-tracker';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook into plugin lifecycle events
		add_action('admin_init', array($this, 'check_user_cap'));
		add_action('activated_plugin', array($this, 'track_plugin_activation'), 10, 2);
		add_action('deactivated_plugin', array($this, 'track_plugin_deactivation'), 10, 1);
		add_action('deleted_plugin', array($this, 'track_plugin_deletion'), 10, 2);
		add_action('upgrader_process_complete', array($this, 'track_plugin_update_or_install'), 10, 2);
		add_action('admin_menu', array($this, 'admin_menu_KjB654'));
		// add_action('wp_ajax_export_plugin_logs', array($this, 'handle_export_logs'));
	}

	/**
	 * Checks user capability.
	 */
	public function check_user_cap() {
		return current_user_can('manage_options');
	}

	/**
	 * Track plugin activation.
	 */
	public function track_plugin_activation($plugin, $network_wide) {
		if (!$this->is_dashboard_action()) {
			return;
		}
		
		$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
		$plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
		$plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
		
		$this->log_activity('activation', $plugin_name, $plugin, $plugin_version);
	}

	/**
	 * Track plugin deactivation.
	 */
	public function track_plugin_deactivation($plugin) {
		if (!$this->is_dashboard_action()) {
			return;
		}
		
		$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
		$plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
		$plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
		
		$this->log_activity('deactivation', $plugin_name, $plugin, $plugin_version);
	}

	/**
	 * Track plugin deletion.
	 */
	public function track_plugin_deletion($plugin, $deleted) {
		if (!$this->is_dashboard_action()) {
			return;
		}
		
		$plugin_parts = explode('/', $plugin);
		$plugin_name = $plugin_parts[0];
		
		$this->log_activity('deletion', $plugin_name, $plugin, '');
	}

	/**
	 * Track plugin updates and installations.
	 */
	public function track_plugin_update_or_install($upgrader, $options) {
		if (!$this->is_dashboard_action()) {
			return;
		}
		
		if (empty($options['type']) || $options['type'] !== 'plugin') {
			return;
		}
		
		if ($options['action'] === 'update' && !empty($options['plugins']) && is_array($options['plugins'])) {
			foreach ($options['plugins'] as $plugin) {
				$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
				$plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
				$plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
				
				$this->log_activity('update', $plugin_name, $plugin, $plugin_version);
			}
		}
		
		if ($options['action'] === 'install' && $upgrader instanceof Plugin_Upgrader) {
			$plugin_file = $this->get_plugin_file_from_upgrader($upgrader, $options);
			
			if (!empty($plugin_file) && file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
				$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
				$plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_file;
				$plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
				
				$this->log_activity('installation', $plugin_name, $plugin_file, $plugin_version);
			}
		}
	}

	/**
	 * Get plugin file from upgrader
	 */
	private function get_plugin_file_from_upgrader($upgrader, $options) {
		$plugin_file = $upgrader->plugin_info();
		
		if (empty($plugin_file) && isset($options['plugin'])) {
			$plugin_file = $options['plugin'];
		} elseif (empty($plugin_file) && !empty($upgrader->result) && is_array($upgrader->result) && isset($upgrader->result['destination_name'])) {
			$plugin_file = $this->find_main_plugin_file($upgrader->result['destination_name']);
		}
		
		return $plugin_file;
	}

	/**
	 * Find main plugin file
	 */
	private function find_main_plugin_file($destination_name) {
		$plugin_file = $destination_name . '/' . $destination_name . '.php';
		
		if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $destination_name;
			if (is_dir($plugin_dir)) {
				$plugin_files = glob($plugin_dir . '/*.php');
				if (!empty($plugin_files)) {
					foreach ($plugin_files as $file) {
						$plugin_data = get_plugin_data($file);
						if (!empty($plugin_data['Name'])) {
							$plugin_file = $destination_name . '/' . basename($file);
							break;
						}
					}
				}
			}
		}
		
		return $plugin_file;
	}

	/**
	 * Check if current request is a dashboard action.
	 */
	private function is_dashboard_action() {
		return is_admin();
	}

	/**
	 * Log plugin activity.
	 */
	private function log_activity($action, $plugin_name, $plugin_file, $plugin_version) {
		try {
			$logs = $this->get_logs();
			
			$logs[] = array(
				'action' => sanitize_text_field($action),
				'plugin_name' => sanitize_text_field($plugin_name),
				'plugin_file' => sanitize_text_field($plugin_file),
				'plugin_version' => sanitize_text_field($plugin_version),
				'user_id' => get_current_user_id(),
				'timestamp' => current_time('mysql')
			);
			
			if (count($logs) > self::MAX_LOGS) {
				$logs = array_slice($logs, -self::MAX_LOGS);
			}
			
			$this->update_logs($logs);
		} catch (Exception $e) {
			error_log('Plugin Activity Tracker Error: ' . $e->getMessage());
		}
	}

	/**
	 * Get logs from cache or database
	 */
	private function get_logs() {
		$logs = get_transient('plugin_activity_logs_transient');
		
		if (false === $logs) {
			$logs = get_option('plugin_activity_logs', array());
			set_transient('plugin_activity_logs_transient', $logs, self::CACHE_DURATION);
		}
		
		return $logs;
	}

	/**
	 * Update logs in database and cache
	 */
	private function update_logs($logs) {
		update_option('plugin_activity_logs', $logs, true);
		set_transient('plugin_activity_logs_transient', $logs, self::CACHE_DURATION);
	}

	/**
	 * Add admin menu item.
	 */
	public function admin_menu_KjB654() {
		add_submenu_page(
			'plugins.php',
			__('Plugin Activity History', self::TEXT_DOMAIN),
			__('Plugin Activity', self::TEXT_DOMAIN),
			'manage_options',
			'plugin-activity-history',
			array($this, 'display_activity_page')
		);
	}

	/**
	 * Display the activity page.
	 */
	public function display_activity_page() {
		if (!$this->check_user_cap()) {
			wp_die(__('You do not have sufficient permissions to access this page.', self::TEXT_DOMAIN));
		}

		// Handle clear logs action
		if (isset($_POST['clear_logs']) && check_admin_referer('clear_plugin_logs', 'clear_logs_nonce')) {
			delete_transient('plugin_activity_logs_transient');
		}

		// Get paginated logs
		$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
		$logs = $this->get_paginated_logs($current_page);
		$total_items = count($this->get_logs());
		$total_pages = ceil($total_items / self::ITEMS_PER_PAGE);

		// Display page
		echo '<div class="wrap plugin-activity-tracker">';
		echo '<h1>' . esc_html__('Plugin Activity History', self::TEXT_DOMAIN) . '</h1>';
		
		settings_errors('plugin_activity_tracker');
		
		// Filter form
		$this->display_filter_form();
		
		// Export and Clear buttons
		$this->display_action_buttons();
		
		// Display table
		if (empty($logs)) {
			echo '<p>' . esc_html__('No plugin activity has been recorded yet.', self::TEXT_DOMAIN) . '</p>';
		} else {
			$this->display_logs_table($logs);
			$this->display_pagination($current_page, $total_pages);
		}
		
		echo '</div>';
	}

	/**
	 * Display filter form
	 */
	private function display_filter_form() {
		echo '<div class="filter-form">';
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="plugin-activity-history">';
		echo wp_nonce_field('filter_plugin_logs', 'filter_nonce', true, false);
		
		echo '<label for="filter-action">' . esc_html__('Activity Type:', self::TEXT_DOMAIN) . ' </label>';
		echo '<select id="filter-action" name="action">';
		echo '<option value="">' . esc_html__('All Activities', self::TEXT_DOMAIN) . '</option>';
		echo '<option value="activation">' . esc_html__('Activations', self::TEXT_DOMAIN) . '</option>';
		echo '<option value="deactivation">' . esc_html__('Deactivations', self::TEXT_DOMAIN) . '</option>';
		echo '<option value="update">' . esc_html__('Updates', self::TEXT_DOMAIN) . '</option>';
		echo '<option value="deletion">' . esc_html__('Deletions', self::TEXT_DOMAIN) . '</option>';
		echo '<option value="installation">' . esc_html__('Installations', self::TEXT_DOMAIN) . '</option>';
		echo '</select>';
		
		echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Filter', self::TEXT_DOMAIN) . '">';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Display action buttons
	 */
	private function display_action_buttons() {
		echo '<div class="action-buttons">';
		echo '<form method="post" style="display:inline;">';
		echo wp_nonce_field('clear_plugin_logs', 'clear_logs_nonce', true, false);
		echo '<input type="submit" name="clear_logs" class="button button-secondary" value="' . esc_attr__('Clear Logs', self::TEXT_DOMAIN) . '" onclick="return confirm(\'' . esc_js(__('Are you sure you want to clear all logs?', self::TEXT_DOMAIN)) . '\');">';
		echo '</form>';
		
		// echo '<button type="button" class="button button-primary export-logs">' . esc_html__('Export Logs', self::TEXT_DOMAIN) . '</button>';
		echo '</div>';
	}

	/**
	 * Display logs table
	 */
	private function display_logs_table($logs) {
		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Date & Time', self::TEXT_DOMAIN) . '</th>';
		echo '<th>' . esc_html__('User', self::TEXT_DOMAIN) . '</th>';
		echo '<th>' . esc_html__('Action', self::TEXT_DOMAIN) . '</th>';
		echo '<th>' . esc_html__('Plugin', self::TEXT_DOMAIN) . '</th>';
		echo '<th>' . esc_html__('Version', self::TEXT_DOMAIN) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		
		foreach ($logs as $log) {
			$user_info = get_userdata($log['user_id']);
			$username = $user_info ? $user_info->display_name : __('Unknown User', self::TEXT_DOMAIN);
			
			$action_labels = array(
				'activation' => __('Activated', self::TEXT_DOMAIN),
				'deactivation' => __('Deactivated', self::TEXT_DOMAIN),
				'update' => __('Updated', self::TEXT_DOMAIN),
				'deletion' => __('Deleted', self::TEXT_DOMAIN),
				'installation' => __('Installed', self::TEXT_DOMAIN)
			);
			
			$action_label = isset($action_labels[$log['action']]) ? $action_labels[$log['action']] : $log['action'];
			$action_class = 'action-' . $log['action'];
			
			echo '<tr>';
			echo '<td>' . esc_html(get_date_from_gmt($log['timestamp'])) . '</td>';
			echo '<td>' . esc_html($username) . '</td>';
			echo '<td><span class="action-badge ' . esc_attr($action_class) . '">' . esc_html($action_label) . '</span></td>';
			echo '<td>' . esc_html($log['plugin_name']) . '</td>';
			echo '<td>' . esc_html($log['plugin_version']) . '</td>';
			echo '</tr>';
		}
		
		echo '</tbody></table>';
	}

	/**
	 * Display pagination
	 */
	private function display_pagination($current_page, $total_pages) {
		if ($total_pages > 1) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo paginate_links(array(
				'base' => add_query_arg('paged', '%#%'),
				'format' => '',
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'total' => $total_pages,
				'current' => $current_page
			));
			echo '</div></div>';
		}
	}

	/**
	 * Get paginated logs
	 */
	private function get_paginated_logs($current_page) {
		$logs = $this->get_logs();
		$logs = array_reverse($logs);
		
		$action_filter = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
		
		if (!empty($action_filter)) {
			$logs = array_filter($logs, function($log) use ($action_filter) {
				return $log['action'] === $action_filter;
			});
		}
		
		$offset = ($current_page - 1) * self::ITEMS_PER_PAGE;
		return array_slice($logs, $offset, self::ITEMS_PER_PAGE);
	}

	/**
	 * Handle export logs
	 */
	public function handle_export_logs() {
		if (!check_ajax_referer('export_plugin_logs_nonce', 'nonce', false)) {
			wp_send_json_error(__('Invalid nonce', self::TEXT_DOMAIN));
		}

		if (!$this->check_user_cap()) {
			wp_send_json_error(__('Insufficient permissions', self::TEXT_DOMAIN));
		}

		$logs = $this->get_logs();
		$filename = 'plugin-activity-logs-' . date('Y-m-d') . '.csv';
		
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		
		$output = fopen('php://output', 'w');
		
		// Add CSV headers
		fputcsv($output, array(
			__('Date & Time', self::TEXT_DOMAIN),
			__('User', self::TEXT_DOMAIN),
			__('Action', self::TEXT_DOMAIN),
			__('Plugin', self::TEXT_DOMAIN),
			__('Version', self::TEXT_DOMAIN)
		));
		
		// Add data rows
		foreach ($logs as $log) {
			$user_info = get_userdata($log['user_id']);
			$username = $user_info ? $user_info->display_name : __('Unknown User', self::TEXT_DOMAIN);
			
			fputcsv($output, array(
				get_date_from_gmt($log['timestamp']),
				$username,
				$log['action'],
				$log['plugin_name'],
				$log['plugin_version']
			));
		}
		
		fclose($output);
		wp_die();
	}
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    if (current_user_can('manage_options')) {
        new PAH_Plugin_Activity_Tracker();
    }
});