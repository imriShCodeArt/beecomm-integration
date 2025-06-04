<?php
if (!defined('ABSPATH'))
    exit;

require_once BEECOMM_PLUGIN_LIB . 'orders/format-items.php';

/**
 * Build the Beecomm API payload based on a WooCommerce order.
 *
 * @param WC_Order $order
 * @return array
 */
function beecomm_build_order_payload($order)
{
    $order_id = $order->get_id();
    $order_method = get_post_meta($order_id, ORDER_METHOD, true);
    $orderType = ($order_method === 'delivery') ? 2 : 0;

    // Store preparation time or default
    $preparation_time = get_post_meta($order_id, BEECOM_ORDER_PREPARATION_TIME, true) ?: 'N/A';

    // Tip
    $tip = beecomm_extract_tip($order);

    // Remarks: note + serving options
    $remarks = trim(
        ($order->get_customer_note() ? $order->get_customer_note() . ', ' : '') .
        beecomm_format_serving_options($order->get_meta('serving_option', true))
    );

    // Order items + optional toppings
    $items = beecomm_format_order_items($order);

    $base = [
        "OrderType" => $orderType,
        "FirstName" => $order->get_billing_first_name(),
        "LastName" => $order->get_billing_last_name(),
        "Phone" => $order->get_billing_phone(),
        "Remarks" => $remarks,
        "DiscountSum" => $order->get_total_discount(),
        "OuterCompId" => 0,
        "OuterCompOrderId" => $order_id,
        "Items" => $items,
        "Payments" => [
            [
                "PaymentType" => $order->get_payment_method() === 'cod' ? 2 : 6,
                "PaymentSum" => $order->get_total(),
                "PaymentName" => $order->get_payment_method() ?: "אשראי",
                "CreditCard" => "",
                "CreditCardTokef" => "",
                "CreditCardCvv" => "",
                "CreditCardHolderID" => "",
                "PaymentRemark" => $order->get_payment_method() ?: null
            ]
        ],
        "Dinners" => $order->get_meta('number_of_diners'),
        "ArrivalTime" => null,
        "Email" => $order->get_billing_email(),
        "Tip" => $tip,
        "tableNumber" => 0
    ];

    if ($order_method === 'delivery') {
        $base["DeliveryInfo"] = [
            "DeliveryCost" => $order->get_shipping_total(),
            "DeliveryRemarks" => $order->get_customer_note(),
            "City" => $order->get_billing_city(),
            "Street" => $order->get_shipping_address_1(),
            "HomeNum" => $order->get_shipping_address_2(),
            "Appatrtment" => get_post_meta($order_id, 'billing_apartment', true),
            "Floor" => get_post_meta($order_id, 'shipping_floor', true),
            "CompanyName" => $order->get_shipping_company()
        ];
    }

    return [
        'branchId' => '620e0cae2b8fb5052ecb1278',
        'orderInfo' => $base
    ];
}

/**
 * Extract tip amount from order fee lines.
 */
function beecomm_extract_tip($order): float
{
    $fee_lines = $order->get_items('fee');
    foreach ($fee_lines as $item) {
        if ($item->get_name() === 'טיפ לשליח 🚴') {
            return (float) $item->get_total();
        }
    }
    return 0.0;
}

/**
 * Format serving options into remarks string.
 */
function beecomm_format_serving_options($options): string
{
    if (!is_array($options))
        return '';
    $map = [
        'sticks' => 'צ\'ופסטיקס',
        'cutlery' => 'סכ"ום',
        'none' => 'ללא'
    ];
    return implode(', ', array_filter(array_map(fn($o) => $map[$o] ?? '', $options)));
}
