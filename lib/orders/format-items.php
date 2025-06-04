<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Formats WooCommerce order items for Beecomm payload.
 *
 * @param WC_Order $order
 * @return array
 */
function beecomm_format_order_items(WC_Order $order): array
{
    $items = [];

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if (!$product)
            continue;

        $arr_item = [
            "NetID" => $product->get_sku(),
            "ItemName" => $item->get_name(),
            "Quantity" => $item->get_quantity(),
            "Price" => $item->get_subtotal(),
            "UnitPrice" => $product->get_price(),
            "BelongTo" => null,
            "BillRemarks" => "מספר הזמנה באתר: " . $order->get_id(),
            "SubItems" => null,
            "Toppings" => [],
            "Remarks" => ''
        ];

        list($toppings, $remarks) = beecomm_extract_toppings_and_remarks($item);
        $arr_item['Toppings'] = $toppings;
        $arr_item['Remarks'] = $remarks;

        $items[] = $arr_item;
    }

    return $items;
}

/**
 * Extract toppings and remarks from product meta data.
 *
 * @param WC_Order_Item_Product $item
 * @return array [array $toppings, string $remarks]
 */
function beecomm_extract_toppings_and_remarks($item): array
{
    $toppings = [];
    $remarks = '';
    $meta = $item->get_meta_data();

    foreach ($meta as $field) {
        $extra = $field->get_data();
        if ($extra['key'] === '_exoptions' && is_array($extra['value'])) {
            foreach ($extra['value'] as $toppingField) {
                if (!isset($toppingField['_type']))
                    continue;

                if ($toppingField['_type'] !== 'text') {
                    $toppings[] = [
                        "NetID" => $toppingField['sku'] ?? '',
                        "ItemName" => $toppingField['value'] ?? '',
                        "Quantity" => 1.0,
                        "Price" => $toppingField['price'] ?? '0.00',
                        "UnitPrice" => $toppingField['price'] ?? '0.00',
                    ];
                } else {
                    $remarks = "הערות למנה: " . $toppingField['value'];
                }
            }
        }
    }

    return [$toppings, $remarks];
}
