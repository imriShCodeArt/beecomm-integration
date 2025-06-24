<?php
/**
 * Plugin Name: התממשקות לביקום
 * Description: שליחת פרטי ההזמנות באתר למערכת Beecomm
 * Version:     2.0.0
 * Author:      M.L Web Solutions
 * Author URI:  https://clients.libiserv.co.il/
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Define core plugin constants
if (!defined('BEECOMM_INTEGRATION_VERSION')) {
	define('BEECOMM_INTEGRATION_VERSION', '2.0.0');
}
if (!defined('BEECOMM_PLUGIN_DIR')) {
	define('BEECOMM_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('BEECOMM_PLUGIN_URL')) {
	define('BEECOMM_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Load Composer autoload if available (for future extension)
$autoload_path = BEECOMM_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($autoload_path)) {
	require_once $autoload_path;
}

// Load main plugin class
require_once BEECOMM_PLUGIN_DIR . 'includes/class-beecomm-integration.php';

// Initialize and run the plugin
function run_beecomm_integration(): void
{
	$plugin = new Beecomm_Integration();
	$plugin->run();
}
run_beecomm_integration();
