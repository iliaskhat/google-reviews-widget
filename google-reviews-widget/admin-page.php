<?php
// admin-page.php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'ilias_google_reviews_add_admin_page' );
add_action( 'admin_init', 'ilias_google_reviews_settings_init' );
add_action( 'update_option_ilias_google_reviews_badge_position', 'ilias_clear_cache_on_position_change', 10, 2 );

function ilias_clear_cache_on_position_change( $old_value, $new_value ) {
	if ( function_exists( 'wpfc_clear_all_cache' ) ) {
		wpfc_clear_all_cache();
	}
}
function ilias_google_reviews_add_admin_page() {
	add_menu_page(
		'Google Reviews Instellingen',
		'Google Reviews',
		'manage_options',
		'ilias-google-reviews',
		'ilias_google_reviews_render_admin_page',
		'dashicons-star-filled',
		100
	);
}

function ilias_google_reviews_render_admin_page() {
	?>
	<div class="wrap">
		<h1>Google Reviews Settings</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'ilias_google_reviews_settings' );
			do_settings_sections( 'ilias-google-reviews' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function ilias_google_reviews_settings_init() {
	register_setting( 'ilias_google_reviews_settings', 'ilias_google_reviews_api_key' );
	register_setting( 'ilias_google_reviews_settings', 'ilias_google_reviews_place_id' );
	register_setting( 'ilias_google_reviews_settings', 'ilias_google_reviews_business_name' );
	register_setting( 'ilias_google_reviews_settings', 'ilias_google_reviews_google_link' );
	register_setting( 'ilias_google_reviews_settings', 'ilias_google_reviews_badge_position' );

	add_settings_section(
		'ilias_google_reviews_section',
		'Configurate your settings:',
		null,
		'ilias-google-reviews'
	);

	add_settings_field(
		'ilias_google_reviews_api_key',
		'Google API Key',
		'ilias_google_reviews_api_key_render',
		'ilias-google-reviews',
		'ilias_google_reviews_section'
	);

	add_settings_field(
		'ilias_google_reviews_place_id',
		'Google Place ID',
		'ilias_google_reviews_place_id_render',
		'ilias-google-reviews',
		'ilias_google_reviews_section'
	);

	add_settings_field(
		'ilias_google_reviews_business_name',
		'Business Name',
		'ilias_google_reviews_business_name_render',
		'ilias-google-reviews',
		'ilias_google_reviews_section'
	);

	add_settings_field(
		'ilias_google_reviews_google_link',
		'Link to Google page',
		'ilias_google_reviews_google_link_render',
		'ilias-google-reviews',
		'ilias_google_reviews_section'
	);
	add_settings_field(
		'ilias_google_reviews_badge_position',
		'Badge Position',
		'ilias_google_reviews_badge_position_callback',
		'ilias-google-reviews',
		'ilias_google_reviews_section'
	);
}

function ilias_google_reviews_api_key_render() {
	$value = get_option( 'ilias_google_reviews_api_key' );
	echo "<input type='password' name='ilias_google_reviews_api_key' value='" . esc_attr( $value ) . "' class='regular-text'>";
}

function ilias_google_reviews_place_id_render() {
	$value = get_option( 'ilias_google_reviews_place_id' );
	echo "<input type='text' name='ilias_google_reviews_place_id' value='" . esc_attr( $value ) . "' class='regular-text'>";
}

function ilias_google_reviews_business_name_render() {
	$value = get_option( 'ilias_google_reviews_business_name' );
	echo "<input type='text' name='ilias_google_reviews_business_name' value='" . esc_attr( $value ) . "' class='regular-text'>";
}

function ilias_google_reviews_google_link_render() {
	$value = get_option( 'ilias_google_reviews_google_link' );
	echo "<input type='url' name='ilias_google_reviews_google_link' value='" . esc_attr( $value ) . "' class='regular-text'>";
}
function ilias_google_reviews_badge_position_callback() {
	$value = get_option( 'ilias_google_reviews_badge_position', 'left' );
	?>
	<select name="ilias_google_reviews_badge_position">
		<option value="left" <?php selected( $value, 'left' ); ?>>Left under</option>
		<option value="right" <?php selected( $value, 'right' ); ?>>Right under</option>
	</select>
	<?php
}