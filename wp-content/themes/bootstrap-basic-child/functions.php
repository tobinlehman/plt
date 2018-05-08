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
function theme_enqueue_styles() {

    $parent_style = 'bootstrap-basic';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bootstrap-basic-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

if (!function_exists('bootstrapBasicWidgetsInit')) {
	/**
	 * Register widget areas
	 */
	function bootstrapBasicWidgetsInit2() 
	{
		register_sidebar(array(
			'name'          => __('Top Bar', 'bootstrap-basic-child'),
			'id'            => 'top-bar',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('Logo', 'bootstrap-basic-child'),
			'id'            => 'logo',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Homepage - Description', 'bootstrap-basic-child'),
			'id'            => 'homepage-description',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Homepage - Resources', 'bootstrap-basic-child'),
			'id'            => 'homepage-resources',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Homepage - News', 'bootstrap-basic-child'),
			'id'            => 'homepage-news',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Homepage - News Footer', 'bootstrap-basic-child'),
			'id'            => 'homepage-news-footer',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Trainings - Description', 'bootstrap-basic-child'),
			'id'            => 'trainings-description',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Trainings - Calls to Action', 'bootstrap-basic-child'),
			'id'            => 'trainings-cta',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Trainings - Testimonials', 'bootstrap-basic-child'),
			'id'            => 'trainings-testimonials',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Trainings - FAQ', 'bootstrap-basic-child'),
			'id'            => 'trainings-faq',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('Attend a Training - Testimonials', 'bootstrap-basic-child'),
			'id'            => 'attendatraining-testimonials',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum - Description', 'bootstrap-basic-child'),
			'id'            => 'curriculum-description',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum - Offerings', 'bootstrap-basic-child'),
			'id'            => 'curriculum-offerings',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum - Call to Action', 'bootstrap-basic-child'),
			'id'            => 'curriculum-cta',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum - Testimonials', 'bootstrap-basic-child'),
			'id'            => 'curriculum-testimonials',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum - Lesson Plans', 'bootstrap-basic-child'),
			'id'            => 'curriculum-lesson-plans',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Resources - Description', 'bootstrap-basic-child'),
			'id'            => 'resources-description',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Resources - Tools', 'bootstrap-basic-child'),
			'id'            => 'resources-tools',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Resources - Grants', 'bootstrap-basic-child'),
			'id'            => 'resources-grants',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum Offerings - Education', 'bootstrap-basic-child'),
			'id'            => 'curriculum-offerings-education',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Curriculum Offerings - Unit', 'bootstrap-basic-child'),
			'id'            => 'curriculum-offerings-unit',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('Curriculum Offerings - Title', 'bootstrap-basic-child'),
			'id'            => 'curriculum-offerings-title',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('GreenSchools - Description', 'bootstrap-basic-child'),
			'id'            => 'greenschools-description',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	
		
		register_sidebar(array(
			'name'          => __('GreenSchools - Benefits', 'bootstrap-basic-child'),
			'id'            => 'greenschools-benefits',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));				
		
		register_sidebar(array(
			'name'          => __('GreenSchools - Investigations', 'bootstrap-basic-child'),
			'id'            => 'greenschools-investigations',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));			

		register_sidebar(array(
			'name'          => __('GreenSchools - Grants', 'bootstrap-basic-child'),
			'id'            => 'greenschools-grants',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	

		register_sidebar(array(
			'name'          => __('GreenSchools - Call to Action', 'bootstrap-basic-child'),
			'id'            => 'greenschools-cta',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	
		
		register_sidebar(array(
			'name'          => __('GreenSchools - Testimonials', 'bootstrap-basic-child'),
			'id'            => 'greenschools-testimonials',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	

		register_sidebar(array(
			'name'          => __('News & Stories - Bottom of Page', 'bootstrap-basic-child'),
			'id'            => 'newstories-bottomfeed',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	

/**		register_sidebar(array(
			'name'          => __('Key Links - Activity', 'bootstrap-basic-child'),
			'id'            => 'key-links-activity',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - News', 'bootstrap-basic-child'),
			'id'            => 'key-links-news',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Story', 'bootstrap-basic-child'),
			'id'            => 'key-links-story',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Educator Tip', 'bootstrap-basic-child'),
			'id'            => 'key-links-educatortip',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - State', 'bootstrap-basic-child'),
			'id'            => 'key-links-state',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - STEM Connection', 'bootstrap-basic-child'),
			'id'            => 'key-links-stemconnection',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Literature Connection', 'bootstrap-basic-child'),
			'id'            => 'key-links-literatureconnection',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Family Activity', 'bootstrap-basic-child'),
			'id'            => 'key-links-familyactivity',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Curriculum', 'bootstrap-basic-child'),
			'id'            => 'key-links-curriculum',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - Sample Lesson Plan', 'bootstrap-basic-child'),
			'id'            => 'key-links-samplelessonplan',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Key Links - EE Resource', 'bootstrap-basic-child'),
			'id'            => 'key-links-eeresource',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));  **/

		register_sidebar(array(
			'name'          => __('Key Links - Page', 'bootstrap-basic-child'),
			'id'            => 'key-links-page',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('State Page - Outstanding Educator', 'bootstrap-basic-child'),
			'id'            => 'state-outstanding-educator',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));	
								
		register_sidebar(array(
			'name'          => __('AddThis', 'bootstrap-basic-child'),
			'id'            => 'addthis',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('Email Signup', 'bootstrap-basic-child'),
			'id'            => 'email-signup',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));

		register_sidebar(array(
			'name'          => __('General Page Footer - Calls to Action', 'bootstrap-basic-child'),
			'id'            => 'general-footer-cta',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('The Branch - Evergreen Content', 'bootstrap-basic-child'),
			'id'            => 'branch-evergreen',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('Footer Menu', 'bootstrap-basic-child'),
			'id'            => 'footer-menu',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));
		
		register_sidebar(array(
			'name'          => __('Footer Social Icons', 'bootstrap-basic-child'),
			'id'            => 'footer-social-icons',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '',
			'after_title'   => '',
		));



	}// bootstrapBasicWidgetsInit
}
add_action('widgets_init', 'bootstrapBasicWidgetsInit2');



/**
 * Customize the login screen
 */
function my_login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/style-login.css' );
}
add_action( 'login_enqueue_scripts', 'my_login_stylesheet' );



/** FANCIEST AUTHOR BOX STYLES **/

add_action( 'wp_enqueue_scripts', 'my_theme_ts_fab_add_scripts_styles' );
function my_theme_ts_fab_add_scripts_styles() {
	wp_enqueue_style( 'ts_fab_css' );
	wp_enqueue_script( 'ts_fab_js' );
}


 
function remove_head_scripts() { 
   remove_action('wp_head', 'wp_print_scripts'); 
   remove_action('wp_head', 'wp_print_head_scripts', 9); 
   remove_action('wp_head', 'wp_enqueue_scripts', 1);
 
   add_action('wp_footer', 'wp_print_scripts', 5);
   add_action('wp_footer', 'wp_enqueue_scripts', 5);
   add_action('wp_footer', 'wp_print_head_scripts', 5); 
} 
add_action( 'wp_enqueue_scripts', 'remove_head_scripts' );


/* LIMIT EXCERPT LENGTH */
function custom_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );



/* ADD CUSTOM POST TYPES TO ARCHIVE */
function plt_add_custom_types( $query ) {
  if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
    $query->set( 'post_type', array(
     'post', 'nav_menu_item', 'educator_tip', 'story', 'stem_connection', 'literature_connectio', 'outstanding_educator', 'family_activity', 'curriculum_ct', 'sample_lesson_plan'  
		));
	  return $query;
	}
}
add_filter( 'pre_get_posts', 'plt_add_custom_types' );



/* CUSTOM LOGOUT */
add_action( 'wp_logout', 'auto_redirect_external_after_logout');
function auto_redirect_external_after_logout(){
  wp_redirect(home_url());
  exit();
}

add_filter('acf/settings/remove_wp_meta_box', '__return_false');

?>