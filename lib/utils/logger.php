<?php
/**
 * Logging utilities for Beecomm integration.
 */

/**
 * Writes a structured log entry to a custom Beecomm log file in the uploads directory.
 *
 * @param string|array $entry    The message or array to log.
 * @param string       $type     Log level (e.g., 'info', 'error').
 * @param string       $method   Optional: originating function or context.
 * @param string       $mode     File mode ('a' = append, 'w' = overwrite).
 * @param string       $filename Optional: filename without extension.
 * @return string|null
 */
function beecommLog($entry, $type = 'info', $method = '', $mode = 'a', $filename = 'beecomm-sms-log')
{
    $upload_dir = wp_upload_dir();

    if (!isset($upload_dir['basedir'])) {
        return null;
    }

    $path = $upload_dir['basedir'] . '/' . $filename . '.log';

    if (is_array($entry)) {
        $entry = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_line = "{$timestamp}::{$type}::{$method}::{$entry}\n";

    $fh = fopen($path, $mode);
    if ($fh) {
        fwrite($fh, $log_line);
        fclose($fh);
    }

    return $log_line;
}

/**
 * Logs errors specifically from within try/catch blocks or system exceptions.
 *
 * @param string $message
 * @param string $method Optional
 * @return void
 */
function wofErrorLog($message, $method = 'exception')
{
    beecommLog($message, 'error', $method);
}
