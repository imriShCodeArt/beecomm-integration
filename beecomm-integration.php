<?php
/**
 * Plugin Name: התממשקות לביקום
 * Description: שליחת פרטי ההזמנות באתר למערכת Beecomm
 * Version:     1.1.2
 * Author:      M.L Web Solutions
 * Author URI:  https://clients.libiserv.co.il/
 */

define('BEECOMM_PLUGIN_DIR', rtrim(plugin_dir_path(__FILE__), '/') . '/');
define('BEECOMM_PLUGIN_LIB', BEECOMM_PLUGIN_DIR . 'lib/');

$required_files = [
    // Constants and Core Integration
    'beecomm_constants.php',
    'integration.php',

    // Logger
    'utils/logger.php',

    // Order & API Logic
    'orders/order-utils.php',
    'api/beecomm-status.php',

    // SMS System
    'sms/send-order-status-sms.php',
    'sms/templates.php',

    // Cron Jobs
    'cron/update-order-status.php',
    'cron/hooks.php',

    // Admin Settings Page (modular)
    'admin/settings/index.php',
    'admin/settings/render-settings-page.php',
    'admin/settings/register-settings.php',
    'admin/settings/field-callbacks.php',
    'admin/settings/helpers.php',

    // Admin Log Viewer Page (modular)
    'admin/log-viewer/index.php',
    'admin/log-viewer/log-reader.php',
    'admin/log-viewer/render-log-table.php',
    'admin/log-viewer/render-helpers.php',
];

foreach ($required_files as $file) {
    $path = BEECOMM_PLUGIN_LIB . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log("Beecomm plugin error: missing file $path");
    }
}
