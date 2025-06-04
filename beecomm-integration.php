<?php
/**
 * Plugin Name: התממשקות לביקום
 * Description: שליחת פרטי ההזמנות באתר למערכת Beecomm
 * Version:     1.1.2
 * Author:      M.L Web Solutions
 * Author URI:  https://clients.libiserv.co.il/
 */

define( 'BEECOMM_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) . '/' );
define( 'BEECOMM_PLUGIN_LIB', BEECOMM_PLUGIN_DIR . 'lib/' );

$required_files = [
    'beecomm_constants.php',
    'integration.php',
    'admin_page.php',
    'log-viewer.php',

    // Modular structure
    'utils/logger.php',

    'orders/order-utils.php',
    'api/beecomm-status.php',

    'sms/send-order-status-sms.php',
    'sms/templates.php',

    'cron/update-order-status.php',
    'cron/hooks.php',
];

foreach ( $required_files as $file ) {
    $path = BEECOMM_PLUGIN_LIB . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    } else {
        error_log( "Beecomm plugin error: missing file $path" );
    }
}
