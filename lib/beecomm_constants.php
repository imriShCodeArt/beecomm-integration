<?php
//setting constants
const BEECOMM_INTEGRATION_OPTIONS        = 'beecomm_integration_options';
const BEECOMM_ORDER_STATUS_CRON          = 'beecomm_order_status_cron';
const BEECOMM_ORDER_STATUS_CRON_INTERVAL = 'beecomm_order_status_cron_interval';
const BEECOMM_ADMIN_PHONE                = 'beecomm_admin_phone';
const ORDER_METHOD                       = 'order_method';
const BEECOM_ORDER_PREPARATION_TIME      = 'preparation_time';


//delivery template
const BEECOMM_ORDER_HOLD_TEMPLATE     = 'beecomm_order_hold_template';
const BEECOMM_ORDER_COMPLETE_TEMPLATE = 'beecomm_order_complete_template';
//pickup template
const BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP     = 'beecomm_order_hold_template_pickup';
const BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP = 'beecomm_order_complete_template_pickup';

const BEECOMM_NUMBER_OF_ORDER_TO_PROCESS = 'beecomm_number_of_order_to_process';


//meta constants
const BEECOM_ORDER_STATUS_POST_META_KEY = '_beecomm_order_status';
const BEECOM_ORDER_STATUS_RETRY_COUNT   = '_beecomm_order_status_retry_count';

//api constants
const BEECOM_ORDER_STATUS_API_URL = '/api/v2/services/orderCenter/getOrderStatus';

//order status constants
const BEECOM_ORDER_STATUS_CODE = [
	0 => 'wc-on-hold',
	1 => 'wc-completed',
	2 => 'wc-pending',
];

//default order status template
const BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT = 30;
const BEECOMM_ORDER_GETTER_TAGS                  = 'Order Message Template.<br>
            Use the following tags to customize the message.<br>
            tags will call the getters of the order object, so you can use any getter of the order object.:<br>
            {{id}} - The order ID<br>
            {{status}} - The order status<br>
            {{total}} - The order total<br>
            {{formatted_billing_address}} - Get the Formatted Billing Address<br>
            {{preparation_time}} - Get Order Preparation time<br>
            {{billing_first_name}} - Get the Billing First Name<br>
            {{billing_last_name}} - Get the Billing Last Name<br>
            {{billing_company}} - Get the Billing Company<br>
            {{billing_address_1}} - Get the Billing Address 1<br>
            {{billing_address_2}} - Get the Billing Address 2<br>
            {{billing_city}} - Get the Billing City<br>
            {{billing_state}} - Get the Billing State<br>
            {{billing_postcode}} - Get the Billing Postcode<br>
            {{billing_country}} - Get the Billing Country<br>
            {{billing_email}} - Get the Billing Email<br>
            {{billing_phone}} - Get the Billing Phone<br>
            {{formatted_shipping_address}} - Get the Formatted Shipping Address<br>
            {{shipping_first_name}} - Get the Shipping First Name<br>
            {{shipping_last_name}} - Get the Shipping Last Name<br>
            {{shipping_company}} - Get the Shipping Company<br>
            {{shipping_address_1}} - Get the Shipping Address 1<br>
            {{shipping_address_2}} - Get the Shipping Address 2<br>
            {{shipping_city}} - Get the Shipping City<br>
            {{shipping_state}} - Get the Shipping State<br>
            {{shipping_postcode}} - Get the Shipping Postcode<br>
            {{shipping_country}} - Get the Shipping Country<br>
            {{shipping_phone}} - Get the Shipping Phone<br>
            {{order_items}} - Get the Order Items<br>
            {{order_notes}} - Get the Order Notes<br>
            {{order_date}} - Get the Order Date<br>
            {{order_payment_method}} - Get the Order Payment Method<br>
            {{order_payment_method_title}} - Get the Order Payment Method Title<br>
            {{order_shipping_method}} - Get the Order Shipping Method<br>
            {{order_shipping_method_title}} - Get the Order Shipping Method Title<br>
            {{order_tax}} - Get the Order Tax<br>
            {{order_shipping}} - Get the Order Shipping<br>
            {{order_discount}} - Get the Order Discount<br>
            {{order_total}} - Get the Order Total<br>
            {{order_version}} - Get the Order Version<br>
            {{order_status}} - Get the Order Status<br>
            {{order_currency}} - Get the Order Currency<br>
            {{order_customer_ip}} - Get the Order Customer IP<br>
            ';
const BEECOM_DEFAULT_ORDER_TEMPLATE              = '
	Order Status Updated./n
	Dear {{billing_first_name}}/n
	Your order {{id}} Status has been Updated to {{status}}./n
	Preparation Time: {{preparation_time}}/n
	For more details please visit your account./n
	Thank you for shopping with us.';