# 📦 Changelog

All notable changes to the BeeComm Integration plugin will be documented in this file.

---

## [2.0.0] - 2025-06-17

### 🆕 Added
- Full PSR-4 compliant refactor using Composer autoloading.
- Clean OOP architecture: services, use-cases, registrars, and infrastructure modules.
- Modular folders: `Core`, `Orders`, `API`, `SMS`, `Cron`, `Elementor`, `Admin`, `Utils`.
- `Logger` service with custom log formatting and auto-generated log folder.
- Admin settings panel using `SettingsPage` with dynamic field registration.
- Elementor integration via `NewsletterHook`.
- Cron scheduler and handler via `StatusUpdater` with `beecomm_check_order_status` hook.
- In-dashboard log viewer using `LogReader` and `TableRenderer` with status color badges.

### 🔄 Changed
- Replaced all procedural logic with domain-specific classes and namespaces.
- Logging system replaced with `Logger::info()` / `Logger::error()` using timestamped JSON-friendly logs.
- SMS handling abstracted into `SmsDispatcher` and `SmsTemplateManager`.
- Configuration moved to `config/plugin-config.php`.

### 🛠 Removed
- All legacy files: `beecomm_constants.php`, `integration.php`, flat `lib/` structure.
- Deprecated logger helpers (`beecomm_log()`, `wofErrorLog()`), replaced with `Logger`.

---

## [1.1.3] - 2025-06-17

### Added
- New `api/beecomm-status.php` handler to allow querying order status externally
- `beecomm_log()` helper in `utils/logger.php` for consistent debug and error logging

### Changed
- Updated `BEECOM_ORDER_STATUS_CODE[2]` from `wc-pending` → `wc-processing` to better reflect WooCommerce flow
- Refactored `get_orders_by_status()` to strip `wc-` prefix and log fetched orders
- Rewrote `get_order_template_content()` logic to use traditional `if/elseif` instead of `match`

---

## [1.1.2] - 2025-06-04

### Added
- Modular structure with folders for orders, API, utils, admin, and elementor
- New `beecomm-payload.php` to build structured order data

### Changed
- Deprecated `integration.php` replaced by modular components
- `beecomm_get_base_url()` centralized in `api/request.php`

---

## [1.1.1] - 2025-06-04

### Added
- `README.md` and `CHANGELOG.md` files

---

## [1.1.0] - 2025-06-04

### Added
- Order sync via WP Cron
- SMS system with template tags
- Admin config: SMS templates, sync interval, phone number
- Logging of SMS and sync events

### Changed
- Dedicated constants file
- Enhanced log formatting and viewer

### Fixed
- Retry mechanism for failed syncs
- Improved error handling

---

## [1.0.0] - Initial Release

### Added
- Core BeeComm integration
- Admin settings panel
- WooCommerce → BeeComm order mapping
- Basic logging and admin log viewer