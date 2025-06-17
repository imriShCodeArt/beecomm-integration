# BeeComm Integration for WooCommerce

A modernized WordPress plugin that integrates your WooCommerce-based restaurant with the BeeComm POS system, providing real-time order sync, status updates, and SMS notifications.

---

## 🚀 Version

**v2.0.0** – Fully refactored with PSR-4 autoloading, OOP architecture, centralized configuration, and enhanced modularity.

---

## 🔧 Features

- ✅ Push WooCommerce orders to BeeComm upon order processing
- 🔁 Periodic order status sync from BeeComm via WP Cron
- 📩 SMS notifications for customers and admins based on order events
- 🧱 Clean OOP architecture (SRP, DI-ready)
- 🔍 In-dashboard log viewer with status badge indicators
- ⚙️ Admin settings panel for credentials and behavior customization
- 🌐 Elementor form integration for newsletter signups

---

## 📁 Folder Structure (PSR-4)

```
src/
├── Core/               # Plugin bootstrap & hook registration
├── Admin/              # Admin UI (Settings, Log Viewer, Order Columns)
├── API/                # AuthService + RequestService
├── Orders/             # Order sending, payload building, formatting
├── SMS/                # SMS dispatch & templates
├── Cron/               # StatusUpdater (schedules + cron logic)
├── Elementor/          # Elementor form hook (newsletter)
├── Utils/              # Logger, MetaHandler
config/                 # plugin-config.php with centralized settings
beecomm-integration.php # Entry point (minimal bootstrap)
```

---

## 📦 Installation

1. Upload plugin to `/wp-content/plugins/beecomm-integration`
2. Run `composer dump-autoload` (if developing locally)
3. Activate the plugin via **WordPress Admin > Plugins**
4. Configure settings in **Settings > BeeComm Integration**

---

## ⚙️ Configuration

All settings are managed via `config/plugin-config.php` or the WordPress admin panel.

- **API Key / Store ID** – Provided by BeeComm
- **Cron Hook** – `beecomm_check_order_status`
- **Log Path** – Located in `/wp-content/beecomm-logs/beecomm-log.txt`

---

## 🔄 Cron Behavior

- Hook: `beecomm_check_order_status`
- Runs hourly (or can be triggered manually)
- For each order with `_beecomm_external_id`, queries BeeComm for status
- Updates WooCommerce status if changed

---

## 📩 SMS Notifications

- Hooked into `woocommerce_order_status_changed`
- SMS sent via `SmsDispatcher`
- Template-driven, customizable via `SmsTemplateManager`

Example default template:

```
Your order #{order_id} is now being processed.
```

---

## 🪵 Log Viewer

- Located under **Settings > BeeComm Logs**
- Uses `Logger::info()` and `Logger::error()`
- Output shown in admin with visual indicators for severity

---

## 🧑‍💻 Developer Notes

- Autoloads via Composer (`composer.json`)
- All logic organized by domain + responsibility
- Use `Logger`, `MetaHandler`, `RequestService` internally
- Extendable via hooks, filters, or additional services

---

## 📄 License

GPLv2 or later

## 🙏 Credits

Developed for restaurants using BeeComm POS.  
Maintained by your technical team.
