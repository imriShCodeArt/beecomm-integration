<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://clients.libiserv.co.il/
 * @since      1.0.0
 *
 * @package    Beecomm_Integration
 * @subpackage Beecomm_Integration/includes
 */

use Beecomm_Integration\Beecomm_Cron;
use Beecomm_Integration\Beecomm_Order_Processor;
use Beecomm_Integration\Beecomm_Sms;
use Beecomm_Integration\Beecomm_Newsletter;

/**
 * The core plugin class.
 *
 * Defines internationalization, admin/public hooks, scheduled tasks,
 * and initializes the main services of the plugin.
 *
 * @since      1.0.0
 * @package    Beecomm_Integration
 * @subpackage Beecomm_Integration/includes
 * @author     M.L Web Solutions <imriw@libiserv.co.il>
 */
class Beecomm_Integration
{
	/**
	 * The plugin's unique name.
	 *
	 * @var string
	 */
	public const PLUGIN_NAME = 'beecomm-integration';

	/**
	 * The loader that orchestrates all hooks of the plugin.
	 *
	 * @var Beecomm_Integration_Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor for the plugin core.
	 */
	public function __construct()
	{
		$this->version = defined('BEECOMM_INTEGRATION_VERSION') ? BEECOMM_INTEGRATION_VERSION : '1.0.0';
		$this->plugin_name = self::PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->add_action('woocommerce_order_status_processing', Beecomm_Order_Processor::class, 'process_order', 1, 1);
	}

	/**
	 * Load all required plugin files.
	 *
	 * @return void
	 */
	private function load_dependencies(): void
	{
		$plugin_dir = plugin_dir_path(dirname(__FILE__));

		require_once $plugin_dir . 'includes/beecomm_constants.php';
		require_once $plugin_dir . 'includes/class-beecomm-integration-loader.php';
		require_once $plugin_dir . 'includes/class-beecomm-integration-i18n.php';
		require_once $plugin_dir . 'admin/class-beecomm-integration-admin.php';
		require_once $plugin_dir . 'public/class-beecomm-integration-public.php';
		require_once $plugin_dir . 'includes/class-beecomm-order-processor.php';
		require_once $plugin_dir . 'includes/class-beecomm-sms.php';
		require_once $plugin_dir . 'includes/class-beecomm-cron.php';
		require_once $plugin_dir . 'includes/class-beecomm-newsletter.php';

		$this->loader = new Beecomm_Integration_Loader();
	}

	/**
	 * Set the locale for internationalization.
	 *
	 * @return void
	 */
	private function set_locale(): void
	{
		$plugin_i18n = new Beecomm_Integration_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Define hooks for the admin area.
	 *
	 * @return void
	 */
	private function define_admin_hooks(): void
	{
		$plugin_admin = new Beecomm_Integration_Admin($this->get_plugin_name(), $this->get_version());
		$plugin_admin->register_hooks($this->loader);
	}

	/**
	 * Define hooks for the public-facing functionality.
	 *
	 * @return void
	 */
	private function define_public_hooks(): void
	{
		$plugin_public = new Beecomm_Integration_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$this->loader->add_action('woocommerce_order_status_processing', Beecomm_Order_Processor::class, 'process_order', 1, 1);
		$this->loader->add_action('woocommerce_order_status_changed', Beecomm_Sms::class, 'maybe_send', 10, 4);

		$this->define_cron_hooks();

		Beecomm_Newsletter::init();
	}

	/**
	 * Register scheduled cron jobs and intervals.
	 *
	 * @return void
	 */
	private function define_cron_hooks(): void
	{
		$this->loader->add_action('init', Beecomm_Cron::class, 'register');
		$this->loader->add_filter('cron_schedules', Beecomm_Cron::class, 'add_schedule');
		$this->loader->add_action(BEECOMM_ORDER_STATUS_CRON, Beecomm_Cron::class, 'run');
	}

	/**
	 * Run the plugin – initialize the loader.
	 *
	 * @return void
	 */
	public function run(): void
	{
		$this->loader->run();
	}

	/**
	 * Get the plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name(): string
	{
		return $this->plugin_name;
	}

	/**
	 * Get the loader instance.
	 *
	 * @return Beecomm_Integration_Loader
	 */
	public function get_loader(): Beecomm_Integration_Loader
	{
		return $this->loader;
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string
	{
		return $this->version;
	}
}
