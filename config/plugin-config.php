<?php
// config/plugin-config.php

defined('ABSPATH') || exit;

return [
    'plugin_slug'         => 'beecomm-integration',
    'option_group'        => 'beecomm_options',
    'option_name'         => 'beecomm_integration_settings',
    'cron_hook'           => 'beecomm_check_order_status',
    'log_file_name'       => 'beecomm-log.txt',
    'log_file_directory'  => WP_CONTENT_DIR . '/beecomm-logs/',
    'api_base_url'        => 'https://api.beecomm.co.il',
];
