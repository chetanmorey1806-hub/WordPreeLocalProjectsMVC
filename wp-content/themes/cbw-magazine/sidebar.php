<?php
/**
 * Sidebar with a widget area plus an evergreen "most read" fallback.
 *
 * @package CBW_Magazine
 */
?>
<aside class="cbw-layout__side" role="complementary">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php endif; ?>

	<section class="widget cbw-widget-popular">
		<h3 class="widget-title"><?php esc_html_e( 'Most Read', 'cbw' ); ?></h3>
		<?php
		$cbw_pop = new WP_Query( array(
			'posts_per_page'      => 5,
			'orderby'             => 'comment_count',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => is_singular() ? array( get_the_ID() ) : array(),
		) );
		$cbw_rank = 0;
		while ( $cbw_pop->have_posts() ) :
			$cbw_pop->the_post();
			$cbw_rank++;
			?>
			<article class="cbw-rank">
				<span class="cbw-rank__num" aria-hidden="true"><?php echo esc_html( str_pad( $cbw_rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div>
					<?php cbw_category_badge(); ?>
					<h4 class="cbw-rank__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
				</div>
			</article>
		<?php
		endwhile;
		wp_reset_postdata();
		?>
	</section>

	<section class="widget cbw-widget-cta">
		<h3 class="widget-title"><?php esc_html_e( 'Get Featured', 'cbw' ); ?></h3>
		<p><?php esc_html_e( 'Nominate a founder, CEO or fast-growing company for an upcoming GMS cover story.', 'cbw' ); ?></p>
		<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/about/contact/' ) ); ?>"><?php esc_html_e( 'Pitch our editors', 'cbw' ); ?></a>
	</section>
</aside>
