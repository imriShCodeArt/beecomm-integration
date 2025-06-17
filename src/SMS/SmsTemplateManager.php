<?php

namespace BeeComm\SMS;

use WC_Order;

final class SmsTemplateManager
{
    private static array $defaultTemplates = [
        'processing' => 'Your order #{order_id} is now being processed.',
        'completed' => 'Your order #{order_id} is complete. Thank you!',
        'failed' => 'There was an issue with your order #{order_id}. Please contact support.',
    ];

    public static function getMessageForStatus(string $status, WC_Order $order): ?string
    {
        $template = self::$defaultTemplates[$status] ?? null;

        if (!$template) {
            return null;
        }

        return strtr($template, [
            '{order_id}' => $order->get_id(),
        ]);
    }
}
