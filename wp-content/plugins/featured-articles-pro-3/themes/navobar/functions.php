<?php
/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @version 2.4
 */

/**
 * For detailed instructions about what hooks and filters you can use when developing themes for
 * FeaturedArticles, please visit: http://www.codeflavors.com/documentation/wp-actions-and-filters/
 */

/**
 * Theme details filter for Navobar theme
 */
function fa_navobar_theme_details( $defaults ){
	$defaults = array(
		'author'		=>'CodeFlavors',
		'author_uri'	=>'http://www.codeflavors.com',
		'copyright'		=>'author',
		'compatibility'	=>'Featured Articles 2.4',
		'fields'=>array(
			'layout-show-title' 	=> false,
			'layout-show-main-nav' 	=> false,
			'layout-show-side-nav' 	=> false,
			'js-position-in' 	=> false,
			'js-distance-in' 	=> false,
			'js-position-out' 	=> false,
			'js-distance-out' 	=> false
		),
		'colors' => array(
			'dark'
		),
		'stylesheets' => array(
			'jquery-ui-dialog' 	=> true,
			'font-awesome' 		=> true,
		),
		'scripts' => array(
			'jquery-ui-dialog' => true
		),
		'message' => 'This theme has full image background. Check your image size to be same or close to slider size under Slide Content Options -> Image'
	);	
	return $defaults;
	
}
add_filter( 'fa-theme-details-navobar', 'fa_navobar_theme_details', 1 );

/**
 * For color configuration. Gets called by the plugin to display the theme color scheme configuration page.
 * Function must be named fa_theme_css_THEME_NAME
 */
function fa_theme_css_navobar(){
	
	$rules = array(
		// parent element must be stored under key container. All other elements can have any key.
		'container' => array(
			'css_selector' => '.fa-slidenav', // all child elements from container will descend from this
			'description' => 'Slideshow container',
			'properties' => array( // not all properties are supported
				'border-width' => '0px',
				'border-style' => 'none',
				'border-color' => 'transparent', 
				'background-color' => '#000000',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'top left'
			)
		),
		
		'text_container' => array(
			'css_selector' => '.slide  .content',
			'description' => 'Slide text container',
			'properties' => array(
				'background-color' => 'transparent',
				'background-image' => 'images/content_bg.png',
				'background-repeat' => 'repeat',
				'background-position' => 'left top'				
			)
		),
		
		'slide_text' => array(
			'css_selector' => '.slide  .content div.fa_content',
			'description' => 'Slide text',
			'properties' => array(
				'font-size' => '1em',
				'color' => '#FFFFFF'
			)
		),
		
		'slide_anchor' => array(
			'css_selector' => '.slide  .content div.fa_content a',
			'description' => 'Links in slide description',
			'properties' => array(
				'font-size' => '1em',
				'text-decoration' => 'none',
				'color' => '#FFFFFF'
			)
		),
		
		'slide_anchor_hover' => array(
			'css_selector' => '.slide  .content div.fa_content a:HOVER',
			'description' => 'Hovered links in slide description',
			'properties' => array(
				'font-size' => '1em',
				'text-decoration' => 'underline',
				'color' => '#FFFFFF'
			)
		),
		
		'navigation_container' => array(
			'css_selector' => '.navigation-items',
			'description' => 'Navigation container',
			'properties' => array(
				'background-color' => '#292929',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'left top'
			)
		),

		'navigation_odd' => array(
			'css_selector' => '.navigation-items li',
			'description' => 'Navigation element - all',
			'properties' => array(
				'background-color' => '#7f7f7f',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'left top'
			)
		),
		
		'navigation_even' => array(
			'css_selector' => '.navigation-items li.even',
			'description' => 'Navigation element - even',
			'properties' => array(
				'background-color' => '#949494',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'left top'
			)
		),
		
		'navigation_active' => array(
			'css_selector' => '.navigation-items li.active',
			'description' => 'Navigation element - active',
			'properties' => array(
				'background-color' => '#3c3c3c',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'left top'
			)
		),
		
		'navigation_hover' => array(
			'css_selector' => '.navigation-items li.hover',
			'description' => 'Navigation element - hover',
			'properties' => array(
				'background-color' => '#999999',
				'background-image' => 'none',
				'background-repeat' => 'no-repeat',
				'background-position' => 'left top'
			)
		),
		
		'navigation_text' => array(
			'css_selector' => '.navigation-items li strong',
			'description' => 'Navigation text',
			'properties' => array(
				'color' => '#FFFFFF',
				'font-size' => '1em'
			)
		),
		
		'navigation_timer' => array(
			'css_selector' => '.navigation-items .timer',
			'description' => 'Navigation timer - visible on autoslide',
			'properties' => array(
				'background-color' => '#000',
				'background-image' => 'none',
				'background-position' => 'left top',
				'background-repeat' => 'no-repeat'
			)
		),
		
		'navigation_thumb' => array(
			'css_selector' => 'div.nav-thumb',
			'outside_container' => true, // flag element as being outside the main slideshow container
			'description' => 'Navigation thumbnail',
			'properties' => array(
				'border-width' => '3px',
				'border-style' => 'solid',
				'border-color' => '#3c3c3c',
				'border-radius' => '0px',
				'box-shadow' => '1px -1px 2px 0px #000000'
			)
		),
		
		'nav_overflow_back' => array(
			'css_selector' => '.navigation a.slide-back',
			'description' => 'Navigation elements - slide back',
			'properties' => array(
				'background-color' => '#292929',
				'background-image' => 'images/left_nav_overflow.png',
				'background-position' => 'center top',
				'background-repeat' => 'no-repeat'
			)
		),
		
		'nav_overflow_back_hover' => array(
			'css_selector' => '.navigation a.slide-back:HOVER',
			'description' => 'Navigation elements - slide back hover',
			'properties' => array(
				'background-color' => '#292929',
				'background-image' => 'images/left_nav_overflow.png',
				'background-position' => 'center bottom',
				'background-repeat' => 'no-repeat'
			)
		),
		
		'nav_overflow_fwd' => array(
			'css_selector' => '.navigation a.slide-forward',
			'description' => 'Navigation elements - slide forward',
			'properties' => array(
				'background-color' => '#292929',
				'background-image' => 'images/right_nav_overflow.png',
				'background-position' => 'center top',
				'background-repeat' => 'no-repeat'
			)
		),
		
		'nav_overflow_fwd_hover' => array(
			'css_selector' => '.navigation a.slide-forward:HOVER',
			'description' => 'Navigation elements - slide forward hover',
			'properties' => array(
				'background-color' => '#292929',
				'background-image' => 'images/right_nav_overflow.png',
				'background-position' => 'center bottom',
				'background-repeat' => 'no-repeat'
			)
		)
	);
	
	return $rules;
}
?>