<?php

namespace BeeComm\Orders;

use WC_Order;
use BeeComm\Orders\ItemFormatter;
use BeeComm\Orders\OrderUtils;

final class OrderPayloadBuilder
{
    public static function build(WC_Order $order): array
    {
        return [
            'order_id' => $order->get_id(),
            'customer_name' => OrderUtils::getCustomerName($order),
            'customer_phone' => OrderUtils::getCustomerPhone($order),
            'status' => OrderUtils::getOrderStatusSlug($order),
            'shipping_method' => OrderUtils::getShippingMethod($order),
            'is_paid' => OrderUtils::isOrderPaid($order),
            'items' => ItemFormatter::format($order),
            'total' => $order->get_total(),
            'created_at' => $order->get_date_created() ? $order->get_date_created()->date('c') : null,
            // Add other fields as needed (address, notes, etc.)
        ];
    }
}
