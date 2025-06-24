<?php

namespace Beecomm_Integration;

use WC_Order;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Beecomm_Cron
 *
 * Handles scheduling and execution of a custom WP-Cron job that syncs
 * WooCommerce order statuses with Beecomm’s remote system.
 *
 * @package Beecomm_Integration
 */
class Beecomm_Cron
{
    /**
     * Initializes cron hooks.
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('init', [self::class, 'register']);
        add_filter('cron_schedules', [self::class, 'add_schedule']);
        add_action(BEECOMM_ORDER_STATUS_CRON, [self::class, 'run']);
    }

    /**
     * Registers the scheduled event if not already scheduled.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!wp_next_scheduled(BEECOMM_ORDER_STATUS_CRON)) {
            wp_schedule_event(time(), BEECOMM_ORDER_STATUS_CRON_INTERVAL, BEECOMM_ORDER_STATUS_CRON);
        }
    }

    /**
     * Adds a custom cron interval based on plugin settings.
     *
     * @param array $schedules Existing cron schedules.
     * @return array Modified cron schedules.
     */
    public static function add_schedule(array $schedules): array
    {
        $interval = get_option(BEECOMM_ORDER_STATUS_CRON_INTERVAL);
        $schedules[BEECOMM_ORDER_STATUS_CRON_INTERVAL] = [
            'interval' => $interval ? $interval * 60 : 14400, // Default: 4 hours
            'display' => $interval
                ? __('Every ' . $interval . ' minutes', 'beecomm-integration')
                : __('Every 4 hours', 'beecomm-integration'),
        ];
        return $schedules;
    }

    /**
     * Runs the cron job to sync order statuses with Beecomm.
     *
     * @return void
     */
    public static function run(): void
    {
        try {
            $orders = self::get_orders_by_status();

            if (empty($orders)) {
                return;
            }

            foreach ($orders as $order) {
                $status_code = self::get_beecomm_order_status($order->get_id());

                if (array_key_exists($status_code, BEECOM_ORDER_STATUS_CODE)) {
                    $new_status = BEECOM_ORDER_STATUS_CODE[$status_code];

                    if ($order->get_status() !== $new_status) {
                        $order->update_status($new_status);
                    }

                    if (class_exists(Beecomm_Sms::class)) {
                        $is_sent = Beecomm_Sms::send($order->get_id(), $new_status);
                        if ($is_sent) {
                            delete_post_meta($order->get_id(), BEECOM_ORDER_STATUS_RETRY_COUNT);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            wofErrorLog($e->getMessage());
        }
    }

    /**
     * Returns the last 50 processing/completed WooCommerce orders.
     *
     * @return WC_Order[]
     */
    private static function get_orders_by_status(): array
    {
        return wc_get_orders([
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => ['processing', 'completed'],
        ]);
    }

    /**
     * Queries Beecomm API for current status of an order.
     *
     * Handles retry logic and updates post meta accordingly.
     *
     * @param int $order_id WooCommerce order ID.
     * @return int Status code: 1 (synced), 2 (retrying), 0 (failed).
     */
    private static function get_beecomm_order_status(int $order_id): int
    {
        try {
            $response = get_post_meta($order_id, BEECOM_ORDER_STATUS_POST_META_KEY, true);

            if (!empty($response['result']) && !empty($response['orderCenterId'])) {
                $api_result = make_beecomm_api_call('POST', BEECOM_ORDER_STATUS_API_URL, [
                    'orderCenterId' => $response['orderCenterId'],
                ]);

                $decoded = json_decode(stripslashes($api_result), true);

                return (!empty($decoded['result']) && $decoded['result']) ? 1 : 0;
            }

            return 0;
        } catch (Exception $e) {
            wofErrorLog($e->getMessage());

            $retry_count = (int) get_post_meta($order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, true);

            if ($retry_count < BEECOMM_MAX_RETRY_COUNT) {
                update_post_meta($order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, $retry_count + 1);
                return 2;
            }

            return 0;
        }
    }
}
