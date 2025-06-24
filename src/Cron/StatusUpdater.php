<?php

namespace BeeComm\Cron;

use BeeComm\API\StatusSyncService;
use BeeComm\Utils\Logger;
use BeeComm\Config\Constants;

final class StatusUpdater
{
    public function register(): void
    {
        add_action('init', [$this, 'scheduleCron']);
        add_filter('cron_schedules', [$this, 'addCustomSchedule']);
        add_action(Constants::CRON_HOOK_CHECK_STATUS, [self::class, 'checkStatus']);
    }

    public function scheduleCron(): void
    {
        if (!wp_next_scheduled(Constants::CRON_HOOK_CHECK_STATUS)) {
            wp_schedule_event(time(), Constants::CRON_INTERVAL_FOUR_HOURS, Constants::CRON_HOOK_CHECK_STATUS);
        }
    }

    public function addCustomSchedule(array $schedules): array
    {
        $schedules[Constants::CRON_INTERVAL_FOUR_HOURS] = [
            'interval' => 4 * Constants::HOUR_IN_SECONDS,
            'display' => __('Every 4 Hours', Constants::TEXT_DOMAIN),
        ];
        return $schedules;
    }

    public static function checkStatus(): void
    {
        $reviewedOrders = StatusSyncService::syncOrderStatusFromBeecomm();

        Logger::info('✅ Cron executed: ' . Constants::CRON_HOOK_CHECK_STATUS . ' completed.', [
            'orders_reviewed' => $reviewedOrders,
            'total' => count($reviewedOrders),
        ]);
    }
}
