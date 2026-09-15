<?php
/**
 * Magazine covers from the site's own photographs.
 *
 * Each issue gets a full-bleed photo from the media library under the GMS
 * masthead, with its lead stories as cover lines: the same stories the
 * homepage flipbook opens to. cover-art.py does the drawing; this script
 * picks the photos, attaches the results to the issues and carries each
 * photo's credit over, since the Commons licences require attribution on
 * derived images too.
 *
 * Run from the WordPress root:  php brand/covers.php
 * Draw only, into a folder, without touching the site:
 *                                php brand/covers.php --preview=/some/dir
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$preview = '';
foreach ( array_slice( $argv, 1 ) as $arg ) {
	if ( 0 === strpos( $arg, '--preview=' ) ) {
		$preview = substr( $arg, 10 );
	}
}

require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/*
 * Issue slug => [ media library photo title, horizontal focus 0–1 ].
 * Places rather than faces: the stories are the magazine's own, so no real
 * person is put on a cover.
 */
$photos = array(
	'the-reinvention-issue'    => array( 'Connaught Place, New Delhi at night', .62 ),
	'the-founders-issue'       => array( 'BHIVE Workspace - HSR Bangalore', .55 ),
	'the-capital-issue'        => array( 'Hitec City buildings (30087)', .5 ),
	'the-operators-issue'      => array( 'WDG4D Freight Locomotive', .55 ),
	'the-scale-issue'          => array( 'Skyline pic Mumbai', .4 ),
	'the-next-decade-issue'    => array( 'DLF Epitome Tower, DLF Phase 3, Gurugram', .3 ),
	'the-leadership-issue'     => array( 'Bandra Worli sea link, Mumbai', .7 ),
	'the-global-markets-issue' => array( 'International Container Transshipment Terminal, Kochi', .5 ),
);

function covers_text( $s ) {
	return html_entity_decode( wp_strip_all_tags( $s ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

function covers_photo( $title ) {
	$ids = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	return $ids ? (int) $ids[0] : 0;
}

/* Same issues, order and story picks as the homepage showcase. */
$issues = get_posts( array(
	'post_type'      => 'cbw_issue',
	'posts_per_page' => 8,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

$tmp  = $preview ? untrailingslashit( $preview ) : trailingslashit( get_temp_dir() ) . 'gms-covers-' . getmypid();
wp_mkdir_p( $tmp );
$jobs = array();
$src  = array();
$used = array();

foreach ( $issues as $iss ) {
	$ids = cbw_issue_stories( $iss, 6, $used );
	if ( empty( $photos[ $iss->post_name ] ) ) {
		fwrite( STDERR, "skip {$iss->post_name}: no photo chosen\n" );
		continue;
	}
	list( $title, $focus ) = $photos[ $iss->post_name ];
	$photo = covers_photo( $title );
	$file  = $photo ? get_attached_file( $photo ) : '';
	if ( ! $file || ! is_readable( $file ) ) {
		fwrite( STDERR, "skip {$iss->post_name}: photo \"{$title}\" not found\n" );
		continue;
	}

	$lines = array();
	foreach ( array_slice( $ids, 0, 2 ) as $k => $id ) {
		$term    = cbw_primary_category( $id );
		$lines[] = array(
			'kicker' => 0 === $k ? 'Cover story' : ( $term ? covers_text( $term->name ) : 'Inside' ),
			'text'   => covers_text( get_the_title( $id ) ),
		);
	}

	$src[ $iss->ID ] = $photo;
	$jobs[]          = array(
		'id'       => $iss->ID,
		'out'      => $tmp . '/cover-' . $iss->post_name . '.jpg',
		'photo'    => $file,
		'focus'    => $focus,
		'masthead' => 'GMS',
		'brand'    => 'Global Media Star',
		'month'    => get_the_date( 'F Y', $iss ),
		'edition'  => 'India edition',
		'title'    => covers_text( $iss->post_title ),
		'excerpt'  => covers_text( get_the_excerpt( $iss ) ),
		'lines'    => $lines,
	);
}

file_put_contents( $tmp . '/jobs.json', wp_json_encode( $jobs ) );
passthru( 'python3 ' . escapeshellarg( __DIR__ . '/cover-art.py' ) . ' ' . escapeshellarg( $tmp . '/jobs.json' ), $status );
if ( 0 !== $status ) {
	fwrite( STDERR, "cover-art.py failed ({$status})\n" );
	exit( 1 );
}
if ( $preview ) {
	echo "Preview drawn in {$tmp}; nothing attached.\n";
	exit( 0 );
}

$done = 0;
foreach ( $jobs as $job ) {
	if ( ! is_readable( $job['out'] ) ) {
		continue;
	}
	$uploads = wp_upload_dir();
	$dest    = trailingslashit( $uploads['path'] ) . wp_unique_filename( $uploads['path'], basename( $job['out'] ) );
	if ( ! @copy( $job['out'], $dest ) ) {
		continue;
	}
	$att = wp_insert_attachment( array(
		'guid'           => trailingslashit( $uploads['url'] ) . basename( $dest ),
		'post_mime_type' => 'image/jpeg',
		'post_title'     => $job['title'],
		'post_status'    => 'inherit',
	), $dest, $job['id'] );
	if ( ! $att || is_wp_error( $att ) ) {
		continue;
	}
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $dest ) );
	/* translators: 1: issue title, 2: month and year. */
	update_post_meta( $att, '_wp_attachment_image_alt', sprintf( 'Cover of %1$s, %2$s', $job['title'], $job['month'] ) );
	foreach ( array( 'cbw_credit_artist', 'cbw_credit_license', 'cbw_credit_source' ) as $key ) {
		update_post_meta( $att, $key, get_post_meta( $src[ $job['id'] ], $key, true ) );
	}
	update_post_meta( $att, 'cbw_cover_photo', $src[ $job['id'] ] );

	// Replace the previous cover, but only one this script or brandart.php
	// made for this issue, never a photo from the library.
	$old = get_post_thumbnail_id( $job['id'] );
	set_post_thumbnail( $job['id'], $att );
	if ( $old && (int) get_post_field( 'post_parent', $old ) === (int) $job['id'] && (int) $old !== (int) $src[ $job['id'] ] ) {
		wp_delete_attachment( $old, true );
	}
	$done++;
	echo 'cover #', $att, ' for ', $job['title'], ' from photo #', $src[ $job['id'] ], "\n";
}

array_map( 'unlink', glob( $tmp . '/*' ) );
rmdir( $tmp );
echo "Covers made: {$done} of " . count( $issues ) . "\n";
