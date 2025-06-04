<?php
/**
 * Initializes Beecomm integration by loading modular components.
 */

// Order logic
require_once __DIR__ . '/orders/beecomm-payload.php';
require_once __DIR__ . '/orders/send-order.php';
require_once __DIR__ . '/orders/format-items.php';

// API logic
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/request.php';

// Utils
require_once __DIR__ . '/utils/meta.php';

// Elementor integrations
require_once __DIR__ . '/elementor/newsletter-hook.php';

// Admin
require_once __DIR__ . '/admin/order-columns.php';
