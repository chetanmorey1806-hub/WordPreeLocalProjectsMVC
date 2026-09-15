<?php
/**
 * Regenerates the editor avatars in the Midnight Navy + Champagne Gold
 * palette. Magazine covers are drawn from the site's photographs by covers.php.
 */
define( 'ART_FONT_BOLD', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf' );
define( 'ART_FONT_REG',  '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf' );

/** Midnight navy vertical ground. */
function art_ground( $im, $w, $h, $shift = 0 ) {
	for ( $y = 0; $y < $h; $y++ ) {
		$t = $y / max( 1, $h - 1 );
		imageline( $im, 0, $y, $w, $y, imagecolorallocate( $im,
			(int) ( 11 + 11 * ( 1 - $t ) + $shift ),
			(int) ( 23 + 15 * ( 1 - $t ) + $shift ),
			(int) ( 38 + 20 * ( 1 - $t ) + $shift ) ) );
	}
}

/** Editor avatar: midnight navy tile with champagne rule. */
function art_avatar( $path, $initials, $step, $w = 800 ) {
	$h  = (int) round( $w * .75 );
	$im = imagecreatetruecolor( $w, $h );
	imageantialias( $im, true );
	art_ground( $im, $w, $h, $step * 3 );

	// Champagne bloom, varied per editor by size and position only, so the
	// palette stays consistent across the team.
	$acc = imagecolorallocatealpha( $im, 201, 164, 76, 82 );
	imagefilledellipse( $im,
		(int) ( $w * ( .62 + .08 * ( $step % 3 ) ) ),
		(int) ( $h * ( .16 + .06 * ( $step % 2 ) ) ),
		(int) ( $w * ( .62 + .07 * ( $step % 3 ) ) ),
		(int) ( $w * ( .62 + .07 * ( $step % 3 ) ) ), $acc );

	$white = imagecolorallocate( $im, 255, 255, 255 );
	$gold  = imagecolorallocate( $im, 227, 200, 117 );

	if ( is_readable( ART_FONT_BOLD ) ) {
		$fs = $h * .34;
		$bb = imagettfbbox( $fs, 0, ART_FONT_BOLD, $initials );
		imagettftext( $im, $fs, 0,
			(int) ( ( $w - ( $bb[2] - $bb[0] ) ) / 2 ),
			(int) ( ( $h + ( $bb[1] - $bb[7] ) ) / 2 - $h * .06 ),
			$white, ART_FONT_BOLD, $initials );
	}
	imagefilledrectangle( $im, (int) ( $w * .44 ), (int) ( $h * .74 ), (int) ( $w * .56 ), (int) ( $h * .74 ) + 6, $gold );

	imagejpeg( $im, $path, 90 );
	imagedestroy( $im );
}

/* ------------------------------------------------------------- Rebuild */
require_once ABSPATH . 'wp-admin/includes/image.php';
$uploads = wp_upload_dir();
$SP = dirname( __FILE__ );

function art_attach( $file, $title, $parent, $alt ) {
	$uploads = wp_upload_dir();
	$dest = trailingslashit( $uploads['path'] ) . wp_unique_filename( $uploads['path'], basename( $file ) );
	if ( ! @copy( $file, $dest ) ) { return 0; }
	$att = wp_insert_attachment( array(
		'guid'           => trailingslashit( $uploads['url'] ) . basename( $dest ),
		'post_mime_type' => 'image/jpeg',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $dest, $parent );
	if ( is_wp_error( $att ) || ! $att ) { return 0; }
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $dest ) );
	update_post_meta( $att, '_wp_attachment_image_alt', $alt );
	return $att;
}

$avatars = 0; $step = 0;
foreach ( get_users( array( 'role__in' => array( 'author' ), 'orderby' => 'ID' ) ) as $u ) {
	$parts = preg_split( '/\s+/', trim( $u->display_name ) );
	$ini   = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) . ( isset( $parts[1] ) ? mb_substr( $parts[1], 0, 1 ) : '' ) );
	$file  = $SP . '/av-' . $u->user_nicename . '.jpg';
	art_avatar( $file, $ini, $step++ );
	$old = get_user_meta( $u->ID, 'cbw_avatar_id', true );
	$att = art_attach( $file, $u->display_name, 0, $u->display_name );
	@unlink( $file );
	if ( $att ) {
		if ( $old ) { wp_delete_attachment( $old, true ); }
		update_user_meta( $u->ID, 'cbw_avatar_id', $att );
		$avatars++;
	}
}

WP_CLI::success( "Avatars regenerated: {$avatars}" );
