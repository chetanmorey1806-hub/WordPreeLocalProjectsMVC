<?php
/**
 * Magazine Issue post type.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cbw_register_issue_cpt() {
	register_post_type( 'cbw_issue', array(
		'labels' => array(
			'name'               => __( 'Magazine Issues', 'cbw' ),
			'singular_name'      => __( 'Magazine Issue', 'cbw' ),
			'add_new_item'       => __( 'Add New Issue', 'cbw' ),
			'edit_item'          => __( 'Edit Issue', 'cbw' ),
			'all_items'          => __( 'All Issues', 'cbw' ),
			'menu_name'          => __( 'Magazine Issues', 'cbw' ),
		),
		'public'        => true,
		'has_archive'   => 'issues',
		'menu_icon'     => 'dashicons-book-alt',
		'menu_position' => 21,
		'rewrite'       => array( 'slug' => 'issue', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'cbw_register_issue_cpt' );
