<?php
/**
 * Search results.
 *
 * @package CBW_Magazine
 */

get_header(); ?>

<div class="cbw-wrap cbw-page">
	<?php cbw_breadcrumbs(); ?>

	<header class="cbw-pagehead">
		<span class="cbw-section__kicker"><?php esc_html_e( 'Search', 'cbw' ); ?></span>
		<h1 class="cbw-pagehead__title">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Results for “%s”', 'cbw' ), esc_html( get_search_query() ) );
			?>
		</h1>
		<p class="cbw-pagehead__sub">
			<?php
			global $wp_query;
			/* translators: %d: result count. */
			printf( esc_html( _n( '%d matching story', '%d matching stories', (int) $wp_query->found_posts, 'cbw' ) ), (int) $wp_query->found_posts );
			?>
		</p>
		<?php get_search_form(); ?>
	</header>

	<div class="cbw-layout">
		<div class="cbw-layout__main">
			<?php if ( have_posts() ) : ?>
				<div class="cbw-grid cbw-grid--2">
					<?php while ( have_posts() ) : the_post(); cbw_card( 'card' ); endwhile; ?>
				</div>
				<?php cbw_pagination(); ?>
			<?php else : ?>
				<div class="cbw-empty">
					<h2><?php esc_html_e( 'No stories matched that search.', 'cbw' ); ?></h2>
					<p><?php esc_html_e( 'Try a company name, a founder’s name, or a broader topic such as “funding” or “leadership”.', 'cbw' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer();
