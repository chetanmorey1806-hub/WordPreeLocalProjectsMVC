<?php
/**
 * Single article.
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'cbw-article' ); ?>>

		<div class="cbw-wrap cbw-page cbw-page--crumbs">
			<?php cbw_breadcrumbs(); ?>
		</div>

		<header class="cbw-article__head">
			<div class="cbw-wrap cbw-article__headinner">
				<?php cbw_category_badge(); ?>
				<h1 class="cbw-article__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="cbw-article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<?php cbw_meta_line(); ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="cbw-article__hero">
				<div class="cbw-wrap">
					<?php the_post_thumbnail( 'cbw-hero' ); ?>
					<?php cbw_thumb_credit( 'cbw-credit--hero' ); ?>
				</div>
			</figure>
		<?php endif; ?>

		<div class="cbw-wrap cbw-page">
			<div class="cbw-layout">
				<div class="cbw-layout__main">
					<div class="cbw-prose">
						<?php
						the_content();
						wp_link_pages( array(
							'before' => '<div class="cbw-linkpages">' . esc_html__( 'Pages:', 'cbw' ),
							'after'  => '</div>',
						) );
						?>
					</div>

					<?php cbw_share_links(); ?>

					<?php if ( has_tag() ) : ?>
						<div class="cbw-tags"><?php the_tags( '<span class="cbw-tags__label">' . esc_html__( 'Filed under', 'cbw' ) . '</span>', '', '' ); ?></div>
					<?php endif; ?>

					<section class="cbw-author">
						<span class="cbw-author__avatar"><?php echo get_avatar( get_the_author_meta( 'ID' ), 72 ); ?></span>
						<div>
							<span class="cbw-section__kicker"><?php esc_html_e( 'Written by', 'cbw' ); ?></span>
							<h3 class="cbw-author__name"><?php the_author_posts_link(); ?></h3>
							<p class="cbw-author__bio">
								<?php
								$cbw_bio = get_the_author_meta( 'description' );
								echo esc_html( $cbw_bio ? $cbw_bio : __( 'Staff writer at Global Media Star, covering founders, growth companies and the decisions behind them.', 'cbw' ) );
								?>
							</p>
						</div>
					</section>

					<?php cbw_post_nav(); ?>

					<?php
					$cbw_term = cbw_primary_category();
					if ( $cbw_term ) {
						$cbw_rel = new WP_Query( array(
							'cat'            => $cbw_term->term_id,
							'posts_per_page' => 3,
							'post__not_in'   => array( get_the_ID() ),
							'no_found_rows'  => true,
						) );
						if ( $cbw_rel->have_posts() ) : ?>
							<section class="cbw-related">
								<?php cbw_section_heading( __( 'Related reading', 'cbw' ), get_category_link( $cbw_term ), $cbw_term->name ); ?>
								<div class="cbw-grid cbw-grid--3">
									<?php while ( $cbw_rel->have_posts() ) : $cbw_rel->the_post(); cbw_card( 'card' ); endwhile; ?>
								</div>
							</section>
						<?php endif;
						wp_reset_postdata();
					}
					?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php get_sidebar(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
