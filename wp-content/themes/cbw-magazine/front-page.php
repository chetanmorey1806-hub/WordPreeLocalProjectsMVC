<?php
/**
 * Magazine home page.
 *
 * @package CBW_Magazine
 */

get_header();

$cbw_used = array();
?>
<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
<?php

/* -------------------------------------------------------- Hero slider */
$cbw_hero = new WP_Query( array(
	'posts_per_page'      => 5,
	'ignore_sticky_posts' => false,
	'no_found_rows'       => true,
) );

if ( $cbw_hero->have_posts() ) :
	$cbw_total = count( $cbw_hero->posts );
	?>
	<section class="cbw-slider" aria-roledescription="<?php esc_attr_e( 'carousel', 'cbw' ); ?>" aria-label="<?php esc_attr_e( 'Featured stories', 'cbw' ); ?>">
		<div class="cbw-slider__slides">
			<?php
			while ( $cbw_hero->have_posts() ) :
				$cbw_hero->the_post();
				$cbw_i      = $cbw_hero->current_post;
				$cbw_used[] = get_the_ID();
				?>
				<article
					class="cbw-slide<?php echo 0 === $cbw_i ? ' is-active' : ''; ?>"
					id="cbw-slide-<?php echo (int) $cbw_i; ?>"
					role="group"
					aria-roledescription="<?php esc_attr_e( 'slide', 'cbw' ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: slide count. */ __( '%1$d of %2$d', 'cbw' ), $cbw_i + 1, $cbw_total ) ); ?>"
					<?php echo $cbw_i ? ' aria-hidden="true" inert' : ''; ?>
				>
					<div class="cbw-slide__media">
						<?php
						if ( has_post_thumbnail() ) {
							// The headline carries the meaning; the photo is atmosphere.
							the_post_thumbnail( 'full', array(
								'alt'           => '',
								'sizes'         => '100vw',
								'loading'       => $cbw_i ? 'lazy' : 'eager',
								'fetchpriority' => $cbw_i ? 'auto' : 'high',
							) );
						} else {
							cbw_thumbnail( 'cbw-hero' );
						}
						?>
					</div>
					<?php cbw_thumb_credit( 'cbw-credit--slide' ); ?>
					<div class="cbw-wrap cbw-slide__content">
						<div class="cbw-slide__eyebrow">
							<span class="cbw-slide__num"><?php echo esc_html( sprintf( '%02d', $cbw_i + 1 ) ); ?></span>
							<?php cbw_category_badge(); ?>
						</div>
						<h2 class="cbw-slide__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="cbw-slide__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<div class="cbw-slide__foot">
							<?php cbw_meta_line(); ?>
							<a class="cbw-btn cbw-btn--gold cbw-btn--shine" href="<?php the_permalink(); ?>" tabindex="-1"><?php esc_html_e( 'Read story', 'cbw' ); ?><?php cbw_icon( 'arrow-right', 16 ); ?></a>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $cbw_total > 1 ) : ?>
			<div class="cbw-slider__bar">
				<div class="cbw-wrap cbw-slider__barinner">
					<div class="cbw-slider__tabs">
						<?php foreach ( $cbw_hero->posts as $cbw_i => $cbw_p ) : ?>
							<button class="cbw-slider__tab" type="button" data-index="<?php echo (int) $cbw_i; ?>" aria-controls="cbw-slide-<?php echo (int) $cbw_i; ?>"<?php echo 0 === $cbw_i ? ' aria-current="true"' : ''; ?>>
								<span class="cbw-slider__tabprogress" aria-hidden="true"></span>
								<span class="cbw-slider__tabnum"><?php echo esc_html( sprintf( '%02d', $cbw_i + 1 ) ); ?></span>
								<span class="cbw-slider__tabtitle"><?php echo esc_html( get_the_title( $cbw_p ) ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<div class="cbw-slider__ctrls">
						<button class="cbw-slider__ctrl" type="button" data-go="prev">
							<?php cbw_icon( 'chevron-left', 20 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Previous slide', 'cbw' ); ?></span>
						</button>
						<button class="cbw-slider__ctrl cbw-slider__play" type="button" aria-pressed="false">
							<?php cbw_icon( 'pause', 16, 'cbw-slider__pauseicon' ); ?>
							<?php cbw_icon( 'play', 16, 'cbw-slider__playicon' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Pause slideshow', 'cbw' ); ?></span>
						</button>
						<button class="cbw-slider__ctrl" type="button" data-go="next">
							<?php cbw_icon( 'chevron-right', 20 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Next slide', 'cbw' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php
endif;
wp_reset_postdata();

/* ------------------------------------------ Editor's picks carousel */
$cbw_picks = new WP_Query( array(
	'posts_per_page' => 8,
	'post__not_in'   => $cbw_used,
	'no_found_rows'  => true,
) );

if ( $cbw_picks->have_posts() ) : ?>
	<section class="cbw-section cbw-section--picks">
		<div class="cbw-wrap">
			<?php cbw_section_heading( __( "Editor's Picks", 'cbw' ), get_permalink( get_option( 'page_for_posts' ) ), __( 'Handpicked this week', 'cbw' ), 'cbw-picks-track', 'sparkles' ); ?>
			<div class="cbw-carousel">
				<div class="cbw-carousel__track" id="cbw-picks-track" tabindex="0" role="region" aria-label="<?php esc_attr_e( "Editor's Picks", 'cbw' ); ?>">
					<?php
					while ( $cbw_picks->have_posts() ) :
						$cbw_picks->the_post();
						$cbw_used[] = get_the_ID();
						echo '<div class="cbw-carousel__item">';
						cbw_card( 'card' );
						echo '</div>';
					endwhile;
					?>
				</div>
			</div>
		</div>
	</section>
<?php endif;
wp_reset_postdata();

/* -------------------------------------------------- Explore sections */
$cbw_sections = get_pages( array(
	'parent'      => 0,
	'sort_column' => 'menu_order',
	'exclude'     => implode( ',', array_filter( array( (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ) ) ) ),
) );

if ( $cbw_sections ) : ?>
	<section class="cbw-section cbw-section--alt cbw-section--explore">
		<div class="cbw-wrap">
			<?php cbw_section_heading( __( 'Explore sections', 'cbw' ), '', __( 'Every beat we cover', 'cbw' ), '', 'layers' ); ?>
			<div class="cbw-explore">
				<?php
				$cbw_shown = array();
				foreach ( $cbw_sections as $cbw_section ) :
					$cbw_term  = cbw_page_category( $cbw_section->ID );
					$cbw_count = 0;
					$cbw_img   = (int) get_post_thumbnail_id( $cbw_section );
					// A recent story's photo, skipping any a neighbouring card already
					// shows. Sections without a category borrow from the whole magazine.
					$cbw_latest = new WP_Query( array(
						'cat'            => $cbw_term ? $cbw_term->term_id : 0,
						'posts_per_page' => 12,
					) );
					if ( $cbw_term ) {
						$cbw_count = (int) $cbw_latest->found_posts;
					}
					if ( ! $cbw_img || $cbw_term || in_array( $cbw_img, $cbw_shown, true ) ) {
						foreach ( $cbw_latest->posts as $cbw_p ) {
							$cbw_att = (int) get_post_thumbnail_id( $cbw_p );
							if ( $cbw_att && ! in_array( $cbw_att, $cbw_shown, true ) ) {
								$cbw_img = $cbw_att;
								break;
							}
						}
					}
					$cbw_shown[] = $cbw_img;
					$cbw_kids = count( get_pages( array( 'parent' => $cbw_section->ID, 'fields' => 'ids' ) ) );
					?>
					<a class="cbw-explore__card cbw-reveal" href="<?php echo esc_url( get_permalink( $cbw_section ) ); ?>">
						<span class="cbw-explore__media" aria-hidden="true">
							<?php echo $cbw_img ? wp_get_attachment_image( $cbw_img, 'cbw-card', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : ''; ?>
						</span>
						<span class="cbw-explore__icon"><?php cbw_icon( cbw_section_icon( $cbw_section ), 22 ); ?></span>
						<span class="cbw-explore__body">
							<span class="cbw-explore__title"><?php echo esc_html( get_the_title( $cbw_section ) ); ?></span>
							<span class="cbw-explore__count">
								<?php
								$cbw_bits = array();
								if ( $cbw_count ) {
									/* translators: %d: number of stories. */
									$cbw_bits[] = sprintf( _n( '%d story', '%d stories', $cbw_count, 'cbw' ), $cbw_count );
								}
								if ( $cbw_kids ) {
									/* translators: %d: number of sub-sections. */
									$cbw_bits[] = sprintf( _n( '%d section', '%d sections', $cbw_kids, 'cbw' ), $cbw_kids );
								}
								echo esc_html( implode( ' · ', $cbw_bits ) );
								?>
							</span>
						</span>
						<span class="cbw-explore__arrow" aria-hidden="true"><?php cbw_icon( 'arrow-right', 18 ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	wp_reset_postdata();
endif;

/* ------------------------------------------- One block per top section */
$cbw_block = 0;
foreach ( $cbw_sections as $cbw_section ) {
	$cbw_term = cbw_page_category( $cbw_section->ID );
	if ( ! $cbw_term ) {
		continue;
	}

	$cbw_q = new WP_Query( array(
		'cat'            => $cbw_term->term_id,
		'posts_per_page' => 5,
		'post__not_in'   => $cbw_used,
		'no_found_rows'  => true,
	) );

	if ( ! $cbw_q->have_posts() ) {
		wp_reset_postdata();
		continue;
	}

	$cbw_block++;
	$cbw_alt = ( 0 === $cbw_block % 2 ) ? ' cbw-section--alt' : '';
	?>
	<section class="cbw-section<?php echo esc_attr( $cbw_alt ); ?>">
		<div class="cbw-wrap">
			<?php cbw_section_heading( get_the_title( $cbw_section ), get_permalink( $cbw_section ), __( 'Section', 'cbw' ), '', cbw_section_icon( $cbw_section ) ); ?>
			<div class="cbw-split">
				<div class="cbw-split__main">
					<?php $cbw_q->the_post(); $cbw_used[] = get_the_ID(); cbw_card( 'feature' ); ?>
				</div>
				<div class="cbw-split__side">
					<?php
					while ( $cbw_q->have_posts() ) :
						$cbw_q->the_post();
						$cbw_used[] = get_the_ID();
						cbw_card( 'list' );
					endwhile;
					?>
				</div>
			</div>

			<?php
			$cbw_children = get_pages( array( 'parent' => $cbw_section->ID, 'sort_column' => 'menu_order' ) );
			if ( $cbw_children ) : ?>
				<ul class="cbw-chiprow">
					<?php foreach ( $cbw_children as $cbw_child ) : ?>
						<li><a class="cbw-chip" href="<?php echo esc_url( get_permalink( $cbw_child ) ); ?>"><?php echo esc_html( get_the_title( $cbw_child ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();

	if ( 3 === $cbw_block ) {
		cbw_newsletter();
	}
}

/* --------------------------------------------------- By the numbers */
$cbw_stats = array(
	array( 'newspaper', (int) wp_count_posts()->publish, __( 'Stories published', 'cbw' ) ),
	array( 'book', (int) wp_count_posts( 'cbw_issue' )->publish, __( 'Magazine issues', 'cbw' ) ),
	array( 'pen', count( get_users( array( 'role__in' => array( 'author' ), 'fields' => 'ID' ) ) ), __( 'Contributing editors', 'cbw' ) ),
	array( 'languages', count( cbw_languages() ), __( 'Languages', 'cbw' ) ),
);
?>
<section class="cbw-stats" aria-labelledby="cbw-stats-title">
	<div class="cbw-wrap">
		<h2 class="screen-reader-text" id="cbw-stats-title"><?php esc_html_e( 'By the numbers', 'cbw' ); ?></h2>
		<div class="cbw-stats__grid">
			<?php foreach ( $cbw_stats as $cbw_stat ) : ?>
				<div class="cbw-stat cbw-reveal">
					<span class="cbw-stat__icon"><?php cbw_icon( $cbw_stat[0], 24 ); ?></span>
					<span class="cbw-stat__value" data-count="<?php echo (int) $cbw_stat[1]; ?>"><?php echo esc_html( number_format_i18n( $cbw_stat[1] ) ); ?></span>
					<span class="cbw-stat__label"><?php echo esc_html( $cbw_stat[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php

/* ------------------------------------------------ Magazine issues */
$cbw_issues = new WP_Query( array(
	'post_type'      => 'cbw_issue',
	'posts_per_page' => 8,
	'no_found_rows'  => true,
) );

if ( $cbw_issues->have_posts() ) : ?>
	<section class="cbw-section cbw-section--issues">
		<div class="cbw-wrap">
			<?php cbw_section_heading( __( 'The Magazine', 'cbw' ), home_url( '/magazine/' ), __( 'In print & digital', 'cbw' ), 'cbw-issues-track', 'book' ); ?>
			<div class="cbw-carousel cbw-carousel--issues">
				<div class="cbw-carousel__track" id="cbw-issues-track" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'The Magazine', 'cbw' ); ?>">
					<?php while ( $cbw_issues->have_posts() ) : $cbw_issues->the_post(); ?>
						<div class="cbw-carousel__item">
							<article class="cbw-issue">
								<a class="cbw-issue__cover" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php cbw_thumbnail( 'cbw-cover' ); ?></a>
								<h3 class="cbw-issue__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="cbw-issue__meta"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
							</article>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif;
wp_reset_postdata();

get_footer();
