<?php
/**
 * Main cron job callback to update order statuses from Beecomm.
 */

function beecomm_cron_update_order_status()
{
	try {
		$orders = get_orders_by_status();

		if (empty($orders)) {
			return;
		}

		foreach ($orders as $order) {
			$status_code = get_beecomm_order_status($order->get_id());

			if (array_key_exists($status_code, BEECOM_ORDER_STATUS_CODE)) {
				$new_status = BEECOM_ORDER_STATUS_CODE[1];

				$order->update_status($new_status);

				$is_sent = send_order_status_sms($order->get_id(), $new_status);

				if ($is_sent) {
					delete_post_meta($order->get_id(), BEECOM_ORDER_STATUS_RETRY_COUNT);
				}
			}
		}
	} catch (Exception $e) {
		wofErrorLog($e->getMessage());
	}
}
