<?php
/**
 * Reads and parses the log file into structured entries.
 *
 * @return array Parsed log entries with keys: date_time, branch_id, order_info.
 */
function beecomm_get_log_entries()
{
    $uploads_dir = wp_upload_dir();
    $path = $uploads_dir['basedir'] . '/wof-log.log';

    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $lines = explode('inbeeCommOrder', $content);
    $entries = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line))
            continue;

        $parts = explode('::', $line, 2);
        $date = $parts[0] ?? '';
        $data = json_decode($parts[1] ?? '', true);

        if (!is_array($data))
            continue;

        $entries[] = [
            'date_time' => $date,
            'branch_id' => $data['branchId'] ?? '',
            'order_info' => $data['orderInfo'] ?? [],
        ];
    }

    return $entries;
}
