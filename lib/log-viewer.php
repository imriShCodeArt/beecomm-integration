<?php

/**
 * Registers the Log Viewer page under Settings.
 */
function custom_setting_page() {
    add_options_page( 'Log Viewer', 'Log Viewer', 'manage_options', 'log-viewer', 'render_log_viewer' );
}
add_action( 'admin_menu', 'custom_setting_page' );

/**
 * Renders the Log Viewer admin page.
 */
function render_log_viewer() {
    $uploads_dir    = wp_upload_dir();
    $log_file_path  = $uploads_dir['basedir'] . '/wof-log.log';

    if ( ! file_exists( $log_file_path ) ) {
        echo '<div class="notice notice-error"><p>Log file not found.</p></div>';
        return;
    }

    $log_content = file_get_contents( $log_file_path );
    $log_entries = explode( "inbeeCommOrder", $log_content );

    echo '<style>
        .log-table {
            border-collapse: collapse;
            width: 100%;
        }
        .log-table th, .log-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .log-table th {
            background-color: #f2f2f2;
        }
    </style>';

    echo '<table class="log-table">';
    echo '<thead><tr><th>Date/Time</th><th>Branch ID</th><th>Order Info</th></tr></thead>';
    echo '<tbody>';

    foreach ( $log_entries as $log_entry ) {
        $log_entry = trim( $log_entry );
        if ( empty( $log_entry ) ) continue;

        $parts = explode( '::', $log_entry );
        $date_time = $parts[0] ?? '';
        $log_data = json_decode( $parts[1] ?? '', true );

        if ( ! is_array( $log_data ) ) continue;

        $branch_id = $log_data['branchId'] ?? '';
        $order_info = $log_data['orderInfo'] ?? [];
        $formatted_order_info = format_order_info( $order_info );

        echo '<tr>';
        echo '<td style="width: 200px;">' . esc_html( $date_time ) . '</td>';
        echo '<td>' . esc_html( $branch_id ) . '</td>';
        echo '<td>' . $formatted_order_info . '</td>';
        echo '</tr>';
        echo '<tr><td colspan="3"><br></td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
}

/**
 * Formats the order info section of a log entry.
 */
function format_order_info( $order_info ) {
    $html = '<table>';
    foreach ( $order_info as $key => $value ) {
        $label = '<strong>' . esc_html( $key ) . '</strong>';

        if ( $key === 'Items' ) {
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . format_items( $value ) . '</td></tr>';
        } elseif ( $key === 'Payments' ) {
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . format_payments( $value ) . '</td></tr>';
        } elseif ( $key === 'DeliveryInfo' ) {
            $html .= "<tr><td colspan=\"3\">{$label}</td></tr>";
            $html .= format_delivery_info( $value );
        } else {
            $value = is_array( $value ) ? format_sub_array( $value ) : esc_html( $value );
            $html .= "<tr><td>{$label}</td><td colspan=\"2\">{$value}</td></tr>";
        }
    }
    $html .= '</table>';
    return $html;
}

/**
 * Recursively formats nested arrays.
 */
function format_sub_array( $data ) {
    $html = '<table>';
    foreach ( $data as $key => $value ) {
        $label = '<strong>' . esc_html( $key ) . '</strong>';
        $value = is_array( $value ) ? format_sub_array( $value ) : esc_html( $value );
        $html .= "<tr><td>{$label}</td><td colspan=\"2\">{$value}</td></tr>";
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats order item entries.
 */
function format_items( $items ) {
    $html = '';
    foreach ( $items as $item ) {
        $html .= '<table>';
        foreach ( $item as $key => $value ) {
            $label = '<strong>' . esc_html( $key ) . '</strong>';
            if ( $key === 'Toppings' ) {
                $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . format_toppings( $value ) . '</td></tr>';
            } else {
                $html .= "<tr><td>{$label}</td><td colspan=\"2\">" . esc_html( $value ) . '</td></tr>';
            }
        }
        $html .= '</table><br>';
    }
    return $html;
}

/**
 * Formats topping entries.
 */
function format_toppings( $toppings ) {
    $html = '<table>';
    foreach ( $toppings as $topping ) {
        $html .= '<tr>';
        foreach ( $topping as $key => $value ) {
            $html .= '<td><strong>' . esc_html( $key ) . '</strong></td><td>' . esc_html( $value ) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats payment entries.
 */
function format_payments( $payments ) {
    $html = '<table>';
    foreach ( $payments as $payment ) {
        $html .= '<tr>';
        foreach ( $payment as $key => $value ) {
            $html .= '<td><strong>' . esc_html( $key ) . '</strong></td><td>' . esc_html( $value ) . '</td></tr><tr></tr>';
        }
    }
    $html .= '</table>';
    return $html;
}

/**
 * Formats delivery info fields.
 */
function format_delivery_info( $delivery_info ) {
    $html = '<table>';
    foreach ( $delivery_info as $key => $value ) {
        $html .= '<tr><td><strong>' . esc_html( $key ) . '</strong></td><td colspan="2">' . esc_html( $value ) . '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}
