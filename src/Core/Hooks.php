<?php

namespace BeeComm\Core;

use BeeComm\Admin\OrderColumns;
use BeeComm\Admin\SettingsPage;
use BeeComm\Cron\StatusUpdater;
use BeeComm\Elementor\NewsletterHook;
use BeeComm\Orders\OrderSender;
use BeeComm\SMS\SmsDispatcher;

final class Hooks
{
    public function register(): void
    {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'onPluginsLoaded']);

        $this->registerWooCommerceHooks();
    }


    private function registerWooCommerceHooks(): void
    {
        add_action('woocommerce_order_status_processing', [OrderSender::class, 'sendToBeeComm'], 10, 1);
        add_action('woocommerce_order_status_changed', [SmsDispatcher::class, 'maybeSendSmsOnStatusChange'], 10, 4);
        add_action('beecomm_check_order_status', [StatusUpdater::class, 'checkStatus']);
    }


    public function init(): void
    {
        if (is_admin()) {
            (new SettingsPage())->register();
            (new OrderColumns())->register();
        }

        (new StatusUpdater())->register();
        (new NewsletterHook())->register();
    }

    public function onPluginsLoaded(): void
    {
        load_plugin_textdomain('beecomm-integration', false, basename(dirname(__DIR__, 2)) . '/languages');
    }
}
