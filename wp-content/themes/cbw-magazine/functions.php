<?php
/**
 * CBW Magazine theme functions.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CBW_VERSION', '1.1.0' );

/**
 * Theme setup.
 */
function cbw_setup() {
	load_theme_textdomain( 'cbw', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 104,
		'width'       => 153,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	add_image_size( 'cbw-hero', 1200, 675, true );
	add_image_size( 'cbw-card', 640, 400, true );
	add_image_size( 'cbw-thumb', 160, 120, true );
	add_image_size( 'cbw-cover', 480, 640, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'cbw' ),
		'topbar'  => __( 'Top Bar Menu', 'cbw' ),
		'footer'  => __( 'Footer Menu', 'cbw' ),
	) );
}
add_action( 'after_setup_theme', 'cbw_setup' );

/**
 * Content width.
 */
function cbw_content_width() {
	$GLOBALS['content_width'] = 800;
}
add_action( 'after_setup_theme', 'cbw_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function cbw_assets() {
	wp_enqueue_style(
		'cbw-fonts',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cbw-main', get_template_directory_uri() . '/assets/css/main.css', array(), CBW_VERSION );
	wp_enqueue_script( 'cbw-main', get_template_directory_uri() . '/assets/js/main.js', array(), CBW_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cbw_assets' );

/**
 * Flag that scripts run before first paint. Entrance animations start their
 * elements hidden only under .js, so nothing is lost when scripts are off.
 */
function cbw_js_flag() {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}
add_action( 'wp_head', 'cbw_js_flag', 0 );

/**
 * Widget areas.
 */
function cbw_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Article Sidebar', 'cbw' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Shown beside articles and archives.', 'cbw' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number. */
			'name'          => sprintf( __( 'Footer Column %d', 'cbw' ), $i ),
			'id'            => 'footer-' . $i,
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'cbw_widgets_init' );

/**
 * Excerpt tweaks.
 */
function cbw_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'cbw_excerpt_length' );

function cbw_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'cbw_excerpt_more' );

/**
 * Body classes.
 */
function cbw_body_classes( $classes ) {
	if ( is_page_template( 'page-templates/template-section.php' ) ) {
		$classes[] = 'section-landing';
	}
	return $classes;
}
add_filter( 'body_class', 'cbw_body_classes' );

require_once get_template_directory() . '/inc/icons.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/nav-walker.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/forms.php';
require_once get_template_directory() . '/inc/i18n.php';
require_once get_template_directory() . '/inc/theme-mode.php';
require_once get_template_directory() . '/inc/admin-style.php';
