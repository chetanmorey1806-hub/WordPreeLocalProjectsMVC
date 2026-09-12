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
			<p class="cbw-topbar__date"><?php cbw_icon( 'calendar', 14 ); ?><?php echo esc_html( date_i18n( 'l, F j, Y' ) ); ?></p>

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

			<?php cbw_social_links(); ?>
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
				<a class="cbw-btn cbw-btn--gold cbw-btn--shine" href="<?php echo esc_url( home_url( '/about/advertise-with-us/' ) ); ?>"><?php cbw_icon( 'sparkles', 16 ); ?><?php esc_html_e( 'Advertise', 'cbw' ); ?></a>
				<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( home_url( '/magazine/latest-issue/' ) ); ?>"><?php cbw_icon( 'book', 16 ); ?><?php esc_html_e( 'Latest Issue', 'cbw' ); ?></a>
			</div>
		</div>
	</header>

	<nav class="cbw-nav" id="cbw-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'cbw' ); ?>">
		<div class="cbw-wrap cbw-nav__inner">
			<button class="cbw-burger" type="button" aria-expanded="false" aria-controls="cbw-nav-panel">
				<span class="cbw-burger__bars" aria-hidden="true"></span>
				<span class="cbw-burger__label"><?php esc_html_e( 'Menu', 'cbw' ); ?></span>
			</button>

			<?php // Shown in the bar once the masthead has scrolled away. ?>
			<a class="cbw-nav__mini" href="<?php echo esc_url( home_url( '/' ) ); ?>" tabindex="-1" aria-hidden="true">
				<?php if ( get_site_icon_url() ) : ?>
					<img src="<?php echo esc_url( get_site_icon_url( 64 ) ); ?>" width="32" height="32" alt="">
				<?php endif; ?>
				<span><?php bloginfo( 'name' ); ?></span>
			</a>

			<?php // On small screens this becomes an off-canvas panel over a dimmed page. ?>
			<div class="cbw-nav__backdrop" hidden></div>
			<div class="cbw-nav__panel" id="cbw-nav-panel">
				<div class="cbw-nav__panelhead">
					<span class="cbw-nav__paneltitle"><?php bloginfo( 'name' ); ?></span>
					<button class="cbw-nav__close" type="button">
						<?php cbw_icon( 'close', 20 ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'cbw' ); ?></span>
					</button>
				</div>

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

				<?php // Search and calls to action live in the panel on small screens. ?>
				<div class="cbw-nav__drawer">
					<?php get_search_form(); ?>
					<div class="cbw-nav__cta">
						<a class="cbw-btn cbw-btn--gold" href="<?php echo esc_url( home_url( '/about/advertise-with-us/' ) ); ?>"><?php esc_html_e( 'Advertise', 'cbw' ); ?></a>
						<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( home_url( '/magazine/latest-issue/' ) ); ?>"><?php esc_html_e( 'Latest Issue', 'cbw' ); ?></a>
					</div>
					<?php cbw_social_links( 'cbw-social cbw-social--panel' ); ?>
				</div>
			</div>
		</div>
		<span class="cbw-nav__progress" aria-hidden="true"></span>
	</nav>

	<?php cbw_ticker(); ?>

	<main id="cbw-content" class="cbw-main">
