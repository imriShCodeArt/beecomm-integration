<?php
/**
 * Builds and processes SMS templates based on order status and method.
 */

/**
 * Returns the message template based on order status and shipping method.
 *
 * @param string $order_status
 * @param string $order_method Either 'pickup' or 'delivery'
 * @return string
 */
function get_order_template_content( $order_status, $order_method = 'pickup' ) {
	$options = get_option( BEECOMM_INTEGRATION_OPTIONS );
	$default_template = __( BEECOM_DEFAULT_ORDER_TEMPLATE, 'beecomm-integration' );

	$template_key = match ( true ) {
		$order_status === BEECOM_ORDER_STATUS_CODE[0] && $order_method === 'pickup' => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP,
		$order_status === BEECOM_ORDER_STATUS_CODE[0]                               => BEECOMM_ORDER_HOLD_TEMPLATE,
		$order_status === BEECOM_ORDER_STATUS_CODE[1] && $order_method === 'pickup' => BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP,
		$order_status === BEECOM_ORDER_STATUS_CODE[1]                               => BEECOMM_ORDER_COMPLETE_TEMPLATE,
		default => null,
	};

	return $template_key ? ( $options[ $template_key ] ?? $default_template ) : $default_template;
}

/**
 * Replaces {{placeholders}} in template with real order data.
 *
 * @param WC_Order $order
 * @param string $content
 * @return string
 */
function process_order_status_template( $order, $content ) {
	preg_match_all( '/{{(.*?)}}/', $content, $matches );

	if ( empty( $matches[1] ) ) {
		return $content;
	}

	foreach ( $matches[1] as $tag ) {
		$replacement = '';

		if ( $tag === BEECOM_ORDER_PREPARATION_TIME ) {
			$replacement = $order->get_meta( BEECOM_ORDER_PREPARATION_TIME ) ?: 20;
		} elseif ( method_exists( $order, 'get_' . $tag ) ) {
			$replacement = $order->{'get_' . $tag}();
		}

		$content = str_replace( '{{' . $tag . '}}', $replacement, $content );
	}

	return $content;
}
