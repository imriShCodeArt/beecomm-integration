<?php

namespace BeeComm\SMS;

use WC_Order;
use BeeComm\Utils\Logger;
use BeeComm\SMS\SmsTemplateManager;

final class SmsDispatcher
{
    public static function maybeSendSmsOnStatusChange(int $order_id, string $old_status, string $new_status, $order): void
    {
        if (!$order instanceof WC_Order) {
            return;
        }

        $phone = $order->get_billing_phone();
        if (!$phone || !preg_match('/^\+?\d{9,15}$/', $phone)) {
            return;
        }

        $message = SmsTemplateManager::getMessageForStatus($new_status, $order);

        if (!$message) {
            return;
        }

        try {
            self::sendSms($phone, $message);
            Logger::info("SMS sent to {$phone} for order #{$order_id}.");
        } catch (\Throwable $e) {
            Logger::error("Failed to send SMS for order #{$order_id}: " . $e->getMessage());
        }
    }

    private static function sendSms(string $phone, string $message): void
    {
        // Replace this with your actual SMS API integration
        // Example stub:
        // wp_remote_post('https://sms.api/send', [...])
        error_log("[SMS to {$phone}]: {$message}");
    }
}
