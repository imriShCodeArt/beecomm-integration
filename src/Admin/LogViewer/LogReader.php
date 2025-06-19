<?php

namespace BeeComm\Admin\LogViewer;

use BeeComm\Config\PluginConfig;

final class LogReader
{
    private string $logPath;

    public function __construct()
    {
        $config = PluginConfig::get();
        $this->logPath = $config['log_file_directory'] . $config['log_file_name'];
    }

    public function getEntries(): array
    {
        if (!file_exists($this->logPath) || !is_readable($this->logPath)) {
            return [];
        }

        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_reverse($lines);
    }
}
