<?php

namespace BeeComm\Orders;

use WC_Order;
use BeeComm\API\RequestService;
use BeeComm\Orders\OrderPayloadBuilder;
use BeeComm\Utils\Logger;

final class OrderSender
{
    public static function sendToBeeComm(int $order_id): void
    {
        $order = wc_get_order($order_id);

        if (!$order instanceof WC_Order) {
            return;
        }

        $payload = OrderPayloadBuilder::build($order);

        try {
            $response = RequestService::post('/orders', $payload);
            Logger::info("Order #{$order_id} sent to BeeComm.", $response);
        } catch (\Throwable $e) {
            Logger::error("Failed to send order #{$order_id}: " . $e->getMessage());
        }
    }
}
