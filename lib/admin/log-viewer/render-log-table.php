<?php
require_once __DIR__ . '/log-reader.php';
require_once __DIR__ . '/render-helpers.php';

/**
 * Renders the Log Viewer admin page with parsed logs in a table.
 */
function beecomm_render_log_viewer_page()
{
    $entries = beecomm_get_log_entries();

    if (empty($entries)) {
        echo '<div class="notice notice-error"><p>No log entries found.</p></div>';
        return;
    }

    echo '<style>
        .log-table { border-collapse: collapse; width: 100%; }
        .log-table th, .log-table td { border: 1px solid #ccc; padding: 8px; }
        .log-table th { background-color: #f2f2f2; }
    </style>';

    echo '<table class="log-table">';
    echo '<thead><tr><th>Date/Time</th><th>Branch ID</th><th>Order Info</th></tr></thead>';
    echo '<tbody>';

    foreach ($entries as $entry) {
        echo '<tr>';
        echo '<td style="width: 200px;">' . esc_html($entry['date_time']) . '</td>';
        echo '<td>' . esc_html($entry['branch_id']) . '</td>';
        echo '<td>' . beecomm_format_order_info($entry['order_info']) . '</td>';
        echo '</tr><tr><td colspan="3"><br></td></tr>';
    }

    echo '</tbody></table>';
}
