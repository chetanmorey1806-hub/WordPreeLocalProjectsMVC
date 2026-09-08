<?php
/**
 * Site header.
 *
 * @package CBW_Magazine
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#cbw-content"><?php esc_html_e( 'Skip to content', 'cbw' ); ?></a>

<div id="page" class="cbw-site">

	<div class="cbw-topbar">
		<div class="cbw-wrap cbw-topbar__inner">
			<p class="cbw-topbar__date"><?php echo esc_html( date_i18n( 'l, F j, Y' ) ); ?></p>

			<?php
			if ( has_nav_menu( 'topbar' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'topbar',
					'container'      => 'nav',
					'container_class' => 'cbw-topbar__nav',
					'menu_class'     => 'cbw-topbar__menu',
					'depth'          => 1,
				) );
			}
			?>

			<?php cbw_theme_toggle(); ?>

			<?php cbw_lang_switcher(); ?>

			<ul class="cbw-social" aria-label="<?php esc_attr_e( 'Social links', 'cbw' ); ?>">
				<li><a href="#" aria-label="LinkedIn"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5A2.5 2.5 0 112.5 6 2.5 2.5 0 014.98 3.5zM3 8.98h4v12H3zM9.5 8.98h3.83v1.64h.05a4.2 4.2 0 013.78-2.08c4.04 0 4.79 2.66 4.79 6.12v6.32h-4v-5.6c0-1.34-.02-3.06-1.86-3.06-1.87 0-2.15 1.46-2.15 2.96v5.7h-4z"/></svg></a></li>
				<li><a href="#" aria-label="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.66l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.01 4.13H5.05z"/></svg></a></li>
				<li><a href="#" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7H7.9v-2.9h2.54V9.85c0-2.52 1.5-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.45 2.9h-2.33v7C18.34 21.21 22 17.06 22 12.06z"/></svg></a></li>
				<li><a href="#" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85C2.38 3.92 3.89 2.38 7.15 2.23 8.42 2.17 8.8 2.16 12 2.16zm0 5.17A4.67 4.67 0 1016.67 12 4.67 4.67 0 0012 7.33zm0 7.7A3.03 3.03 0 1115.03 12 3.03 3.03 0 0112 15.03zm4.85-8.99a1.09 1.09 0 101.09 1.09 1.09 1.09 0 00-1.09-1.09z"/></svg></a></li>
			</ul>
		</div>
	</div>

	<header class="cbw-header">
		<div class="cbw-wrap cbw-header__inner">
			<div class="cbw-brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="cbw-brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="cbw-brand__mark" aria-hidden="true">GMS</span>
						<span class="cbw-brand__text">
							<span class="cbw-brand__name"><?php bloginfo( 'name' ); ?></span>
							<span class="cbw-brand__tag"><?php bloginfo( 'description' ); ?></span>
						</span>
					</a>
				<?php endif; ?>
			</div>

			<div class="cbw-header__actions">
				<?php get_search_form(); ?>
				<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/about/advertise-with-us/' ) ); ?>"><?php esc_html_e( 'Advertise', 'cbw' ); ?></a>
				<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( home_url( '/magazine/latest-issue/' ) ); ?>"><?php esc_html_e( 'Latest Issue', 'cbw' ); ?></a>
			</div>
		</div>
	</header>

	<nav class="cbw-nav" id="cbw-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'cbw' ); ?>">
		<div class="cbw-wrap cbw-nav__inner">
			<button class="cbw-burger" type="button" aria-expanded="false" aria-controls="cbw-primary-menu">
				<span class="cbw-burger__bars" aria-hidden="true"></span>
				<span class="cbw-burger__label"><?php esc_html_e( 'Menu', 'cbw' ); ?></span>
			</button>

			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_id'        => 'cbw-primary-menu',
				'menu_class'     => 'cbw-nav__list',
				'container'      => false,
				'walker'         => new CBW_Nav_Walker(),
				'fallback_cb'    => 'cbw_menu_fallback',
			) );
			?>

			<?php // Search and calls to action live in the drawer on small screens. ?>
			<div class="cbw-nav__drawer">
				<?php get_search_form(); ?>
				<div class="cbw-nav__cta">
					<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/about/advertise-with-us/' ) ); ?>"><?php esc_html_e( 'Advertise', 'cbw' ); ?></a>
					<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( home_url( '/magazine/latest-issue/' ) ); ?>"><?php esc_html_e( 'Latest Issue', 'cbw' ); ?></a>
				</div>
			</div>
		</div>
	</nav>

	<main id="cbw-content" class="cbw-main">
