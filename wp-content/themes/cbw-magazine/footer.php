<?php
/**
 * Site footer.
 *
 * @package CBW_Magazine
 */
?>
	</main><!-- .cbw-main -->

	<footer class="cbw-footer">
		<div class="cbw-wrap">
			<div class="cbw-footer__top">
				<div class="cbw-footer__brand">
					<a class="cbw-brand__link cbw-brand__link--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="cbw-brand__mark" aria-hidden="true">GMS</span>
						<span class="cbw-brand__name"><?php bloginfo( 'name' ); ?></span>
					</a>
					<p class="cbw-footer__blurb"><?php bloginfo( 'description' ); ?></p>
					<p class="cbw-footer__blurb"><?php esc_html_e( 'Business journalism for founders, chief executives and the teams building what comes next.', 'cbw' ); ?></p>
					<a class="cbw-btn cbw-btn--gold cbw-btn--shine" href="<?php echo esc_url( home_url( '/magazine/latest-issue/' ) ); ?>"><?php cbw_icon( 'book', 16 ); ?><?php esc_html_e( 'Read the latest issue', 'cbw' ); ?></a>
					<?php cbw_social_links( 'cbw-social cbw-social--footer' ); ?>
				</div>

				<div class="cbw-footer__cols">
					<?php
					// Mirror the primary navigation so every section stays reachable from the footer.
					$cbw_footer_sections = get_pages( array(
						'parent'      => 0,
						'sort_column' => 'menu_order',
						'exclude'     => implode( ',', array_filter( array( (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ) ) ) ),
					) );

					foreach ( $cbw_footer_sections as $cbw_section ) :
						$cbw_children = get_pages( array(
							'parent'      => $cbw_section->ID,
							'sort_column' => 'menu_order',
						) );
						if ( empty( $cbw_children ) ) {
							continue;
						}
						?>
						<nav class="cbw-footer__col" aria-labelledby="foot-<?php echo esc_attr( $cbw_section->ID ); ?>">
							<h4 class="widget-title" id="foot-<?php echo esc_attr( $cbw_section->ID ); ?>">
								<a href="<?php echo esc_url( get_permalink( $cbw_section ) ); ?>"><?php echo esc_html( get_the_title( $cbw_section ) ); ?></a>
							</h4>
							<ul>
								<?php foreach ( $cbw_children as $cbw_child ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $cbw_child ) ); ?>"><?php echo esc_html( get_the_title( $cbw_child ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
				<div class="cbw-footer__widgets">
					<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
							<div class="cbw-footer__widget"><?php dynamic_sidebar( 'footer-' . $i ); ?></div>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<div class="cbw-footer__bottom">
				<p class="cbw-copy">
					<?php
					/* translators: %1$s: year, %2$s: site name. */
					printf( esc_html__( '© %1$s %2$s. All rights reserved.', 'cbw' ), esc_html( date_i18n( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
					?>
				</p>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location'  => 'footer',
						'container'       => 'nav',
						'container_class' => 'cbw-footer__legal',
						'menu_class'      => 'cbw-footer__legal-list',
						'depth'           => 1,
					) );
				}
				?>
			</div>
		</div>
	</footer>

	<a class="cbw-totop" href="#page">
		<svg class="cbw-totop__ring" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><circle cx="24" cy="24" r="22" pathLength="100"/></svg>
		<?php cbw_icon( 'arrow-up', 18 ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'cbw' ); ?></span>
	</a>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
