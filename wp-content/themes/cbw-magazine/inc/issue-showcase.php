<?php
/**
 * The magazine showcase on the front page.
 *
 * A 3D shelf holds every issue's cover. Opening one lifts it off the shelf into
 * a reader where it turns like a printed magazine. Every page of every book is
 * rendered here into a <template>, so the pages carry the site's own content —
 * translated titles, real stories — and a closed book costs no requests.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets, on the front page only.
 */
function cbw_showcase_assets() {
	if ( ! is_front_page() ) {
		return;
	}
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'cbw-showcase-serif',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap',
		array(),
		null
	);
	// filemtime, so an edit reaches the browser without a version bump.
	wp_enqueue_style( 'cbw-showcase', $uri . '/assets/css/showcase.css', array( 'cbw-main' ), filemtime( $dir . '/assets/css/showcase.css' ) );
	wp_enqueue_script( 'cbw-showcase', $uri . '/assets/js/showcase.js', array(), filemtime( $dir . '/assets/js/showcase.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'cbw_showcase_assets', 20 );

/**
 * A night skyline for the backdrop, picked once a day.
 *
 * @return int Attachment ID, or 0.
 */
function cbw_showcase_backdrop() {
	$id = (int) get_transient( 'cbw_showcase_bg' );
	if ( $id && get_post( $id ) ) {
		return $id;
	}

	$args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'fields'         => 'ids',
	);

	$q  = new WP_Query( $args + array( 's' => 'Skyline at Night' ) );
	$id = $q->posts ? (int) $q->posts[0] : 0;

	if ( ! $id ) {
		$q  = new WP_Query( $args + array( 'meta_key' => 'cbw_photo_theme', 'meta_value' => 'city' ) );
		$id = $q->posts ? (int) $q->posts[0] : 0;
	}

	set_transient( 'cbw_showcase_bg', $id, DAY_IN_SECONDS );
	return $id;
}

/**
 * The stories inside an issue: that month's first, then the ones just before
 * it. $used carries across issues so no two books print the same story.
 *
 * @param WP_Post $issue Issue.
 * @param int     $count How many.
 * @param array   $used  Post IDs already placed in another book.
 * @return int[]
 */
function cbw_issue_stories( $issue, $count, &$used ) {
	$base = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$ids = get_posts( $base + array(
		'posts_per_page' => $count,
		'post__not_in'   => $used,
		'date_query'     => array( array(
			'year'  => (int) get_the_date( 'Y', $issue ),
			'month' => (int) get_the_date( 'n', $issue ),
		) ),
	) );

	if ( count( $ids ) < $count ) {
		$ids = array_merge( $ids, get_posts( $base + array(
			'posts_per_page' => $count - count( $ids ),
			'post__not_in'   => array_merge( $used, $ids ),
			'date_query'     => array( array( 'before' => get_the_date( 'Y-m-01', $issue ) ) ),
		) ) );
	}

	if ( count( $ids ) < $count ) {
		$ids = array_merge( $ids, get_posts( $base + array(
			'posts_per_page' => $count - count( $ids ),
			'post__not_in'   => array_merge( $used, $ids ),
		) ) );
	}

	$used = array_merge( $used, $ids );
	return $ids;
}

/**
 * Print the pages of one book.
 *
 * Page 1 is the cover, 2 the editor's letter, 3 the contents, then one page
 * per story, and the back cover last. The count is kept even, because a
 * printed magazine has no odd leaf.
 *
 * @param WP_Post $issue Issue.
 * @param int[]   $ids   Story IDs.
 */
function cbw_book_pages( $issue, $ids ) {
	$site  = get_bloginfo( 'name' );
	$month = get_the_date( 'F Y', $issue );
	$title = get_the_title( $issue );
	$run   = '<div class="fbp__run"><span>' . esc_html( $site ) . '</span><span>' . esc_html( $month ) . '</span></div>';
	$foot  = function ( $n ) {
		return '<div class="fbp__foot">' . (int) $n . '</div>';
	};
	$pages = array();

	/* Front cover */
	$pages[] = '<div class="fbp fbp--cover">'
		. wp_get_attachment_image( get_post_thumbnail_id( $issue ), 'full', false, array(
			'alt'       => $title,
			'loading'   => 'eager',
			'draggable' => 'false',
			'sizes'     => '(max-width: 820px) 92vw, 42vw',
		) )
		. '</div>';

	/* From the editor */
	$editor = get_user_by( 'login', 'e.hartley' );
	$lede   = wp_strip_all_tags( get_the_excerpt( $issue ) );
	$pages[] = '<div class="fbp fbp--editor">' . $run
		. '<span class="fbp__kicker">' . esc_html__( 'From the editor', 'cbw' ) . '</span>'
		. '<h3 class="fbp__title">' . esc_html( $title ) . '</h3>'
		. '<div class="fbp__text">'
		. ( $lede ? '<p class="fbp__lede">' . esc_html( $lede ) . '</p>' : '' )
		. '<p>' . esc_html__( 'Every edition is built around one idea rather than a news cycle. In this one, we follow it through the companies, founders and chief executives living it right now.', 'cbw' ) . '</p>'
		. '</div>'
		. '<p class="fbp__sign">' . esc_html( $editor ? $editor->display_name : $site )
		. '<span>' . esc_html__( 'Editor-in-Chief', 'cbw' ) . '</span></p>'
		. $foot( 2 ) . '</div>';

	/* Contents */
	$toc = '';
	foreach ( $ids as $k => $id ) {
		$term = cbw_primary_category( $id );
		$toc .= '<li><span class="fbp__tocnum">' . esc_html( sprintf( '%02d', 4 + $k ) ) . '</span>'
			. '<span class="fbp__toctext"><em>' . esc_html( 0 === $k ? __( 'Cover story', 'cbw' ) : ( $term ? $term->name : '' ) ) . '</em>'
			. esc_html( wp_trim_words( get_the_title( $id ), 10, '…' ) ) . '</span></li>';
	}
	$pages[] = '<div class="fbp fbp--contents">' . $run
		. '<span class="fbp__kicker">' . esc_html__( 'In this issue', 'cbw' ) . '</span>'
		. '<h3 class="fbp__title">' . esc_html( $title ) . '</h3>'
		. '<ol class="fbp__toc">' . $toc . '</ol>'
		. $foot( 3 ) . '</div>';

	/* One page per story */
	foreach ( $ids as $k => $id ) {
		$post = get_post( $id );
		$term = cbw_primary_category( $id );
		$img  = get_post_thumbnail_id( $id );
		$para = '';
		if ( preg_match( '#<p>(.*?)</p>#s', $post->post_content, $m ) ) {
			$para = wp_trim_words( wp_strip_all_tags( $m[1] ), 70, '…' );
		}

		$pages[] = '<article class="fbp fbp--story">' . $run
			. ( $img ? '<figure class="fbp__img">' . wp_get_attachment_image( $img, 'cbw-card', false, array(
				'alt'       => '',
				'loading'   => 'eager',
				'draggable' => 'false',
			) ) . '</figure>' : '' )
			. '<span class="fbp__kicker">' . esc_html( 0 === $k ? __( 'Cover story', 'cbw' ) : ( $term ? $term->name : '' ) ) . '</span>'
			. '<h3 class="fbp__title">' . esc_html( get_the_title( $id ) ) . '</h3>'
			. '<p class="fbp__stand">' . esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ) . '</p>'
			. '<div class="fbp__text"><p>' . esc_html( $para ) . '</p></div>'
			. '<a class="fbp__more" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html__( 'Continue reading', 'cbw' ) . ' &rarr;</a>'
			. $foot( 4 + $k ) . '</article>';
	}

	/* Keep an even count once the back cover is added: pad with a blank page
	   when what we have so far is already even. */
	if ( 0 === count( $pages ) % 2 ) {
		$pages[] = '<div class="fbp fbp--blank"></div>';
	}

	/* Back cover */
	$logo    = (int) get_option( 'cbw_logo_dark' );
	$pages[] = '<div class="fbp fbp--back">'
		. ( $logo
			? wp_get_attachment_image( $logo, 'medium', false, array( 'alt' => $site, 'class' => 'fbp__logo', 'loading' => 'eager', 'draggable' => 'false' ) )
			: '<strong class="fbp__logo">' . esc_html( $site ) . '</strong>' )
		. '<p class="fbp__tag">' . esc_html( get_bloginfo( 'description' ) ) . '</p>'
		. '<p class="fbp__note">' . esc_html__( 'Print and digital, eight editions a year.', 'cbw' ) . '</p>'
		. '<a class="fbp__more fbp__more--light" href="' . esc_url( get_permalink( $issue ) ) . '">' . esc_html__( 'Read the full issue', 'cbw' ) . ' &rarr;</a>'
		. '<span class="fbp__url">' . esc_html( preg_replace( '#^https?://#', '', home_url() ) ) . '</span>'
		. '</div>';

	echo implode( '', $pages ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped piecewise above.
}

/**
 * Render the showcase section and the reader.
 */
function cbw_issue_showcase() {
	$issues = get_posts( array(
		'post_type'      => 'cbw_issue',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	if ( ! $issues ) {
		return;
	}

	$bg   = cbw_showcase_backdrop();
	$used = array();
	$l10n = array(
		'cover' => __( 'Cover', 'cbw' ),
		'back'  => __( 'Back cover', 'cbw' ),
		/* translators: 1: left page, 2: right page, 3: page count. */
		'pages' => __( 'Pages %1$d–%2$d of %3$d', 'cbw' ),
		/* translators: 1: page number, 2: page count. */
		'page'  => __( 'Page %1$d of %2$d', 'cbw' ),
	);
	?>
	<section class="cbw-showcase" aria-labelledby="cbw-showcase-title">
		<?php if ( $bg ) : ?>
			<div class="cbw-showcase__bg" aria-hidden="true">
				<?php echo wp_get_attachment_image( $bg, 'full', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '100vw' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="cbw-wrap cbw-showcase__head cbw-reveal">
			<h2 class="cbw-showcase__title" id="cbw-showcase-title">
				<?php
				printf(
					/* translators: %s: the emphasised second half of the heading, "Make the Cover". */
					esc_html__( 'Where Leaders %s', 'cbw' ),
					'<em>' . esc_html__( 'Make the Cover', 'cbw' ) . '</em>'
				);
				?>
			</h2>
			<p class="cbw-showcase__tag"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<p class="cbw-showcase__sub"><?php esc_html_e( 'A magazine for founders, chief executives and the teams building what comes next.', 'cbw' ); ?></p>
		</div>

		<div class="cbw-shelf" aria-roledescription="<?php esc_attr_e( 'carousel', 'cbw' ); ?>" aria-label="<?php esc_attr_e( 'Magazine issues', 'cbw' ); ?>">
			<button class="cbw-shelf__nav cbw-shelf__nav--prev" type="button" data-go="-1">
				<?php cbw_icon( 'chevron-left', 22 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Previous issue', 'cbw' ); ?></span>
			</button>

			<div class="cbw-shelf__stage">
				<?php foreach ( $issues as $i => $issue ) :
					$title = get_the_title( $issue );
					$date  = get_the_date( 'F Y', $issue );
					?>
					<a class="cbw-shelf__item<?php echo 0 === $i ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( get_permalink( $issue ) ); ?>"
						draggable="false"
						data-book="cbw-book-<?php echo (int) $issue->ID; ?>"
						data-title="<?php echo esc_attr( $title ); ?>"
						data-date="<?php echo esc_attr( $date ); ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: issue title and month. */ __( 'Open %s', 'cbw' ), $title . ', ' . $date ) ); ?>"
						<?php echo $i ? 'tabindex="-1"' : ''; ?>>
						<span class="cbw-shelf__cover">
							<?php echo wp_get_attachment_image( get_post_thumbnail_id( $issue ), 'cbw-cover', false, array( 'alt' => '', 'loading' => 'lazy', 'draggable' => 'false' ) ); ?>
							<span class="cbw-shelf__open" aria-hidden="true"><?php cbw_icon( 'book', 16 ); ?><?php esc_html_e( 'Read this issue', 'cbw' ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>

			<button class="cbw-shelf__nav cbw-shelf__nav--next" type="button" data-go="1">
				<?php cbw_icon( 'chevron-right', 22 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Next issue', 'cbw' ); ?></span>
			</button>

			<p class="cbw-shelf__caption" aria-live="polite">
				<strong class="cbw-shelf__name"><?php echo esc_html( get_the_title( $issues[0] ) ); ?></strong>
				<span class="cbw-shelf__date"><?php echo esc_html( get_the_date( 'F Y', $issues[0] ) ); ?></span>
				<span class="cbw-shelf__hint"><?php esc_html_e( 'Tap a cover to open the issue', 'cbw' ); ?></span>
			</p>
			<button class="cbw-shelf__play" type="button" aria-pressed="false">
				<?php cbw_icon( 'pause', 16 ); ?>
				<?php cbw_icon( 'play', 16 ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Pause slideshow', 'cbw' ); ?></span>
			</button>
		</div>

		<?php foreach ( $issues as $issue ) : ?>
			<template id="cbw-book-<?php echo (int) $issue->ID; ?>"><?php cbw_book_pages( $issue, cbw_issue_stories( $issue, 6, $used ) ); ?></template>
		<?php endforeach; ?>
	</section>

	<dialog class="cbw-book" id="cbw-book" data-l10n="<?php echo esc_attr( wp_json_encode( $l10n ) ); ?>">
		<div class="cbw-book__backdrop" data-close></div>

		<div class="cbw-book__top">
			<p class="cbw-book__title"><strong></strong><span></span></p>
			<button class="cbw-book__btn" type="button" data-close>
				<?php cbw_icon( 'close', 20 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Close', 'cbw' ); ?></span>
			</button>
		</div>

		<div class="cbw-book__viewport">
			<div class="cbw-book__fly">
				<div class="cbw-book__stage"><div class="cbw-book__book"></div></div>
			</div>
		</div>

		<div class="cbw-book__bar">
			<button class="cbw-book__btn" type="button" data-turn="-1">
				<?php cbw_icon( 'chevron-left', 22 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Previous page', 'cbw' ); ?></span>
			</button>
			<span class="cbw-book__count" aria-live="polite"></span>
			<button class="cbw-book__btn" type="button" data-turn="1">
				<?php cbw_icon( 'chevron-right', 22 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Next page', 'cbw' ); ?></span>
			</button>
		</div>
	</dialog>
	<?php
}
