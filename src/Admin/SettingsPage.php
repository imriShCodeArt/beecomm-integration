<?php

namespace BeeComm\Admin;

final class SettingsPage
{
    private string $optionGroup;
    private string $optionName;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/plugin-config.php';

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
        register_setting($this->optionGroup, $this->optionName);

        add_settings_section(
            'beecomm_main',
            __('BeeComm API Settings', 'beecomm-integration'),
            function () {
                echo '<p>' . esc_html__('Configure BeeComm credentials.', 'beecomm-integration') . '</p>';
            },
            $this->optionName
        );


        $this->addField('api_key', __('API Key', 'beecomm-integration'));
        $this->addField('store_id', __('Store ID', 'beecomm-integration'));
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
