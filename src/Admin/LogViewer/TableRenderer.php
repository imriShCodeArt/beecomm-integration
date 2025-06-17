<?php

namespace BeeComm\Admin\LogViewer;

final class TableRenderer
{
    public function render(): void
    {
        $entries = (new LogReader())->getEntries();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('BeeComm Log Viewer', 'beecomm-integration'); ?></h1>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Log Entry', 'beecomm-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td><?php esc_html_e('No log entries found.', 'beecomm-integration'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $line): ?>
                            <tr>
                                <td><code><?php echo esc_html($line); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
