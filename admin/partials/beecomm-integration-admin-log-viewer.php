<div class="wrap">
    <h1>צפייה בלוג</h1>
    <table class="widefat striped log-table">
        <thead>
            <tr>
                <th style="width:20%;">Time</th>
                <th style="width:10%;">Level</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($log_entries as $entry): ?>
                <tr class="log-row level-<?php echo esc_attr($entry['level']); ?>">
                    <td class="time_columns" style="width:20%;"><?php echo esc_html($entry['time']); ?></td>
                    <td><?php echo esc_html(strtoupper($entry['level'])); ?></td>
                    <td>
                        <pre><?php echo $entry['message']; ?></pre>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    .log-table pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .level-error td {
        background: #ffe5e5;
    }

    .level-warning td {
        background: #fff8e5;
    }

    .level-info td {
        background: #e5f5ff;
    }

    .level-debug td {
        background: #f1f1f1;
    }
    .time_columns{
        max-width: 100px!important;
    }
</style>