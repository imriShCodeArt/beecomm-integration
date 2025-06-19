<?php
/**
 * Fetches the latest order status from Beecomm's API.
 */

if (!defined('BEECOMM_MAX_RETRY_COUNT')) {
	define('BEECOMM_MAX_RETRY_COUNT', 3);
}

/**
 * Retrieves status code from Beecomm for a given order.
 *
 * @param int $order_id
 * @return int Status code: 1 = confirmed, 0 = rejected/unknown, 2 = retry requested
 */
function get_beecomm_order_status($order_id)
{
	try {
		$response = get_post_meta($order_id, BEECOM_ORDER_STATUS_POST_META_KEY, true);

		if (
			!empty($response)
			&& !empty($response['result'])
			&& $response['result']
			&& !empty($response['orderCenterId'])
		) {
			$api_result = make_beecomm_api_call('POST', BEECOM_ORDER_STATUS_API_URL, [
				'orderCenterId' => $response['orderCenterId'],
			]);

			$decoded = json_decode(stripslashes($api_result), true);

			return (isset($decoded['result']) && $decoded['result']) ? 1 : 0;
		}

		return 0;
	} catch (Exception $e) {
		wofErrorLog($e->getMessage());

		$retry_count = (int) get_post_meta($order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, true);

		if ($retry_count < BEECOMM_MAX_RETRY_COUNT) {
			update_post_meta($order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, $retry_count + 1);
			return 2; // tell cron to retry later
		}

		return 0;
	}
}
