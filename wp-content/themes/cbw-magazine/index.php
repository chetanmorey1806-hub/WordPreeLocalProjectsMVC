<?php
/**
 * Fallback archive / blog index.
 *
 * @package CBW_Magazine
 */

get_header(); ?>

<div class="cbw-wrap cbw-page">
	<?php cbw_breadcrumbs(); ?>

	<header class="cbw-pagehead">
		<h1 class="cbw-pagehead__title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
			} else {
				esc_html_e( 'Latest Stories', 'cbw' );
			}
			?>
		</h1>
		<p class="cbw-pagehead__sub"><?php esc_html_e( 'Reporting on the founders, chief executives and companies shaping the market.', 'cbw' ); ?></p>
	</header>

	<div class="cbw-layout">
		<div class="cbw-layout__main">
			<?php if ( have_posts() ) : ?>
				<div class="cbw-grid cbw-grid--2">
					<?php
					while ( have_posts() ) :
						the_post();
						cbw_card( 'card' );
					endwhile;
					?>
				</div>
				<?php cbw_pagination(); ?>
			<?php else : ?>
				<p class="cbw-empty"><?php esc_html_e( 'No stories published yet.', 'cbw' ); ?></p>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer();
