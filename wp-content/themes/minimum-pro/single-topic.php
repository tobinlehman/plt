<?php
/**
 *
 * WP Post Template: Your New Post Template Name
 * PLT Topic Post Type Template *
 * 
 */
 
 //Add Activity Menu Widget
function rp_topic_menu_widget(){
  genesis_widget_area( 'topic-menu-widget', array(
		'before' => '<nav class="nav-primary rp-topic-conditional-menu genesis-nav-menu" itemtype="http://schema.org/SiteNavigationElement" itemscope="itemscope" role="navigation"><div class="wrap">',
		'after'  => '</div></nav>',
	) );
}

remove_action ('genesis_after_header', 'genesis_do_nav');
add_action( 'genesis_after_header', 'rp_topic_menu_widget' );


// Add Widget Box at top of Activity Post - For Tooltip Control
function rp_activity_widget(){
  genesis_widget_area( 'activity-page-top', array(
		'before' => '<div class="activity-page-top widget-area"><div class="wrap">',
		'after'  => '</div></div> Hello',
	) );
}

add_action( 'genesis_before_content_sidebar_wrap', 'rp_activity_widget' );


genesis();