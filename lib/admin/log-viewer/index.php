<?php
/**
 * Registers the Log Viewer page in the WordPress admin.
 */
add_action('admin_menu', function () {
    add_options_page(
        'Log Viewer',
        'Log Viewer',
        'manage_options',
        'log-viewer',
        'beecomm_render_log_viewer_page'
    );
});
