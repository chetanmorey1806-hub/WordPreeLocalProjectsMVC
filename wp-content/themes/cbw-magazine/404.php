<?php
/**
 * 404 page.
 *
 * @package CBW_Magazine
 */

get_header(); ?>

<div class="cbw-wrap cbw-page">
	<div class="cbw-empty cbw-empty--404">
		<span class="cbw-section__kicker"><?php esc_html_e( 'Error 404', 'cbw' ); ?></span>
		<h1><?php esc_html_e( 'That page has gone to press elsewhere.', 'cbw' ); ?></h1>
		<p><?php esc_html_e( 'The story you were looking for has moved or never existed. Search the archive, or start from the front page.', 'cbw' ); ?></p>
		<?php get_search_form(); ?>
		<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the front page', 'cbw' ); ?></a>
	</div>

	<?php
	$cbw_recent = new WP_Query( array( 'posts_per_page' => 3, 'no_found_rows' => true ) );
	if ( $cbw_recent->have_posts() ) : ?>
		<section class="cbw-section">
			<?php cbw_section_heading( __( 'Latest from GMS', 'cbw' ) ); ?>
			<div class="cbw-grid cbw-grid--3">
				<?php while ( $cbw_recent->have_posts() ) : $cbw_recent->the_post(); cbw_card( 'card' ); endwhile; ?>
			</div>
		</section>
	<?php endif;
	wp_reset_postdata(); ?>
</div>

<?php get_footer();
