<?php

namespace BeeComm\Utils;

final class Logger
{
    private static string $logPath;

    private static function ensureLogPath(): void
    {
        if (!isset(self::$logPath)) {
            $config = require __DIR__ . '/../../config/plugin-config.php';
            self::$logPath = $config['log_file_directory'] . $config['log_file_name'];
        }

        if (!file_exists(dirname(self::$logPath))) {
            wp_mkdir_p(dirname(self::$logPath));
        }
    }

    public static function info(string $message, $context = null): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, $context = null): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, $context = null): void
    {
        self::ensureLogPath();

        $timestamp = current_time('mysql');
        $line = "[{$timestamp}] [{$level}] {$message}";

        if ($context !== null) {
            $line .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        file_put_contents(self::$logPath, $line . PHP_EOL, FILE_APPEND);
    }
}
