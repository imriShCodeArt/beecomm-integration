<?php
/**
 * Plugin Name: BeeComm Integration for WooCommerce
 * Description: Syncs WooCommerce with BeeComm POS, sends SMS updates, and more.
 * Version: 2.0.0
 * Author: Your Agency
 * Text Domain: beecomm-integration
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use BeeComm\Core\Plugin;

Plugin::getInstance()->boot();
