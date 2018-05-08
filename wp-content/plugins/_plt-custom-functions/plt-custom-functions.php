<?php


/*
Plugin Name: PLT Custom Functions
Plugin URI: http://wishlistmaster.com
Description: Custom Functions for Pilot Site
Version: 1.0
Author: Bob Patterson
Author URI: http://wishlistmaster.com
*/

// Rearrange the admin menu
  function custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    return array(
      'index.php', // Dashboard
      'edit.php?post_type=unit-topic', // Custom type one
      //'edit.php?post_type=lesson', // Custom type two
      'edit.php?post_type=activity',// Custom type three
      'edit.php?post_type=student-page',
      'edit.php?post_type=teacher-resource',
      'edit.php?post_type=addl-resource',
	  'separator1', // First separator
      'upload.php', // Media
      'edit.php?post_type=page' // Pages
         );
  }

  add_filter('custom_menu_order', 'custom_menu_order'); // Activate custom_menu_order
  add_filter('menu_order', 'custom_menu_order');



/** Create Topic custom post type */

add_action('init', 'rbp_register_my_cpt_topic');
function rbp_register_my_cpt_topic() {
register_post_type('unit-topic', array(
'label' => 'Topic',	
'description' => 'Topic',
'public' => true,	
'show_ui' => true,	
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'unit-topic', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail','author','page-attributes','post-formats', 'genesis-cpt-archives-settings'),
'taxonomies' => array('category'),
'menu_icon' => 'dashicons-images-alt',
'labels' => array (	
  'name' => 'Topic',
  'singular_name' => 'Topic',
  'menu_name' => 'Topics',
  'add_new' => 'Add New Topic',
  'add_new_item' => 'Add New Topic',
  'edit' => 'Edit',	
  'edit_item' => 'Edit Topic',
  'new_item' => 'New Topic',
  'view' => 'View Topic',
  'view_item' => 'View Topic',
  'search_items' => 'Search Topics',
  'not_found' => 'No Topic item Found',
  'not_found_in_trash' => 'No Topic item Found in Trash',
  'parent' => 'Parent Topic',
)					
) ); }				


/** Create Additional Resources custom post type */

add_action('init', 'rbp_register_my_cpt_addl_resource');
function rbp_register_my_cpt_addl_resource() {
register_post_type('addl-resource', array(
'label' => 'Additional Resources',	
'description' => 'Additional Resources',
'public' => true,	
'show_ui' => true,	
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'addl-resource', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail','author','page-attributes','post-formats', 'genesis-cpt-archives-settings'),
'taxonomies' => array('category'),
'menu_icon' => 'dashicons-carrot',
'labels' => array (	
  'name' => 'Additional Resource',
  'singular_name' => 'Additional Resource',
  'menu_name' => 'Additional Resources',
  'add_new' => 'Add New Topic',
  'add_new_item' => 'Add New Resource',
  'edit' => 'Edit',	
  'edit_item' => 'Edit Resource',
  'new_item' => 'New Resource',
  'view' => 'View Resource',
  'view_item' => 'View Resource',
  'search_items' => 'Search Additional Resources',
  'not_found' => 'No Topic item Found',
  'not_found_in_trash' => 'No Resource item Found in Trash',
  'parent' => 'Parent Resource',
)					
) ); }		





/** Create Activity custom post type */

add_action('init', 'rbp_register_my_cpt_activity');
function rbp_register_my_cpt_activity() {
register_post_type('activity', array(
'label' => 'Activity',
'description' => 'Activity',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'activity', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail','author','page-attributes','post-formats', 'genesis-cpt-archives-settings'),
'taxonomies' => array('category','post_tag'),
'menu_icon' => 'dashicons-images-alt2',
'labels' => array (
  'name' => 'Activity',
  'singular_name' => 'Activity',
  'menu_name' => 'Activities',
  'add_new' => 'Add New Activity',
  'add_new_item' => 'Add New Activity',
  'edit' => 'Edit',
  'edit_item' => 'Edit Activity',
  'new_item' => 'New Activity',
  'view' => 'View Activity',
  'view_item' => 'View Activity',
  'search_items' => 'Search Activity',
  'not_found' => 'No Activity item Found',
  'not_found_in_trash' => 'No Activity item Found in Trash',
  'parent' => 'Parent Activity',
)
) ); }

/** Create Student Page custom post type */

add_action('init', 'rbp_register_my_cpt_student_page');
function rbp_register_my_cpt_student_page() {
register_post_type('student-page', array(
'label' => 'Student Page',
'description' => 'Student Page',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'student-page', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail','author','page-attributes','post-formats', 'genesis-cpt-archives-settings'),
'taxonomies' => array('category','post_tag'),
'menu_icon' => 'dashicons-universal-access',
'labels' => array (
  'name' => 'Student Page',
  'singular_name' => 'Student Page',
  'menu_name' => 'Student Pages',
  'add_new' => 'Add New Student Page',
  'add_new_item' => 'Add New Student Page',
  'edit' => 'Edit',
  'edit_item' => 'Edit Student Page',
  'new_item' => 'New Student Page',
  'view' => 'View Student Page',
  'view_item' => 'View Student Page',
  'search_items' => 'Search Student Page',
  'not_found' => 'No Student Page item Found',
  'not_found_in_trash' => 'No Student Page item Found in Trash',
  'parent' => 'Parent Student Page',
)
) ); }

/** Create Teacher Resource custom post type */

add_action('init', 'rbp_register_my_cpt_teacher_resource');
function rbp_register_my_cpt_teacher_resource() {
register_post_type('teacher-resource', array(
'label' => 'Teacher Resource',
'description' => 'Teacher Resource',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'teacher-resource', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail','author','page-attributes','post-formats', 'genesis-cpt-archives-settings'),
'taxonomies' => array('category','post_tag'),
'menu_icon' => 'dashicons-welcome-learn-more',
'labels' => array (
  'name' => 'Teacher Resource',
  'singular_name' => 'Teacher Resource',
  'menu_name' => 'Teacher Resources',
  'add_new' => 'Add New Teacher Resource',
  'add_new_item' => 'Add New Teacher Resource',
  'edit' => 'Edit',
  'edit_item' => 'Edit Teacher Resource',
  'new_item' => 'New Teacher Resource',
  'view' => 'View Teacher Resource',
  'view_item' => 'View Teacher Resource',
  'search_items' => 'Search Teacher Resource',
  'not_found' => 'No Teacher Resource item Found',
  'not_found_in_trash' => 'No Teacher Resource item Found in Trash',
  'parent' => 'Parent Teacher Resource',
)
) ); }




// Includes Custom Post Types in Category Page
add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if(is_category() || is_tag()) {
    $post_type = get_query_var('post_type');
	if($post_type)
	    $post_type = $post_type;
	else
	    $post_type = array('post','topic','activity', 'student-page', 'teacher-resource', 'addl-resource','nav_menu_item'); 
    $query->set('post_type',$post_type);
	return $query;
    }
}




//  Shortcodes to highlight text color in Standards Tooltips

function rp_ngss_practice( $atts, $content = null ) {
	return '<span class="ngss-practice">' . $content . '</span>';
}
add_shortcode( 'practice_color', 'rp_ngss_practice' );

function rp_ngss_core( $atts, $content = null ) {
	return '<span class="ngss-core">' . $content . '</span>';
}
add_shortcode( 'core_color', 'rp_ngss_core' );

function rp_ngss_cross( $atts, $content = null ) {
	return '<span class="ngss-cross">' . $content . '</span>';
}
add_shortcode( 'cross_color', 'rp_ngss_cross' );

function rp_ngss_pe( $atts, $content = null ) {
	return '<span class="ngss-pe">' . $content . '</span>';
}
add_shortcode( 'pe_color', 'rp_ngss_pe' );


function rp_ela_color( $atts, $content = null ) {
	return '<span class="ela-color">' . $content . '</span>';
}
add_shortcode( 'ela_color', 'rp_ela_color' );


function rp_math_color( $atts, $content = null ) {
	return '<span class="math-color">' . $content . '</span>';
}
add_shortcode( 'math_color', 'rp_math_color' );

function rp_c3_color( $atts, $content = null ) {
	return '<span class="c3-color">' . $content . '</span>';
}
add_shortcode( 'c3_color', 'rp_c3_color' );








?>