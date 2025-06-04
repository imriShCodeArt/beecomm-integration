# BeeComm Integration for WooCommerce

A WordPress plugin that integrates your WooCommerce-based restaurant with BeeComm POS system, providing real-time order sync, status updates, and SMS notifications.

## 🔧 Features

- ✅ Push WooCommerce orders to BeeComm upon order processing
- 🔁 Sync order status from BeeComm to WooCommerce using WP Cron
- 📩 Send dynamic SMS notifications to customers and admins
- 🧱 Modular architecture with centralized constants
- ⚙️ Configurable admin panel for credentials, cron, and templates
- 🪵 Enhanced logging system with in-dashboard log viewer

## 📦 Installation

1. Upload the plugin files to the `/wp-content/plugins/beecomm-integration` directory or install via the WordPress admin.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > אינטגרציה לBeecomm** to configure the plugin.

## ⚙️ Configuration

### BeeComm API Settings
- **Client ID**: Provided by BeeComm support
- **Client Secret**: Provided by BeeComm support

### Cron Settings
- **Order Sync Interval**: Choose how often to sync order statuses
- **Number of Orders to Process**: Limit the number of orders processed in each cron run

### SMS Settings
- **Admin Phone**: Phone number to notify on 'On-Hold' orders
- **SMS Templates**: Customize messages using dynamic `{{tags}}`

### Example tags:
- `{{id}} - Order ID`
- `{{billing_first_name}} - Customer First Name`
- `{{status}} - Order Status`
- `{{preparation_time}} - Estimated Preparation Time`


## 🗓 Cron Job Behavior

The plugin schedules a cron job (`beecomm_order_status_cron`) to:
- Fetch status of orders with `wc-pending`
- Update status based on BeeComm response
- Retry up to 3 times if BeeComm fails

## 📩 SMS Notifications

Uses dynamic templates to notify:
- Customers: Order completed or in progress
- Admin: Order placed and on hold

Supports `Wof_Sms_Api::getInstance()` for sending SMS.

## 🪵 Log Viewer

Logs are stored in:
- `wp-content/uploads/wof-log.log` (order push)
- `wp-content/uploads/beecomm-sms-log.log` (SMS logs)

View logs under **Settings > Log Viewer**

## 🧑‍💻 Developer Notes

- Hooks:
  - `woocommerce_order_status_processing` triggers order sync
  - Cron event `beecomm_order_status_cron` checks remote status
- Constants defined in `lib/beecomm_contants.php`
- Logs written using `beecommLog()` helper

## 📄 License

This plugin is licensed under the GPLv2 or later.

## 🧬 Credits

Developed for sushi restaurants using BeeComm POS.  
Maintained by your web development team.

---

Version: 1.1.1
