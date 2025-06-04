<?php

// Add a new setting page
function custom_setting_page() {
    add_options_page( 'Log Viewer', 'Log Viewer', 'manage_options', 'log-viewer', 'render_log_viewer' );
}
add_action( 'admin_menu', 'custom_setting_page' );



// Log viewer page callback function
function render_log_viewer() {
    // Get the uploads directory path
    $uploads_dir = wp_upload_dir();
    $log_file_path = $uploads_dir['basedir'] . '/wof-log.log';

    // Read the log file
    $log_content = file_get_contents($log_file_path);

    // Parse and format log entries
    $log_entries = explode("inbeeCommOrder", $log_content);

    // Output log entries in a table
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
    foreach ($log_entries as $log_entry) {
        $log_entry = trim($log_entry);
        if (!empty($log_entry)) {
            $parts = explode('::', $log_entry);
            $date_time = $parts[0];
            $log_data = json_decode($parts[1], true);

            $branch_id = isset($log_data['branchId']) ? $log_data['branchId'] : '';

            $order_info = isset($log_data['orderInfo']) ? $log_data['orderInfo'] : [];
            $order_info_formatted = format_order_info($order_info);

            echo '<tr>';
            echo '<td style="width: 200px;">' . htmlspecialchars($date_time) . '</td>';
            echo '<td>' . htmlspecialchars($branch_id) . '</td>';
            echo '<td>' . $order_info_formatted . '</td>';
            echo '</tr>';
            echo '<tr><td colspan="3"><br></td></tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';
}

// Format order info parameters
function format_order_info($order_info) {
    $formatted_data = '<table>';
    foreach ($order_info as $key => $value) {
        if ($key === 'Items') {
            $formatted_data .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . format_items($value) . '</td></tr>';
        } elseif ($key === 'Payments') {
            $formatted_data .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . format_payments($value) . '</td></tr>';
        } elseif ($key === 'DeliveryInfo') {
            $formatted_data .= '<tr><td colspan="3"><strong>' . htmlspecialchars($key) . '</strong></td></tr>';
            $formatted_data .= format_delivery_info($value);
        } else {
            if (is_array($value)) {
                $value = format_sub_array($value); // Handle nested arrays
            }
            $formatted_data .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . htmlspecialchars($value) . '</td></tr>';
        }
    }
    $formatted_data .= '</table>';

    return $formatted_data;
}

// Format nested arrays
function format_sub_array($data) {
    $formatted_sub_array = '<table>';
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = format_sub_array($value); // Recursive call for nested arrays
        }
        $formatted_sub_array .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . htmlspecialchars($value) . '</td></tr>';
    }
    $formatted_sub_array .= '</table>';

    return $formatted_sub_array;
}
// Format items parameters
function format_items($items) {
    $formatted_items = '';
    foreach ($items as $item) {
        $formatted_items .= '<table>';
        foreach ($item as $key => $value) {
            if ($key === 'Toppings') {
                $formatted_items .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . format_toppings($value) . '</td></tr>';
            } else {
                $formatted_items .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . htmlspecialchars($value) . '</td></tr>';
            }
        }
        $formatted_items .= '</table><br>';
    }

    return $formatted_items;
}

// Format toppings parameters
function format_toppings($toppings) {
    $formatted_toppings = '<table>';
    foreach ($toppings as $topping) {
        $formatted_toppings .= '<tr>';
        foreach ($topping as $key => $value) {
            $formatted_toppings .= '<td><strong>' . htmlspecialchars($key) . '</strong></td><td>' . htmlspecialchars($value) . '</td>';
        }
        $formatted_toppings .= '</tr>';
    }
    $formatted_toppings .= '</table>';

    return $formatted_toppings;
}

// Format payments parameters
function format_payments($payments) {
    $formatted_payments = '<table>';
    foreach ($payments as $payment) {
        $formatted_payments .= '<tr>';
        foreach ($payment as $key => $value) {
            $formatted_payments .= '<td><strong>' . htmlspecialchars($key) . '</strong></td><td>' . htmlspecialchars($value) . '</td>';
            $formatted_payments .= '</tr><tr></tr>'; // Add line break after each parameter
        }
    }
    $formatted_payments .= '</table>';

    return $formatted_payments;
}


// Format delivery info parameters
function format_delivery_info($delivery_info) {
    $formatted_delivery_info = '<table>';
    foreach ($delivery_info as $key => $value) {
        $formatted_delivery_info .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td colspan="2">' . htmlspecialchars($value) . '</td></tr>';
    }
   // $formatted_delivery_info .= '</table>';

    return $formatted_delivery_info;
}
?>