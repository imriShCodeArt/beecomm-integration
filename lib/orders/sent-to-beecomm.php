<?php
add_action('woocommerce_order_status_processing', 'sent_to_beecomm', 1, 1);

function sent_to_beecomm($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order)
        return;

    require_once BEECOMM_PLUGIN_LIB . 'orders/beecomm-payload.php';
    require_once BEECOMM_PLUGIN_LIB . 'orders/send-order.php';

    $payload = beecomm_build_order_payload($order);
    $response = beecomm_send_order($payload);

    update_post_meta($order_id, '_beecomm_order_status', $response);
    $order->add_order_note('נשלח לביקום');
}
