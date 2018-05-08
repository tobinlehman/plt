<?php
/**
 *
 * WP Post Template: Your New Post Template Name
 * PLT Topic Post Type Template *
 * 
 */
 
// $auth_url = "http://shop.plt.org/api/query/licenses";
// $cookie = new WP_Http_Cookie( 'JSESSIONID' );
// $cookie->name = 'JSESSIONID';
// $cookie->value = '3A7F3DE452BF18DC99BB91429EAD0758';
// // $cookie->value = '';
// $cookies[] = $cookie;
// $auth_args = array(
// 	'cookies' => $cookies
// );
// $auth_response = wp_remote_get($auth_url, $auth_args);
// $auth_body = wp_remote_retrieve_body($auth_response);
// $auth_headers = wp_remote_retrieve_headers($auth_response);
// $auth_response_code = wp_remote_retrieve_response_code($auth_response);
// $user_license_check = json_decode($auth_body)->{'user-licenses'};
// $user_licenses = null;

// if(empty($user_license_check)){
// 	header("Location: http://shop.plt.org/~userInfo"); /* Redirect browser */
// 	exit();
// }
 

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
		'after'  => '</div></div>',
	) );
}

add_action( 'genesis_before_content_sidebar_wrap', 'rp_activity_widget' );

get_template_part('eunit', 'header'); ?>
    
<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">

	<span property="itemListElement" typeof="ListItem">
 		<a property="item" typeof="WebPage" title="Go to Eunit." href="http://eunit.devsite.work/listing" class="home">
			<span property="name">
				Eunits
			</span>
		</a>

	</span>
	&gt; 
	<span property="itemListElement" typeof="ListItem">
	 	<a property="item" typeof="WebPage" title="Go to Topic." href="<?php the_permalink(); ?>" class="post post-unit-topic-archive">
			<span property="name">
				<?php the_title(); ?>
			</span>
		</a>
	</span>

</div>

<?php


genesis_markup( array(
	'open'   => '<div %s>',
	'context' => 'content-sidebar-wrap',
) );

do_action( 'genesis_before_content' );
genesis_markup( array(
	'open'   => '<main %s>',
	'context' => 'content',
) );

	do_action( 'genesis_before_loop' );

	do_action( 'genesis_before_content_sidebar_wrap' );

	echo "<a style='float:right;margin-top: -17px;margin-right: -20px;width: 40px;height: auto;text-decoration:none !important;border-bottom:0px solid !important;' onclick='window.print();cursor:pointer;' class='printer'><img style='cursor:pointer;' src='" .  get_bloginfo('stylesheet_directory') . "/images/printer@2x.png'></a>";
	echo "<h1>" . get_the_title() . "</h1>"; 
	the_content();
	do_action( 'genesis_after_loop' );

	genesis_markup( array(
		'close' => '</main>', // End .content.
		'context' => 'content',
	) );


	$site_layout = genesis_site_layout();

	

// Don't load sidebar on pages that don't need it.
if ( 'full-width-content' === $site_layout ){

	return;

}else{

	genesis_markup( array(
		'open'    => '<aside %s>' . genesis_sidebar_title( 'sidebar' ),
		'context' => 'sidebar-primary',
	) );

	the_post_thumbnail();

	do_action( 'genesis_before_sidebar_widget_area' );
	do_action( 'genesis_sidebar' );
	do_action( 'genesis_after_sidebar_widget_area' );

	// End .sidebar-primary.
	genesis_markup( array(
		'close'   => '</aside>',
		'context' => 'sidebar-primary',
	) );

}

genesis_markup( array(
	'close'   => '</div>',
	'context' => 'content-sidebar-wrap',
) );

do_action( 'genesis_after_content_sidebar_wrap' );

get_template_part('eunit', 'footer');