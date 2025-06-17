<?php

namespace BeeComm\Cron;

use BeeComm\API\StatusSyncService;

final class StatusUpdater
{
    public function register(): void
    {
        // Register on plugin activation (optional if done elsewhere)
        if (!wp_next_scheduled('beecomm_check_order_status')) {
            wp_schedule_event(time(), 'hourly', 'beecomm_check_order_status');
        }

        // Clean up on plugin deactivation — should be called in uninstall hook
        register_deactivation_hook(__FILE__, function () {
            wp_clear_scheduled_hook('beecomm_check_order_status');
        });
    }

    public function checkStatus(): void
    {
        StatusSyncService::syncOrderStatusFromBeecomm();
    }
}
