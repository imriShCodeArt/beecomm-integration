<?php
/**
 * Registers Beecomm integration settings, sections, and fields.
 */

add_action('admin_init', 'beecomm_register_settings');

/**
 * Registers all plugin options, settings sections, and fields.
 */
function beecomm_register_settings()
{
    register_setting(
        'beecomm_integration_options',
        'beecomm_integration_options',
        'beecomm_integration_options_validate'
    );

    // === API Credentials Section ===
    add_settings_section(
        'api_settings',
        'API Settings',
        'beecomm_integration_section_text',
        'beecomm_integration'
    );

    add_settings_field(
        'beecomm_integration_setting_client_id',
        'Client ID',
        'beecomm_integration_setting_client_id',
        'beecomm_integration',
        'api_settings'
    );

    add_settings_field(
        'beecomm_integration_setting_client_secret',
        'Client Secret',
        'beecomm_integration_setting_client_secret',
        'beecomm_integration',
        'api_settings'
    );

    // === Order Template Section ===
    $order_template_args = [
        'beecomm_order_template' => [
            'title' => 'Order Message Template',
            'page' => 'beecomm_integration',
            'callback' => 'beecomm_order_template_section_callback',
            'description' => BEECOMM_ORDER_GETTER_TAGS,
            'fields' => [
                ['id' => BEECOMM_ORDER_STATUS_CRON_INTERVAL, 'title' => 'Order Cron Interval', 'type' => 'text', 'description' => 'Interval in minutes'],
                ['id' => BEECOMM_ADMIN_PHONE, 'title' => 'Admin Phone', 'type' => 'text', 'description' => 'Phone number to notify'],
                ['id' => BEECOMM_NUMBER_OF_ORDER_TO_PROCESS, 'title' => 'Number of Orders', 'type' => 'text', 'description' => 'Default: ' . BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT],
                ['id' => BEECOMM_ORDER_COMPLETE_TEMPLATE, 'title' => 'הודעת הזמנה שהושלמה (מסירה)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה הושלמה (מסירה)'],
                ['id' => BEECOMM_ORDER_HOLD_TEMPLATE, 'title' => 'הודעת הזמנה בהמתנה (מסירה)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה ממתינה (מסירה)'],
                ['id' => BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP, 'title' => 'הודעת הזמנה שהושלמה (איסוף)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה הושלמה (איסוף)'],
                ['id' => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP, 'title' => 'הודעת הזמנה ממתינה (איסוף)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה ממתינה (איסוף)'],
            ]
        ]
    ];

    foreach ($order_template_args as $section_id => $section) {
        add_settings_section(
            $section_id,
            $section['title'],
            $section['callback'],
            $section['page'],
            $section
        );

        foreach ($section['fields'] as $field) {
            add_settings_field(
                $field['id'],
                $field['title'],
                'beecomm_integration_setting_order_template',
                $section['page'],
                $section_id,
                $field
            );
        }
    }
}

/**
 * Validation callback for plugin options.
 *
 * @param array $input
 * @return array
 */
function beecomm_integration_options_validate($input)
{
    // You can add validation here
    return $input;
}

/**
 * Description for the API section.
 */
function beecomm_integration_section_text()
{
    echo '<p>כאן יש להכניס את פרטי המשתמש ממערכת Beecomm. את הפרטים יש לבקש מהתמיכה של Beecomm</p>';
}
