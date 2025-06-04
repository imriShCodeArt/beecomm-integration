<?php

/**
 * Formats the order info section of a log entry.
 *
 * @param array $order_info Associative array containing keys like Items, Payments, DeliveryInfo, etc.
 * @return string HTML representation of the order info.
 */
function beecomm_format_order_info($order_info)
{
    $html = '<table>';
    foreach ($order_info as $key => $value) {
        $label = '<strong>' . esc_html($key) . '</strong>';

        if ($key === 'Items') {
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . beecomm_format_items($value) . '</td></tr>';
        } elseif ($key === 'Payments') {
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . beecomm_format_payments($value) . '</td></tr>';
        } elseif ($key === 'DeliveryInfo') {
            $html .= "<tr><td colspan=\"3\">{$label}</td></tr>";
            $html .= beecomm_format_delivery_info($value);
        } else {
            $formatted_value = is_array($value)
                ? beecomm_format_sub_array($value)
                : esc_html($value);
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">{$formatted_value}</td></tr>";
        }
    }
    $html .= '</table>';
    return $html;
}

/**
 * Recursively formats a nested associative array as an HTML table.
 *
 * @param array $data
 * @return string HTML output.
 */
function beecomm_format_sub_array($data)
{
    $html = '<table>';
    foreach ($data as $key => $value) {
        $label = '<strong>' . esc_html($key) . '</strong>';
        $val = is_array($value) ? beecomm_format_sub_array($value) : esc_html($value);
        $html .= "<tr><td>{$label}</td><td colspan=\"2\">{$val}</td></tr>";
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats the Items array into an HTML representation.
 *
 * @param array $items
 * @return string HTML table of items and their fields.
 */
function beecomm_format_items($items)
{
    $html = '';
    foreach ($items as $item) {
        $html .= '<table>';
        foreach ($item as $key => $value) {
            $label = '<strong>' . esc_html($key) . '</strong>';
            if ($key === 'Toppings') {
                $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . beecomm_format_toppings($value) . '</td></tr>';
            } else {
                $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . esc_html($value) . '</td></tr>';
            }
        }
        $html .= '</table><br>';
    }
    return $html;
}

/**
 * Formats the Toppings array into an HTML representation.
 *
 * @param array $toppings
 * @return string HTML table of topping fields.
 */
function beecomm_format_toppings($toppings)
{
    $html = '<table>';
    foreach ($toppings as $topping) {
        $html .= '<tr>';
        foreach ($topping as $key => $value) {
            $html .= '<td><strong>' . esc_html($key) . '</strong></td><td>' . esc_html($value) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats the Payments array into an HTML representation.
 *
 * @param array $payments
 * @return string HTML table of payment details.
 */
function beecomm_format_payments($payments)
{
    $html = '<table>';
    foreach ($payments as $payment) {
        $html .= '<tr>';
        foreach ($payment as $key => $value) {
            $html .= '<td><strong>' . esc_html($key) . '</strong></td><td>' . esc_html($value) . '</td></tr><tr></tr>';
        }
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats the DeliveryInfo array into an HTML table.
 *
 * @param array $delivery_info
 * @return string HTML table of delivery details.
 */
function beecomm_format_delivery_info($delivery_info)
{
    $html = '<table>';
    foreach ($delivery_info as $key => $value) {
        $html .= '<tr><td><strong>' . esc_html($key) . '</strong></td><td colspan="2">' . esc_html($value) . '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}
