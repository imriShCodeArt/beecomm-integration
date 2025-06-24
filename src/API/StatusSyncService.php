<?php

namespace BeeComm\API;

use BeeComm\Utils\Logger;
use WC_Order;

final class StatusSyncService
{
    public static function syncOrderStatusFromBeecomm(): array
    {
        $reviewed = [];
        $skipped = 0;

        $orders = wc_get_orders([
            'status' => ['wc-processing', 'wc-completed'],
            'limit' => 50,
        ]);

        Logger::info('🕵️ Fetching WooCommerce orders for status sync', [
            'status_filter' => ['wc-processing', 'wc-completed'],
            'found_orders_count' => count($orders),
            'order_ids' => array_map(fn(WC_Order $order) => $order->get_id(), $orders),
        ]);

        foreach ($orders as $order) {
            $orderId = $order->get_id();
            $externalId = $order->get_meta('_beecomm_external_id');

            if (empty($externalId)) {
                Logger::info("⚠️ Skipping order #{$orderId}: Missing _beecomm_external_id");
                $skipped++;
                continue;
            }

            try {
                $endpoint = "/orders/{$externalId}/status";
                Logger::info("🔗 Fetching BeeComm status", [
                    'order_id' => $orderId,
                    'external_id' => $externalId,
                    'endpoint' => $endpoint,
                ]);

                $response = RequestService::get($endpoint);
                $newStatus = $response['status'] ?? null;

                Logger::info('📩 Received status response', [
                    'order_id' => $orderId,
                    'external_id' => $externalId,
                    'response' => $response,
                ]);

                if ($newStatus && $order->get_status() !== $newStatus) {
                    $order->update_status($newStatus);
                    Logger::info("✅ Updated order #{$orderId} status to '{$newStatus}'");
                }

                $reviewed[] = [
                    'order_id' => $orderId,
                    'external_id' => $externalId,
                    'status' => $newStatus ?: 'unchanged',
                ];
            } catch (\Throwable $e) {
                Logger::error("❌ Failed to sync order #{$orderId}", [
                    'external_id' => $externalId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Logger::info('🔍 Status sync summary', [
            'fetched_orders' => count($orders),
            'processed_orders' => count($reviewed),
            'skipped_orders' => $skipped,
        ]);

        Logger::info('✅ Status sync completed', [
            'orders_reviewed' => $reviewed,
            'total' => count($reviewed),
        ]);

        return $reviewed;
    }
}
