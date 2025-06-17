# BeeComm Integration for WooCommerce

A WordPress plugin that integrates your WooCommerce-based restaurant with BeeComm POS system, providing real-time order sync, status updates, and SMS notifications.

---

## 🚀 Version

**v1.1.3** – Improved order status sync logic, added BeeComm status API, and enhanced logging/debugging.

---

## 🔧 Features

- ✅ Push WooCommerce orders to BeeComm upon order processing
- 🔁 Sync order status from BeeComm to WooCommerce using WP Cron
- 📩 Send dynamic SMS notifications to customers and admins
- 🧱 **Modular architecture** with separated responsibilities
- ⚙️ Configurable admin panel for credentials, cron, and templates
- 🪵 Enhanced logging system with in-dashboard log viewer

---

## 📁 Folder Structure (as of v1.1.2)

- `orders/` – Handles BeeComm payload formatting and order dispatching
- `api/` – Authentication and HTTP requests to BeeComm API
- `sms/` – Status-based SMS logic and templates
- `cron/` – Scheduled order status checks
- `admin/settings/` – Settings panel for plugin configuration
- `admin/log-viewer/` – Backend viewer for plugin logs
- `utils/` – Shared helpers (e.g. logger, meta fields)

---

## 📦 Installation

1. Upload the plugin to `/wp-content/plugins/beecomm-integration` or install via WordPress Admin.
2. Activate the plugin from the **Plugins** page.
3. Go to **Settings > אינטגרציה לBeecomm** and configure your credentials and settings.

---

## ⚙️ Configuration

### BeeComm API Settings
- **Client ID** / **Client Secret** – Provided by BeeComm support

### Cron Settings
- **Sync Interval** – How often to sync order statuses
- **Batch Size** – Number of orders to process per run

### SMS Settings
- **Admin Phone** – For receiving critical order alerts
- **Templates** – Customizable with dynamic `{{tags}}`

Example Tags:
- `{{id}}` – Order ID
- `{{billing_first_name}}` – Customer first name
- `{{status}}` – WooCommerce order status
- `{{preparation_time}}` – Estimated prep time

---

## 🗓 Cron Job Behavior

The plugin registers a cron job (`beecomm_order_status_cron`) that:
- Fetches all `wc-processing` orders
- Queries BeeComm for each order’s status
- Updates WooCommerce orders accordingly
- Retries failed syncs up to 3 times

---

## 📩 SMS Notifications

Triggered for:
- **Customers** when order status changes
- **Admin** when a new order is placed and is on hold

SMS is sent using:
```php
Wof_Sms_Api::getInstance()->send()
```

---

## 🪵 Log Viewer

Log files:
- `wof-log.log` – Order sync logs
- `beecomm-sms-log.log` – SMS send logs

Both available under:  
**Settings > Log Viewer**

---

## 🧑‍💻 Developer Notes

- Hooks used:
  - `woocommerce_order_status_processing` → triggers BeeComm sync
  - `beecomm_order_status_cron` → triggers cron sync
- Logs written with:
  - `wofErrorLog()` – legacy error logging
  - `beecomm_log()` – new lightweight debug/error logger
- Constants defined in: `lib/beecomm_constants.php`

---

## 📄 License

This plugin is licensed under the GPLv2 or later.

## 🧬 Credits

Developed for sushi restaurants using BeeComm POS.  
Maintained by your web development team.
