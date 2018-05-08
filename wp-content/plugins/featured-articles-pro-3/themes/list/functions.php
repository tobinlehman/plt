<?php
/**
 * Some details about the theme. 
 * Also notice key Fields. It stores the above field and flags it as enabled for this theme. All other themes will display this field disabled.
 */
function fa_list_theme_details( $defaults ){
	
	$description = "Playlist style theme width slides list presented on the right side. Video enabled and responsive.";
	
	$defaults = array(
		'author' 		=> 'CodeFlavors',
		'author_uri' 	=> 'http://www.codeflavors.com',
		'copyright' 	=> 'author',
		'compatibility' => '3.0',
		'version'		=> '1.0',
		'name'			=> 'List',
		'fields'		=> array(
			'layout-show-main-nav' 	=> false,
			'layout-show-side-nav' 	=> false,
			'js-effect' 			=> false
		),
		// the main slider can have different extra CSS classes that can slide the layout in a different way
		// all the variations are specified here as css_class => message
		'classes' => array(
			'left-nav' => __( 'Navigation on the left', 'fapro' )
		),
		'colors' => array(
			'dark'
		),
		'stylesheets' => array(
			'font-awesome' => true,
			'jquery-ui-dialog' => true
		),
		'scripts' => array(
			'jquery-ui-dialog' => true
		),
		'message' => 'Video enabled and responsive.',
		'description' => $description
	);

	return $defaults;	
}
add_filter('fa-theme-details-' . fa_get_theme_key( __FILE__ ), 'fa_list_theme_details', 1);

/**
 * Color scheme
 */
function fa_theme_css_list(){
	$rules = array(
		'container' => array(
			'css_selector' 	=> '.fa_slider_list',
			'description' 	=> __( 'Slider container' , 'fapro' ),
			'properties' 	=> array(
				'background-color' => '#FFFFFF',
				'border-width' => '0',
				'border-style' => 'none',
				'border-color' => 'transparent',
				'border-radius'=> 0
			)
		),
		'image_container' => array(
			'css_selector' 	=> '.fa-image-container',
			'description' 	=> __( 'Slide image container' , 'fapro' ),
			'properties' 	=> array(
				'border-width' => 1,
				'border-style' => 'solid',
				'border-color' => '#000000',
				'background-color' => 'transparent'
			)
		),
		'slide_title' => array(
			'css_selector' 	=> '.slide-content h2.title',
			'description' 	=> __( 'Slide title' , 'fapro' ),
			'properties' 	=> array(
				'color' 	=> '#000000',
				'font-size' => '1.75em'
			)
		),
		'slide_title_anchor' => array(
			'css_selector' 	=> '.slide-content h2.title a',
			'description' 	=> __( 'Slide title link' , 'fapro' ),
			'properties' 	=> array(
				'color' 	=> '#000000'
			)
		),
		'slide_date' => array(
			'css_selector' 	=> '.slide-content .slide-date',
			'description' 	=> __('Slide date', 'fapro'),
			'properties' 	=> array(
				'color' => '#000000',
				'font-size' => '1em'
			)
		),
		'slide_author' => array(
			'css_selector' 	=> '.slide-content .slide-author',
			'description' 	=> __('Slide author name', 'fapro'),
			'properties'	=> array(
				'color' => '#000000',
				'font-size' => '1em'
			)
		),
		'slide_author_link' => array(
			'css_selector' 	=> '.slide-content .slide-author a',
			'description' 	=> __('Slide author link', 'fapro'),
			'properties' => array(
				'color' => '#000000',
				'text-decoration' => 'none'
			)
		),
		'slide_text' => array(
			'css_selector' 	=> '.slide-content .text',
			'description' 	=> __( 'Slide text' , 'fapro' ),
			'properties' 	=> array(
				'color' => '#000000',
				'font-size' => '1em'
			)
		),
		'read_more_link' => array(
			'css_selector' 	=> '.slide-content .read-more',
			'description' 	=> __( 'Read more link' , 'fapro' ),
			'properties' 	=> array(
				'color' => '#000',
				'text-decoration' => 'underline',
				'font-size' => '1.2em'
			)
		),
		'play_video_link' => array(
			'css_selector' 	=> '.slide-content .play-video',
			'description' 	=> __( 'Play video link' , 'fapro' ),
			'properties' 	=> array(
				'color' => '#000',
				'text-decoration' => 'none',
				'font-size'	=> '1.2em'
			)
		),
		'navigation_container' => array(
			'css_selector' 	=> '.navigation',
			'description' 	=> __('Navigation container', 'fapro'),
			'properties' 	=> array(
				'background-color' => '#333333'
			)
		),
		'nav_element' => array(
			'css_selector' 			=> '.navigation .nav',
			'description' 	=> __( 'Navigation element' , 'fapro' ),
			'properties' 	=> array(
				'border-bottom-width' => '1px',
				'border-bottom-style' => 'solid',
				'border-bottom-color' => '#6D6D6D'
			)
		),
		'nav_element_active' => array(
			'css_selector' 			=> '.navigation .nav.active',
			'description' 	=> __( 'Active navigation element' , 'fapro' ),
			'properties' 	=> array(
				'background-color' => '#000000'
			)
		),
		'nav_element_link' => array(
			'css_selector' 			=> '.navigation .nav a',
			'description' 	=> __( 'Navigation element anchor' , 'fapro' ),
			'properties' 	=> array(
				'color' => '#FFFFFF',
				'font-size' => '1em'
			)
		),
		'nav_element_link_image' => array(
			'css_selector' 			=> '.navigation .nav img',
			'description' 	=> __( 'Navigation element image' , 'fapro' ),
			'properties' 	=> array(
				'border-width' => '1px',
				'border-style' => 'solid',
				'border-color' => '#000000'
			)
		)
	);
	return $rules;
}