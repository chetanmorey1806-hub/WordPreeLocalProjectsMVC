<?php
/**
 * Default page template.
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="cbw-wrap cbw-page">
		<?php cbw_breadcrumbs(); ?>

		<header class="cbw-pagehead">
			<?php
			$cbw_parent = wp_get_post_parent_id( get_the_ID() );
			if ( $cbw_parent ) {
				printf( '<span class="cbw-section__kicker">%s</span>', esc_html( get_the_title( $cbw_parent ) ) );
			}
			?>
			<h1 class="cbw-pagehead__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="cbw-pagehead__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<div class="cbw-layout">
			<div class="cbw-layout__main">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="cbw-page__hero">
						<?php the_post_thumbnail( 'cbw-hero' ); ?>
						<?php cbw_thumb_credit(); ?>
					</figure>
				<?php endif; ?>

				<div class="cbw-prose">
					<?php the_content(); ?>
				</div>

				<?php
				$cbw_kids = get_pages( array( 'parent' => get_the_ID(), 'sort_column' => 'menu_order' ) );
				if ( $cbw_kids ) : ?>
					<section class="cbw-subpages">
						<?php cbw_section_heading( __( 'In this section', 'cbw' ) ); ?>
						<ul class="cbw-chiprow">
							<?php foreach ( $cbw_kids as $cbw_kid ) : ?>
								<li><a class="cbw-chip" href="<?php echo esc_url( get_permalink( $cbw_kid ) ); ?>"><?php echo esc_html( get_the_title( $cbw_kid ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
