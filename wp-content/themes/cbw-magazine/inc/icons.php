<?php
/**
 * Inline SVG icon set.
 *
 * Stroke icons drawn on a 24px grid (paths after Lucide, ISC licence), inlined
 * so they inherit currentColor and cost no extra requests.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Path data for every icon, keyed by name.
 *
 * @return array
 */
function cbw_icon_paths() {
	return array(
		'home'          => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
		'briefcase'     => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
		'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		'star'          => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
		'rocket'        => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
		'megaphone'     => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
		'mic'           => '<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/>',
		'book'          => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
		'info'          => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
		'layers'        => '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
		'globe'         => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
		'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
		'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
		'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
		'arrow-right'   => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
		'arrow-up'      => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
		'check'         => '<path d="M20 6 9 17l-5-5"/>',
		'clock'         => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'calendar'      => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'flame'         => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
		'trending'      => '<path d="M22 7 13.5 15.5 8.5 10.5 2 17"/><path d="M16 7h6v6"/>',
		'newspaper'     => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/>',
		'pen'           => '<path d="M12 20h9"/><path d="M16.38 3.62a1 1 0 0 1 3 3L7.37 18.63a2 2 0 0 1-.86.5l-2.87.84a.5.5 0 0 1-.62-.62l.84-2.87a2 2 0 0 1 .5-.86z"/>',
		'languages'     => '<path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/>',
		'mail'          => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'play'          => '<path d="M6 3v18l14-9z"/>',
		'pause'         => '<rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/>',
		'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'close'         => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'sparkles'      => '<path d="M12 3 13.9 8.8 20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2z"/><path d="M19 2v4M21 4h-4"/>',
	);
}

/**
 * Markup for one icon.
 *
 * @param string $name  Icon name from cbw_icon_paths().
 * @param int    $size  Rendered size in px.
 * @param string $class Extra class.
 * @return string
 */
function cbw_get_icon( $name, $size = 18, $class = '' ) {
	$paths = cbw_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	// The solid shapes read better filled at small sizes.
	$filled = in_array( $name, array( 'play', 'pause' ), true );

	return sprintf(
		'<svg class="cbw-icon cbw-icon--%1$s%2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="%4$s" stroke="%5$s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%6$s</svg>',
		esc_attr( $name ),
		$class ? ' ' . esc_attr( $class ) : '',
		(int) $size,
		$filled ? 'currentColor' : 'none',
		$filled ? 'none' : 'currentColor',
		$paths[ $name ]
	);
}

/**
 * Echo an icon.
 */
function cbw_icon( $name, $size = 18, $class = '' ) {
	echo cbw_get_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup.
}

/**
 * The icon that stands for a top-level section, by page slug.
 *
 * @param int|WP_Post $page Page.
 * @return string Icon name.
 */
function cbw_section_icon( $page ) {
	$map = array(
		'home'          => 'home',
		'business'      => 'briefcase',
		'entrepreneurs' => 'users',
		'features'      => 'star',
		'startups'      => 'rocket',
		'marketing'     => 'megaphone',
		'pr-media'      => 'mic',
		'magazine'      => 'book',
		'about'         => 'info',
		'stories'       => 'newspaper',
	);
	$slug = get_post_field( 'post_name', $page );
	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'layers';
}
