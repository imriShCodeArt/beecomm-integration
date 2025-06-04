<?php

function beecomm_add_settings_page() {
	add_options_page( 'אינטגרציה לBeecomm', 'אינטגרציה לBeecomm', 'manage_options', 'beecomm-integration', 'beecomm_render_plugin_settings_page' );
}

add_action( 'admin_menu', 'beecomm_add_settings_page' );

function beecomm_render_plugin_settings_page() {
	?>
    <h2>אינטגרציה לBeecomm</h2>
    <form action="options.php" method="post">
		<?php
		settings_fields( 'beecomm_integration_options' );
		do_settings_sections( 'beecomm_integration' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>"/>
    </form>
	<?php
}

function beecomm_register_settings() {
	register_setting( 'beecomm_integration_options', 'beecomm_integration_options', 'beecomm_integration_options_validate' );
	add_settings_section( 'api_settings', 'API Settings', 'beecomm_integration_section_text', 'beecomm_integration' );

	add_settings_field( 'beecomm_integration_setting_client_id', 'Client ID', 'beecomm_integration_setting_client_id', 'beecomm_integration', 'api_settings' );
	add_settings_field( 'beecomm_integration_setting_client_secret', 'Client Secret', 'beecomm_integration_setting_client_secret', 'beecomm_integration', 'api_settings' );

	//completed order message template
	$order_template_args = [
		'beecomm_order_template' => [
			'title'       => 'Order Message Template',
			'page'        => 'beecomm_integration',
			'callback'    => 'beecomm_order_template_section_callback',
			'description' => BEECOMM_ORDER_GETTER_TAGS,
			'fields'      => [
				[
					'id'          => BEECOMM_ORDER_STATUS_CRON_INTERVAL,
					'title'       => 'Order Cron Interval',
					'type'        => 'text',
					'description' => 'Enter the order cron interval in minutes',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_ADMIN_PHONE,
					'title'       => 'Admin Phone',
					'type'        => 'text',
					'description' => 'Enter the admin phone number',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_NUMBER_OF_ORDER_TO_PROCESS,
					'title'       => 'Number of orders to process',
					'type'        => 'text',
					'description' => 'Number of orders to process in each cron run (default '.BEECOMM_NUMBER_OF_ORDER_TO_PROCESS_DEFAULT.')',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_ORDER_COMPLETE_TEMPLATE,
					'title'       => 'הודעת הזמנה שהושלמה(מסירה)',
					'type'        => 'textarea',
					'description' => 'הודעה שתשלח ללקוח כאשר ההזמנה הושלמה בהצלחה(מסירה)',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_ORDER_HOLD_TEMPLATE,
					'title'       => 'הודעת הזמנה בהמתנה(מסירה)',
					'type'        => 'textarea',
					'description' => 'הודעה שתשלח ללקוח כאשר ההזמנה בהמתנה(מסירה)',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_ORDER_COMPLETE_TEMPLATE_PICKUP,
					'title'       => 'הודעת הזמנה שהושלמה (איסוף)',
					'type'        => 'textarea',
					'description' => 'הודעה שתשלח ללקוח כאשר ההזמנה הושלמה בהצלחה(איסוף)',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
				[
					'id'          => BEECOMM_ORDER_HOLD_TEMPLATE_PICKUP,
					'title'       => 'הודעת הזמנה ממתינה (איסוף)',
					'type'        => 'textarea',
					'description' => 'הודעה שתשלח ללקוח כאשר ההזמנה בהמתנה(איסוף)',
					'callback'    => 'beecomm_integration_setting_order_template',
					'page'        => 'beecomm_integration',
					'section'     => 'beecomm_order_template',
				],
			]
		]
	];

	foreach ( $order_template_args as $key => $value ) {
		//add section
		add_settings_section( $key, $value['title'], $value['callback'], $value['page'], $value );
		$fields = $value['fields'] ?? [];
		foreach ( $fields as $template ) {
			add_settings_field( $template['id'], $template['title'], $template['callback'], $template['page'], $template['section'], $template );
		}
	}
}

function beecomm_order_template_section_callback( $args ) {
    ?>
    <style>
        #msg-instructions{
            position: relative;
        }
        .tag-info {
            position: absolute;
            background-color: #f1f1f1;
            border-top: 5px solid #7ad03a;
            border-bottom: 5px solid #7ad03a;
            padding: 10px 10px;
            right: 5%;
            top: -50px;
        }
    </style>
<?php
	echo '<div id="msg-instructions"><p class="tag-info" id="' . esc_attr( $args['id'] ) . '">' . $args['description'] . '</p></div>';
}

function beecomm_integration_setting_order_template( $args ) {
	$options = get_option( BEECOMM_INTEGRATION_OPTIONS );
	if ( ! isset( $options[ $args['id'] ] ) ) {
		$options[ $args['id'] ] = '';
	}
	if ( $args['type'] == 'text' ) {
		echo "<input id='{$args['id']}' name='beecomm_integration_options[{$args['id']}]' type='text' value='" . esc_attr( $options[ $args['id'] ] ?? '' ) . "'/>";
		echo "<p class='description'>{$args['description']}</p>";
	} else {
		echo "<textarea id='{$args['id']}' name='beecomm_integration_options[{$args['id']}]' type='text' rows='10' cols='50'>" . esc_attr( $options[ $args['id'] ] ?? '' ) . "</textarea>";
	}
}


add_action( 'admin_init', 'beecomm_register_settings' );


function beecomm_integration_options_validate( $input ) {
//	$newinput['api_key'] = trim( $input['api_key'] );
//	if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
//		$newinput['api_key'] = '';
//	}
//
//	return $newinput;
	return $input;
}


function beecomm_integration_section_text() {
	echo '<p>כאן יש להכניס את פרטי המשתמש ממערכת Beecomm. את הפרטים יש לבקש מהתמיכה של Beecomm</p>';
}

function beecomm_integration_setting_client_id() {
	$options = get_option( 'beecomm_integration_options' );
	echo "<input id='beecomm_integration_setting_client_id' name='beecomm_integration_options[client_id]' type='text' value='" . esc_attr( $options['client_id'] ) . "' />";
}

function beecomm_integration_setting_client_secret() {
	$options = get_option( 'beecomm_integration_options' );
	echo "<input id='beecomm_integration_setting_client_secret' name='beecomm_integration_options[client_secret]' type='text' value='" . esc_attr( $options['client_secret'] ) . "' />";
}
