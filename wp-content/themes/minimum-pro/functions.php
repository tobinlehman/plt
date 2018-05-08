<?php

add_filter('wp_handle_upload_prefilter', 'wpse47415_pre_upload');
add_filter('wp_handle_upload', 'wpse47415_post_upload');

function wpse47415_pre_upload($file){
    add_filter('upload_dir', 'wpse47415_custom_upload_dir');
    return $file;
}

function wpse47415_post_upload($fileinfo){
    remove_filter('upload_dir', 'wpse47415_custom_upload_dir');
    return $fileinfo;
}

function wpse47415_custom_upload_dir($path){    
    $extension = substr(strrchr($_POST['name'],'.'),1);
    if(!empty($path['error']) ||  $extension != 'pdf') { return $path; } //error or other filetype; do nothing. 
    $customdir = '/pdf';
    $path['path']    = str_replace($path['subdir'], '', $path['path']); 
    $path['url']     = str_replace($path['basedir'], '', $path['url']);         
    $path['subdir']  = $customdir;
    $path['path']   .= $customdir; 
    $path['url']    .= $customdir;  

    return $path;
}

//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'minimum', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'minimum' ) );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', __( 'Minimum Pro Theme', 'minimum' ) );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/minimum/' );
define( 'CHILD_THEME_VERSION', '3.0.1' );

add_shortcode('wp_caption', 'img_caption_shortcode');
add_shortcode('caption', 'img_caption_shortcode');

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Enqueue scripts
add_action( 'wp_enqueue_scripts', 'minimum_enqueue_scripts' );
// $glossary_path = WP_PLUGIN_URL . "/" . "tooltipglossary/";
// wp_enqueue_script("tooltip-js", $glossary_path . "tooltip.js");
// wp_enqueue_style("tooltip-css", $glossary_path . "tooltip.css");

global $post;
// var_dump(get_post_type());
if(get_post_type($post->ID) == "activity" ){
	add_filter("the_content", "cm_tooltip_parse", 9999);
	// add_filter( "the_content", 'wpautop' );
}


function minimum_enqueue_scripts() {
	
	wp_enqueue_style('eunit', get_bloginfo( 'stylesheet_directory' ) . '/eunit.css' , null, 1.1);

	wp_enqueue_script( 'minimum-responsive-menu', get_bloginfo( 'stylesheet_directory' ) . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_script( 'eunit-main', get_bloginfo( 'stylesheet_directory' ) . '/js/main.js', array( 'jquery' ), '1.1.0' );

	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'minimum-google-fonts', '//fonts.googleapis.com/css?family=Roboto:300,400|Roboto+Slab:300,400', array(), CHILD_THEME_VERSION );
	global $post;

}
function eunit_widget_area( $id, $args = array() ) {

	if ( ! $id )
		return false;

	$defaults = apply_filters( 'genesis_widget_area_defaults', array(
		'before'              => genesis_html5() ? '<aside class="widget-area">' . genesis_sidebar_title( $id ) : '<div class="widget-area">',
		'after'               => genesis_html5() ? '</aside>' : '</div>',
		'default'             => '',
		'show_inactive'       => 0,
		'before_sidebar_hook' => 'genesis_before_' . $id . '_widget_area',
		'after_sidebar_hook'  => 'genesis_after_' . $id . '_widget_area',
	), $id, $args );

	$args = wp_parse_args( $args, $defaults );

	if ( ! is_active_sidebar( $id ) && ! $args['show_inactive'] )
		return false;

	// Opening markup.
	// echo $args['before'];

	// Before hook.
	if ( $args['before_sidebar_hook'] )
			// do_action( $args['before_sidebar_hook'] );

	if ( ! dynamic_sidebar( $id ) )
		// var_dump($args['default']);
		$html = html_entity_decode($args['default']);
		var_dump($html);

	// After hook.
	if( $args['after_sidebar_hook'] )
			// do_action( $args['after_sidebar_hook'] );

	// Closing markup.
	// echo $args['after'];

	return true;

}
// get the page name
function my_get_menu_item_name( $loc ) {
    global $post;

    $locs = get_nav_menu_locations();

    $menu = wp_get_nav_menu_object( $locs[$loc] );

    if($menu) {

        $items = wp_get_nav_menu_items($menu->term_id);

        foreach ($items as $k => $v) {
            // Check if this menu item links to the current page
            if ($items[$k]->object_id == $post->ID) {
                $name = $items[$k]->title;
                break;
            }
        }

    }
    return $name;
}

// Fixed Sidebar JS

function fixed_sidebar_jquery(){

    wp_register_script( 'custom-script', get_bloginfo( 'stylesheet_directory' ) . '/js/fixed-bar.js', array( 'jquery' ) );
 }
 
add_action( 'wp_enqueue_scripts', 'fixed_sidebar_jquery' );


//* Add new image sizes
add_image_size( 'portfolio', 540, 340, TRUE );
add_image_size( 'topic', 300, 200, true );
add_image_size( 'studentpage', 200, 133, true );

//* Add support for custom background
add_theme_support( 'custom-background', array( 'wp-head-callback' => 'minimum_background_callback' ) ); 

//* Add custom background callback for background color
function minimum_background_callback() {

	if ( ! get_background_color() )
		return;

	printf( '<style>body { background-color: #%s; }</style>' . "\n", get_background_color() );

}
function add_query_vars($vars){
    $vars[] = "grade_level";
    return $vars;
}
add_filter('query_vars', 'add_query_vars');
function add_rewrite_rules($aRules){
    $aNewRules = array('listing/([^/]+)/?$' => 'index.php?page_id=6801&grade_level=$matches[1]');
    $aRules = $aNewRules + $aRules;
    return $aRules;
}
add_filter('rewrite_rules_array', 'add_rewrite_rules');

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width'           => 180,
	'height'          => 125,
	'header-selector' => '.site-title a',
	'header-text'     => false
) );

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'site-tagline',
	'nav',
	'subnav',
	'home-featured',
	'site-inner',
	'footer-widgets',
	'footer'
) );

//* Add support for 3-column footer widgets
//add_theme_support( 'genesis-footer-widgets', 3 );

//* Unregister layout settings
genesis_unregister_layout( 'content-sidebar-sidebar' );
genesis_unregister_layout( 'sidebar-content-sidebar' );
genesis_unregister_layout( 'sidebar-sidebar-content' );

//* Unregister secondary sidebar 
unregister_sidebar( 'sidebar-alt' );

//* Create portfolio custom post type
//add_action( 'init', 'minimum_portfolio_post_type' );
//function minimum_portfolio_post_type() {
//
//	register_post_type( 'portfolio',
//		array(
//			'labels' => array(
//				'name'          => __( 'Portfolio', 'minimum' ),
//				'singular_name' => __( 'Portfolio', 'minimum' ),
//			),
//			'exclude_from_search' => true,
//			'has_archive'         => true,
//			'hierarchical'        => true,
//			'menu_icon'           => 'dashicons-admin-page',
//			'public'              => true,
//			'rewrite'             => array( 'slug' => 'portfolio', 'with_front' => false ),
//			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'revisions', 'page-attributes', 'genesis-seo' ),
//		)
//	);
//	
//}

//* Remove site description
remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

//* Reposition the primary navigation menu
//remove_action( 'genesis_after_header', 'genesis_do_nav' );
//add_action( 'genesis_after_header', 'genesis_do_nav', 15 );

//* Reposition the secondary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
//add_action( 'genesis_before_header', 'genesis_do_subnav', 7 );

//* Reduce the secondary navigation menu to one level depth
//add_filter( 'wp_nav_menu_args', 'minimum_secondary_menu_args' );
//function minimum_secondary_menu_args( $args ){
//
//	if( 'secondary' != $args['theme_location'] )
//	return $args;
//
//	$args['depth'] = 1;
//	return $args;
//
//}

//* Add the site tagline section
add_action( 'genesis_after_header', 'minimum_site_tagline' );
function minimum_site_tagline() {

	printf( '<div %s>', genesis_attr( 'site-tagline' ) );
	genesis_structural_wrap( 'site-tagline' );

		printf( '<div %s>', genesis_attr( 'site-tagline-left' ) );
		printf( '<p %s>%s</p>', genesis_attr( 'site-description' ), esc_html( get_bloginfo( 'description' ) ) );
		echo '</div>';
	
		printf( '<div %s>', genesis_attr( 'site-tagline-right' ) );
		genesis_widget_area( 'site-tagline-right' );
		echo '</div>';

	genesis_structural_wrap( 'site-tagline', 'close' );
	echo '</div>';

}

//* Hook after post widget after the entry content
add_action( 'genesis_after_entry', 'minimum_after_entry', 5 );
function minimum_after_entry() {

	if ( is_singular( 'post' ) )
		genesis_widget_area( 'after-entry', array(
			'before' => '<div class="after-entry widget-area">',
			'after'  => '</div>',
		) );

}

//* Modify the size of the Gravatar in the author box
add_filter( 'genesis_author_box_gravatar_size', 'minimum_author_box_gravatar' );
function minimum_author_box_gravatar( $size ) {

	return 144;

}

//* Modify the size of the Gravatar in the entry comments
add_filter( 'genesis_comment_list_args', 'minimum_comments_gravatar' );
function minimum_comments_gravatar( $args ) {

	$args['avatar_size'] = 96;
	return $args;

}

//* Change the number of portfolio items to be displayed (props Bill Erickson)
add_action( 'pre_get_posts', 'minimum_portfolio_items' );
function minimum_portfolio_items( $query ) {

	if ( $query->is_main_query() && !is_admin() && is_post_type_archive( 'portfolio' ) ) {
		$query->set( 'posts_per_page', '6' );
	}

}

//* Remove comment form allowed tags
add_filter( 'comment_form_defaults', 'minimum_remove_comment_form_allowed_tags' );
function minimum_remove_comment_form_allowed_tags( $defaults ) {
	
	$defaults['comment_notes_after'] = '';
	return $defaults;

}

//* Register widget areas
//genesis_register_sidebar( array(
//	'id'          => 'site-tagline-right',
//	'name'        => __( 'Site Tagline Right', 'minimum' ),
//	'description' => __( 'This is the site tagline right section.', 'minimum' ),
//) );

//genesis_register_sidebar( array(
//	'id'          => 'home-featured-2',
//	'name'        => __( 'Home Featured 2', 'minimum' ),
//	'description' => __( 'This is the home featured 2 section.', 'minimum' ),
//) );
//genesis_register_sidebar( array(
//	'id'          => 'home-featured-3',
//	'name'        => __( 'Home Featured 3', 'minimum' ),
//	'description' => __( 'This is the home featured 3 section.', 'minimum' ),
//) );
//genesis_register_sidebar( array(
//	'id'          => 'home-featured-4',
//	'name'        => __( 'Home Featured 4', 'minimum' ),
//	'description' => __( 'This is the home featured 4 section.', 'minimum' ),
//) );
//genesis_register_sidebar( array(
//	'id'          => 'after-entry',
//	'name'        => __( 'After Entry', 'minimum' ),
//	'description' => __( 'This is the after entry widget area.', 'minimum' ),
//) );
genesis_register_sidebar( array(
	'id'          => 'activity-menu-widget',
	'name'        => __( 'Activity Page Menu', 'minimum' ),
	'description' => __( 'This is the menu above the Activity page.', 'minimum' ),
) );
genesis_register_sidebar( array(
	'id'          => 'topic-menu-widget',
	'name'        => __( 'Unit/Topic Menu', 'minimum' ),
	'description' => __( 'This is the menu above the Unit Overview page.', 'minimum' ),
) );
genesis_register_sidebar( array(
	'id'          => 'gradelevel-menu-widget',
	'name'        => __( 'Grade Level Menu', 'minimum' ),
	'description' => __( 'This is the menu above the Unit selection page.', 'minimum' ),
) );

genesis_register_sidebar( array(
	'id'          => 'activity-page-top',
	'name'        => __( 'Activity Page Top', 'minimum' ),
	'description' => __( 'This is the widget area at the top of each activity page.', 'minimum' ),
) );
//genesis_register_sidebar( array(
//	'id'          => 'home-featured-1',
//	'name'        => __( 'Home Featured 1', 'minimum' ),
//	'description' => __( 'This is the home featured 1 section.', 'minimum' ),
//) );
//



 // Topic Template for Taxonomies
 
function rp_topic_template( $template ) {
  if( is_tax( array( 'grade-level' ) ) )
    $template = get_query_template( 'archive-topic' );
  return $template;
}
add_filter( 'template_include', 'rp_topic_template' );

// Student Page Template for Taxonomies

function rp_studentpage_template( $template ) {
  if( is_tax( array( 'unit-topic' ) ) )
    $template = get_query_template( 'archive-student-page' );
  return $template;
}
add_filter( 'template_include', 'rp_studentpage_template' );



/**
 * Add 'page-attributes' to Topic Post Type
 *
 * @param array $args, arguments passed to register_post_type
 * @return array $args
 */
function rp_topic_post_type_args( $args ) {
	$args['supports'][] = 'page-attributes';
	return $args;
}
add_filter( 'topicposttype_args', 'rp_topic_post_type_args' );

/**
 * Sort projects by menu order 
 *
 */

//  Function below disabled 2015-10-13 by RP
//function rp_topic_query( $query ) {
//	if( $query->is_main_query() && !is_admin() && ( is_post_type_archive( 'topic' ) || is_tax( array( 'grade-level' ) ) ) ) {
//		$query->set( 'orderby', 'menu_order' );
//		$query->set( 'order', 'ASC' );
//	}
//}
//add_action( 'pre_get_posts', 'rp_topic_query(' );

// Set Autolog out to 14 Days

add_filter( 'auth_cookie_expiration', 'keep_me_logged_in_for_14_days' );
function keep_me_logged_in_for_14_days( $expirein ) {
    return 1209600; // 14 Days in seconds
}


// Create theme locations for conditional menus
if ( function_exists( 'register_nav_menus' ) ) {
	$args = array(
	  'admin' => __( 'Admin Menu' ),
	  'what-climate-menu' => __( 'What is Climate Menu'),
	  'carbon-cycle-menu' => __( 'Carbon Cycle Menu'),
	  'carbon-dioxide-menu' => __( 'Carbon Dioxide Menu'),
	  'climate-time-machine-menu' => __( 'Climate Time Machine Menu'),
	  'bigfoot-menu' => __( 'Are You Bigfoot Menu')
	  
	  	  
	);
	register_nav_menus( $args );
}



// Conditional Lesson Menu Below Header

function rp_add_header_menu (){

global $post;
$id = get_the_ID(); 
$cats = get_the_category($id);
$activity_category = $cats[1]->category_nicename;
//
//$terms = get_the_terms($post->ID, 'activity' );
//if ($terms && ! is_wp_error($terms)) :
//	$term_slugs_arr = array();
//	foreach ($terms as $term) {
//	    $term_slugs_arr[] = $term->slug;
//	}
//	$terms_slug_str = join( " ", $term_slugs_arr);
//endif;
//echo $terms_slug_str;

print_r($activity_category);

}

add_shortcode('test_menu', 'rp_add_header_menu');

//remove_action( 'genesis_after_header', 'genesis_do_nav' );
//add_action('genesis_after_header', 'rp_add_header_menu');





// Allow Shortcodes in Widgets

add_filter('widget_text', 'do_shortcode');

//* Add support for post formats
//add_theme_support( 'post-formats', array(
//	'audio',
//	'gallery',
//	'image',
//	'link',
//	'video'
//) );


//* Enable the superfish script
add_filter( 'genesis_superfish_enabled', '__return_true' );


function rp_scripts_method() {
	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/js/change-tt-toggle-text.js',
		array( 'jquery' )
	);
}

add_action( 'wp_enqueue_scripts', 'rp_scripts_method' );


//  Add Category Name to Body Class - so that we can apply custom menu color to specific grade levels

add_filter('body_class','add_category_to_single');
function add_category_to_single($classes, $class) {
  if (is_single() ) {
    global $post;
    foreach((get_the_category($post->ID)) as $category) {
      // add category slug to the $classes array
      $classes[] = $category->category_nicename;
    }
  }
  // return the $classes array
  return $classes;
}






