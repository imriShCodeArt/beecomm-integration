<?php

namespace BeeComm\Config;

defined('ABSPATH') || exit;

final class PluginConfig
{
    public static function get(): array
    {
        return [
            'plugin_slug' => 'beecomm-integration',
            'option_group' => 'beecomm_options',
            'option_name' => 'beecomm_integration_settings',
            'cron_hook' => 'beecomm_check_order_status',
            'log_file_name' => 'beecomm-log.txt',
            'log_file_directory' => WP_CONTENT_DIR . '/beecomm-logs/',
            'api_base_url' => 'https://biapp.beecomm.co.il:8094',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = self::get();
        return $config[$key] ?? $default;
    }
}
