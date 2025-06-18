<?php

require_once BEECOMM_PLUGIN_LIB . 'orders/order-utils.php';


if (!defined('ABSPATH'))
	exit;

add_action('woocommerce_order_status_changed', 'maybe_send_order_status_sms', 10, 4);

/**
 * Conditional wrapper for SMS sending logic.
 *
 * @param int $order_id
 * @param string $old_status
 * @param string $new_status
 * @param WC_Order $order
 */
function maybe_send_order_status_sms($order_id, $old_status, $new_status, $order)
{
	// Optional: Only send on specific status changes
	$allowed_statuses = ['processing', 'completed', 'on-hold'];



	if (in_array($new_status, $allowed_statuses, true)) {
		send_order_status_sms($order_id, $new_status);
	}
}

/**
 * Sends an SMS based on order status and delivery method.
 *
 * @param int $order_id
 * @param string $order_status WooCommerce status (e.g., 'wc-completed')
 * @return bool
 */
function send_order_status_sms($order_id, $order_status)
{

	try {
		$order = wc_get_order($order_id);
		$order_method = get_order_method($order_id);
		$content = get_order_template_content($order_status, $order_method);
		$content = process_order_status_template($order, $content);

		$sms_api = Wof_Sms_Api::getInstance();

		$phone = ($order_status === BEECOM_ORDER_STATUS_CODE[0])
			? get_option(BEECOMM_ADMIN_PHONE)
			: $order->get_billing_phone();

		if (empty($phone)) {
			beecommLog("Phone number not found for order ID {$order_id}");
			return false;
		}

		$response = $sms_api->sendSms($phone, $content);

		beecommLog([
			'order_id' => $order_id,
			'phone' => $phone,
			'content' => $content,
			'response' => $response,
		]);

		return isset($response['status']) && $response['status'];
	} catch (Exception $e) {
		wofErrorLog($e->getMessage());
		return false;
	}
}
