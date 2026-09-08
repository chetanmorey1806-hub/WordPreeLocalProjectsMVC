<?php
/**
 * Regenerates the magazine covers and editor avatars in the
 * Midnight Navy + Champagne Gold palette.
 */
define( 'ART_FONT_BOLD', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf' );
define( 'ART_FONT_REG',  '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf' );

/** Midnight navy vertical ground. */
function art_ground( $im, $w, $h, $shift = 0 ) {
	for ( $y = 0; $y < $h; $y++ ) {
		$t = $y / max( 1, $h - 1 );
		imageline( $im, 0, $y, $w, $y, imagecolorallocate( $im,
			(int) ( 16 + 14 * ( 1 - $t ) + $shift ),
			(int) ( 28 + 20 * ( 1 - $t ) + $shift ),
			(int) ( 66 + 26 * ( 1 - $t ) + $shift ) ) );
	}
}

/**
 * Magazine cover: midnight navy with a champagne geometric composition.
 */
function art_cover( $path, $label, $title, $seed ) {
	mt_srand( $seed );
	list( $w, $h ) = array( 900, 1200 );
	$im = imagecreatetruecolor( $w, $h );
	imageantialias( $im, true );
	art_ground( $im, $w, $h );

	$champ = function ( $alpha ) use ( $im ) {
		return imagecolorallocatealpha( $im, 192, 162, 101, $alpha );
	};
	$champ_l = function ( $alpha ) use ( $im ) {
		return imagecolorallocatealpha( $im, 216, 190, 139, $alpha );
	};

	$variant = $seed % 3;
	if ( 0 === $variant ) {
		// Concentric champagne arcs.
		$cx = ( $seed % 2 ) ? (int) ( $w * .78 ) : (int) ( $w * .22 );
		$cy = (int) ( $h * .3 );
		imagesetthickness( $im, 2 );
		for ( $r = (int) ( $w * .18 ); $r < $w * 1.8; $r += (int) ( $w * .07 ) ) {
			imagearc( $im, $cx, $cy, $r, $r, 0, 360, $champ( 86 ) );
		}
		imagefilledellipse( $im, $cx, $cy, (int) ( $w * .16 ), (int) ( $w * .16 ), $champ( 58 ) );
	} elseif ( 1 === $variant ) {
		// Diagonal champagne bars.
		$step = (int) ( $w * .06 );
		for ( $x = -$h; $x < $w * 1.2; $x += $step ) {
			$thick = ( 0 === ( (int) ( $x / $step ) % 3 ) ) ? (int) ( $step * .4 ) : (int) ( $step * .12 );
			imagefilledpolygon( $im, array( $x, 0, $x + $thick, 0, $x + $thick + $h, $h, $x + $h, $h ), 4, $champ( 94 ) );
		}
		imagefilledellipse( $im, (int) ( $w * .72 ), (int) ( $h * .3 ), (int) ( $w * .58 ), (int) ( $w * .58 ), $champ_l( 98 ) );
	} else {
		// Champagne dot matrix under a soft disc.
		$gap = (int) ( $w * .038 );
		$dot = max( 3, (int) ( $gap * .2 ) );
		for ( $x = $gap; $x < $w; $x += $gap ) {
			for ( $y = $gap; $y < $h * .78; $y += $gap ) {
				imagefilledellipse( $im, $x, $y, $dot, $dot, $champ( 80 ) );
			}
		}
		imagefilledellipse( $im, (int) ( $w * .66 ), (int) ( $h * .34 ), (int) ( $w * .5 ), (int) ( $w * .5 ), $champ_l( 96 ) );
		imagesetthickness( $im, 3 );
		imagearc( $im, (int) ( $w * .66 ), (int) ( $h * .34 ), (int) ( $w * .62 ), (int) ( $w * .62 ), 0, 360, $champ( 68 ) );
	}
	imagesetthickness( $im, 1 );

	// Scrim so the masthead and title stay legible.
	$start = (int) ( $h * .48 );
	for ( $y = $start; $y < $h; $y++ ) {
		$t = ( $y - $start ) / max( 1, $h - $start );
		imageline( $im, 0, $y, $w, $y, imagecolorallocatealpha( $im, 5, 11, 28, (int) ( 127 - 96 * $t ) ) );
	}

	$white = imagecolorallocate( $im, 255, 255, 255 );
	$gold  = imagecolorallocate( $im, 216, 190, 139 );
	$pad   = (int) ( $w * .085 );

	if ( is_readable( ART_FONT_BOLD ) ) {
		$ms = $w * .034;
		imagettftext( $im, $ms, 0, $pad, $pad + (int) $ms, $white, ART_FONT_BOLD, 'GMS' );
		$bx = imagettfbbox( $ms, 0, ART_FONT_BOLD, 'GMS' );
		imagettftext( $im, $w * .019, 0, $pad + ( $bx[2] - $bx[0] ) + (int) ( $w * .02 ), $pad + (int) $ms, $gold, ART_FONT_REG, 'GLOBAL MEDIA STAR' );

		// Month label above a champagne rule.
		$ls = $w * .026;
		imagefilledrectangle( $im, $pad, (int) ( $h * .70 ), $pad + (int) ( $w * .13 ), (int) ( $h * .70 ) + 6, $gold );
		imagettftext( $im, $ls, 0, $pad, (int) ( $h * .70 ) - (int) ( $ls * .55 ), $gold, ART_FONT_BOLD, mb_strtoupper( $label ) );

		// Issue title, wrapped.
		$ts    = $w * .054;
		$maxw  = $w - $pad * 2;
		$lines = array(); $cur = '';
		foreach ( preg_split( '/\s+/', $title ) as $word ) {
			$try = $cur ? $cur . ' ' . $word : $word;
			$bb  = imagettfbbox( $ts, 0, ART_FONT_BOLD, $try );
			if ( ( $bb[2] - $bb[0] ) > $maxw && $cur ) { $lines[] = $cur; $cur = $word; }
			else { $cur = $try; }
		}
		if ( $cur ) { $lines[] = $cur; }
		$y = (int) ( $h * .70 ) + (int) ( $ts * 1.9 );
		foreach ( array_slice( $lines, 0, 3 ) as $ln ) {
			imagettftext( $im, $ts, 0, $pad, $y, $white, ART_FONT_BOLD, $ln );
			$y += (int) ( $ts * 1.32 );
		}
	}

	imagejpeg( $im, $path, 86 );
	imagedestroy( $im );
}

/** Editor avatar: midnight navy tile with champagne rule. */
function art_avatar( $path, $initials, $step, $w = 800 ) {
	$h  = (int) round( $w * .75 );
	$im = imagecreatetruecolor( $w, $h );
	imageantialias( $im, true );
	art_ground( $im, $w, $h, $step * 3 );

	// Champagne bloom, varied per editor by size and position only, so the
	// palette stays consistent across the team.
	$acc = imagecolorallocatealpha( $im, 192, 162, 101, 82 );
	imagefilledellipse( $im,
		(int) ( $w * ( .62 + .08 * ( $step % 3 ) ) ),
		(int) ( $h * ( .16 + .06 * ( $step % 2 ) ) ),
		(int) ( $w * ( .62 + .07 * ( $step % 3 ) ) ),
		(int) ( $w * ( .62 + .07 * ( $step % 3 ) ) ), $acc );

	$white = imagecolorallocate( $im, 255, 255, 255 );
	$gold  = imagecolorallocate( $im, 216, 190, 139 );

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

$covers = 0;
foreach ( get_posts( array( 'post_type' => 'cbw_issue', 'posts_per_page' => -1, 'post_status' => 'publish' ) ) as $iss ) {
	$old  = get_post_thumbnail_id( $iss->ID );
	$file = $SP . '/cover-' . $iss->post_name . '.jpg';
	art_cover( $file, get_the_date( 'F Y', $iss ), $iss->post_title, abs( crc32( $iss->post_name ) ) );
	$att = art_attach( $file, $iss->post_title, $iss->ID, $iss->post_title );
	@unlink( $file );
	if ( $att ) {
		set_post_thumbnail( $iss->ID, $att );
		if ( $old ) { wp_delete_attachment( $old, true ); }
		$covers++;
	}
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

WP_CLI::success( "Covers regenerated: {$covers} | avatars: {$avatars}" );
