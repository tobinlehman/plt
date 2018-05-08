<?php
/**
 * Some details about the theme. 
 * Also notice key Fields. It stores the above field and flags it as enabled for this theme. All other themes will display this field disabled.
 */
function fa_accordion_theme_details( $defaults ){
	$defaults = array(
		'author'		=> 'CodeFlavors',
		'AuthorURI'		=> 'http://www.codeflavors.com',
		'copyright'		=> 'author',
		'compatibility'	=> '3.0',
		'version'		=> '2.0',
		'name'			=> 'Accordion',	
		'fields'		=> array(
			'layout-show-title' => false,
			'layout-show-main-nav' => false,
			'layout-show-side-nav' => false,
			'js-auto-slide' => false,
			'js-click-stop' => false,
			'js-slide-duration' => false,
			'js-effect' => false,
			'js-cycle' => false,
			'js-position-in' => false,
			'js-distance-in' => false,
			'js-position-out' => false,
			'js-distance-out' => false
		),
		'colors'		=> array(
			'dark'
		),
		'stylesheets' => array(
			'font-awesome' 		=> true,
			'jquery-ui-dialog' 	=> true
		),
		'scripts' => array(
			'jquery-ui-dialog' => true
		),	
		'message'		=> 'Video enabled and responsive.',
		'description'	=> ''
	);	
	return $defaults;
	
}
add_filter('fa-theme-details-accordion', 'fa_accordion_theme_details', 1);

/**
 * For color configuration. Gets called by the plugin to display the theme color scheme configuration page.
 * Function must be named fa_theme_css_THEME_NAME
 */
function fa_theme_css_accordion(){
	
	$rules = array(
		// parent element must be stored under key container. All other elements can have any key.
		'container' => array(
			'css_selector' => '.fa-accordion', // all child elements from container will descend from this
			'description' => __( 'Slideshow container', 'fapro' ),
			'properties' => array( // not all properties are supported
				'border-width' 	=> '0px',
				'border-style' 	=> 'none',
				'border-color' 	=> 'transparent', 
				'border-radius' => '0px',
				'box-shadow' 	=> '0px 0px 0px 0px transparent'
			)
		),
		'slide_container' => array(
			'css_selector' => '.fa-accordion-inside .slide',
			'description' => __( 'Single slide', 'fapro' ),
			'properties' => array(
				'box-shadow' 		=> '-2px -2px 10px #000',
				'background-color' 	=> '#000000'
			)
		),
		'play_video_overlay_link' => array(
			'css_selector' => '.slide .slide-content .play-video-overlay',
			'description' => __( 'Play video link over image', 'fapro' ),
			'properties' => array(
				'color' 			=> '#FFFFFF',
				'background-color' 	=> '#000',
				'border-radius' 	=> '5px',
				'text-shadow' 		=> '0px 0px 0px transparent',
			)
		),	
		'text_container' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info',
			'description' => __( 'Slide text container', 'fapro' ),
			'properties' => array(
				'background-image' => 'images/fill.png'
			)
		),
		'slide_title' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info h2.title',
			'description' => __( 'Slide title', 'fapro' ),
			'properties' => array(
				'font-size' 	=> '2em',
				'color' 		=> '#FFFFFF',
				'font-weight' 	=> 'normal'
			)
		),
		'slide_title_link' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info h2.title a',
			'description' => __( 'Slide title with anchor', 'fapro' ),
			'properties' => array(
				'color' 			=> '#FFFFFF',
				'text-decoration' 	=> 'none'
			)
		),
		'slide_text' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info div.hide',
			'description' => __( 'Slide text', 'fapro' ),
			'properties' => array(
				'font-size' => '1em',
				'color' 	=> '#FFFFFF'
			)
		),
		'slide_date' => array(
			'css_selector' 	=> '.fa-accordion-inside .slide div.info .fa-date',
			'description' 	=> __( 'Slide date', 'fapro' ),
			'properties' 	=> array(
				'color' 	=> '#FFFFFF',
				'font-size' => '1em'
			)
		),
		'slide_author' => array(
			'css_selector' 	=> '.fa-accordion-inside .slide div.info .fa-author',
			'description' 	=> __( 'Slide author', 'fapro' ),
			'properties' 	=> array(
				'color' 	=> '#FFFFFF',
				'font-size' => '1em'
			)
		),
		'slide_author_link' => array(
			'css_selector' 	=> '.fa-accordion-inside .slide div.info .fa-author a',
			'description' 	=> __( 'Slide author link', 'fapro' ),
			'properties' 	=> array(
				'color' 			=> '#FFFFFF',
				'font-size' 		=> '1em',
				'text-decoration' 	=> 'none'
			)
		),		
		'slide_read_more' => array(
			'css_selector' 	=> '.fa-accordion-inside .slide div.info .fa-read-more',
			'description'	=> __( 'Read more link', 'fapro' ),
			'properties'	=> array(
				'color' 			=> '#FFFFFF',
				'font-size' 		=> '1em',
				'text-decoration' 	=> 'underline'
			)
		),
		'slide_play_video_text' => array(
			'css_selector' 	=> '.fa-accordion-inside .slide div.info .play-video-text',
			'description'	=> __( 'Play video link below text', 'fapro' ),
			'properties' => array(
				'color' 			=> '#FFFFFF',
				'font-size' 		=> '1em',
				'text-decoration' 	=> 'underline'
			)
		),		
		'slide_title_idle' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info h2.title.idle',
			'description' => __( 'Slide title idle', 'fapro' ),
			'properties' => array(
				'font-size' 		=> '1em',
				'font-weight' 		=> 'normal',
				'color' 			=> '#FFFFFF',
				'text-transform' 	=> 'uppercase'
			)
		),
		'slide_title_idle_link' => array(
			'css_selector' => '.fa-accordion-inside .slide div.info h2.title.idle a',
			'description' => __( 'Slide title idle link', 'fapro' ),
			'properties' => array(
				'color' 			=> '#FFFFFF',
				'font-style' 		=> 'none',
				'text-decoration' 	=> 'none',
			)
		)
	);
	
	return $rules;
}

