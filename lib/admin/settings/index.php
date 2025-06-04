<?php
/**
 * Registers the Beecomm Integration Settings page in the WP Admin menu.
 */

add_action('admin_menu', 'beecomm_register_admin_settings_page');

/**
 * Adds Beecomm integration settings page under Settings.
 */
function beecomm_register_admin_settings_page()
{
    add_options_page(
        'אינטגרציה לBeecomm',               // Page title
        'אינטגרציה לBeecomm',               // Menu title
        'manage_options',                   // Capability
        'beecomm-integration',              // Menu slug
        'beecomm_render_plugin_settings_page' // Callback (defined in render-settings-page.php)
    );
}
