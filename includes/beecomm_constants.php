<?php

/**
 * === General Options ===
 */
const BEECOMM_INTEGRATION_OPTIONS = 'beecomm_integration_options';
const BEECOMM_NUMBER_OF_ORDER_TO_PROCESS = 'beecomm_number_of_order_to_process';
const BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT = 30;
const ORDER_METHOD = 'order_method';
const BEECOM_ORDER_PREPARATION_TIME = 'preparation_time';

/**
 * === Cron Settings ===
 */
const BEECOMM_ORDER_STATUS_CRON = 'beecomm_order_status_cron';
const BEECOMM_ORDER_STATUS_CRON_INTERVAL = 'beecomm_order_status_cron_interval';

/**
 * === Admin Settings ===
 */
const BEECOMM_ADMIN_PHONE = 'beecomm_admin_phone';

/**
 * === SMS Templates (Delivery & Pickup) ===
 */
const BEECOMM_ORDER_HOLD_TEMPLATE = 'beecomm_order_hold_template';
const BEECOMM_ORDER_COMPLETE_TEMPLATE = 'beecomm_order_complete_template';
const BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP = 'beecomm_order_hold_template_pickup';
const BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP = 'beecomm_order_complete_template_pickup';

/**
 * === Meta Keys ===
 */
const BEECOM_ORDER_STATUS_POST_META_KEY = '_beecomm_order_status';
const BEECOM_ORDER_STATUS_RETRY_COUNT = '_beecomm_order_status_retry_count';

/**
 * === API Constants ===
 */
const BEECOM_ORDER_STATUS_API_URL = '/api/v2/services/orderCenter/getOrderStatus';

/**
 * === Order Status Mapping ===
 * Used to map numeric Beecomm responses to WooCommerce statuses.
 */
const BEECOM_ORDER_STATUS_CODE = [
    0 => 'wc-on-hold',
    1 => 'wc-completed',
    2 => 'wc-processing',
];

/**
 * === Template Description Tags ===
 * These can be used in SMS message templates.
 */
const BEECOMM_ORDER_GETTER_TAGS = 'Order Message Template.<br>
Use the following tags to customize the message.<br>
Tags will call the getters of the order object, so you can use any getter of the order object:<br>
{{id}}, {{status}}, {{total}}, {{formatted_billing_address}}, {{preparation_time}},<br>
{{billing_first_name}}, {{billing_last_name}}, {{billing_company}}, {{billing_address_1}}, {{billing_address_2}},<br>
{{billing_city}}, {{billing_state}}, {{billing_postcode}}, {{billing_country}}, {{billing_email}}, {{billing_phone}},<br>
{{formatted_shipping_address}}, {{shipping_first_name}}, {{shipping_last_name}}, {{shipping_company}},<br>
{{shipping_address_1}}, {{shipping_address_2}}, {{shipping_city}}, {{shipping_state}}, {{shipping_postcode}},<br>
{{shipping_country}}, {{shipping_phone}}, {{order_items}}, {{order_notes}}, {{order_date}},<br>
{{order_payment_method}}, {{order_payment_method_title}}, {{order_shipping_method}},<br>
{{order_shipping_method_title}}, {{order_tax}}, {{order_shipping}}, {{order_discount}},<br>
{{order_total}}, {{order_version}}, {{order_status}}, {{order_currency}}, {{order_customer_ip}}';

/**
 * === Default Message Template ===
 */
const BEECOM_DEFAULT_ORDER_TEMPLATE = '
סטטוס ההזמנה עודכן.
{{billing_first_name}} היקר/ה,
סטטוס ההזמנה שלך מספר {{id}} עודכן ל-{{status}}.
זמן הכנה משוער: {{preparation_time}}
למידע נוסף, ניתן להיכנס לחשבון המשתמש שלך.
תודה שקנית אצלנו.';
