<?php

namespace Beecomm_Integration;

use WC_Order;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Beecomm_Sms
 *
 * Handles SMS sending for order status changes.
 *
 * @package Beecomm_Integration
 */
class Beecomm_Sms
{
    /**
     * Initialize hooks for sending SMS on order status change.
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('woocommerce_order_status_changed', [self::class, 'maybe_send'], 10, 4);
    }

    /**
     * Conditionally trigger SMS based on new order status.
     *
     * @param int         $order_id
     * @param string      $old_status
     * @param string      $new_status
     * @param WC_Order    $order
     *
     * @return void
     */
    public static function maybe_send($order_id, $old_status, $new_status, $order): void
    {
        $allowed_statuses = ['processing', 'completed', 'on-hold'];

        if (in_array($new_status, $allowed_statuses, true)) {
            self::send($order_id, $new_status);
        }
    }

    /**
     * Send SMS based on order status.
     *
     * @param int    $order_id
     * @param string $order_status
     *
     * @return bool Whether the SMS was successfully sent.
     */
    public static function send($order_id, $order_status): bool
    {
        try {
            $order = wc_get_order($order_id);
            $order_method = self::get_order_method($order_id);
            $content = self::process_template($order, self::get_template($order_status, $order_method));

            $sms_api = \Wof_Sms_Api::getInstance();

            $phone = ($order_status === BEECOM_ORDER_STATUS_CODE[0])
                ? get_option(BEECOMM_ADMIN_PHONE)
                : $order->get_billing_phone();

            if (empty($phone)) {
                self::log("Phone number not found for order ID {$order_id}");
                return false;
            }

            $response = $sms_api->sendSms($phone, $content);

            self::log([
                'order_id' => $order_id,
                'phone' => $phone,
                'content' => $content,
                'response' => $response,
            ]);

            return isset($response['status']) && $response['status'];
        } catch (Exception $e) {
            self::log($e->getMessage(), 'error', __METHOD__);
            return false;
        }
    }

    /**
     * Get the appropriate SMS template for given status and method.
     *
     * @param string $status One of 'processing', 'completed', or 'on-hold'
     * @param string $method Either 'pickup' or 'delivery'
     *
     * @return string Template string
     */
    private static function get_template(string $status, string $method = 'pickup'): string
    {
        $raw = get_option(BEECOMM_INTEGRATION_OPTIONS);
        $options = is_array($raw) ? $raw : maybe_unserialize($raw);
        if (!is_array($options)) {
            $options = [];
        }

        $default = BEECOM_DEFAULT_ORDER_TEMPLATE;

        // Normalize status (remove wc- if exists)
        $normalized = str_replace('wc-', '', $status);

        // Combine status and method to get the key
        $status_method = "{$normalized}_{$method}";

        $key = match ($status_method) {
            'on-hold_pickup' => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP,
            'on-hold_delivery' => BEECOMM_ORDER_HOLD_TEMPLATE,
            'completed_pickup' => BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP,
            'completed_delivery' => BEECOMM_ORDER_COMPLETE_TEMPLATE,
            'processing_pickup' => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP,
            'processing_delivery' => BEECOMM_ORDER_HOLD_TEMPLATE,
            default => null,
        };

        error_log("Template key: " . ($key ?? 'null'));

        return $key && !empty($options[$key]) ? $options[$key] : $default;
    }



    /**
     * Replace template tags with order data.
     *
     * @param WC_Order $order
     * @param string   $template
     *
     * @return string Parsed template
     */
    private static function process_template(WC_Order $order, string $template): string
    {
        error_log("🔧 [process_template] התחלת עיבוד תבנית להזמנה מס׳: " . $order->get_id());

        // הכנת תוכן המוצרים בהזמנה
        $items_description = [];
        foreach ($order->get_items() as $item) {
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $items_description[] = "{$product_name} ×{$quantity}";
        }
        $items_string = implode(".\n", $items_description);
        error_log("📦 [process_template] פרטי מוצרים: " . $items_string);

        // שליפת פרטי הזמנה
        $order_id = $order->get_id();
        $billing_name = $order->get_billing_first_name();
        $status_label = wc_get_order_status_name($order->get_status());
        $prep_time = $order->get_meta(BEECOM_ORDER_PREPARATION_TIME) ?: 20;

        // הרכבת ההודעה
        $prefix = "שלום {$billing_name},\n";
        if ($order->get_status() === 'processing') {
            $status_message = 'התקבלה במערכת ונמצאת בטיפול';
        } elseif ($order->get_status() === 'completed') {
            $status_message = 'מוכנה';
        } else {
            $status_message = 'סטטוס ההזמנה עודכן: ' . wc_get_order_status_name($order->get_status());
        }

        $prefix .= "הזמנה מספר #{$order_id} {$status_message}.\n";
        $prefix .= "מוצרים: \n{$items_string}.\n";
        $prefix .= "זמן הכנה משוער: {$prep_time} דקות.\n";

        // הוספת קו ריק לפני גוף ההודעה המקורי
        $template = $prefix . "\n" . $template;

        // איתור והחלפת תגים
        preg_match_all('/{{(.*?)}}/', $template, $matches);
        error_log("🔍 [process_template] תגים שנמצאו: " . implode(', ', $matches[1]));

        foreach ($matches[1] as $tag) {
            $replacement = '';

            if ($tag === BEECOM_ORDER_PREPARATION_TIME) {
                $replacement = $prep_time;
                error_log("⏱️ [process_template] החלפת {{$tag}} עם זמן הכנה: {$replacement}");
            } elseif (method_exists($order, 'get_' . $tag)) {
                $replacement = $order->{'get_' . $tag}();
                error_log("✅ [process_template] החלפת {{$tag}} עם ערך: {$replacement}");
            } else {
                error_log("⚠️ [process_template] לא נמצא תחליף לתג {{$tag}}");
            }

            $template = str_replace('{{' . $tag . '}}', $replacement, $template);
        }

        error_log("✅ [process_template] תוצאה סופית:\n" . $template);

        return $template;
    }




    /**
     * Append a log entry to the log file.
     *
     * @param mixed  $entry     String or array of log data
     * @param string $type      Log type: 'info' or 'error'
     * @param string $method    Calling method
     * @param string $mode      File mode (usually 'a')
     * @param string $filename  Log file name without extension
     *
     * @return string|null Written log line or null on failure
     */
    private static function log($entry, string $type = 'info', string $method = '', string $mode = 'a', string $filename = 'beecomm-sms-log'): ?string
    {
        $upload_dir = wp_upload_dir();
        if (!isset($upload_dir['basedir'])) {
            return null;
        }

        $path = "{$upload_dir['basedir']}/{$filename}.log";
        if (is_array($entry)) {
            $entry = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $timestamp = date('Y-m-d H:i:s');
        $log = "{$timestamp}::{$type}::{$method}::{$entry}\n";

        file_put_contents($path, $log, FILE_APPEND);
        return $log;
    }

    /**
     * Gets the shipping method type (pickup or delivery) for a given order.
     *
     * @param int $order_id
     *
     * @return string 'pickup' or 'delivery'
     */
    private static function get_order_method($order_id): string
    {
        $allowed_methods = ['pickup', 'delivery'];
        $method = get_post_meta($order_id, ORDER_METHOD, true);

        return (in_array($method, $allowed_methods, true))
            ? $method
            : 'pickup';
    }
}
