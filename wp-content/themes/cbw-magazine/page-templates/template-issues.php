<?php
/**
 * Template Name: Magazine Issues
 * Template Post Type: page
 *
 * Lists magazine issues. Set the page excerpt to "latest" behaviour by adding
 * the custom field cbw_issue_limit.
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();

	$cbw_limit = (int) get_post_meta( get_the_ID(), 'cbw_issue_limit', true );
	$cbw_limit = $cbw_limit ? $cbw_limit : 12;
	$cbw_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
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

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="cbw-page__hero">
				<?php the_post_thumbnail( 'cbw-hero' ); ?>
				<?php cbw_thumb_credit(); ?>
			</figure>
		<?php endif; ?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="cbw-prose cbw-prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php
		$cbw_q = new WP_Query( array(
			'post_type'      => 'cbw_issue',
			'posts_per_page' => $cbw_limit,
			'paged'          => $cbw_paged,
		) );

		if ( $cbw_q->have_posts() ) :

			if ( 1 === $cbw_limit ) :
				$cbw_q->the_post(); ?>
				<div class="cbw-issue-single">
					<figure class="cbw-issue-single__cover"><?php cbw_thumbnail( 'cbw-cover' ); ?></figure>
					<div class="cbw-issue-single__body">
						<span class="cbw-section__kicker"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></span>
						<h2 class="cbw-pagehead__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="cbw-prose"><?php the_excerpt(); ?></div>
						<div class="cbw-issue-single__actions">
							<a class="cbw-btn cbw-btn--gold" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read this issue', 'cbw' ); ?></a>
							<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( home_url( '/magazine/magazine-archive/' ) ); ?>"><?php esc_html_e( 'Browse the archive', 'cbw' ); ?></a>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="cbw-grid cbw-grid--4">
					<?php while ( $cbw_q->have_posts() ) : $cbw_q->the_post(); ?>
						<article class="cbw-issue">
							<a class="cbw-issue__cover" href="<?php the_permalink(); ?>"><?php cbw_thumbnail( 'cbw-cover' ); ?></a>
							<h2 class="cbw-issue__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="cbw-issue__meta"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
						</article>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<p class="cbw-empty"><?php esc_html_e( 'No issues published yet.', 'cbw' ); ?></p>
		<?php endif;
		wp_reset_postdata(); ?>
	</div>
	<?php
endwhile;

get_footer();
