<?php
/**
 * Magazine home page.
 *
 * @package CBW_Magazine
 */

get_header();

$cbw_used = array();

/* ---------------------------------------------------------------- Hero */
$cbw_hero = new WP_Query( array(
	'posts_per_page'      => 5,
	'ignore_sticky_posts' => false,
	'no_found_rows'       => true,
) );

if ( $cbw_hero->have_posts() ) :
	$cbw_hero_posts = $cbw_hero->posts;
	$cbw_lead       = array_shift( $cbw_hero_posts );
	$cbw_used[]     = $cbw_lead->ID;
	?>
	<section class="cbw-hero">
		<div class="cbw-wrap cbw-hero__grid">

			<?php $post = $cbw_lead; setup_postdata( $post ); // phpcs:ignore ?>
			<article class="cbw-hero__lead">
				<a class="cbw-hero__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php cbw_thumbnail( 'cbw-hero' ); ?></a>
				<div class="cbw-hero__body">
					<?php cbw_category_badge(); ?>
					<h1 class="cbw-hero__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<p class="cbw-hero__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php cbw_meta_line(); ?>
				</div>
			</article>
			<?php wp_reset_postdata(); ?>

			<div class="cbw-hero__side">
				<?php
				foreach ( $cbw_hero_posts as $post ) : // phpcs:ignore
					setup_postdata( $post );
					$cbw_used[] = $post->ID;
					cbw_card( 'list' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
endif;
wp_reset_postdata();

/* ------------------------------------------------- Editor's picks strip */
$cbw_picks = new WP_Query( array(
	'posts_per_page' => 4,
	'post__not_in'   => $cbw_used,
	'no_found_rows'  => true,
) );

if ( $cbw_picks->have_posts() ) : ?>
	<section class="cbw-section cbw-section--picks">
		<div class="cbw-wrap">
			<?php cbw_section_heading( __( "Editor's Picks", 'cbw' ), get_permalink( get_option( 'page_for_posts' ) ), __( 'Handpicked this week', 'cbw' ) ); ?>
			<div class="cbw-grid cbw-grid--4">
				<?php
				while ( $cbw_picks->have_posts() ) :
					$cbw_picks->the_post();
					$cbw_used[] = get_the_ID();
					cbw_card( 'card' );
				endwhile;
				?>
			</div>
		</div>
	</section>
<?php endif;
wp_reset_postdata();

/* ------------------------------------------- One block per top section */
$cbw_sections = get_pages( array(
	'parent'      => 0,
	'sort_column' => 'menu_order',
	'exclude'     => implode( ',', array_filter( array( (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ) ) ) ),
) );

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
			<?php cbw_section_heading( get_the_title( $cbw_section ), get_permalink( $cbw_section ), __( 'Section', 'cbw' ) ); ?>
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

/* ------------------------------------------------------ Magazine issues */
$cbw_issues = new WP_Query( array(
	'post_type'      => 'cbw_issue',
	'posts_per_page' => 4,
	'no_found_rows'  => true,
) );

if ( $cbw_issues->have_posts() ) : ?>
	<section class="cbw-section cbw-section--issues">
		<div class="cbw-wrap">
			<?php cbw_section_heading( __( 'The Magazine', 'cbw' ), home_url( '/magazine/' ), __( 'In print & digital', 'cbw' ) ); ?>
			<div class="cbw-grid cbw-grid--4">
				<?php while ( $cbw_issues->have_posts() ) : $cbw_issues->the_post(); ?>
					<article class="cbw-issue">
						<a class="cbw-issue__cover" href="<?php the_permalink(); ?>"><?php cbw_thumbnail( 'cbw-cover' ); ?></a>
						<h3 class="cbw-issue__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="cbw-issue__meta"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
					</article>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php endif;
wp_reset_postdata();

get_footer();
