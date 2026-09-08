<?php
/**
 * Category / tag / author / date archives.
 *
 * @package CBW_Magazine
 */

get_header(); ?>

<div class="cbw-wrap cbw-page">
	<?php cbw_breadcrumbs(); ?>

	<header class="cbw-pagehead">
		<span class="cbw-section__kicker"><?php echo esc_html( cbw_archive_kicker() ); ?></span>
		<h1 class="cbw-pagehead__title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php
		$cbw_desc = get_the_archive_description();
		if ( $cbw_desc ) {
			echo '<div class="cbw-pagehead__sub">' . wp_kses_post( $cbw_desc ) . '</div>';
		}
		?>
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
				<p class="cbw-empty"><?php esc_html_e( 'Nothing filed here yet — check back soon.', 'cbw' ); ?></p>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer();
