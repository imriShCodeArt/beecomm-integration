<?php
	//add_action('woocommerce_thankyou', 'send_to_beecomm', 10, 1);
	add_action( 'woocommerce_order_status_processing', 'send_to_beecomm', 1, 1 );
	function send_to_beecomm( $order_id ) {
		if ( ! $order_id ) {
			return;
		}
				
		// Allow code execution only once
		$order = wc_get_order( $order_id );
		if ( $order->is_paid() ) {
			$paid = __( 'yes' );
			} else {
			$paid = __( 'no' );
		}
		
		
		$first_name = !empty($order->get_billing_first_name()) ? $order->get_billing_first_name() : 'אנונימי';
		$last_name = !empty($order->get_billing_last_name()) ? $order->get_billing_last_name() : 'לשולחן';
		$paymentSum = $order->get_total();
		$apartment  = get_post_meta( $order_id, 'billing_apartment', true );
		$apartment  = $apartment && $apartment != '&#8220;&#8221;' ? $apartment : get_post_meta( $order_id, 'billing_apartment', true );
		$floor = get_post_meta( $order_id, 'shipping_floor', true );
		$floor = $floor && $floor != '&#8220;&#8221;' ? $floor : get_post_meta( $order_id, 'billing_floor', true );
		$payment_method = $order->get_payment_method();
		
		$takeaway_preparation_time = $order->get_meta('takeaway_preparation_time');
		$delivery_arrival_time = $order->get_meta('delivery_arrival_time');

		$number_of_diners = $order->get_meta('number_of_diners');
		
		//Table number - MANDATORY!
		$table_number = $order->get_meta('table_number') ?: '0';
		
		
		
	//	$shipping_method = $order->get_shipping_method();
		$shipping_methods = $order->get_shipping_methods();
		$shipping_display =	$order->get_shipping_to_display();
		
		    // Log details of each shipping method
    foreach ($shipping_methods as $index => $shipping_method) {
        $method_id = $shipping_method->get_method_id(); // Get the method ID
        
        // Log the details
        error_log("Shipping Method $index:");
        error_log("Method ID: $method_id");
    }

		
		// Order Method
		$order_method = $order->get_meta( 'exwfood_order_method' );

		// 0 = takeaway | 1 = dinein | 2 = delivery
		if ($order_method == 'takeaway') {
			$orderType = 0;
		} elseif ($order_method == 'dinein') {
			$orderType = 1;
		} elseif ($order_method == 'delivery') {
			$orderType = 2;
		} else {
			$orderType = 2;
		}

		error_log('Shipping method = ' . $shipping_method);
		error_log('Shipping methods = ' . $shipping_methods);
		
		
		//Tip
		$order_fee_line_items = $order->get_items('fee');
		$tip = 0.0; // Default value if tip is not found
		foreach ($order_fee_line_items as $item) {
			if ($item->get_name() === 'טיפ לשליח 🚴') {
				$tip = $item->get_total();
				break;
			}
		}
		
		// Serving options
		$serving_options = $order->get_meta('serving_option');
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
		
		// Order Remarks
		$remarks = "הזמנה מהאתר {$order_id}";

		// Determine and append time remark
		$timeRemark = !empty($takeaway_preparation_time) ? $takeaway_preparation_time : $delivery_arrival_time;
		if (!empty($timeRemark)) {
			$remarks .= ", משלוח עד " . $timeRemark;
		}

		// Append serving options and customer note if available
		if (!empty($serving_options_remarks)) {
			$remarks .= ", {$serving_options_remarks}";
		}
		if (!empty($order->get_customer_note())) {
			$remarks .= ", הערות משלוח: {$order->get_customer_note()}";
		}
		
		$arr = array(
		"OrderType" => $orderType,
        "FirstName"        => $first_name ?? $order->get_billing_first_name(),
        "LastName"         => $last_name ?? $order->get_billing_last_name(),
        "Phone"            => $order->get_billing_phone() ?: $table_number,
	//	"Remarks" => "הזמנה מהאתר {$order_id}" . (", משלוח עד " . ($takeaway_preparation_time ?: $delivery_arrival_time)) . ($serving_options_remarks ? ", {$serving_options_remarks}" : "") . ($order->get_customer_note() ? ", הערות משלוח: {$order->get_customer_note()}" : ""),
		"Remarks" 		   => $remarks,
        "DiscountSum"      => $order->get_discount_total(),
        "OuterCompId"      => 0,
        "OuterCompOrderId" => $order_id,
        "Items"            => array(),
        "Payments"         => array(
		array(
		"PaymentType"        => $payment_method == "cod" ? 2 : 6,
		"PaymentSum"         => $paymentSum,
		"PaymentName"        => $payment_method ?? "אשראי",
		"CreditCard"         => "",
		"CreditCardTokef"    => "",
		"CreditCardCvv"      => "",
		"CreditCardHolderID" => "",
		"PaymentRemark"      => $payment_method ?? null
		)
		
        ),
        "Dinners"          => $number_of_diners,
        "ArrivalTime"      => null,
        "Email"            => $order->get_billing_email(),
        "Tip" => $tip,
        "tableNumber"      => $table_number ?? '0',
		);
		
		if ($method_id == 'szbd-shipping-method' || $order_method == 'delivery') {
			$arr["DeliveryInfo"]= array(
            "DeliveryCost"    => $order->get_shipping_total(),
        //    "DeliveryRemarks" => $order->get_customer_note(),
		    "DeliveryRemarks" => $order->get_customer_note(),
            "City"            => $order->get_billing_city(),
            "Street"          => $order->get_shipping_address_1(),
            "HomeNum"         => $order->get_shipping_address_2(),
            "Appatrtment"       => $apartment,
            "Floor"           => $floor,
            "CompanyName"     => $order->get_shipping_company()
			);
			}
		 elseif ($order_method == 'takeaway'){
			//$order->add_order_note( '<span style="backgrond:orangered; color: white;">איסוף עצמי</span>' );
			$arr['Remarks'] = $arr['Remarks'] ? $arr['Remarks'] . ' , ' . 'איסוף' : 'איסוף';
			} elseif ($order_method == 'dinein'){
				$arr['Remarks'] = $arr['Remarks'] ? $arr['Remarks'] . ' | ' . 'שולחן' : 'שולחן';
			}
		
		// Loop through order items
		foreach ( $order->get_items() as $item_id => $item ) {
			//Get the WC_Product object
			$product = $item->get_product();
			
			$arr_item = array(
            "NetID"       => $product->get_sku(),
            "ItemName"    => $item->get_name(),
            "Quantity"    => $item->get_quantity(),
            "Price"       => $item->get_subtotal(),
            "UnitPrice"   => $product->get_price(),
            "BelongTo"    => null,
            "BillRemarks" => null,
            "SubItems"    => null,
			);
			
			
			$meta = $item->get_meta_data();
			if ($meta) {
				$toppings = [];
				$item_remarks = '';
				foreach ($meta as $field){
					$extra = $field->get_data();
					if($extra['key'] == '_exoptions'){
						foreach ($extra['value'] as $toppingField){
							if($toppingField['_type'] !== 'text'){
								$topping_price = $toppingField['price'] ?? '0.00';
								$topping_sku = $toppingField['sku'] ?? '';
								$toppings[]    = array(
								"NetID"     => $topping_sku,
								"ItemName"  => $toppingField['value'] ?? '',
								"Quantity"  => 1.0,
								"Price"     => 0,
								"UnitPrice" => 0,
								);
								} else {
								$item_remarks = "הערות: " . $toppingField['value'];
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
		error_log( 'beeCommOrder :  '.json_encode($arr));
		$url = beecomm_get_base_url() . '/api/v2/services/orderCenter/pushOrder';
		
		$curl = curl_init();
		
		curl_setopt_array( $curl, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($arr),
        CURLOPT_HTTPHEADER     => array(
		'Content-Type: application/json',
		'access_token: '. beecomm_get_auth()
        ),
		) );
		
		$response = curl_exec( $curl );
		
		curl_close( $curl );
		
		update_post_meta($order_id, '_beecomm_order_status', $response);
		
		$order->add_order_note( 'נשלח לביקום' );
		
	}
	
	function beecomm_get_base_url() {
		return 'https://biapp.beecomm.co.il:8094';
	}
	
	function beecomm_get_client_data() {
		$options = get_option( 'beecomm_integration_options' );
		$data    = array(
        "client_id"     => $options['client_id'],
        "client_secret" => $options['client_secret']
		);
		return json_encode($data);
	}
	
	function beecomm_get_auth() {
		
		$url     = beecomm_get_base_url() . '/v2/oauth/token';
		
		$curl = curl_init();
		
		curl_setopt_array( $curl, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => beecomm_get_client_data(),
        CURLOPT_HTTPHEADER     => array(
		'Content-Type: application/json'
        ),
		) );
		
		$response = curl_exec( $curl );
		
		$formatted = json_decode( $response );
		curl_close($curl);
		//	return ;
		return $formatted->access_token;
	}
	
	
	add_filter('manage_edit-shop_order_columns', function ($columns){
		$columns['beecomm_status'] = 'סטטוס ביקום';
		$columns['beecomm_orderID'] = 'מספר הזמנה ביקום';
		return $columns;
	});
	
	add_action( 'manage_shop_order_posts_custom_column', 'populate_custom_order_column_data', 10, 2 );
	
	function populate_custom_order_column_data( $column, $post_id ) {
		if ( 'beecomm_status' === $column ) {
			$order = wc_get_order( $post_id );
			$response = $order->get_meta( '_beecomm_order_status' );
			$decoded = json_decode(stripslashes($response), true);
			$status = $decoded['message'] ?? '';
			
			if ($status == 'order accepted') {
				$status = '<span class="dashicons dashicons-yes"></span> התקבל בביקום';
			}
			
			echo $status;
		}

		if ( 'beecomm_orderID' === $column ) {
			$order = wc_get_order( $post_id );
			$response = $order->get_meta( '_beecomm_order_status' );
			$decoded = json_decode(stripslashes($response), true);
			$orderId = $decoded['orderCenterId'] ?? '';
			echo $orderId;
		}
	}

/*
add_action('add_meta_boxes', 'add_beecomm_custom_button');

function add_beecomm_custom_button() {
    add_meta_box('beecomm_custom_button', 'פעולות ביקום', 'beecomm_custom_button_callback', 'shop_order', 'side', 'high');
}

function beecomm_custom_button_callback($post) {
    echo '<button id="beecomm-rerun" class="button button-primary button-large">שידור לביקום מחדש</button>';
    ?>
    <script type="text/javascript">
        jQuery('#beecomm-rerun').on('click', function() {
            var data = {
                action: 'beecomm_rerun_function',
                order_id: '<?php echo $post->ID; ?>'
            };
            jQuery.post(ajaxurl, data, function(response) {
                alert('בוצע ' + data.order_id);
            });
        });
    </script>
    <?php
}

add_action('wp_ajax_beecomm_rerun_function', 'beecomm_rerun_function');

function beecomm_rerun_function() {
    $order_id = $_POST['order_id'];
    // Reset the processed flag
  //  $order = wc_get_order( $order_id );
  //  $order->update_meta_data('_beecomm_processed', 'no');
 //   $order->save();

    // Run the function
    send_to_beecomm($order_id);

    wp_die(); // this is required to terminate immediately and return a proper response
}*/