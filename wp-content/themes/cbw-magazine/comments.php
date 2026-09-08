<?php
/**
 * Comments.
 *
 * @package CBW_Magazine
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="cbw-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="cbw-comments__title">
			<?php
			$cbw_count = get_comments_number();
			/* translators: %s: comment count. */
			printf( esc_html( _n( '%s Response', '%s Responses', $cbw_count, 'cbw' ) ), esc_html( number_format_i18n( $cbw_count ) ) );
			?>
		</h2>

		<ol class="cbw-comments__list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'avatar_size'=> 48,
				'short_ping' => true,
			) );
			?>
		</ol>

		<?php the_comments_pagination( array(
			'prev_text' => __( '&larr; Older', 'cbw' ),
			'next_text' => __( 'Newer &rarr;', 'cbw' ),
		) ); ?>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply'        => __( 'Join the discussion', 'cbw' ),
		'class_submit'       => 'cbw-btn cbw-btn--gold',
		'comment_notes_before' => '<p class="cbw-form__note">' . esc_html__( 'Your email address will not be published.', 'cbw' ) . '</p>',
	) );
	?>
</section>
