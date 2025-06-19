<?php
/**
 * Plugin Name: התממשקות לביקום
 * Description: שליחת פרטי ההזמנות באתר למערכת Beecomm
 * Version:     2.0.0
 * Author:      M.L Web Solutions
 * Author URI:  https://clients.libiserv.co.il/
 */

// Define plugin version constant
if ( ! defined( 'BEECOMM_INTEGRATION_VERSION' ) ) {
	define( 'BEECOMM_INTEGRATION_VERSION', '2.0.0' );
}

// Define core path constants
define( 'BEECOMM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEECOMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load core plugin class
require_once BEECOMM_PLUGIN_DIR . 'includes/class-beecomm-integration.php';

// Legacy procedural bootstrapping (will be removed as we refactor)
require_once BEECOMM_PLUGIN_DIR . 'lib/bootstrap-legacy.php';

// Initialize and run the plugin
function run_beecomm_integration() {
	$plugin = new Beecomm_Integration();
	$plugin->run();
}
run_beecomm_integration();
