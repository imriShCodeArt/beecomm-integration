# 📦 Changelog

All notable changes to the BeeComm Integration plugin will be documented in this file.

---

## [1.1.2] - 2025-06-04

### Added

- Refactored plugin into a modular structure with organized folders:
  - `orders/` – order formatting and sending logic
  - `api/` – authentication and request handling
  - `utils/` – reusable utilities (e.g., meta helpers, logging)
  - `admin/` – settings UI and log viewer
  - `elementor/` – newsletter form integration
- New `beecomm-payload.php` to build structured order data before sending

### Changed

- Removed deprecated file `integration.php` and split responsibilities into domain-specific modules
- Centralized `beecomm_get_base_url()` inside `api/request.php` and reused across the codebase
- Removed `admin_page.php` and `log-viewer.php` in favor of modularized equivalents
- Replaced inline cURL code with reusable API call wrapper `make_beecomm_api_call()`

---

## [1.1.1] - 2025-06-04

### 📝 Added

- 📘 `README.md` file with detailed plugin overview, configuration instructions, and usage documentation.
- 🧾 `CHANGELOG.md` file to track all versioned changes in the plugin.

---

## [1.1.0] - 2025-06-04

### ✨ Added

- ✅ **Order status synchronization** from BeeComm to WooCommerce via WP Cron.
- ✅ **SMS notification system** with dynamic template tags for customer/admin messaging.
- ✅ **Admin configuration options** for:
  - SMS templates (per order status & method)
  - Admin phone number
  - Order sync interval
  - Number of orders processed per cron run
- ✅ **Dynamic tag processor** for generating SMS content using placeholders like `{{billing_first_name}}`, `{{status}}`, etc.
- ✅ **Logging system** for SMS actions (`beecomm-sms-log.log`).
- ✅ **Enhanced log viewer UI** with formatted order and status logs.

### 🧱 Changed

- ♻️ Refactored plugin structure with a dedicated `beecomm_constants.php` file for centralized configuration.
- ♻️ Improved logging format using `beecommLog()` with timestamped structured entries.
- ♻️ Extended log viewer to read logs from `wp-content/uploads`.

### 🐛 Fixed

- Minor improvements in error handling during order push and SMS sending.
- Retry mechanism added for failed BeeComm status checks.

---

## [1.0.0] - Initial Release

### ✨ Added

- ✅ Core integration with BeeComm API for order dispatch on `woocommerce_order_status_processing`.
- ✅ Admin settings page to store BeeComm `Client ID` and `Client Secret`.
- ✅ Data mapping of WooCommerce orders to BeeComm-compatible JSON structure.
- ✅ Logging of sent orders to `error_log`.
- ✅ Custom WooCommerce admin columns to show BeeComm sync status and order ID.
- ✅ HTML-based log viewer in WordPress settings.

---
