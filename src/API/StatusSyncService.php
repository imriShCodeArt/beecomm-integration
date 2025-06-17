<?php

namespace BeeComm\API;

use BeeComm\Utils\Logger;

final class StatusSyncService
{
    public static function syncOrderStatusFromBeecomm(): void
    {
        $orders = wc_get_orders([
            'status' => ['processing', 'on-hold'],
            'limit' => 50,
        ]);

        foreach ($orders as $order) {
            try {
                $externalId = $order->get_meta('_beecomm_external_id');
                if (!$externalId) {
                    continue;
                }

                $response = RequestService::get("/orders/{$externalId}/status");
                $newStatus = $response['status'] ?? null;

                if ($newStatus && $order->get_status() !== $newStatus) {
                    $order->update_status($newStatus);
                    Logger::info("Synced order #{$order->get_id()} status to {$newStatus}.");
                }

            } catch (\Throwable $e) {
                Logger::error("Failed to sync order #{$order->get_id()}: " . $e->getMessage());
            }
        }
    }
}
