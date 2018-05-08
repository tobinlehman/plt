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

get_template_part('eunit', 'header'); 

?>

<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">

	<span property="itemListElement" typeof="ListItem">
 		<a property="item" typeof="WebPage" title="Go to Eunit" href="http://eunit.devsite.work/listing" class="home">
			<span property="name">
				Eunits
			</span>
		</a>

	</span>
	&gt; 
	<span property="itemListElement" typeof="ListItem">
 		<a property="item" typeof="WebPage" title="Go to Activity" href="http://eunit.devsite.work/unit-topic/energy-in-ecosystems" class="home">
			<span property="name">
				<?php 
					echo $activity;
				?>
			</span>
		</a>

	</span>
	&gt; 
	<span property="itemListElement" typeof="ListItem">
	 	<a property="item" typeof="WebPage" title="Current Page" href="<?php the_permalink(); ?>" class="post post-unit-topic-archive">
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

	 // var_dump(do_filter('genesis_after_header'));
	// var_dump(genesis_widget_area('activity-menu-widget'));
	
	// echo eunit_widget_area('activity-menu-widget');

	// $w = get_option('activity-menu-widget');
	// var_dump($w);
	
	do_action('genesis_before_content_sidebar_wrap');
	
	echo "<a style='float:right;margin-top: -17px;margin-right: -20px;width: 40px;height: auto;text-decoration:none !important;border-bottom:0px solid !important;' onclick='window.print();cursor:pointer;' class='printer'><img style='cursor:pointer;' src='" .  get_bloginfo('stylesheet_directory') . "/images/printer@2x.png'></a>";

	echo "<h1>" . get_the_title() . "</h1>"; 
	do_action('genesis_after_header');
	do_action( 'genesis_before_loop' );
	// do_action( 'genesis_loop' );
	// do_action( 'genesis_before_content_sidebar_wrap' );
	if ( is_page_template( 'page_blog.php' ) ) {

		$include = genesis_get_option( 'blog_cat' );
		$exclude = genesis_get_option( 'blog_cat_exclude' ) ? explode( ',', str_replace( ' ', '', genesis_get_option( 'blog_cat_exclude' ) ) ) : '';
		$paged   = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		// Easter Egg.
		$query_args = wp_parse_args(
			genesis_get_custom_field( 'query_args' ),
			array(
				'cat'              => $include,
				'category__not_in' => $exclude,
				'showposts'        => genesis_get_option( 'blog_cat_num' ),
				'paged'            => $paged,
			)
		);

		genesis_custom_loop( $query_args );
	} else {
		// genesis_standard_loop();
		// Use old loop hook structure if not supporting HTML5.
		if ( ! genesis_html5() ) {
			genesis_legacy_loop();
			return;
		}

		if ( have_posts() ) :

			do_action( 'genesis_before_while' );
			while ( have_posts() ) : the_post();

				// add_filter('the_content', 'cm_tooltip_parse');
				do_action( 'genesis_before_entry' );

				printf( '<article %s>', genesis_attr( 'entry' ) );

					// do_action( 'genesis_entry_header' );

					do_action( 'genesis_before_entry_content' );

					printf( '<div %s>', genesis_attr( 'entry-content' ) );

					$auth_url = "http://shop.plt.org/api/query/licenses";
					$cookie = new WP_Http_Cookie( 'JSESSIONID' );
					$cookie->name = 'JSESSIONID';
					$cookie->value = '09C432793C979A3390FDE1BB5A1D9C2A';
					$cookies[] = $cookie;
					$auth_args = array(
						'cookies' => $cookies
					);
					$auth_response = wp_remote_get($auth_url, $auth_args);
					$auth_body = wp_remote_retrieve_body($auth_response);
					$auth_headers = wp_remote_retrieve_headers($auth_response);
					$auth_response_code = wp_remote_retrieve_response_code($auth_response);
					$user_license_check = json_decode($auth_body)->{'user-licenses'};
					$user_license_bindings = json_decode($auth_body)->{'license-bindings'};
					// var_dump(json_decode($auth_body));
					$user_licenses = null;
					$user_licenses_b = array();
					if(empty($user_license_check)){
						$status = "sign-in";
					} else {
					    $json = json_decode($auth_body);
					    // var_dump($json);
						$user_licenses_data = $json->{'user-licenses'}[0]->{'user-license-info'}->userlicenses;
						foreach($user_licenses_data as $ul){
							// var_dump($ul);
							$user_licenses[] = array(
								"id" => $ul->controlled,
								"active" => $ul->active
							);
						}
						// foreach($user_license_bindings as $ulb){
						// 	$user_licenses_b[] = array(
						// 		'id' => $ulb->{'object'},
						// 		"active" => $ulb->{'license'}->{'active'}
						// 	);
						// }
					}
					var_dump($user_licenses);
					// var_dump($user_licenses_b);

					echo '</div>';

					do_action( 'genesis_after_entry_content' );

					// do_action( 'genesis_entry_footer' );

				echo '</article>';

				do_action( 'genesis_after_entry' );

			endwhile; // End of one post.
			do_action( 'genesis_after_endwhile' );

		else : // If no posts exist.
			do_action( 'genesis_loop_else' );
		endif; // End loop.
	}
	do_action( 'genesis_after_loop' );
	
	genesis_markup( array(
		'close' => '</main>', // End .content.
		'context' => 'content',
	) );

// do_action( 'genesis_after_content' );
$site_layout = genesis_site_layout();


// Don't load sidebar on pages that don't need it.
if ( 'full-width-content' === $site_layout ){

	return;

}else{

	genesis_markup( array(
		'open'    => '<aside %s>' . genesis_sidebar_title( 'sidebar' ),
		'context' => 'sidebar-primary',
	) );

	echo get_the_post_thumbnail($thumb_id);

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