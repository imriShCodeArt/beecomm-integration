<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add custom columns to WooCommerce orders list
add_filter('manage_edit-shop_order_columns', function ($columns) {
    $columns['beecomm_status'] = 'סטטוס ביקום';
    $columns['beecomm_orderID'] = 'מספר הזמנה ביקום';
    return $columns;
});

// Populate custom column data
add_action('manage_shop_order_posts_custom_column', function ($column, $post_id) {
    if ('beecomm_status' === $column) {
        $order = wc_get_order($post_id);
        $response = $order->get_meta('_beecomm_order_status');
        $decoded = json_decode(stripslashes($response), true);
        $status = $decoded['message'] ?? '';

        if ($status === 'order accepted') {
            $status = '<span style="color: green;">&#x2714;</span> אושר';
        }

        echo $status;
    }

    if ('beecomm_orderID' === $column) {
        $order = wc_get_order($post_id);
        $response = $order->get_meta('_beecomm_order_status');
        $decoded = json_decode(stripslashes($response), true);
        $orderId = $decoded['orderCenterId'] ?? '';
        echo '<input type="text" value="' . esc_attr($orderId) . '" readonly>';
    }
}, 10, 2);
