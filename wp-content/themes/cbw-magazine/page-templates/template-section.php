<?php
/**
 * Template Name: Section Landing
 * Template Post Type: page
 *
 * A magazine section page: intro copy, a lead story, a grid of the rest of the
 * section's articles, and links to sibling/child sections.
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();

	$cbw_term  = cbw_page_category();
	$cbw_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$cbw_kids  = get_pages( array( 'parent' => get_the_ID(), 'sort_column' => 'menu_order' ) );
	$cbw_parent_id = wp_get_post_parent_id( get_the_ID() );

	$cbw_q = null;
	if ( $cbw_term ) {
		$cbw_q = new WP_Query( array(
			'cat'            => $cbw_term->term_id,
			'posts_per_page' => 9,
			'paged'          => $cbw_paged,
		) );
	}
	?>

	<div class="cbw-wrap cbw-page cbw-page--crumbs">
		<?php cbw_breadcrumbs(); ?>
	</div>

	<?php
	$cbw_bg = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'cbw-hero' ) : '';
	?>
	<header class="cbw-sectionhead<?php echo $cbw_bg ? ' cbw-sectionhead--photo' : ''; ?>"<?php
		echo $cbw_bg ? ' style="--shot:url(' . esc_url( $cbw_bg ) . ')"' : ''; ?>>
		<div class="cbw-wrap">
			<span class="cbw-section__kicker cbw-section__kicker--light">
				<?php echo esc_html( $cbw_parent_id ? get_the_title( $cbw_parent_id ) : __( 'Section', 'cbw' ) ); ?>
			</span>
			<h1 class="cbw-sectionhead__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="cbw-sectionhead__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<?php
			// Sibling sections double as sub-navigation for the section.
			$cbw_sibs = get_pages( array(
				'parent'      => $cbw_parent_id ? $cbw_parent_id : get_the_ID(),
				'sort_column' => 'menu_order',
			) );
			$cbw_sibs = $cbw_kids ? $cbw_kids : $cbw_sibs;

			if ( $cbw_sibs ) : ?>
				<ul class="cbw-chiprow cbw-chiprow--light">
					<?php foreach ( $cbw_sibs as $cbw_sib ) : ?>
						<li>
							<a class="cbw-chip<?php echo ( $cbw_sib->ID === get_the_ID() ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $cbw_sib ) ); ?>">
								<?php echo esc_html( get_the_title( $cbw_sib ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</header>

	<div class="cbw-wrap cbw-page">
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="cbw-prose cbw-prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>

		<div class="cbw-layout">
			<div class="cbw-layout__main">
				<?php if ( $cbw_q && $cbw_q->have_posts() ) : ?>

					<?php if ( 1 === $cbw_paged ) : ?>
						<div class="cbw-lead">
							<?php $cbw_q->the_post(); cbw_card( 'feature' ); ?>
						</div>
					<?php endif; ?>

					<div class="cbw-grid cbw-grid--2">
						<?php while ( $cbw_q->have_posts() ) : $cbw_q->the_post(); cbw_card( 'card' ); endwhile; ?>
					</div>

					<?php
					$cbw_links = paginate_links( array(
						'total'              => $cbw_q->max_num_pages,
						'current'            => $cbw_paged,
						'prev_text'          => __( '&larr; Previous', 'cbw' ),
						'next_text'          => __( 'Next &rarr;', 'cbw' ),
						'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'cbw' ) . ' </span>',
					) );
					if ( $cbw_links ) {
						echo '<nav class="navigation cbw-pagination" aria-label="' . esc_attr__( 'Section pagination', 'cbw' ) . '"><div class="nav-links">' . wp_kses_post( $cbw_links ) . '</div></nav>';
					}
					?>

				<?php else : ?>
					<div class="cbw-empty">
						<h2><?php esc_html_e( 'This section is being commissioned.', 'cbw' ); ?></h2>
						<p><?php esc_html_e( 'Our editors are working on the next set of stories for this section. In the meantime, browse the latest across the magazine.', 'cbw' ); ?></p>
						<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the front page', 'cbw' ); ?></a>
					</div>
				<?php endif;
				wp_reset_postdata(); ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
