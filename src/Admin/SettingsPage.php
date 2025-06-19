<?php

namespace BeeComm\Admin;

use BeeComm\Config\PluginConfig;

final class SettingsPage
{
    private string $optionGroup;
    private string $optionName;

    public function __construct()
    {
        $config = PluginConfig::get();

        $this->optionGroup = $config['option_group'];
        $this->optionName = $config['option_name'];
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addMenuPage(): void
    {
        add_options_page(
            __('BeeComm Integration', 'beecomm-integration'),
            __('BeeComm Integration', 'beecomm-integration'),
            'manage_options',
            $this->optionName,
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting($this->optionGroup, $this->optionName, [$this, 'validate']);

        $this->addApiSection();
        $this->addOrderTemplateSection();
    }

    private function addApiSection(): void
    {
        add_settings_section(
            'beecomm_main',
            __('BeeComm API Settings', 'beecomm-integration'),
            function () {
                echo '<p>' . esc_html__('Configure BeeComm credentials.', 'beecomm-integration') . '</p>';
            },
            $this->optionName
        );

        $this->addField('api_key', __('API Key', 'beecomm-integration'), 'text');
        $this->addField('store_id', __('Store ID', 'beecomm-integration'), 'text');
    }

    private function addOrderTemplateSection(): void
    {
        add_settings_section(
            'beecomm_order_template',
            __('Order Message Template', 'beecomm-integration'),
            function () {
                echo '<p>' . esc_html__('Customize order status message templates sent via SMS.', 'beecomm-integration') . '</p>';
            },
            $this->optionName
        );

        $fields = [
            ['id' => 'cron_interval', 'label' => 'Order Cron Interval', 'type' => 'text'],
            ['id' => 'admin_phone', 'label' => 'Admin Phone', 'type' => 'text'],
            ['id' => 'order_complete_delivery', 'label' => 'הודעת הזמנה שהושלמה (מסירה)', 'type' => 'textarea'],
            ['id' => 'order_hold_delivery', 'label' => 'הודעת הזמנה בהמתנה (מסירה)', 'type' => 'textarea'],
            ['id' => 'order_complete_pickup', 'label' => 'הודעת הזמנה שהושלמה (איסוף)', 'type' => 'textarea'],
            ['id' => 'order_hold_pickup', 'label' => 'הודעת הזמנה ממתינה (איסוף)', 'type' => 'textarea'],
        ];

        foreach ($fields as $field) {
            $this->addField($field['id'], $field['label'], $field['type'], 'beecomm_order_template');
        }
    }



    private function addField(string $id, string $label): void
    {
        add_settings_field(
            $id,
            $label,
            function () use ($id) {
                $this->renderTextInput($id);
            },
            $this->optionName,
            'beecomm_main'
        );

    }

    private function renderTextInput(string $id): void
    {
        $options = get_option($this->optionName);
        $value = esc_attr($options[$id] ?? '');

        echo "<input type='text' name='{$this->optionName}[{$id}]' value='{$value}' class='regular-text' />";
    }

    public function renderSettingsPage(): void
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('BeeComm Integration Settings', 'beecomm-integration'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->optionGroup);
                do_settings_sections($this->optionName);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
