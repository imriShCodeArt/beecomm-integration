<?php

namespace BeeComm\Orders;

use WC_Order;

final class OrderUtils
{
    public static function getCustomerPhone(WC_Order $order): ?string
    {
        return $order->get_billing_phone() ?: null;
    }

    public static function getCustomerName(WC_Order $order): string
    {
        return trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    }

    public static function getOrderStatusSlug(WC_Order $order): string
    {
        return $order->get_status();
    }

    public static function isOrderPaid(WC_Order $order): bool
    {
        return $order->is_paid();
    }

    public static function getShippingMethod(WC_Order $order): ?string
    {
        $methods = $order->get_shipping_methods();
        return $methods ? reset($methods)->get_name() : null;
    }

    // Add more methods here if needed from the original file
}
