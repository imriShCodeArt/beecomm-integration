<?php

/**
 * The file that defines the core plugin class.
 *
 * @link       https://clients.libiserv.co.il/
 * @since      2.0.0
 *
 * @package    Beecomm_Integration
 * @subpackage Beecomm_Integration/includes
 */

/**
 * Core plugin class.
 *
 * Defines admin, public, i18n, and core loader logic.
 *
 * @package    Beecomm_Integration
 * @subpackage Beecomm_Integration/includes
 */
class Beecomm_Integration
{

	/**
	 * Plugin name constant.
	 */
	public const PLUGIN_NAME = 'beecomm-integration';

	/**
	 * Loader for registering all hooks.
	 *
	 * @var Beecomm_Integration_Loader
	 */
	protected $loader;

	/**
	 * Unique plugin identifier.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Initialize the plugin.
	 */
	public function __construct()
	{
		$this->version = defined('BEECOMM_INTEGRATION_VERSION') ? BEECOMM_INTEGRATION_VERSION : '2.0.0';
		$this->plugin_name = self::PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load dependencies and instantiate core classes.
	 */
	private function load_dependencies()
	{
		$plugin_dir = plugin_dir_path(dirname(__FILE__));

		require_once $plugin_dir . 'includes/class-beecomm-integration-loader.php';
		require_once $plugin_dir . 'includes/class-beecomm-integration-i18n.php';
		require_once $plugin_dir . 'admin/class-beecomm-integration-admin.php';
		require_once $plugin_dir . 'public/class-beecomm-integration-public.php';

		$this->loader = new Beecomm_Integration_Loader();
	}

	/**
	 * Set up plugin internationalization.
	 */
	private function set_locale()
	{
		$plugin_i18n = new Beecomm_Integration_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register admin-related hooks.
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Beecomm_Integration_Admin($this->get_plugin_name(), $this->get_version());
		$plugin_admin->register_hooks($this->loader);
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Beecomm_Integration_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	/**
	 * Run the plugin (hook registrations).
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * Get plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * Get loader instance.
	 *
	 * @return Beecomm_Integration_Loader
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version()
	{
		return $this->version;
	}
}
