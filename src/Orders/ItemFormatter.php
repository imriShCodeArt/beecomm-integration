<?php

namespace BeeComm\Orders;

use WC_Order;
use WC_Order_Item_Product;

final class ItemFormatter
{
    /**
     * Formats all order items into BeeComm-compatible array.
     *
     * @param WC_Order $order
     * @return array
     */
    public static function format(WC_Order $order): array
    {
        $items = [];

        foreach ($order->get_items() as $item_id => $item) {
            if (!$item instanceof WC_Order_Item_Product) {
                continue;
            }

            $product = $item->get_product();

            $items[] = [
                'name' => $item->get_name(),
                'sku' => $product ? $product->get_sku() : null,
                'quantity' => $item->get_quantity(),
                'price' => $order->get_line_total($item, false),
            ];
        }

        return $items;
    }
}
