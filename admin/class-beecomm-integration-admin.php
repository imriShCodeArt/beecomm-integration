<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://clients.libiserv.co.il/
 * @since      1.1.3
 * @package    Beecomm_Integration
 * @subpackage Beecomm_Integration/admin
 */

class Beecomm_Integration_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register all WordPress admin hooks for this plugin.
	 *
	 * @param Beecomm_Integration_Loader $loader The loader instance to register hooks with.
	 */
	public function register_hooks($loader)
	{
		$loader->add_action('admin_menu', $this, 'register_settings_page');
		$loader->add_action('admin_init', $this, 'register_settings');
		$loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
		$loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');

		// WooCommerce order table customization
		$loader->add_filter('manage_edit-shop_order_columns', $this, 'add_order_columns');
		$loader->add_action('manage_shop_order_posts_custom_column', $this, 'render_order_column', 10, 2);
	}


	/**
	 * Register the settings page in the WordPress admin menu.
	 */
	public function register_settings_page()
	{
		add_menu_page(
			'התממשקות לביקום',
			'הגדרות ביקום',
			'manage_options',
			'beecomm-settings',
			[$this, 'render_settings_page'],
			'dashicons-admin-settings',
			81
		);

		add_submenu_page(
			'beecomm-settings',
			'צפייה בלוגים',
			'לוגים',
			'manage_options',
			'beecomm-log-viewer',
			[$this, 'render_log_viewer_page']
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page()
	{
		include plugin_dir_path(__FILE__) . 'partials/beecomm-integration-admin-settings.php';
	}

	/**
	 * Register the plugin settings, sections, and fields.
	 */
	public function register_settings()
	{
		register_setting(
			'beecomm_integration_options',
			'beecomm_integration_options',
			[$this, 'validate_options']
		);

		add_settings_section(
			'api_settings',
			'API Settings',
			[$this, 'section_description_api'],
			'beecomm_integration'
		);

		add_settings_field(
			'beecomm_integration_setting_client_id',
			'Client ID',
			[$this, 'render_client_id_field'],
			'beecomm_integration',
			'api_settings'
		);

		add_settings_field(
			'beecomm_integration_setting_client_secret',
			'Client Secret',
			[$this, 'render_client_secret_field'],
			'beecomm_integration',
			'api_settings'
		);

		$order_template_args = [
			'beecomm_order_template' => [
				'title' => 'Order Message Template',
				'page' => 'beecomm_integration',
				'callback' => [$this, 'render_order_template_section'],
				'description' => BEECOMM_ORDER_GETTER_TAGS,
				'fields' => [
					['id' => BEECOMM_ORDER_STATUS_CRON_INTERVAL, 'title' => 'Order Cron Interval', 'type' => 'text', 'description' => 'Interval in minutes'],
					['id' => BEECOMM_ADMIN_PHONE, 'title' => 'Admin Phone', 'type' => 'text', 'description' => 'Phone number to notify'],
					['id' => BEECOMM_NUMBER_OF_ORDER_TO_PROCESS, 'title' => 'Number of Orders', 'type' => 'text', 'description' => 'Default: ' . BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT],
					['id' => BEECOMM_ORDER_COMPLETE_TEMPLATE, 'title' => 'הודעת הזמנה שהושלמה (מסירה)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה הושלמה (מסירה)'],
					['id' => BEECOMM_ORDER_HOLD_TEMPLATE, 'title' => 'הודעת הזמנה בהמתנה (מסירה)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה ממתינה (מסירה)'],
					['id' => BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP, 'title' => 'הודעת הזמנה שהושלמה (איסוף)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה הושלמה (איסוף)'],
					['id' => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP, 'title' => 'הודעת הזמנה ממתינה (איסוף)', 'type' => 'textarea', 'description' => 'נשלחת כאשר ההזמנה ממתינה (איסוף)'],
				],
			],
		];

		foreach ($order_template_args as $section_id => $section) {
			add_settings_section(
				$section_id,
				$section['title'],
				$section['callback'],
				$section['page'],
				$section
			);

			foreach ($section['fields'] as $field) {
				add_settings_field(
					$field['id'],
					$field['title'],
					[$this, 'render_order_template_field'],
					$section['page'],
					$section_id,
					$field
				);
			}
		}
	}


	/**
	 * Enqueue styles for the admin area.
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/beecomm-integration-admin.css',
			[],
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueue scripts for the admin area.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/beecomm-integration-admin.js',
			['jquery'],
			$this->version,
			false
		);
	}

	public function validate_options($input)
	{
		return $input; // Add validation logic if needed
	}

	public function section_description_api()
	{
		echo '<p>כאן יש להכניס את פרטי המשתמש ממערכת Beecomm. את הפרטים יש לבקש מהתמיכה של Beecomm</p>';
	}

	public function render_client_id_field()
	{
		$options = get_option('beecomm_integration_options');
		$value = $options['client_id'] ?? '';
		echo "<input id='beecomm_integration_setting_client_id' name='beecomm_integration_options[client_id]' type='text' value='" . esc_attr($value) . "' />";
	}

	public function render_client_secret_field()
	{
		$options = get_option('beecomm_integration_options');
		$value = $options['client_secret'] ?? '';
		echo "<input id='beecomm_integration_setting_client_secret' name='beecomm_integration_options[client_secret]' type='text' value='" . esc_attr($value) . "' />";
	}

	public function render_order_template_section($args)
	{
		echo '<div id="msg-instructions"><p class="tag-info" id="' . esc_attr($args['id']) . '">'
			. wp_kses_post($args['description']) . '</p></div>';
	}

	public function render_order_template_field($args)
	{
		$options = get_option(BEECOMM_INTEGRATION_OPTIONS);
		$value = $options[$args['id']] ?? '';
		$id = esc_attr($args['id']);
		$name = "beecomm_integration_options[{$id}]";

		if ($args['type'] === 'text') {
			echo "<input id='{$id}' name='{$name}' type='text' value='" . esc_attr($value) . "' />";
			echo "<p class='description'>" . esc_html($args['description']) . "</p>";
		} else {
			echo "<textarea id='{$id}' name='{$name}' rows='10' cols='50'>" . esc_textarea($value) . "</textarea>";
		}
	}

	public function render_log_viewer_page()
	{
		$log_entries = $this->read_log_file();
		include plugin_dir_path(__FILE__) . 'partials/beecomm-integration-admin-log-viewer.php';
	}

	private function read_log_file(): array
	{
		$uploads = wp_upload_dir();
		$log_path = $uploads['basedir'] . '/wof-log.log';

		if (!file_exists($log_path)) {
			return [['time' => '', 'level' => 'error', 'message' => 'Log file not found.']];
		}

		$content = file_get_contents($log_path);
		$entries = explode('inbeeCommOrder', $content);

		$parsed = [];

		foreach ($entries as $entry) {
			$lines = array_filter(array_map('trim', explode("\n", $entry)));
			if (empty($lines))
				continue;

			$parsed[] = [
				'time' => $this->extract_time($lines),
				'level' => $this->extract_log_level($lines),
				'message' => esc_html(implode("\n", $lines))
			];
		}

		return $parsed;
	}

	private function extract_time(array $lines): string
	{
		foreach ($lines as $line) {
			if (preg_match('/\[(.*?)\]/', $line, $matches)) {
				return $matches[1];
			}
		}
		return '';
	}

	private function extract_log_level(array $lines): string
	{
		foreach ($lines as $line) {
			if (stripos($line, 'ERROR') !== false)
				return 'error';
			if (stripos($line, 'WARN') !== false)
				return 'warning';
			if (stripos($line, 'DEBUG') !== false)
				return 'debug';
			if (stripos($line, 'INFO') !== false)
				return 'info';
		}
		return 'info';
	}

	public function add_order_columns($columns)
	{
		$columns['beecomm_status'] = 'Beecomm Status';
		return $columns;
	}

	public function render_order_column($column, $post_id)
	{
		if ($column === 'beecomm_status') {
			$status = get_post_meta($post_id, '_beecomm_status', true);
			echo esc_html($status ?: 'N/A');
		}
	}



}
