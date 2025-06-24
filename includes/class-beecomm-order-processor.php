<?php

namespace Beecomm_Integration;

use WC_Order;
use WC_Order_Item_Product;

/**
 * Class Beecomm_Order_Processor
 *
 * Handles building and sending WooCommerce orders to Beecomm's external API.
 *
 * @package Beecomm_Integration
 */
class Beecomm_Order_Processor
{
    /**
     * Maximum retry attempts for failed transmissions.
     */
    public const MAX_RETRY_COUNT = 3;

    /**
     * Static branch ID for Beecomm API.
     */
    private const BRANCH_ID = '620e0cae2b8fb5052ecb1278';

    /**
     * Initiates order processing for a given order ID.
     *
     * @param int $order_id
     * @return void
     */
    public static function process_order($order_id): void
    {
        $order = wc_get_order($order_id);
        if (!$order instanceof WC_Order) {
            return;
        }

        $payload = self::build_payload($order);
        $response = self::send_to_api($payload);

        $decoded = json_decode($response, true);
        $order->update_meta_data('_beecomm_order_status', wp_json_encode($decoded));
        $order->add_order_note('נשלח לביקום');
        $order->save();
    }

    /**
     * Builds the payload array to be sent to the Beecomm API.
     *
     * @param WC_Order $order
     * @return array
     */
    private static function build_payload(WC_Order $order): array
    {
        $order_id = $order->get_id();
        $order_method = get_post_meta($order_id, 'ORDER_METHOD', true);
        $orderType = ($order_method === 'delivery') ? 2 : 0;
        $tip = self::extract_tip($order);
        $remarks = trim(
            ($order->get_customer_note() ? $order->get_customer_note() . ', ' : '') .
            self::format_serving_options($order->get_meta('serving_option', true))
        );
        $items = self::format_order_items($order);

        $order_info = [
            "OrderType" => $orderType,
            "FirstName" => $order->get_billing_first_name(),
            "LastName" => $order->get_billing_last_name(),
            "Phone" => $order->get_billing_phone(),
            "Remarks" => $remarks,
            "DiscountSum" => $order->get_total_discount(),
            "OuterCompId" => 0,
            "OuterCompOrderId" => $order_id,
            "Items" => $items,
            "Payments" => [
                [
                    "PaymentType" => $order->get_payment_method() === 'cod' ? 2 : 6,
                    "PaymentSum" => $order->get_total(),
                    "PaymentName" => $order->get_payment_method() ?: "אשראי",
                    "CreditCard" => "",
                    "CreditCardTokef" => "",
                    "CreditCardCvv" => "",
                    "CreditCardHolderID" => "",
                    "PaymentRemark" => $order->get_payment_method() ?: null,
                ]
            ],
            "Dinners" => $order->get_meta('number_of_diners'),
            "ArrivalTime" => null,
            "Email" => $order->get_billing_email(),
            "Tip" => $tip,
            "tableNumber" => 0
        ];

        if ($order_method === 'delivery') {
            $order_info["DeliveryInfo"] = [
                "DeliveryCost" => $order->get_shipping_total(),
                "DeliveryRemarks" => $order->get_customer_note(),
                "City" => $order->get_billing_city(),
                "Street" => $order->get_shipping_address_1(),
                "HomeNum" => $order->get_shipping_address_2(),
                "Appatrtment" => get_post_meta($order_id, 'billing_apartment', true),
                "Floor" => get_post_meta($order_id, 'shipping_floor', true),
                "CompanyName" => $order->get_shipping_company(),
            ];
        }

        return [
            'branchId' => self::BRANCH_ID,
            'orderInfo' => $order_info
        ];
    }

    /**
     * Extracts tip value from fee items.
     *
     * @param WC_Order $order
     * @return float
     */
    private static function extract_tip(WC_Order $order): float
    {
        foreach ($order->get_items('fee') as $item) {
            if ($item->get_name() === 'טיפ לשליח 🚴') {
                return (float) $item->get_total();
            }
        }
        return 0.0;
    }

    /**
     * Formats the serving options field into a human-readable string.
     *
     * @param mixed $options
     * @return string
     */
    private static function format_serving_options($options): string
    {
        if (!is_array($options)) {
            return '';
        }

        $map = ['sticks' => 'צ\'ופסטיקס', 'cutlery' => 'סכ"ום', 'none' => 'ללא'];
        return implode(', ', array_filter(array_map(fn($o) => $map[$o] ?? '', $options)));
    }

    /**
     * Formats the order items for the Beecomm payload.
     *
     * @param WC_Order $order
     * @return array
     */
    private static function format_order_items(WC_Order $order): array
    {
        $items = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            list($toppings, $remarks) = self::extract_toppings_and_remarks($item);

            $items[] = [
                "NetID" => $product->get_sku(),
                "ItemName" => $item->get_name(),
                "Quantity" => $item->get_quantity(),
                "Price" => $item->get_subtotal(),
                "UnitPrice" => $product->get_price(),
                "BelongTo" => null,
                "BillRemarks" => "מספר הזמנה באתר: " . $order->get_id(),
                "SubItems" => null,
                "Toppings" => $toppings,
                "Remarks" => $remarks,
            ];
        }

        return $items;
    }

    /**
     * Extracts toppings and remarks from an order item.
     *
     * @param WC_Order_Item_Product $item
     * @return array{0: array, 1: string}
     */
    private static function extract_toppings_and_remarks($item): array
    {
        $toppings = [];
        $remarks = '';

        foreach ($item->get_meta_data() as $field) {
            $data = $field->get_data();

            if ($data['key'] === '_exoptions' && is_array($data['value'])) {
                foreach ($data['value'] as $opt) {
                    if (!isset($opt['_type'])) {
                        continue;
                    }

                    if ($opt['_type'] !== 'text') {
                        $toppings[] = [
                            "NetID" => $opt['sku'] ?? '',
                            "ItemName" => $opt['value'] ?? '',
                            "Quantity" => 1.0,
                            "Price" => $opt['price'] ?? '0.00',
                            "UnitPrice" => $opt['price'] ?? '0.00',
                        ];
                    } else {
                        $remarks = "הערות למנה: " . $opt['value'];
                    }
                }
            }
        }

        return [$toppings, $remarks];
    }

    /**
     * Sends the prepared payload to Beecomm's API endpoint.
     *
     * @param array $payload
     * @return string
     */
    private static function send_to_api(array $payload): string
    {
        $authToken = self::get_auth();
        $url = self::get_base_url() . '/api/v2/services/orderCenter/pushOrder';

        if (empty($authToken)) {
            error_log('[Beecomm] Access token is empty. Cannot send order.');
            return '{}';
        }

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            error_log('[Beecomm] API request failed: Invalid URL - ' . $url);
            return '{}';
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $authToken,
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            error_log('[Beecomm] API request failed: ' . curl_error($curl));
        } else {
            error_log('[Beecomm] API response: ' . $response);
        }

        curl_close($curl);

        return $response ?: '{}';
    }

    /**
     * Authenticates and retrieves an access token from Beecomm.
     *
     * @return string|null
     */
    private static function get_auth(): ?string
    {
        $url = self::get_base_url() . '/v2/oauth/token';
        $credentials = json_encode(self::get_client_credentials());

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            error_log('[Beecomm] Token URL is invalid: ' . $url);
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $credentials,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            error_log('[Beecomm] Auth request failed.');
            return null;
        }

        $result = json_decode($response);
        $token = $result->access_token ?? null;

        if (!$token) {
            error_log('[Beecomm] Failed to retrieve access token. Response: ' . $response);
        } else {
            error_log('[Beecomm] Access token retrieved.');
        }

        return $token;
    }

    /**
     * Gets the base URL for the Beecomm API.
     *
     * @return string
     */
    private static function get_base_url(): string
    {
        return "https://biapp.beecomm.co.il:8094";
    }

    /**
     * Retrieves the client credentials from plugin settings.
     *
     * @return array
     */
    private static function get_client_credentials(): array
    {
        $options = get_option('beecomm_integration_options', []);
        return [
            'client_id' => $options['client_id'] ?? '',
            'client_secret' => $options['client_secret'] ?? '',
        ];
    }
}
