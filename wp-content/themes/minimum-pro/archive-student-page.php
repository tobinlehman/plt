<?php
/**
 * Topic Student Page
 *
 */
 
  /** Force full width content layout */
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );



/**
 * Display as Columns
 *
 */
function be_portfolio_post_class( $classes ) {
	
	global $wp_query;
	if( !$wp_query->is_main_query() ) 
		return $classes;
		
	$columns = 4;
	
	$column_classes = array( '', '', 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
	$classes[] = $column_classes[$columns];
	if( 0 == $wp_query->current_post || 0 == $wp_query->current_post % $columns )
		$classes[] = 'first';
		
	return $classes;
}
add_filter( 'post_class', 'be_portfolio_post_class' );

// Remove items from loop
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );

//Add Activity Menu Widget
function rp_activity_menu_widget(){
  genesis_widget_area( 'activity-menu-widget', array(
		'before' => '<nav class="nav-primary rp-conditional-menu" itemtype="http://schema.org/SiteNavigationElement" itemscope="itemscope" role="navigation"><div class="wrap">',
		'after'  => '</div></nav>',
	) );
}

remove_action ('genesis_after_header', 'genesis_do_nav');
add_action( 'genesis_after_header', 'rp_activity_menu_widget' );


/**
 * Add Portfolio Image
 *
 */
function rp_studentpage_image() {
	echo wpautop( '<a class="student-page-image-title" href="' . get_permalink() . '">' . genesis_get_image( array( 'size' => 'studentpage' ) ). '</a>' );
}
add_action( 'genesis_entry_content', 'rp_studentpage_image' );
add_filter( 'genesis_pre_get_option_content_archive_thumbnail', '__return_false' );

// Move Title below Image
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
add_action( 'genesis_entry_footer', 'genesis_entry_header_markup_open', 5 );
add_action( 'genesis_entry_footer', 'genesis_entry_header_markup_close', 15 );
add_action( 'genesis_entry_footer', 'genesis_do_post_title' );


genesis();