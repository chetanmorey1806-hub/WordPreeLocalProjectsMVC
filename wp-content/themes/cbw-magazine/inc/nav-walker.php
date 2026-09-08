<?php
/**
 * Navigation walker: adds dropdown panels and an accessible mobile toggle.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CBW_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a sub-menu level.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$classes = 0 === $depth ? 'cbw-subnav cbw-subnav--panel' : 'cbw-subnav';
		$output .= "\n{$indent}<ul class=\"{$classes}\">\n";
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Render one item.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		$has_kids  = in_array( 'menu-item-has-children', $classes, true );

		if ( $has_kids ) {
			$classes[] = 'cbw-has-panel';
		}
		if ( 0 === $depth ) {
			$classes[] = 'cbw-nav__item';
		}

		$class_names = join( ' ', array_filter( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ) );

		$output .= '<li class="' . esc_attr( $class_names ) . '">';

		$atts = array(
			'href'   => ! empty( $item->url ) ? $item->url : '',
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'class'  => 0 === $depth ? 'cbw-nav__link' : 'cbw-subnav__link',
		);

		$attributes = '';
		foreach ( apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth ) as $key => $value ) {
			if ( '' !== $value && false !== $value ) {
				$attributes .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$output .= '<a' . $attributes . '>' . esc_html( $title );
		if ( $has_kids && 0 === $depth ) {
			$output .= '<svg class="cbw-caret" width="10" height="7" viewBox="0 0 10 7" aria-hidden="true" focusable="false"><path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}
		$output .= '</a>';

		if ( $has_kids ) {
			$output .= '<button class="cbw-nav__toggle" type="button" aria-expanded="false">'
				. '<span class="screen-reader-text">' . esc_html( sprintf( __( 'Show submenu for %s', 'cbw' ), $title ) ) . '</span>'
				. '<span class="cbw-nav__toggle-icon" aria-hidden="true"></span></button>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= "</li>\n";
	}
}

/**
 * Fallback used before the primary menu is assigned: lists top-level pages.
 */
function cbw_menu_fallback() {
	echo '<ul id="cbw-primary-menu" class="cbw-nav__list">';
	wp_list_pages( array(
		'title_li' => '',
		'depth'    => 2,
		'sort_column' => 'menu_order,post_title',
	) );
	echo '</ul>';
}
