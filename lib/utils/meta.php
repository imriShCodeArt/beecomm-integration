<?php
/**
 * Helper functions for accessing and updating order meta.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a meta value from an order, fallback to another key if needed.
 *
 * @param int $order_id
 * @param string $primary_key
 * @param string|null $fallback_key
 * @return string|null
 */
function beecomm_get_order_meta(int $order_id, string $primary_key, ?string $fallback_key = null): ?string
{
    $value = get_post_meta($order_id, $primary_key, true);

    if (empty($value) || $value === '&#8220;&#8221;') {
        if ($fallback_key) {
            return get_post_meta($order_id, $fallback_key, true) ?: null;
        }
        return null;
    }

    return $value;
}

/**
 * Save a value to order meta
 *
 * @param int $order_id
 * @param string $key
 * @param mixed $value
 * @return void
 */
function beecomm_update_order_meta(int $order_id, string $key, $value): void
{
    update_post_meta($order_id, $key, $value);
}
