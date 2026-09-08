<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * @package CBW_Magazine
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="cbw-wrap cbw-page">
		<?php cbw_breadcrumbs(); ?>

		<header class="cbw-pagehead">
			<span class="cbw-section__kicker"><?php esc_html_e( 'Get in touch', 'cbw' ); ?></span>
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

		<div class="cbw-contact">
			<div class="cbw-contact__intro">
				<div class="cbw-prose"><?php the_content(); ?></div>

				<ul class="cbw-contact__list">
					<li><strong><?php esc_html_e( 'Editorial desk', 'cbw' ); ?></strong><a href="mailto:editor@globalmediastar.test">editor@globalmediastar.test</a></li>
					<li><strong><?php esc_html_e( 'Story pitches & nominations', 'cbw' ); ?></strong><a href="mailto:pitches@globalmediastar.test">pitches@globalmediastar.test</a></li>
					<li><strong><?php esc_html_e( 'Advertising & partnerships', 'cbw' ); ?></strong><a href="mailto:ads@globalmediastar.test">ads@globalmediastar.test</a></li>
					<li><strong><?php esc_html_e( 'Subscriptions', 'cbw' ); ?></strong><a href="mailto:subs@globalmediastar.test">subs@globalmediastar.test</a></li>
				</ul>
			</div>

			<form class="cbw-form" id="cbw-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>">
				<h2 class="cbw-form__title"><?php esc_html_e( 'Send the newsroom a message', 'cbw' ); ?></h2>

				<?php cbw_form_notice( 'contact' ); ?>

				<p class="cbw-form__row">
					<label for="cbw-name"><?php esc_html_e( 'Full name', 'cbw' ); ?> <span aria-hidden="true">*</span></label>
					<input id="cbw-name" name="cbw_name" type="text" required>
				</p>
				<p class="cbw-form__row">
					<label for="cbw-email"><?php esc_html_e( 'Work email', 'cbw' ); ?> <span aria-hidden="true">*</span></label>
					<input id="cbw-email" name="cbw_email" type="email" required>
				</p>
				<p class="cbw-form__row">
					<label for="cbw-company"><?php esc_html_e( 'Company', 'cbw' ); ?></label>
					<input id="cbw-company" name="cbw_company" type="text">
				</p>
				<p class="cbw-form__row">
					<label for="cbw-topic"><?php esc_html_e( 'What is this about?', 'cbw' ); ?></label>
					<select id="cbw-topic" name="cbw_topic">
						<option><?php esc_html_e( 'Story pitch', 'cbw' ); ?></option>
						<option><?php esc_html_e( 'Founder / CEO feature nomination', 'cbw' ); ?></option>
						<option><?php esc_html_e( 'PR & media enquiry', 'cbw' ); ?></option>
						<option><?php esc_html_e( 'Advertising', 'cbw' ); ?></option>
						<option><?php esc_html_e( 'Subscription support', 'cbw' ); ?></option>
					</select>
				</p>
				<p class="cbw-form__row">
					<label for="cbw-message"><?php esc_html_e( 'Message', 'cbw' ); ?> <span aria-hidden="true">*</span></label>
					<textarea id="cbw-message" name="cbw_message" rows="6" required></textarea>
				</p>

				<p class="cbw-hp" aria-hidden="true">
					<label for="cbw-website"><?php esc_html_e( 'Leave this field empty', 'cbw' ); ?></label>
					<input id="cbw-website" name="cbw_website" type="text" tabindex="-1" autocomplete="off">
				</p>

				<input type="hidden" name="cbw_form" value="contact">
				<input type="hidden" name="cbw_redirect" value="<?php echo esc_url( get_permalink() ); ?>">
				<?php wp_nonce_field( 'cbw_contact', 'cbw_contact_nonce' ); ?>
				<button class="cbw-btn cbw-btn--gold" type="submit"><?php esc_html_e( 'Send message', 'cbw' ); ?></button>
				<p class="cbw-form__note"><?php esc_html_e( 'We reply to editorial enquiries within two business days.', 'cbw' ); ?></p>
			</form>
		</div>
	</div>
	<?php
endwhile;

get_footer();
