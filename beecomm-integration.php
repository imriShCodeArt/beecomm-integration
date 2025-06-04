<?php
/**
 * Plugin Name: התממשקות לביקום
 * Description: שליחת פרטי ההזמנות באתר למערכת Beecomm
 * Version: 1.1
 * Author: M.L Web Solutions
 * Author URI: https://clients.libiserv.co.il/
 **/

define( 'BEECOMM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEECOMM__PLUGIN_LIB', plugin_dir_path( __FILE__ ) .'lib/' );


require_once (BEECOMM__PLUGIN_LIB . '/beecomm_contants.php');
require_once (BEECOMM__PLUGIN_LIB . '/integration.php');
require_once (BEECOMM__PLUGIN_LIB . '/admin_page.php');
require_once (BEECOMM__PLUGIN_LIB . '/log-viewer.php');
require_once (BEECOMM__PLUGIN_LIB . '/order_cron.php');