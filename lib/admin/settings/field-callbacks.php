<?php
/**
 * Renders individual input fields for Beecomm settings.
 */

/**
 * Renders the Client ID field.
 */
function beecomm_integration_setting_client_id()
{
    $options = get_option('beecomm_integration_options');
    $value = $options['client_id'] ?? '';
    echo "<input id='beecomm_integration_setting_client_id' name='beecomm_integration_options[client_id]' type='text' value='" . esc_attr($value) . "' />";
}

/**
 * Renders the Client Secret field.
 */
function beecomm_integration_setting_client_secret()
{
    $options = get_option('beecomm_integration_options');
    $value = $options['client_secret'] ?? '';
    echo "<input id='beecomm_integration_setting_client_secret' name='beecomm_integration_options[client_secret]' type='text' value='" . esc_attr($value) . "' />";
}

/**
 * Renders the dynamic fields under the Order Template section.
 *
 * @param array $args
 */
function beecomm_integration_setting_order_template($args)
{
    $options = get_option(BEECOMM_INTEGRATION_OPTIONS);
    $value = $options[$args['id']] ?? '';
    $id = esc_attr($args['id']);
    $name = "beecomm_integration_options[{$id}]";

    if ($args['type'] === 'text') {
        echo "<input id='{$id}' name='{$name}' type='text' value='" . esc_attr($value) . "' />";
        echo "<p class='description'>" . esc_html($args['description']) . "</p>";
    } else {
        echo "<textarea id='{$id}' name='{$name}' rows='10' cols='50'>" . esc_textarea($value) . "</textarea>";
    }
}
