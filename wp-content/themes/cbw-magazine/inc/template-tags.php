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
	<article <?php post_class( 'cbw-card cbw-card--' . esc_attr( $variant ) ); ?>>
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
 */
function cbw_section_heading( $title, $link = '', $kicker = '' ) {
	echo '<header class="cbw-section__head">';
	echo '<div>';
	if ( $kicker ) {
		printf( '<span class="cbw-section__kicker">%s</span>', esc_html( $kicker ) );
	}
	printf( '<h2 class="cbw-section__title">%s</h2>', esc_html( $title ) );
	echo '</div>';
	if ( $link ) {
		printf(
			'<a class="cbw-viewall" href="%s">%s <span aria-hidden="true">&rarr;</span></a>',
			esc_url( $link ),
			esc_html__( 'View all', 'cbw' )
		);
	}
	echo '</header>';
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
			<div>
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
