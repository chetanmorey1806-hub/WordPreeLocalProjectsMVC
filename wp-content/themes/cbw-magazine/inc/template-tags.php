<?php
/**
 * Reusable template helpers.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the category tied to a page (set as post meta when the page is created,
 * otherwise matched by the page title).
 *
 * @param int|null $page_id Page ID. Defaults to the current post.
 * @return WP_Term|null
 */
function cbw_page_category( $page_id = null ) {
	$page_id = $page_id ? $page_id : get_the_ID();

	$slug = get_post_meta( $page_id, '_cbw_category', true );
	if ( $slug ) {
		$term = get_category_by_slug( $slug );
		if ( $term ) {
			return $term;
		}
	}

	$term = get_term_by( 'name', get_the_title( $page_id ), 'category' );
	return $term ? $term : null;
}

/**
 * Primary category of a post, used for the coloured eyebrow label.
 *
 * @param int|null $post_id Post ID.
 * @return WP_Term|null
 */
function cbw_primary_category( $post_id = null ) {
	$terms = get_the_category( $post_id ? $post_id : get_the_ID() );
	if ( empty( $terms ) ) {
		return null;
	}

	// Prefer the deepest term so "Startup News" wins over its "Startups" parent.
	usort( $terms, function ( $a, $b ) {
		return count( get_ancestors( $b->term_id, 'category' ) ) - count( get_ancestors( $a->term_id, 'category' ) );
	} );

	return $terms[0];
}

/**
 * Print the category eyebrow badge.
 */
function cbw_category_badge( $post_id = null ) {
	$term = cbw_primary_category( $post_id );
	if ( ! $term ) {
		return;
	}
	printf(
		'<a class="cbw-badge" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * Byline: author, date, reading time.
 */
function cbw_meta_line( $show_author = true ) {
	echo '<div class="cbw-meta">';
	if ( $show_author ) {
		printf(
			'<span class="cbw-meta__author">%s <a href="%s">%s</a></span>',
			esc_html__( 'By', 'cbw' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
	printf(
		'<time class="cbw-meta__date" datetime="%s">%s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	printf( '<span class="cbw-meta__read">%s</span>', esc_html( cbw_reading_time() ) );
	echo '</div>';
}

/**
 * Rough reading time for the current post.
 *
 * @return string
 */
function cbw_reading_time() {
	$words   = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: number of minutes. */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'cbw' ), $minutes );
}

/**
 * Featured image with a lettered fallback tile so cards never render empty.
 *
 * @param string $size Image size.
 */
function cbw_thumbnail( $size = 'cbw-card' ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, array( 'loading' => 'lazy', 'alt' => the_title_attribute( array( 'echo' => false ) ) ) );
		return;
	}

	$title = get_the_title();
	$seed  = absint( crc32( $title ) ) % 360;
	printf(
		'<span class="cbw-fallback" style="--seed:%1$ddeg" aria-hidden="true">%2$s</span>',
		$seed,
		esc_html( mb_strtoupper( mb_substr( $title, 0, 1 ) ) )
	);
}

/**
 * Article card.
 *
 * @param string $variant hero|feature|card|list.
 */
function cbw_card( $variant = 'card' ) {
	?>
	<article <?php post_class( 'cbw-card cbw-card--' . esc_attr( $variant ) . ' cbw-reveal' ); ?>>
		<a class="cbw-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php cbw_thumbnail( 'list' === $variant ? 'cbw-thumb' : ( 'hero' === $variant ? 'cbw-hero' : 'cbw-card' ) ); ?>
		</a>
		<div class="cbw-card__body">
			<?php cbw_category_badge(); ?>
			<h3 class="cbw-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php if ( 'list' !== $variant ) : ?>
				<p class="cbw-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<?php cbw_meta_line( 'hero' === $variant || 'feature' === $variant ); ?>
		</div>
	</article>
	<?php
}

/**
 * Section heading with an optional "view all" link.
 *
 * @param string $title    Heading.
 * @param string $link     "View all" target.
 * @param string $kicker   Small label above the heading.
 * @param string $carousel ID of a carousel track to add previous/next buttons for.
 * @param string $icon     Icon shown beside the heading.
 */
function cbw_section_heading( $title, $link = '', $kicker = '', $carousel = '', $icon = '' ) {
	echo '<header class="cbw-section__head">';
	echo '<div class="cbw-section__headtext">';
	if ( $icon ) {
		echo '<span class="cbw-section__icon">' . cbw_get_icon( $icon, 20 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<div>';
	if ( $kicker ) {
		printf( '<span class="cbw-section__kicker">%s</span>', esc_html( $kicker ) );
	}
	printf( '<h2 class="cbw-section__title">%s</h2>', esc_html( $title ) );
	echo '</div></div>';
	if ( $link || $carousel ) {
		echo '<div class="cbw-section__tools">';
		if ( $link ) {
			printf(
				'<a class="cbw-viewall" href="%s">%s %s</a>',
				esc_url( $link ),
				esc_html__( 'View all', 'cbw' ),
				cbw_get_icon( 'arrow-right', 15 ) // phpcs:ignore WordPress.Security.EscapeOutput
			);
		}
		if ( $carousel ) {
			cbw_carousel_nav( $carousel );
		}
		echo '</div>';
	}
	echo '</header>';
}

/**
 * Previous / next buttons for a scroll-snap carousel.
 *
 * @param string $target ID of the track they scroll.
 */
function cbw_carousel_nav( $target ) {
	?>
	<div class="cbw-carousel__nav">
		<button class="cbw-carousel__btn" type="button" data-dir="-1" aria-controls="<?php echo esc_attr( $target ); ?>">
			<?php cbw_icon( 'chevron-left', 18 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'cbw' ); ?></span>
		</button>
		<button class="cbw-carousel__btn" type="button" data-dir="1" aria-controls="<?php echo esc_attr( $target ); ?>">
			<?php cbw_icon( 'chevron-right', 18 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Next', 'cbw' ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * Social profile links.
 *
 * @param string $class List class.
 */
function cbw_social_links( $class = 'cbw-social' ) {
	$icons = array(
		'LinkedIn'  => '<path d="M4.98 3.5A2.5 2.5 0 112.5 6 2.5 2.5 0 014.98 3.5zM3 8.98h4v12H3zM9.5 8.98h3.83v1.64h.05a4.2 4.2 0 013.78-2.08c4.04 0 4.79 2.66 4.79 6.12v6.32h-4v-5.6c0-1.34-.02-3.06-1.86-3.06-1.87 0-2.15 1.46-2.15 2.96v5.7h-4z"/>',
		'X'         => '<path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.66l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.01 4.13H5.05z"/>',
		'Facebook'  => '<path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7H7.9v-2.9h2.54V9.85c0-2.52 1.5-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.45 2.9h-2.33v7C18.34 21.21 22 17.06 22 12.06z"/>',
		'Instagram' => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85C2.38 3.92 3.89 2.38 7.15 2.23 8.42 2.17 8.8 2.16 12 2.16zm0 5.17A4.67 4.67 0 1016.67 12 4.67 4.67 0 0012 7.33zm0 7.7A3.03 3.03 0 1115.03 12 3.03 3.03 0 0112 15.03zm4.85-8.99a1.09 1.09 0 101.09 1.09 1.09 1.09 0 00-1.09-1.09z"/>',
	);
	echo '<ul class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Social links', 'cbw' ) . '">';
	foreach ( $icons as $label => $path ) {
		printf(
			'<li><a href="#" aria-label="%1$s"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%2$s</svg></a></li>',
			esc_attr( $label ),
			$path // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup.
		);
	}
	echo '</ul>';
}

/**
 * Scrolling "Trending" headline strip under the navigation.
 *
 * The headlines are printed twice so the loop is seamless; the copy is hidden
 * from assistive tech and the keyboard. The motion can be paused (WCAG 2.2.2)
 * and does not run at all under prefers-reduced-motion.
 */
function cbw_ticker() {
	$q = new WP_Query( array(
		'posts_per_page'      => 8,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	if ( ! $q->have_posts() ) {
		return;
	}

	$items = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$term    = cbw_primary_category();
		$items[] = array(
			'url'   => get_permalink(),
			'title' => get_the_title(),
			'cat'   => $term ? $term->name : '',
		);
	}
	wp_reset_postdata();
	?>
	<section class="cbw-ticker" aria-labelledby="cbw-ticker-label">
		<div class="cbw-wrap cbw-ticker__inner">
			<h2 class="cbw-ticker__label" id="cbw-ticker-label">
				<?php cbw_icon( 'flame', 15 ); ?>
				<span><?php esc_html_e( 'Trending', 'cbw' ); ?></span>
			</h2>
			<div class="cbw-ticker__viewport">
				<div class="cbw-ticker__rail" style="--cbw-ticker-time:<?php echo (int) ( count( $items ) * 7 ); ?>s">
					<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
						<ul class="cbw-ticker__track"<?php echo $copy ? ' aria-hidden="true"' : ''; ?>>
							<?php foreach ( $items as $item ) : ?>
								<li class="cbw-ticker__item">
									<?php if ( $item['cat'] ) : ?>
										<span class="cbw-ticker__cat"><?php echo esc_html( $item['cat'] ); ?></span>
									<?php endif; ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $copy ? ' tabindex="-1"' : ''; ?>><?php echo esc_html( $item['title'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endfor; ?>
				</div>
			</div>
			<button class="cbw-ticker__toggle" type="button" aria-pressed="false">
				<?php cbw_icon( 'pause', 13, 'cbw-ticker__pause' ); ?>
				<?php cbw_icon( 'play', 13, 'cbw-ticker__play' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Pause headlines', 'cbw' ); ?></span>
			</button>
		</div>
	</section>
	<?php
}

/**
 * Numbered pagination wrapper.
 */
function cbw_pagination() {
	the_posts_pagination( array(
		'mid_size'  => 2,
		'prev_text' => __( '&larr; Previous', 'cbw' ),
		'next_text' => __( 'Next &rarr;', 'cbw' ),
		'class'     => 'cbw-pagination',
	) );
}

/**
 * Breadcrumb trail.
 */
function cbw_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="cbw-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'cbw' ) . '"><ol>';
	printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'cbw' ) );

	if ( is_singular( 'post' ) ) {
		$term = cbw_primary_category();
		if ( $term ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( get_category_link( $term ) ), esc_html( $term->name ) );
		}
		printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $ancestor ) ), esc_html( get_the_title( $ancestor ) ) );
		}
		printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( single_term_title( '', false ) ) );
	} elseif ( is_search() ) {
		printf( '<li aria-current="page">%s</li>', esc_html__( 'Search results', 'cbw' ) );
	} elseif ( is_author() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( get_the_author() ) );
	} else {
		printf( '<li aria-current="page">%s</li>', esc_html__( 'Archive', 'cbw' ) );
	}

	echo '</ol></nav>';
}

/**
 * Newsletter call to action.
 */
function cbw_newsletter() {
	?>
	<section class="cbw-news" aria-labelledby="cbw-news-title">
		<div class="cbw-wrap cbw-news__inner">
			<div class="cbw-news__text">
				<span class="cbw-news__icon"><?php cbw_icon( 'mail', 26 ); ?></span>
				<span class="cbw-section__kicker cbw-section__kicker--light"><?php esc_html_e( 'The GMS Briefing', 'cbw' ); ?></span>
				<h2 id="cbw-news-title" class="cbw-news__title"><?php esc_html_e( 'Founder stories, funding rounds and leadership insight — weekly.', 'cbw' ); ?></h2>
				<p class="cbw-news__sub"><?php esc_html_e( 'Join executives and founders who read Global Media Star before the market opens.', 'cbw' ); ?></p>
			</div>
			<div class="cbw-news__col">
				<?php cbw_form_notice( 'newsletter' ); ?>
				<form class="cbw-news__form" method="post" action="">
					<label class="screen-reader-text" for="cbw-news-email"><?php esc_html_e( 'Email address', 'cbw' ); ?></label>
					<input id="cbw-news-email" name="cbw_news_email" type="email" placeholder="<?php esc_attr_e( 'you@company.com', 'cbw' ); ?>" required>
					<span class="cbw-hp" aria-hidden="true"><input name="cbw_website" type="text" tabindex="-1" autocomplete="off"></span>
					<input type="hidden" name="cbw_form" value="newsletter">
					<input type="hidden" name="cbw_redirect" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">
					<?php wp_nonce_field( 'cbw_newsletter', 'cbw_news_nonce' ); ?>
					<button class="cbw-btn cbw-btn--gold" type="submit"><?php esc_html_e( 'Subscribe', 'cbw' ); ?></button>
				</form>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Small label above an archive title ("Category", "Tag", "Author"…).
 *
 * @return string
 */
function cbw_archive_kicker() {
	if ( is_category() ) {
		return __( 'Category', 'cbw' );
	}
	if ( is_tag() ) {
		return __( 'Topic', 'cbw' );
	}
	if ( is_author() ) {
		return __( 'Author', 'cbw' );
	}
	if ( is_post_type_archive( 'cbw_issue' ) ) {
		return __( 'Magazine', 'cbw' );
	}
	if ( is_date() ) {
		return __( 'Archive', 'cbw' );
	}
	return __( 'Browse', 'cbw' );
}

/**
 * Previous/next article links for single posts.
 */
function cbw_post_nav() {
	$prev = get_previous_post();
	$next = get_next_post();

	if ( ! $prev && ! $next ) {
		return;
	}
	?>
	<nav class="cbw-postnav" aria-label="<?php esc_attr_e( 'More stories', 'cbw' ); ?>">
		<?php if ( $prev ) : ?>
			<a class="cbw-postnav__item" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
				<span class="cbw-postnav__dir">&larr; <?php esc_html_e( 'Previous story', 'cbw' ); ?></span>
				<span class="cbw-postnav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $next ) : ?>
			<a class="cbw-postnav__item cbw-postnav__item--next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
				<span class="cbw-postnav__dir"><?php esc_html_e( 'Next story', 'cbw' ); ?> &rarr;</span>
				<span class="cbw-postnav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
			</a>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Share links for the current article.
 */
function cbw_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$links = array(
		'LinkedIn' => "https://www.linkedin.com/shareArticle?mini=true&url={$url}",
		'X'        => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
		'Facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
		'Email'    => "mailto:?subject={$title}&body={$url}",
	);

	echo '<div class="cbw-share"><span class="cbw-share__label">' . esc_html__( 'Share', 'cbw' ) . '</span>';
	foreach ( $links as $label => $href ) {
		printf(
			'<a class="cbw-share__link" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $href ),
			esc_html( $label )
		);
	}
	echo '</div>';
}

/**
 * Use the generated editor avatar in place of the Gravatar fallback.
 * Falls through to core behaviour for anyone without one.
 */
function cbw_custom_avatar( $args, $id_or_email ) {
	$user = false;

	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', (int) $id_or_email );
	} elseif ( $id_or_email instanceof WP_User ) {
		$user = $id_or_email;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user = get_user_by( 'id', (int) $id_or_email->post_author );
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user = $id_or_email->user_id ? get_user_by( 'id', (int) $id_or_email->user_id ) : false;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	}

	if ( ! $user ) {
		return $args;
	}

	$att = get_user_meta( $user->ID, 'cbw_avatar_id', true );
	if ( ! $att ) {
		return $args;
	}

	$url = wp_get_attachment_image_url( $att, 'thumbnail' );
	if ( $url ) {
		$args['url']          = $url;
		$args['found_avatar'] = true;
	}
	return $args;
}
add_filter( 'pre_get_avatar_data', 'cbw_custom_avatar', 10, 2 );

/**
 * Editorial team grid, rendered from the real user accounts.
 */
function cbw_team_grid() {
	$users = get_users( array( 'role__in' => array( 'author' ), 'orderby' => 'ID' ) );
	if ( ! $users ) {
		return;
	}

	echo '<div class="cbw-team">';
	foreach ( $users as $u ) {
		$att  = get_user_meta( $u->ID, 'cbw_avatar_id', true );
		$role = get_user_meta( $u->ID, 'cbw_role', true );
		$n    = count_user_posts( $u->ID, 'post' );
		?>
		<article class="cbw-team__card">
			<a class="cbw-team__photo" href="<?php echo esc_url( get_author_posts_url( $u->ID ) ); ?>">
				<?php
				if ( $att ) {
					echo wp_get_attachment_image( $att, 'full', false, array( 'alt' => esc_attr( $u->display_name ) ) );
				} else {
					echo get_avatar( $u->ID, 200 );
				}
				?>
			</a>
			<span class="cbw-section__kicker"><?php echo esc_html( $role ? $role : __( 'Contributor', 'cbw' ) ); ?></span>
			<h3 class="cbw-team__name"><a href="<?php echo esc_url( get_author_posts_url( $u->ID ) ); ?>"><?php echo esc_html( $u->display_name ); ?></a></h3>
			<p class="cbw-team__bio"><?php echo esc_html( get_the_author_meta( 'description', $u->ID ) ); ?></p>
			<p class="cbw-team__count">
				<?php
				/* translators: %d: number of published articles. */
				printf( esc_html( _n( '%d story published', '%d stories published', $n, 'cbw' ) ), (int) $n );
				?>
			</p>
		</article>
		<?php
	}
	echo '</div>';
}

/**
 * Render the team grid automatically on the Editorial Team page.
 */
function cbw_append_team_grid( $content ) {
	if ( ! is_page( 'editorial-team' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	ob_start();
	cbw_team_grid();
	return $content . ob_get_clean();
}
add_filter( 'the_content', 'cbw_append_team_grid' );

/**
 * Photo credit for an attachment.
 *
 * Wikimedia Commons images are mostly CC BY / CC BY-SA / GODL-India, which
 * require attribution, so the credit is rendered wherever the image is.
 *
 * @param int    $att_id Attachment ID.
 * @param string $class  Extra class for positioning.
 */
function cbw_photo_credit( $att_id, $class = '' ) {
	if ( ! $att_id ) {
		return;
	}

	$artist  = get_post_meta( $att_id, 'cbw_credit_artist', true );
	$license = get_post_meta( $att_id, 'cbw_credit_license', true );
	$source  = get_post_meta( $att_id, 'cbw_credit_source', true );

	if ( ! $artist && ! $license ) {
		return;
	}

	$text = $artist ? $artist : __( 'Unknown', 'cbw' );
	if ( $license ) {
		$text .= ' · ' . $license;
	}

	printf(
		'<p class="cbw-credit %1$s">%2$s %3$s</p>',
		esc_attr( $class ),
		esc_html__( 'Photo:', 'cbw' ),
		$source
			? '<a href="' . esc_url( $source ) . '" target="_blank" rel="noopener nofollow">' . esc_html( $text ) . '</a>'
			: esc_html( $text )
	);
}

/**
 * Credit for the current post's featured image.
 */
function cbw_thumb_credit( $class = '' ) {
	cbw_photo_credit( get_post_thumbnail_id(), $class );
}
