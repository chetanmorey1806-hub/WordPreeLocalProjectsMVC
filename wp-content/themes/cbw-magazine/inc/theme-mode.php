<?php
/**
 * Light / dark mode.
 *
 * The choice is stored in a cookie so PHP can stamp data-theme on <html>
 * server-side — that is what stops the page flashing the wrong theme. When no
 * choice has been made the page follows the operating system, resolved by a
 * tiny inline script before first paint.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The stored preference: 'light', 'dark', or '' for "follow the system".
 *
 * @return string
 */
function cbw_theme_mode() {
	static $mode = null;
	if ( null !== $mode ) {
		return $mode;
	}
	$raw  = isset( $_COOKIE['cbw_mode'] ) ? sanitize_key( wp_unslash( $_COOKIE['cbw_mode'] ) ) : '';
	$mode = in_array( $raw, array( 'light', 'dark' ), true ) ? $raw : '';
	return $mode;
}

/**
 * Stamp the chosen theme on <html> so the server renders the right one.
 */
function cbw_theme_html_attr( $output ) {
	$mode = cbw_theme_mode();
	if ( $mode ) {
		$output .= ' data-theme="' . esc_attr( $mode ) . '"';
	}
	return $output;
}
add_filter( 'language_attributes', 'cbw_theme_html_attr', 20 );

/**
 * With no stored choice, resolve the system preference before first paint.
 * Runs in <head>, so nothing renders in the wrong theme.
 */
function cbw_theme_head_script() {
	if ( cbw_theme_mode() ) {
		return; // Server already stamped it.
	}
	?>
	<script>
	(function () {
		try {
			var m = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			if (m) { document.documentElement.setAttribute('data-theme', 'dark'); }
		} catch (e) {}
	}());
	</script>
	<?php
}
add_action( 'wp_head', 'cbw_theme_head_script', 1 );

/**
 * The toggle. Three states so a reader can hand control back to the system.
 */
function cbw_theme_toggle() {
	$mode = cbw_theme_mode();
	?>
	<div class="cbw-mode" role="group" aria-label="<?php esc_attr_e( 'Colour mode', 'cbw' ); ?>">
		<button class="cbw-mode__btn" type="button" data-mode="light"
			aria-pressed="<?php echo 'light' === $mode ? 'true' : 'false'; ?>"
			title="<?php esc_attr_e( 'Light', 'cbw' ); ?>">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.4v2.2M12 19.4v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M2.4 12h2.2M19.4 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Light mode', 'cbw' ); ?></span>
		</button>
		<button class="cbw-mode__btn" type="button" data-mode="dark"
			aria-pressed="<?php echo 'dark' === $mode ? 'true' : 'false'; ?>"
			title="<?php esc_attr_e( 'Dark', 'cbw' ); ?>">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 13.2A9 9 0 1110.8 3a7.2 7.2 0 1010.2 10.2z"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Dark mode', 'cbw' ); ?></span>
		</button>
		<button class="cbw-mode__btn cbw-mode__btn--auto" type="button" data-mode="auto"
			aria-pressed="<?php echo '' === $mode ? 'true' : 'false'; ?>"
			title="<?php esc_attr_e( 'Match system', 'cbw' ); ?>">
			<span aria-hidden="true"><?php esc_html_e( 'Auto', 'cbw' ); ?></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Match system setting', 'cbw' ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * Swap in the reversed logo on a dark ground.
 *
 * The supplied artwork sets "GLOBAL" and "STAR" in near-black, which vanishes
 * on midnight navy, so a reversed variant is used instead. The <img> stays in
 * place to hold the layout; only what is painted changes.
 */
function cbw_dark_logo_style() {
	$att = (int) get_option( 'cbw_logo_dark' );
	if ( ! $att ) {
		return;
	}
	$url = wp_get_attachment_image_url( $att, 'full' );
	if ( ! $url ) {
		return;
	}
	?>
	<style id="cbw-dark-logo">
		[data-theme="dark"] .custom-logo{visibility:hidden}
		[data-theme="dark"] .custom-logo-link{
			background-image:url("<?php echo esc_url( $url ); ?>");
			background-repeat:no-repeat;
			background-position:left center;
			background-size:contain;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'cbw_dark_logo_style', 20 );
