<?php
/**
 * Magazine issue archive.
 *
 * @package CBW_Magazine
 */

get_header(); ?>

<div class="cbw-wrap cbw-page">
	<?php cbw_breadcrumbs(); ?>
	<header class="cbw-pagehead">
		<span class="cbw-section__kicker"><?php esc_html_e( 'Magazine', 'cbw' ); ?></span>
		<h1 class="cbw-pagehead__title"><?php esc_html_e( 'Every Issue of Global Media Star', 'cbw' ); ?></h1>
		<p class="cbw-pagehead__sub"><?php esc_html_e( 'Browse the full run of covers, cover stories and special editions.', 'cbw' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="cbw-grid cbw-grid--4">
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="cbw-issue">
					<a class="cbw-issue__cover" href="<?php the_permalink(); ?>"><?php cbw_thumbnail( 'cbw-cover' ); ?></a>
					<h2 class="cbw-issue__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="cbw-issue__meta"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
					<p class="cbw-issue__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
				</article>
			<?php endwhile; ?>
		</div>
		<?php cbw_pagination(); ?>
	<?php else : ?>
		<p class="cbw-empty"><?php esc_html_e( 'No issues published yet.', 'cbw' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer();
