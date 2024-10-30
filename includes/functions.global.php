<?php

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'chatter_get_timezone' ) ) {
	/**
	 *
	 * @return DateTimeZone Install DateTimeZone
	 */
	function chatter_get_timezone() {
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return new DateTimeZone( $timezone_string );
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return new DateTimeZone( $tz_offset );
	}
}

if ( ! function_exists( 'base64url_encode' ) ) {
		function base64url_encode( $data ){
		  return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
		}
}

if ( ! function_exists( 'base64url_decode' ) ) {
		function base64url_decode( $data ){
		  return sanitize_text_field( base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 )) );
		}
}

function do_ch_settings_section( $page, $selected_section ) {
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[ $page ] ) ) {
		return;
	}

	foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

		if ( $selected_section == $section['id'] ) {

			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}
}
