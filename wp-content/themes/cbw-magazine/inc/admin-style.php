<?php
/**
 * The admin and the sign-in page, in the magazine's own colours.
 *
 * Both are restyled, never rebuilt: the stylesheets dress markup that
 * WordPress already prints, so a core update changes the furniture and
 * this only changes the paint.
 *
 * @package CBW_Magazine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin stylesheet, on every admin screen.
 *
 * Loaded last so it wins over core's sheet without a single !important
 * on a rule that has to fight for it.
 */
function cbw_admin_style() {
	wp_enqueue_style(
		'cbw-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'cbw-admin',
		get_template_directory_uri() . '/assets/css/admin.css',
		array( 'cbw-admin-fonts' ),
		CBW_VERSION
	);

	// Only the dashboard needs the counters.
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'dashboard' === $screen->id ) {
		wp_enqueue_script(
			'cbw-admin',
			get_template_directory_uri() . '/assets/js/admin.js',
			array(),
			CBW_VERSION,
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'cbw_admin_style' );

/**
 * The sign-in page's stylesheet.
 */
function cbw_login_style() {
	wp_enqueue_style(
		'cbw-login-fonts',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'cbw-login',
		get_template_directory_uri() . '/assets/css/login.css',
		array( 'login', 'cbw-login-fonts' ),
		CBW_VERSION
	);
}
add_action( 'login_enqueue_scripts', 'cbw_login_style' );

/**
 * The masthead above the sign-in form.
 *
 * Core prints the WordPress logo linked to wordpress.org. It is this
 * site's own door, so it says the site's name and leads home. If a
 * own door, so it says the site's name and leads home.
 *
 * Core prints that text inside the anchor itself
 * (`<h1 class="wp-login-logo"><a>…</a></h1>`) and hides it behind a
 * background image with text-indent. The stylesheet undoes the indent
 * and drops the image, so the name this filter returns is what shows —
 * no second copy printed on top of it.
 */
function cbw_login_logo_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'cbw_login_logo_url' );

function cbw_login_logo_title() {
	return get_bloginfo( 'name', 'display' );
}
add_filter( 'login_headertext', 'cbw_login_logo_title' );

/**
 * The footer credit under the admin.
 */
function cbw_admin_footer_text( $text ) {
	return sprintf(
		/* translators: %s: site name */
		esc_html__( '%s — powered by WordPress', 'cbw' ),
		'<strong>' . esc_html( get_bloginfo( 'name', 'display' ) ) . '</strong>'
	);
}
add_filter( 'admin_footer_text', 'cbw_admin_footer_text' );

/**
 * The masthead image on the sign-in page.
 *
 * The reversed logo, because the sign-in page is midnight navy and the
 * supplied artwork sets two of its three words in near-black.
 */
function cbw_login_logo_image() {
	$att = (int) get_option( 'cbw_logo_dark' );
	if ( ! $att ) {
		return;
	}
	$url = wp_get_attachment_image_url( $att, 'full' );
	if ( ! $url ) {
		return;
	}
	?>
	<style id="cbw-login-logo">
		/* The image carries the wordmark, so the text inside the anchor is
		   hidden visually while staying as the link's accessible name. */
		body.login h1.wp-login-logo a{
			display:block;
			font-size:0;
			line-height:0;
			padding-top:104px;
			background-image:url("<?php echo esc_url( $url ); ?>");
			background-repeat:no-repeat;
			background-position:top center;
			background-size:auto 88px;
		}
		@media (max-width:400px){
			body.login h1.wp-login-logo a{padding-top:82px;background-size:auto 68px}
		}
	</style>
	<?php
}
add_action( 'login_head', 'cbw_login_logo_image', 20 );

/* -------------------------------------------------------------------------
 * The dashboard panel
 *
 * Core's "At a Glance" counts posts and pages and stops there. This site also
 * has magazine issues, reader messages, a photo library with attribution and
 * three languages, none of which show anywhere. This panel puts the real
 * shape of the magazine on the first screen, with a way into each part.
 * ---------------------------------------------------------------------- */

/**
 * Numbers for the panel. Cached briefly: the translation coverage query walks
 * every page, which is not something to repeat on every dashboard load.
 *
 * @return array
 */
function cbw_dashboard_stats() {
	$stats = get_transient( 'cbw_dash_stats' );
	if ( is_array( $stats ) ) {
		return $stats;
	}

	$pages = get_pages( array( 'post_status' => 'publish' ) );
	$translated = 0;
	foreach ( $pages as $p ) {
		if ( get_post_meta( $p->ID, '_cbw_title_hi', true ) && get_post_meta( $p->ID, '_cbw_title_mr', true ) ) {
			$translated++;
		}
	}

	$messages = wp_count_posts( 'cbw_message' );
	$credited = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => 'cbw_credit_artist',
	) );

	$stats = array(
		'articles'   => (int) wp_count_posts( 'post' )->publish,
		'pages'      => count( $pages ),
		'issues'     => (int) wp_count_posts( 'cbw_issue' )->publish,
		'messages'   => isset( $messages->private ) ? (int) $messages->private : 0,
		'media'      => (int) array_sum( (array) wp_count_attachments() ),
		'credited'   => count( $credited ),
		'categories' => (int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) ),
		'translated' => $translated,
		'page_total' => count( $pages ),
	);

	set_transient( 'cbw_dash_stats', $stats, 5 * MINUTE_IN_SECONDS );
	return $stats;
}

/**
 * Clear the cache whenever something it counts changes.
 */
function cbw_dashboard_stats_flush() {
	delete_transient( 'cbw_dash_stats' );
}
add_action( 'save_post', 'cbw_dashboard_stats_flush' );
add_action( 'deleted_post', 'cbw_dashboard_stats_flush' );
add_action( 'add_attachment', 'cbw_dashboard_stats_flush' );
add_action( 'delete_attachment', 'cbw_dashboard_stats_flush' );

/**
 * Register the panel above core's widgets.
 */
function cbw_dashboard_widget() {
	wp_add_dashboard_widget(
		'cbw_overview',
		sprintf(
			/* translators: %s: site name */
			esc_html__( '%s — the magazine at a glance', 'cbw' ),
			get_bloginfo( 'name', 'display' )
		),
		'cbw_dashboard_widget_render'
	);

	cbw_dashboard_place_widget();
}

/**
 * Put the panel first — once.
 *
 * Reordering $wp_meta_boxes in PHP does nothing once a user has a saved
 * dashboard order, because that order wins. So the stored order is edited
 * instead, and a flag records that it was done, so a later decision by the
 * user to move or remove the panel is never undone.
 */
function cbw_dashboard_place_widget() {
	$user = get_current_user_id();
	if ( ! $user || get_user_meta( $user, 'cbw_dash_placed', true ) ) {
		return;
	}
	update_user_meta( $user, 'cbw_dash_placed', 1 );

	$order = get_user_meta( $user, 'meta-box-order_dashboard', true );
	if ( ! is_array( $order ) ) {
		return; // No saved order: the default position is already first.
	}

	$normal = isset( $order['normal'] ) ? array_filter( explode( ',', $order['normal'] ) ) : array();
	if ( in_array( 'cbw_overview', $normal, true ) ) {
		return;
	}
	array_unshift( $normal, 'cbw_overview' );
	$order['normal'] = implode( ',', $normal );
	update_user_meta( $user, 'meta-box-order_dashboard', $order );
}
add_action( 'wp_dashboard_setup', 'cbw_dashboard_widget' );

/**
 * Render the panel.
 */
function cbw_dashboard_widget_render() {
	$s = cbw_dashboard_stats();

	$tiles = array(
		array( 'articles',   __( 'Articles', 'cbw' ),        $s['articles'],   'edit.php',                        'dashicons-media-document' ),
		array( 'pages',      __( 'Pages', 'cbw' ),           $s['pages'],      'edit.php?post_type=page',         'dashicons-admin-page' ),
		array( 'issues',     __( 'Magazine issues', 'cbw' ), $s['issues'],     'edit.php?post_type=cbw_issue',    'dashicons-book-alt' ),
		array( 'messages',   __( 'Messages', 'cbw' ),        $s['messages'],   'edit.php?post_type=cbw_message',  'dashicons-email-alt' ),
		array( 'media',      __( 'Media items', 'cbw' ),     $s['media'],      'upload.php',                      'dashicons-format-gallery' ),
		array( 'categories', __( 'Sections', 'cbw' ),        $s['categories'], 'edit-tags.php?taxonomy=category', 'dashicons-category' ),
	);

	?>
	<div class="cbw-dash">
		<div class="cbw-dash__grid">
			<?php foreach ( $tiles as $i => $t ) :
				list( $key, $label, $value, $link, $icon ) = $t; ?>
				<a class="cbw-dash__tile" href="<?php echo esc_url( admin_url( $link ) ); ?>"
				   style="--i:<?php echo (int) $i; ?>">
					<span class="cbw-dash__icon dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
					<span class="cbw-dash__num" data-count="<?php echo (int) $value; ?>"><?php echo (int) $value; ?></span>
					<span class="cbw-dash__label"><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php // The hero above carries the actions and the translation meter,
		      // so this panel adds only what is not already on the screen. ?>
		<p class="cbw-dash__note">
			<span class="dashicons dashicons-camera" aria-hidden="true"></span>
			<?php
			printf(
				/* translators: %s: number of photographs. */
				esc_html( _n(
					'%s photograph carries its photographer and licence.',
					'%s photographs carry their photographer and licence.',
					$s['credited'],
					'cbw'
				) ),
				'<strong>' . esc_html( number_format_i18n( $s['credited'] ) ) . '</strong>'
			);
			?>
			<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>"><?php esc_html_e( 'Open the library', 'cbw' ); ?></a>
		</p>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * The dashboard hero
 *
 * Core's welcome panel is a links list for a site nobody has built yet. This
 * site is built, so the slot carries the masthead, the date, what shipped this
 * week, and the two numbers worth watching. It uses the welcome_panel hook, so
 * Screen Options still switches it off like any other panel.
 * ---------------------------------------------------------------------- */

/**
 * The hero renders on its own, not through core's welcome panel.
 *
 * The welcome panel is switched by a `show_welcome_panel` user option that
 * core's own dismiss handler owns. Hanging the site's masthead on that meant
 * it could vanish for reasons nothing to do with this theme — which is what
 * happened. `in_admin_header` on the dashboard screen is predictable: it is
 * there, every time, and core's panel is dropped so the two never stack.
 */
function cbw_render_hero() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}
	// A wrapper, because a top margin on the hero collapses out to #wpcontent
	// and takes the admin bar with it. Padding on a wrapper does not collapse.
	echo '<div class="cbw-hero-slot">';
	cbw_welcome_panel();
	echo '</div>';
}
add_action( 'in_admin_header', 'cbw_render_hero' );

/**
 * Drop core's welcome panel.
 *
 * Core registers wp_welcome_panel while the admin boots, which is after a
 * theme file has finished loading — removing it at include time is a no-op.
 * admin_head runs once everything is registered and before the dashboard
 * body is printed, so the removal lands there.
 */
function cbw_drop_core_welcome() {
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}
add_action( 'admin_head', 'cbw_drop_core_welcome' );

/**
 * Render the hero.
 */
function cbw_welcome_panel() {
	$s    = cbw_dashboard_stats();
	$user = wp_get_current_user();
	$hour = (int) current_time( 'G' );

	if ( $hour < 12 ) {
		$greeting = __( 'Good morning', 'cbw' );
	} elseif ( $hour < 17 ) {
		$greeting = __( 'Good afternoon', 'cbw' );
	} else {
		$greeting = __( 'Good evening', 'cbw' );
	}

	// What shipped in the last seven days.
	$week = get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'date_query'     => array( array( 'after' => '7 days ago' ) ),
	) );
	$week_count = count( get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'date_query'     => array( array( 'after' => '7 days ago' ) ),
	) ) );

	$pct  = $s['page_total'] ? round( $s['translated'] / $s['page_total'] * 100 ) : 0;
	$logo = (int) get_option( 'cbw_logo_dark' );
	$logo_url = $logo ? wp_get_attachment_image_url( $logo, 'medium' ) : '';
	?>
	<div class="cbw-hero">
		<div class="cbw-hero__glow" aria-hidden="true"></div>

		<div class="cbw-hero__main">
			<?php if ( $logo_url ) : ?>
				<img class="cbw-hero__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="150" height="102">
			<?php endif; ?>

			<p class="cbw-hero__eyebrow"><?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?></p>
			<h2 class="cbw-hero__title">
				<?php
				/* translators: %s: the editor's display name. */
				printf( esc_html__( '%1$s, %2$s.', 'cbw' ), esc_html( $greeting ), esc_html( $user->display_name ) );
				?>
			</h2>
			<p class="cbw-hero__sub">
				<?php
				printf(
					/* translators: %s: number of articles published this week. */
					esc_html( _n( '%s story went out in the past week.', '%s stories went out in the past week.', $week_count, 'cbw' ) ),
					'<strong>' . esc_html( number_format_i18n( $week_count ) ) . '</strong>'
				);
				?>
			</p>

			<p class="cbw-hero__actions">
				<a class="cbw-hero__btn cbw-hero__btn--gold" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">
					<span class="dashicons dashicons-edit" aria-hidden="true"></span>
					<?php esc_html_e( 'Write a story', 'cbw' ); ?>
				</a>
				<a class="cbw-hero__btn" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=cbw_issue' ) ); ?>">
					<span class="dashicons dashicons-book-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'New issue', 'cbw' ); ?>
				</a>
				<a class="cbw-hero__btn" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
					<span class="dashicons dashicons-format-gallery" aria-hidden="true"></span>
					<?php esc_html_e( 'Media', 'cbw' ); ?>
				</a>
				<a class="cbw-hero__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">
					<span class="dashicons dashicons-external" aria-hidden="true"></span>
					<?php esc_html_e( 'View the site', 'cbw' ); ?>
				</a>
			</p>
		</div>

		<div class="cbw-hero__side">
			<div class="cbw-hero__meter">
				<div class="cbw-hero__meterhead">
					<span><?php esc_html_e( 'Translated pages', 'cbw' ); ?></span>
					<strong><?php echo (int) $pct; ?>%</strong>
				</div>
				<div class="cbw-hero__track"><span style="--pct:<?php echo (int) $pct; ?>%"></span></div>
				<p class="cbw-hero__meterfoot">
					<?php
					printf(
						/* translators: 1: translated pages, 2: total pages. */
						esc_html__( '%1$d of %2$d pages in Hindi and Marathi', 'cbw' ),
						(int) $s['translated'],
						(int) $s['page_total']
					);
					?>
				</p>
			</div>

			<?php if ( $week ) : ?>
				<div class="cbw-hero__recent">
					<h3><?php esc_html_e( 'Latest', 'cbw' ); ?></h3>
					<ul>
						<?php foreach ( $week as $p ) : ?>
							<li>
								<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( wp_trim_words( get_the_title( $p ), 8 ) ); ?></a>
								<time><?php echo esc_html( get_the_date( 'j M', $p ) ); ?></time>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Give the dashboard two columns and spread the widgets across them.
 *
 * With everything stacked in the first container, a wide screen showed one
 * full column beside three empty ones. This runs once, under its own flag, so
 * a later arrangement by the reader is never undone.
 */
function cbw_dashboard_layout() {
	$user = get_current_user_id();
	if ( ! $user || get_user_meta( $user, 'cbw_dash_layout', true ) ) {
		return;
	}
	update_user_meta( $user, 'cbw_dash_layout', 1 );

	// Two columns reads well from a laptop up to a very wide monitor.
	if ( ! get_user_option( 'screen_layout_dashboard', $user ) ) {
		update_user_option( $user, 'screen_layout_dashboard', 2, true );
	}

	$order = get_user_meta( $user, 'meta-box-order_dashboard', true );
	if ( ! is_array( $order ) ) {
		return;
	}

	$normal = isset( $order['normal'] ) ? array_values( array_filter( explode( ',', $order['normal'] ) ) ) : array();
	$side   = isset( $order['side'] ) ? array_values( array_filter( explode( ',', $order['side'] ) ) ) : array();

	// Only rebalance when everything is in one column.
	if ( count( $normal ) < 4 || $side ) {
		return;
	}

	// The magazine panel and what was published stay on the left; the rest,
	// which is WordPress's own reporting, moves right.
	$keep_left = array( 'cbw_overview', 'dashboard_right_now', 'dashboard_activity' );
	$left  = array();
	$right = array();
	foreach ( $normal as $id ) {
		if ( in_array( $id, $keep_left, true ) ) {
			$left[] = $id;
		} else {
			$right[] = $id;
		}
	}

	$order['normal'] = implode( ',', $left );
	$order['side']   = implode( ',', $right );
	update_user_meta( $user, 'meta-box-order_dashboard', $order );
}
add_action( 'load-index.php', 'cbw_dashboard_layout' );
