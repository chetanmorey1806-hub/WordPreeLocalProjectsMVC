<?php
/**
 * Trilingual support: English, Hindi and Marathi.
 *
 * The interface is translated through the theme text domain. Content that
 * lives in the database — page titles, category names, descriptions — is
 * translated through post/term meta so editors can adjust it in wp-admin.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supported languages: code => label, locale, html lang.
 *
 * @return array
 */
function cbw_languages() {
	return array(
		'en' => array( 'label' => 'English', 'name' => 'English', 'short' => 'EN', 'locale' => 'en_US', 'html' => 'en' ),
		'hi' => array( 'label' => 'हिंदी',   'name' => 'Hindi',   'short' => 'हि', 'locale' => 'hi_IN', 'html' => 'hi' ),
		'mr' => array( 'label' => 'मराठी',   'name' => 'Marathi', 'short' => 'मर', 'locale' => 'mr_IN', 'html' => 'mr' ),
	);
}

/**
 * The language for this request: ?lang= wins, then the cookie, then English.
 *
 * @return string
 */
function cbw_lang() {
	static $lang = null;
	if ( null !== $lang ) {
		return $lang;
	}

	$langs = cbw_languages();

	if ( isset( $_GET['lang'] ) ) {
		$candidate = sanitize_key( wp_unslash( $_GET['lang'] ) );
		if ( isset( $langs[ $candidate ] ) ) {
			$lang = $candidate;
			return $lang;
		}
	}

	if ( isset( $_COOKIE['cbw_lang'] ) ) {
		$candidate = sanitize_key( wp_unslash( $_COOKIE['cbw_lang'] ) );
		if ( isset( $langs[ $candidate ] ) ) {
			$lang = $candidate;
			return $lang;
		}
	}

	$lang = 'en';
	return $lang;
}

/**
 * Remember the choice for a year when it arrives in the URL.
 */
function cbw_persist_lang() {
	if ( is_admin() || ! isset( $_GET['lang'] ) ) {
		return;
	}
	$lang  = cbw_lang();
	$known = isset( $_COOKIE['cbw_lang'] ) ? sanitize_key( wp_unslash( $_COOKIE['cbw_lang'] ) ) : '';
	if ( $lang !== $known && ! headers_sent() ) {
		setcookie( 'cbw_lang', $lang, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
}
add_action( 'init', 'cbw_persist_lang' );

/**
 * Switch the front-end locale so the theme text domain resolves.
 * The admin keeps the site's own locale.
 *
 * @param string $locale Current locale.
 * @return string
 */
function cbw_filter_locale( $locale ) {
	if ( is_admin() || wp_doing_ajax() ) {
		return $locale;
	}
	$langs = cbw_languages();
	$lang  = cbw_lang();
	return isset( $langs[ $lang ] ) ? $langs[ $lang ]['locale'] : $locale;
}
add_filter( 'locale', 'cbw_filter_locale' );

/**
 * Correct <html lang="…">.
 */
function cbw_html_lang( $output, $doctype ) {
	if ( 'html' !== $doctype ) {
		return $output;
	}
	$langs = cbw_languages();
	$lang  = cbw_lang();
	$code  = isset( $langs[ $lang ] ) ? $langs[ $lang ]['html'] : 'en';
	return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $code ) . '"', $output );
}
add_filter( 'language_attributes', 'cbw_html_lang', 10, 2 );

/**
 * Body class for language-specific typography.
 */
function cbw_lang_body_class( $classes ) {
	$classes[] = 'lang-' . cbw_lang();
	if ( 'en' !== cbw_lang() ) {
		$classes[] = 'lang-devanagari';
	}
	return $classes;
}
add_filter( 'body_class', 'cbw_lang_body_class' );

/* -------------------------------------------------------------------------
 * Content translation, stored as meta
 * ---------------------------------------------------------------------- */

/**
 * Meta key for a translated field.
 *
 * @param string $field 'title' or 'excerpt'.
 * @param string $lang  Language code.
 * @return string
 */
function cbw_meta_key( $field, $lang ) {
	return '_cbw_' . $field . '_' . $lang;
}

/**
 * Translated post title, when one has been stored.
 */
function cbw_filter_title( $title, $post_id = null ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || ! $post_id || is_admin() ) {
		return $title;
	}
	$t = get_post_meta( $post_id, cbw_meta_key( 'title', $lang ), true );
	return $t ? $t : $title;
}
add_filter( 'the_title', 'cbw_filter_title', 10, 2 );

/**
 * Translated excerpt / standfirst.
 */
function cbw_filter_excerpt( $excerpt, $post = null ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() ) {
		return $excerpt;
	}
	$post_id = $post instanceof WP_Post ? $post->ID : get_the_ID();
	if ( ! $post_id ) {
		return $excerpt;
	}
	$t = get_post_meta( $post_id, cbw_meta_key( 'excerpt', $lang ), true );
	return $t ? $t : $excerpt;
}
add_filter( 'get_the_excerpt', 'cbw_filter_excerpt', 10, 2 );

/**
 * Translated body content, when a translation has been stored for this page.
 */
function cbw_filter_content( $content ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$t = get_post_meta( get_the_ID(), cbw_meta_key( 'content', $lang ), true );
	return $t ? $t : $content;
}
add_filter( 'the_content', 'cbw_filter_content', 5 );

/**
 * Menu labels follow the page they point at.
 */
function cbw_filter_menu_item( $item ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() || empty( $item->object_id ) ) {
		return $item;
	}
	$t = get_post_meta( $item->object_id, cbw_meta_key( 'title', $lang ), true );
	if ( $t ) {
		$item->title = $t;
	}
	return $item;
}
add_filter( 'wp_setup_nav_menu_item', 'cbw_filter_menu_item' );

/**
 * Category names in badges, archive titles and chips.
 */
function cbw_filter_term_name( $name, $term_id = null, $taxonomy = null ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() || ! $term_id ) {
		return $name;
	}
	$t = get_term_meta( $term_id, cbw_meta_key( 'name', $lang ), true );
	return $t ? $t : $name;
}
add_filter( 'get_term', function ( $term ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() || ! isset( $term->term_id ) ) {
		return $term;
	}
	$t = get_term_meta( $term->term_id, cbw_meta_key( 'name', $lang ), true );
	if ( $t ) {
		$term->name = $t;
	}
	return $term;
} );

/**
 * Site tagline.
 */
function cbw_filter_tagline( $value ) {
	$lang = cbw_lang();
	if ( 'en' === $lang || is_admin() ) {
		return $value;
	}
	$t = get_option( 'cbw_tagline_' . $lang );
	return $t ? $t : $value;
}
add_filter( 'option_blogdescription', 'cbw_filter_tagline' );

/* -------------------------------------------------------------------------
 * Switcher UI
 * ---------------------------------------------------------------------- */

/**
 * A URL for the current page in another language.
 *
 * @param string $lang Language code.
 * @return string
 */
function cbw_lang_url( $lang ) {
	global $wp;
	$base = home_url( add_query_arg( array(), $wp->request ? $wp->request . '/' : '' ) );
	$args = $_GET; // phpcs:ignore
	unset( $args['lang'] );
	$args['lang'] = $lang;
	return add_query_arg( array_map( 'rawurlencode', array_map( 'strval', $args ) ), $base );
}

/**
 * Render the language switcher: a disclosure button over a list of links.
 *
 * The options are ordinary links, so the switch still works with scripts off
 * (the list opens on focus instead of on click).
 */
function cbw_lang_switcher() {
	$langs   = cbw_languages();
	$current = cbw_lang();
	$cur     = $langs[ $current ];
	?>
	<div class="cbw-lang">
		<button class="cbw-lang__btn" type="button" aria-expanded="false" aria-controls="cbw-lang-menu">
			<?php cbw_icon( 'globe', 15 ); ?>
			<span class="cbw-lang__current" lang="<?php echo esc_attr( $cur['html'] ); ?>"><?php echo esc_html( $cur['label'] ); ?></span>
			<?php cbw_icon( 'chevron-down', 13, 'cbw-lang__caret' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Choose language', 'cbw' ); ?></span>
		</button>
		<ul class="cbw-lang__menu" id="cbw-lang-menu" aria-label="<?php esc_attr_e( 'Language', 'cbw' ); ?>">
			<?php foreach ( $langs as $code => $meta ) : ?>
				<li>
					<a
						class="cbw-lang__opt<?php echo $code === $current ? ' is-current' : ''; ?>"
						href="<?php echo esc_url( cbw_lang_url( $code ) ); ?>"
						hreflang="<?php echo esc_attr( $meta['html'] ); ?>"
						<?php echo $code === $current ? ' aria-current="true"' : ''; ?>
					>
						<span class="cbw-lang__abbr" aria-hidden="true"><?php echo esc_html( $meta['short'] ); ?></span>
						<span class="cbw-lang__names">
							<span class="cbw-lang__label" lang="<?php echo esc_attr( $meta['html'] ); ?>"><?php echo esc_html( $meta['label'] ); ?></span>
							<?php if ( $meta['label'] !== $meta['name'] ) : ?>
								<span class="cbw-lang__name" lang="en"><?php echo esc_html( $meta['name'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php cbw_icon( 'check', 15, 'cbw-lang__check' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
