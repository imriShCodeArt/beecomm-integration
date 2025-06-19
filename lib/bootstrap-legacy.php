<?php
/**
 * Bootstrap legacy procedural code until fully migrated to OOP.
 *
 * @package Beecomm_Integration
 * @since   1.1.2
 */

defined('ABSPATH') || exit;

// Define the legacy lib path for backwards compatibility
if (!defined('BEECOMM_PLUGIN_LIB')) {
	define('BEECOMM_PLUGIN_LIB', BEECOMM_PLUGIN_DIR . 'lib/');
}


$legacy_files = [
	// Constants
	'beecomm_constants.php',

	// Core Order & API Integration
	'orders/beecomm-payload.php',
	'orders/send-order.php',
	'orders/format-items.php',
	'orders/sent-to-beecomm.php',
	'api/auth.php',
	'api/beecomm-status.php',
	'api/request.php',
	'utils/meta.php',

	// Newsletter and Order Columns
	'elementor/newsletter-hook.php',

	// Logger
	'utils/logger.php',

	// SMS System
	'sms/send-order-status-sms.php',
	'sms/templates.php',

	// Cron Jobs
	'cron/update-order-status.php',
	'cron/hooks.php',
];

foreach ($legacy_files as $file) {
	$path = BEECOMM_PLUGIN_DIR . 'lib/' . $file;
	if (file_exists($path)) {
		require_once $path;
	} else {
		error_log("Beecomm Integration: missing legacy file $path");
	}
}
