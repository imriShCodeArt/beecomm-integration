# BeeComm Integration for WooCommerce

A modern WordPress plugin that seamlessly integrates your WooCommerce-based restaurant with the BeeComm POS system — enabling real-time order dispatch, customer SMS notifications, and admin control via a clean UI.

---

## 🚀 Version

**v2.0.0** – Fully refactored with an object-oriented architecture. See `CHANGELOG.md` for details.

---

## 🔧 Features

- ✅ Sends orders to BeeComm when marked "processing"
- 🔁 Syncs order status via cron job and updates WooCommerce
- 📩 Sends dynamic SMS messages to both customers and admins
- 🧱 Clean, modular OOP architecture
- ⚙️ Admin panel for BeeComm credentials, SMS templates, and settings
- 🪵 File-based logging system with admin log viewer
- 🌍 Internationalization-ready with `.pot` file

---

## 📁 Folder Structure

- `includes/` – Core classes: integration, loader, cron, services
- `admin/` – Admin UI: settings, styles, log viewer
- `public/` – Frontend scripts and partials
- `languages/` – Translation files (`.pot`)
- `beecomm-integration.php` – Plugin entry point

---

## 📦 Installation

1. Upload to `/wp-content/plugins/beecomm-integration`
2. Activate via **Plugins** page in WordPress Admin
3. Go to **Settings > הגדרות ביקום** to configure credentials and behavior

---

## ⚙️ Configuration

### BeeComm API Settings

- **Client ID / Client Secret** – Provided by BeeComm support
- Saved under: `beecomm_integration_options`

### SMS Templates

Customizable templates with tags like:

- `{{billing_first_name}}` – Customer name
- `{{id}}` – WooCommerce Order ID
- `{{preparation_time}}` – Estimated prep time

SMS is sent to:

- **Admin** for `processing` status
- **Customer** for `completed`, `on-hold` and others

Templates vary by:

- Order method (`delivery` or `pickup`)
- Order status

### Cron Settings

- `beecomm_order_status_cron` runs periodically
- Configurable interval and batch size via admin panel
- Syncs status of orders and retries on failure

---

## 🗃️ Logs

Log files:

- `wp-content/uploads/wof-log.log` – Order sync
- `wp-content/uploads/beecomm-sms-log.log` – SMS sends

Visible via:
**Settings > לוגים**

---

## 🧑‍💻 Developer Info

- Hooks:
  - `woocommerce_order_status_processing` – Triggers order sync
  - `woocommerce_order_status_changed` – Triggers SMS
- Cron:
  - `beecomm_order_status_cron`
- Uses native WordPress settings API
- SMS via `Wof_Sms_Api::getInstance()->sendSms()`
- Constants and options declared in `beecomm_constants.php`

---

## 📄 License

GPLv2 or later

---

## 🧬 Credits

Developed for restaurants and delivery businesses using BeeComm POS.  
Maintained by [M.L Web Solutions](mailto:imriw@libiserv.co.il).
