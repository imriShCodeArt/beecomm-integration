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
function get_orders_by_status( $status = null ) {
	if ( $status === null ) {
		$status = BEECOM_ORDER_STATUS_CODE[2]; // Default: pending sync
	}

	$limit = get_option(
		BEECOMM_NUMBER_OF_ORDER_TO_PROCESS,
		BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT
	);

	$orders = wc_get_orders([
		'status' => $status,
		'limit'  => $limit,
	]);

	return ! empty( $orders ) ? $orders : false;
}

/**
 * Gets the shipping method type (pickup or delivery) for a given order.
 *
 * @param int $order_id
 * @return string 'pickup' or 'delivery'
 */
function get_order_method( $order_id ) {
	$allowed_methods = ['pickup', 'delivery'];
	$method = get_post_meta( $order_id, ORDER_METHOD, true );

	return ( in_array( $method, $allowed_methods, true ) )
		? $method
		: 'pickup';
}
