<?php
/**
 * Renders the Beecomm Integration settings page.
 */
function beecomm_render_plugin_settings_page()
{
    ?>
    <div class="wrap">
        <h2>אינטגרציה לBeecomm</h2>

        <form action="options.php" method="post">
            <?php
            settings_fields('beecomm_integration_options');
            do_settings_sections('beecomm_integration');
            submit_button(__('Save', 'beecomm'));
            ?>
        </form>
    </div>
    <?php
}
