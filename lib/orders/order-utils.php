<?php
/**
 * Utility functions for handling WooCommerce orders in Beecomm integration.
 */

/**
 * Retrieves WooCommerce orders with a specific status.
 *
 * @param string|null $status WooCommerce status slug. If null, uses default from constant.
 * @return WC_Order[]|false
 */
function get_orders_by_status($status = null)
{
	// Use default mapped status from constants if none provided.
	if ($status === null) {
		$status = BEECOM_ORDER_STATUS_CODE[2]; // Originally 'wc-processing'
	}

	// 🔧 Strip 'wc-' prefix if present (used for display, not for queries)
	$status = str_replace('wc-', '', $status);

	$limit = get_option(
		BEECOMM_NUMBER_OF_ORDER_TO_PROCESS,
		BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT
	);

	beecomm_log("Fetching orders with status: $status, limit: $limit");

	// ✅ Manually test with hardcoded status (TEMP DEBUG)
	// Uncomment the line below to test if query works with known status
	// $status = 'processing';

	$orders = wc_get_orders([
		'status' => $status,
		'limit' => $limit,
	]);

	if (empty($orders)) {
		beecomm_log("No orders found with status: $status");
		return false;
	}

	beecomm_log("Found " . count($orders) . " orders with status: $status");

	// 🔧 TEMP DEBUG: Log order IDs
	foreach ($orders as $order) {
		beecomm_log('Order ID: ' . $order->get_id() . ' | Status: ' . $order->get_status());
	}

	return $orders;
}

/**
 * Gets the shipping method type (pickup or delivery) for a given order.
 *
 * @param int $order_id
 * @return string 'pickup' or 'delivery'
 */
function get_order_method($order_id)
{
	$allowed_methods = ['pickup', 'delivery'];
	$method = get_post_meta($order_id, ORDER_METHOD, true);

	return (in_array($method, $allowed_methods, true))
		? $method
		: 'pickup';
}
