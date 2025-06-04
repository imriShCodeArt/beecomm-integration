<?php
//add_action('woocommerce_thankyou', 'send_to_beecomm', 10, 1);
add_action('woocommerce_order_status_processing', 'send_to_beecomm', 1, 1);
function send_to_beecomm($order_id)
{
	if (!$order_id) {
		return;
	}
	//mail('annush674@gmail.com', 'ביצוע פעולה מאושר', $response);
	//mail('libimaor@gmail.com', 'ביצוע פעולה', $response);

	$order_method = WC()->session->get('_user_order_method');

	//save order method in order meta
	update_post_meta($order_id, ORDER_METHOD, $order_method);

	// Allow code execution only once
	$order = wc_get_order($order_id);
	if ($order->is_paid()) {
		$paid = __('yes');
	} else {
		$paid = __('no');
	}

	$first_name = $order->get_billing_first_name();
	$last_name = $order->get_billing_last_name();
	$paymentSum = $order->get_total();
	$apartment = get_post_meta($order_id, 'billing_apartment', true);
	$apartment = $apartment && $apartment != '&#8220;&#8221;' ? $apartment : get_post_meta($order_id, 'billing_apartment', true);

	$floor = get_post_meta($order_id, 'shipping_floor', true);
	$floor = $floor && $floor != '&#8220;&#8221;' ? $floor : get_post_meta($order_id, 'billing_floor', true);
	$payment_method = $order->get_payment_method();

	$orderType = ($order_method == 'delivery') ? 2 : 0;

	//Tip
	$order_fee_line_items = $order->get_items('fee');
	$tip = 0.0; // Default value if tip is not found
	foreach ($order_fee_line_items as $item) {
		if ($item->get_name() === 'טיפ לשליח 🚴') {
			$tip = $item->get_total();
			break;
		}
	}

	// Retrieve selected serving options from order meta
	$serving_options = $order->get_meta('serving_option');

	// Prepare serving options remarks
	$serving_options_remarks = '';
	if (!empty($serving_options)) {
		foreach ($serving_options as $option) {
			switch ($option) {
				case 'sticks':
					$serving_options_remarks .= __('צ\'ופסטיקס', 'your-text-domain') . ', ';
					break;
				case 'cutlery':
					$serving_options_remarks .= __('סכ"ום', 'your-text-domain') . ', ';
					break;
				case 'none':
					$serving_options_remarks .= __('ללא', 'your-text-domain') . ', ';
					break;
			}
		}

	}
	// Dinners
	$number_of_diners = $order->get_meta('number_of_diners');

	$arr = array(
		"OrderType" => $orderType,
		"FirstName" => $first_name ?? $order->get_billing_first_name(),
		"LastName" => $last_name ?? $order->get_billing_last_name(),
		"Phone" => $order->get_billing_phone(),
		"Remarks" => ($order->get_customer_note() ? $order->get_customer_note() . ', ' : '') . $serving_options_remarks,
		"DiscountSum" => $order->get_total_discount(),
		"OuterCompId" => 0,
		"OuterCompOrderId" => $order_id,
		"Items" => array(),
		"Payments" => array(
			array(
				"PaymentType" => $payment_method == 'cod' ? 2 : 6,
				"PaymentSum" => $paymentSum,
				"PaymentName" => $payment_method ?? "אשראי",
				"CreditCard" => "",
				"CreditCardTokef" => "",
				"CreditCardCvv" => "",
				"CreditCardHolderID" => "",
				"PaymentRemark" => $payment_method ?? null
			)

		),
		"Dinners" => $number_of_diners,
		"ArrivalTime" => null,
		"Email" => $order->get_billing_email(),
		"Tip" => $tip,
		"tableNumber" => 0
	);

	if ($order_method == 'delivery') {
		$arr["DeliveryInfo"] = array(
			"DeliveryCost" => $order->get_shipping_total(),
			"DeliveryRemarks" => $order->get_customer_note(),
			"City" => $order->get_billing_city(),
			"Street" => $order->get_shipping_address_1(),
			"HomeNum" => $order->get_shipping_address_2(),
			"Appatrtment" => $apartment,
			"Floor" => $floor,
			"CompanyName" => $order->get_shipping_company()
		);
	} else {
		$order->add_order_note('<span style="background:orangered; color: white;">איסוף עצמי</span>');
		$arr['Remarks'] = $arr['Remarks'] ? $arr['Remarks'] . ' , ' . 'איסוף' : 'איסוף';
	}

	// Loop through order items
	foreach ($order->get_items() as $item_id => $item) {
		//Get the WC_Product object
		$product = $item->get_product();

		$arr_item = array(
			"NetID" => $product->get_sku(),
			"ItemName" => $item->get_name(),
			"Quantity" => $item->get_quantity(),
			"Price" => $item->get_subtotal(),
			"UnitPrice" => $product->get_price(),
			"BelongTo" => null,
			"BillRemarks" => "מספר הזמנה באתר: {$order_id}",
			"SubItems" => null,
		);


		$meta = $item->get_meta_data();
		if ($meta) {
			$toppings = [];
			$item_remarks = '';
			foreach ($meta as $field) {
				$extra = $field->get_data();
				if ($extra['key'] == '_exoptions') {
					foreach ($extra['value'] as $toppingField) {
						if (isset($toppingField['_type']) && $toppingField['_type'] !== 'text') {
							$topping_price = $toppingField['price'] ?? '0.00';
							$topping_sku = $toppingField['sku'] ?? '';
							$toppings[] = array(
								"NetID" => $topping_sku,
								"ItemName" => $toppingField['value'] ?? '',
								"Quantity" => 1.0,
								"Price" => $topping_price,
								"UnitPrice" => $topping_price,
							);
						} else {
							$item_remarks = "הערות למנה: " . $toppingField['value'];
						}
					}
				}
			}
			$arr_item['Toppings'] = $toppings;
			$arr_item['Remarks'] = $item_remarks;
		}
		$arr['Items'][] = $arr_item;
	}

	$arr = array(
		'branchId' => '620e0cae2b8fb5052ecb1278',
		'orderInfo' => $arr
	);
	wofErrorLog($arr, 'beeCommOrder');
	error_log('beeCommOrder :  ' . json_encode($arr));
	$url = beecomm_get_base_url() . '/api/v2/services/orderCenter/pushOrder';

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($arr),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'access_token: ' . beecomm_get_auth()
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);

	update_post_meta($order_id, '_beecomm_order_status', $response);

	//mail('annush674@gmail.com', 'ביצוע פעולה מאושר', $response);
	//mail('libimaor@gmail.com', 'ביצוע פעולה מאושר', $response);
	$order->add_order_note('<span style="background:green; color: white;">איסוף עצמי</span>');


}

function make_beecomm_api_call($method, $endpoint, $data = null)
{
	$url = beecomm_get_base_url() . $endpoint;
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'access_token: ' . beecomm_get_auth()
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

function beecomm_get_base_url()
{
	return 'https://biapp.beecomm.co.il:8094';
}

function beecomm_get_client_data()
{
	$options = get_option('beecomm_integration_options');
	$data = array(
		"client_id" => $options['client_id'],
		"client_secret" => $options['client_secret']
	);
	return json_encode($data);
}

function beecomm_get_auth()
{

	$url = beecomm_get_base_url() . '/v2/oauth/token';

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => beecomm_get_client_data(),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		),
	));

	$response = curl_exec($curl);

	$formatted = json_decode($response);
	curl_close($curl);
	//	return ;
	return $formatted->access_token;
}


add_action('elementor_pro/forms/new_record', function ($record, $handler) {
	$form_name = $record->get_form_settings('form_name');
	if ('newslatter' !== $form_name) {
		return;
	}

	$raw_fields = $record->get('fields');
	$fields = [];
	foreach ($raw_fields as $id => $field) {
		$fields[$id] = $field['value'];
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://ui-api.pulseem.coM/api/v1/ClientsApi/AddClients?APIKEY',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => '{
		"clientsData": [
		{
		"email": "##Email##",
		"needOptin": true,
		"overwrite": true
		}
		],
		"groupIds": [
		640213
		]
		}',
		CURLOPT_HTTPHEADER => array(
			'APIKEY: 6xaV90l9UOoKXlZmdXmDPQ==',
			'Content-Type: application/json'
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	echo $response;
});

add_filter('manage_edit-shop_order_columns', function ($columns) {
	$columns['beecomm_status'] = 'סטטוס ביקום';
	$columns['beecomm_orderID'] = 'מספר הזמנה ביקום';
	return $columns;
});

add_action('manage_shop_order_posts_custom_column', 'populate_custom_order_column_data', 10, 2);
function populate_custom_order_column_data($column, $post_id)
{
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
		echo '<input type="text" value="' . $orderId . '" readonly>';
	}
}
