<?php
//setup the cron job to update order status from beecomm

add_action( 'init', 'beecomm_cron_init' );
function beecomm_cron_init() {
	if ( ! wp_next_scheduled( BEECOMM_ORDER_STATUS_CRON ) ) {
		wp_schedule_event( time(), BEECOMM_ORDER_STATUS_CRON_INTERVAL, BEECOMM_ORDER_STATUS_CRON );
	}
//	send_order_status_sms( 367, 'wc-completed' );
}

add_filter( 'cron_schedules', 'beecomm_cron_add_schedule' );
function beecomm_cron_add_schedule( $schedules ) {
	$order_status_interval                           = get_option( BEECOMM_ORDER_STATUS_CRON_INTERVAL );
	$schedules[ BEECOMM_ORDER_STATUS_CRON_INTERVAL ] = array(
		'interval' => $order_status_interval ? $order_status_interval * 60 : 3600 * 4,
		'display'  => $order_status_interval ? __( 'Every ' . $order_status_interval . ' minutes' ) : __( 'Every 4 hours' ),
	);

	return $schedules;
}

add_action( BEECOMM_ORDER_STATUS_CRON, 'beecomm_cron_update_order_status' );
function beecomm_cron_update_order_status() {
	try {
		$orders = get_orders_by_status();
		if ( ! $orders ) {
			return;
		}

		foreach ( $orders as $order ) {
			$status = get_beecomm_order_status( $order->get_id() );
			if ( in_array( $status, array_keys( BEECOM_ORDER_STATUS_CODE ) ) ) {
				$status = BEECOM_ORDER_STATUS_CODE[ $status ];
				$order->update_status( $status );
				$isSend = send_order_status_sms( $order->get_id(), $status );
				if ( $isSend ) {
					delete_post_meta( $order->get_id(), BEECOM_ORDER_STATUS_RETRY_COUNT );
				}
			}
		}
	} catch ( Exception $e ) {
		wofErrorLog( $e->getMessage() );
	}
}

//make api call to beecomm
function get_beecomm_order_status( $order_id ) {
	try {
		$beecomm_order_response = get_post_meta( $order_id, BEECOM_ORDER_STATUS_POST_META_KEY, true );

		//check id beecomm response exists and result is true
		if ( ! empty( $beecomm_order_response ) && isset( $beecomm_order_response['result'] ) && $beecomm_order_response['result'] ) {
			if ( isset( $beecomm_order_response['orderCenterId'] ) && ! empty( $beecomm_order_response['orderCenterId'] ) ) {
				$order_status_response = make_beecomm_api_call( "POST", BEECOM_ORDER_STATUS_API_URL, array( 'orderCenterId' => $beecomm_order_response['orderCenterId'] ) );
				if ( $order_status_response ) {
					$decoded = json_decode( stripslashes( $order_status_response ), true );

					return ( isset( $decoded['result'] ) && $decoded['result'] ) ? 1 : 0;
				}
			}
		}

		return 0;
	} catch ( Exception $e ) {
		wofErrorLog( $e->getMessage() );
		$rety_count = get_post_meta( $order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, true );
		$rety_count = $rety_count ? $rety_count : 0;
		if ( $rety_count < 3 ) {
			update_post_meta( $order_id, BEECOM_ORDER_STATUS_RETRY_COUNT, $rety_count + 1 );

			return 2;
		}

		return 0;
	}
}


//get all orders woocommerce with status pending
function get_orders_by_status( $status = BEECOM_ORDER_STATUS_CODE[2] ) {
	$number_of_orders_to_process = get_option( BEECOMM_NUMBER_OF_ORDER_TO_PROCESS, BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT );
	//get all orders from woocommerce
	$wc_orders = wc_get_orders( array(
		'status' => $status,
		'limit'  => $number_of_orders_to_process
	) );
	if ( empty( $wc_orders ) ) {
		return false;
	}

	return $wc_orders;
}


function get_order_method( $order_id ) {
	$order_methods = array(
		'pickup',
		'delivery'
	);
	$order_method = get_post_meta( $order_id, ORDER_METHOD, true );
	if ( empty( $order_method ) || ! in_array( $order_method, $order_methods ) ) {
		$order_method = 'pickup';
	}
	return $order_method;
}


//send sms about order status
function send_order_status_sms( $order_id, $order_status ) {
	try {
		$order        = wc_get_order( $order_id );
		$order_method = get_order_method( $order_id );
		$content      = get_order_template_content( $order_status , $order_method);
		$content      = process_order_status_template( $order, $content );
		$sms_api      = Wof_Sms_Api::getInstance();
		$phone        = $order->get_billing_phone();
		if ( $order_status == BEECOM_ORDER_STATUS_CODE[0] ) {
			//get admin mobile number
			$phone = get_option( BEECOMM_ADMIN_PHONE );
		}
		if ( empty( $phone ) ) {
			beecommLog( 'Phone number not found for order id ' . $order_id );
			return false;
		}
		$response = $sms_api->sendSms( $phone, $content );
		$log	  = array(
			'phone'   => $phone,
			'content' => $content,
			'response' => $response,
			'order_id' => $order_id,
			'order_object' => $order,
		);
		beecommLog( $log );
		if ( isset( $response['status'] ) && $response['status'] ) {
			return true;
		}

		return false;
	} catch ( Exception $e ) {
		wofErrorLog( $e->getMessage() );
		return false;
	}
}

//get order template by order status
function get_order_template_content( $order_status , $order_method = 'pickup' ) {
	$options                = get_option( BEECOMM_INTEGRATION_OPTIONS );
	$default_order_template = __( BEECOM_DEFAULT_ORDER_TEMPLATE, 'beecomm' );

	switch ( $order_status ) {
		case BEECOM_ORDER_STATUS_CODE[0]:
			if ( $order_method == 'pickup' ) {
				return $options[ BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP ] ?? $default_order_template;
			} else {
				return $options[ BEECOMM_ORDER_HOLD_TEMPLATE ] ?? $default_order_template;
			}
		case BEECOM_ORDER_STATUS_CODE[1]:
			if ( $order_method == 'pickup' ) {
				return $options[ BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP ] ?? $default_order_template;
			} else {
				return $options[ BEECOMM_ORDER_COMPLETE_TEMPLATE ] ?? $default_order_template;
			}
		default:
			return $default_order_template;
	}
}

//process order status sms template
function process_order_status_template( $order, $content ) {
	//get all the tags from content {{}}
	preg_match_all( '/{{(.*?)}}/', $content, $matches );
	if ( empty( $matches ) ) {
		return $content;
	}
	//dynamically invoke the method
	foreach ( $matches[1] as $match ) {
		$method = 'get_' . $match;
		if ( method_exists( $order, $method ) ) {
			$content = str_replace( '{{' . $match . '}}', $order->$method(), $content );
		}

		if ( $match == BEECOM_ORDER_PREPARATION_TIME ) {
			$preparation_time = $order->get_meta( BEECOM_ORDER_PREPARATION_TIME );
			$preparation_time = ! empty( $preparation_time ) ? $preparation_time : 20;
			$content = str_replace( '{{' . $match . '}}', $preparation_time, $content );
		}

	}

	return $content;
}

if (!function_exists('beecommLog')) {
	function beecommLog($entry, $type = 'info', $method = '', $mode = 'a', $file = "beecomm-sms-log")
	{
		// Get WordPress uploads directory.
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		// If the entry is array, json_encode.
		if (is_array($entry)) {
			$entry = json_encode($entry);
		}
		// Write the log file.
		$file = $upload_dir . '/' . $file . '.log';
		$file = fopen($file, $mode);
		//format the log entry date::type::method::entry
		$bytes = date('Y-m-d H:i:s') . '::' . $type . '::' . $method . '::' . $entry . "\n";
		fclose($file);
		return $bytes;
	}
}