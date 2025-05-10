Plugin Activity History Tracker
Contributors: yahya eddaqqaq
Author URI: https://yahaydev.online/
Requires at least: 4.6
Tested up to: 6.8
License: GPLv3
Stable tag: 2.3.0
Text Domain: pah

Description
Track and manage all plugin activity within your WordPress site. This plugin logs actions such as plugin activations, deactivations, updates, and settings changes. Ideal for developers, administrators, and site owners who need a full audit trail of their plugin activity.

Features:

Plugin Activity Logs: Logs every activation, deactivation, update, and configuration change.

Filter Logs: Search through plugin activity by date, plugin name, or action.

User Roles: Restrict access to the activity logs based on user roles.

Secure Logging: Logs are sanitized and stored securely to prevent unauthorized access.

Easy Interface: A user-friendly dashboard widget to view plugin activities at a glance.

Export Logs: Export the activity logs for audits or backup purposes.

Compatibility: Works with all active plugins and themes without conflicts.

WP-CLI Commands:

Install: sudo wp plugin install pah-activity-tracker --allow-root

Activate: sudo wp plugin activate pah-activity-tracker --allow-root

Deactivate: sudo wp plugin deactivate pah-activity-tracker --allow-root

Update: sudo wp plugin update pah-activity-tracker --allow-root

Installation:

Upload pah-activity-tracker to your plugin directory.

Activate via the WordPress dashboard.

Access plugin logs via the plugin settings panel.

Frequently Asked Questions:

How can I filter plugin activity logs?
You can filter logs based on date, plugin name, or action using the filter options in the logs page.

Can I export logs for audits?
Yes, you can export all plugin activity logs as a CSV file for external audits.

Who can access the activity logs?
Only users with admin or specific roles, as configured, can access the activity logs.

Changelog:

1.0.0:

Added secure logging to prevent unauthorized access.

Improved performance by reducing plugin load time.

Resolved compatibility issues with the latest WordPress updates.

