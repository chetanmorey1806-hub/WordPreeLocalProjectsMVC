<?php
/**
 * Contact and newsletter handling.
 *
 * Submissions are stored as `cbw_message` posts (visible in wp-admin) and an
 * email notification is attempted. Uses post/redirect/get so a refresh does
 * not resubmit.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storage for submissions.
 */
function cbw_register_message_cpt() {
	register_post_type( 'cbw_message', array(
		'labels' => array(
			'name'          => __( 'Messages', 'cbw' ),
			'singular_name' => __( 'Message', 'cbw' ),
			'menu_name'     => __( 'Messages', 'cbw' ),
			'all_items'     => __( 'All Messages', 'cbw' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 22,
		'capability_type'     => 'post',
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'        => true,
		'supports'            => array( 'title', 'editor' ),
		'exclude_from_search' => true,
	) );
}
add_action( 'init', 'cbw_register_message_cpt' );

/**
 * Handle both forms before any output is sent.
 */
function cbw_handle_forms() {
	if ( empty( $_POST['cbw_form'] ) ) {
		return;
	}

	$form = sanitize_key( wp_unslash( $_POST['cbw_form'] ) );

	// The form carries its own page URL: wp_get_referer() returns false for a
	// self-posting form because the referer matches the request URI.
	$redirect = '';
	if ( ! empty( $_POST['cbw_redirect'] ) ) {
		$redirect = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['cbw_redirect'] ) ), '' );
	}
	if ( ! $redirect ) {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	}
	$redirect = remove_query_arg( 'cbw', $redirect );

	// Honeypot: real people leave this empty.
	if ( ! empty( $_POST['cbw_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'cbw', 'sent', $redirect ) );
		exit;
	}

	if ( 'contact' === $form ) {
		if ( ! isset( $_POST['cbw_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbw_contact_nonce'] ) ), 'cbw_contact' ) ) {
			wp_safe_redirect( add_query_arg( 'cbw', 'nonce', $redirect ) );
			exit;
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['cbw_name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['cbw_email'] ?? '' ) );
		$company = sanitize_text_field( wp_unslash( $_POST['cbw_company'] ?? '' ) );
		$topic   = sanitize_text_field( wp_unslash( $_POST['cbw_topic'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['cbw_message'] ?? '' ) );

		if ( ! $name || ! is_email( $email ) || ! $message ) {
			wp_safe_redirect( add_query_arg( 'cbw', 'invalid', $redirect ) );
			exit;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'cbw_message',
			'post_status'  => 'private',
			/* translators: 1: topic, 2: sender name. */
			'post_title'   => sprintf( __( '%1$s — %2$s', 'cbw' ), $topic ? $topic : __( 'Enquiry', 'cbw' ), $name ),
			'post_content' => $message,
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, 'cbw_type', 'contact' );
			update_post_meta( $post_id, 'cbw_name', $name );
			update_post_meta( $post_id, 'cbw_email', $email );
			update_post_meta( $post_id, 'cbw_company', $company );
			update_post_meta( $post_id, 'cbw_topic', $topic );

			wp_mail(
				get_option( 'admin_email' ),
				sprintf( '[%s] %s — %s', get_bloginfo( 'name' ), $topic, $name ),
				"From: {$name} <{$email}>\nCompany: {$company}\nTopic: {$topic}\n\n{$message}",
				array( 'Reply-To: ' . $email )
			);
		}

		wp_safe_redirect( add_query_arg( 'cbw', 'sent', $redirect ) . '#cbw-form' );
		exit;
	}

	if ( 'newsletter' === $form ) {
		if ( ! isset( $_POST['cbw_news_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbw_news_nonce'] ) ), 'cbw_newsletter' ) ) {
			wp_safe_redirect( add_query_arg( 'cbw', 'nonce', $redirect ) );
			exit;
		}

		$email = sanitize_email( wp_unslash( $_POST['cbw_news_email'] ?? '' ) );
		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'cbw', 'invalid', $redirect ) . '#cbw-news-title' );
			exit;
		}

		// One record per address.
		$existing = get_posts( array(
			'post_type'   => 'cbw_message',
			'post_status' => 'private',
			'meta_query'  => array(
				array( 'key' => 'cbw_type', 'value' => 'newsletter' ),
				array( 'key' => 'cbw_email', 'value' => $email ),
			),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );

		if ( ! $existing ) {
			$post_id = wp_insert_post( array(
				'post_type'    => 'cbw_message',
				'post_status'  => 'private',
				/* translators: %s: email address. */
				'post_title'   => sprintf( __( 'Newsletter — %s', 'cbw' ), $email ),
				'post_content' => __( 'Subscribed to The GMS Briefing.', 'cbw' ),
			) );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, 'cbw_type', 'newsletter' );
				update_post_meta( $post_id, 'cbw_email', $email );
			}
		}

		wp_safe_redirect( add_query_arg( 'cbw', 'subscribed', $redirect ) . '#cbw-news-title' );
		exit;
	}
}
add_action( 'template_redirect', 'cbw_handle_forms' );

/**
 * Render the notice for the current ?cbw= status.
 *
 * @param string $scope 'contact' or 'newsletter'.
 */
function cbw_form_notice( $scope = 'contact' ) {
	$status = isset( $_GET['cbw'] ) ? sanitize_key( wp_unslash( $_GET['cbw'] ) ) : '';
	if ( ! $status ) {
		return;
	}

	$messages = array(
		'sent'       => array( 'ok',  __( 'Thank you — your message has reached the newsroom. We reply to editorial enquiries within two business days.', 'cbw' ), 'contact' ),
		'subscribed' => array( 'ok',  __( 'You are subscribed to The GMS Briefing. The next edition arrives on Thursday.', 'cbw' ), 'newsletter' ),
		'invalid'    => array( 'err', __( 'Please check the required fields and try again.', 'cbw' ), 'both' ),
		'nonce'      => array( 'err', __( 'That form expired. Please reload the page and try again.', 'cbw' ), 'both' ),
	);

	if ( ! isset( $messages[ $status ] ) ) {
		return;
	}

	list( $kind, $text, $for ) = $messages[ $status ];
	if ( 'both' !== $for && $for !== $scope ) {
		return;
	}

	printf(
		'<div class="cbw-notice cbw-notice--%1$s" role="status">%2$s</div>',
		esc_attr( $kind ),
		esc_html( $text )
	);
}

/**
 * Show the submitter's details in the admin list.
 */
function cbw_message_columns( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'title'      => __( 'Subject', 'cbw' ),
		'cbw_type'   => __( 'Type', 'cbw' ),
		'cbw_email'  => __( 'Email', 'cbw' ),
		'cbw_company'=> __( 'Company', 'cbw' ),
		'date'       => __( 'Received', 'cbw' ),
	);
}
add_filter( 'manage_cbw_message_posts_columns', 'cbw_message_columns' );

function cbw_message_column( $col, $post_id ) {
	if ( in_array( $col, array( 'cbw_type', 'cbw_email', 'cbw_company' ), true ) ) {
		echo esc_html( get_post_meta( $post_id, $col, true ) );
	}
}
add_action( 'manage_cbw_message_posts_custom_column', 'cbw_message_column', 10, 2 );
