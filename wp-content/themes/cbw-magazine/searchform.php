<?php
/**
 * Search form.
 *
 * @package CBW_Magazine
 */
?>
<form role="search" method="get" class="cbw-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="cbw-s-<?php echo esc_attr( wp_unique_id() ); ?>"><?php esc_html_e( 'Search articles', 'cbw' ); ?></label>
	<svg class="cbw-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5" stroke-linecap="round"/></svg>
	<input type="search" class="cbw-search__field" placeholder="<?php esc_attr_e( 'Search stories &amp; founders…', 'cbw' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button class="cbw-search__submit" type="submit"><?php esc_html_e( 'Search', 'cbw' ); ?></button>
</form>
