<?php
/**
 * Single magazine issue.
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="cbw-wrap cbw-page">
		<?php cbw_breadcrumbs(); ?>

		<div class="cbw-issue-single">
			<figure class="cbw-issue-single__cover"><?php cbw_thumbnail( 'cbw-cover' ); ?><?php cbw_thumb_credit( 'cbw-credit--hero' ); ?></figure>

			<div class="cbw-issue-single__body">
				<span class="cbw-section__kicker"><?php echo esc_html( get_the_date( 'F Y' ) ); ?> <?php esc_html_e( 'Issue', 'cbw' ); ?></span>
				<h1 class="cbw-pagehead__title"><?php the_title(); ?></h1>
				<div class="cbw-prose"><?php the_content(); ?></div>

				<div class="cbw-issue-single__actions">
					<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/about/advertise-with-us/' ) ); ?>"><?php esc_html_e( 'Advertise in this issue', 'cbw' ); ?></a>
					<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'cbw_issue' ) ); ?>"><?php esc_html_e( 'All issues', 'cbw' ); ?></a>
				</div>
			</div>
		</div>

		<?php
		$cbw_inside = new WP_Query( array(
			'posts_per_page' => 6,
			'date_query'     => array( array( 'year' => (int) get_the_date( 'Y' ), 'month' => (int) get_the_date( 'n' ) ) ),
			'no_found_rows'  => true,
		) );
		if ( ! $cbw_inside->have_posts() ) {
			wp_reset_postdata();
			$cbw_inside = new WP_Query( array( 'posts_per_page' => 6, 'no_found_rows' => true ) );
		}
		if ( $cbw_inside->have_posts() ) : ?>
			<section class="cbw-section">
				<?php cbw_section_heading( __( 'Inside this issue', 'cbw' ) ); ?>
				<div class="cbw-grid cbw-grid--3">
					<?php while ( $cbw_inside->have_posts() ) : $cbw_inside->the_post(); cbw_card( 'card' ); endwhile; ?>
				</div>
			</section>
		<?php endif;
		wp_reset_postdata(); ?>
	</div>
	<?php
endwhile;

get_footer();
