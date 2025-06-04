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
		<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
	</form>
	<?php
}

function beecomm_register_settings() {
	register_setting( 'beecomm_integration_options', 'beecomm_integration_options', 'beecomm_integration_options_validate' );
	add_settings_section( 'api_settings', 'API Settings', 'beecomm_integration_section_text', 'beecomm_integration' );

	add_settings_field( 'beecomm_integration_setting_client_id', 'Client ID', 'beecomm_integration_setting_client_id', 'beecomm_integration', 'api_settings' );
	add_settings_field( 'beecomm_integration_setting_client_secret', 'Client Secret', 'beecomm_integration_setting_client_secret', 'beecomm_integration', 'api_settings' );
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
