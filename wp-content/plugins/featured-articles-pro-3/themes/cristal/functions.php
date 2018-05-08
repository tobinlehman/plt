<?php 
/**
 * Extend slider option with additional variables
 */
function theme_cristal_extra_options( $option ){
	// get this theme key
	$key = fa_get_theme_key( __FILE__ );
	$option[ $key ] = array(
		'navigation' => 'dots'
	);
	
	return $option;
}
add_filter( 'fa_extra_slider_options', 'theme_cristal_extra_options' );

/**
 * Output for the fields above
 * @param array $options - current options set
 */
function cristal_layout_fields_output( $post ){
	// get the stored values of the options implemented by this theme
	$theme_options = fa_get_theme_options( __FILE__, $post );
	$theme_details = fa_theme_details( false );
?>
<h2><?php printf( __('%s : theme specific settings', 'fapro'), $theme_details['name']);?></h2>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="cristal_navigation"><?php _e('Navigation type', 'fapro');?>:</label></th>
			<td>
				<?php 
					$options = array(
						'dots' 		=> __( 'Dots' , 'fapro' ),
						'carousel' 	=> __('Carousel', 'fapro')
					);
					fa_dropdown(array(
						'options' 	=> $options,
						'name'		=> fa_theme_var_name( 'navigation', __FILE__, false ),
						'id'		=> 'cristal_navigation',
						'selected'	=> $theme_options['navigation'],
						'use_keys'	=> true,
						'select_opt'	=> false
					));
				?>
			</td>
		</tr>
	</tbody>
</table>
<?php 	
}
add_action('fa_theme_layout_settings-' . fa_get_theme_key( __FILE__ ), 'cristal_layout_fields_output', 10, 1);

/**
 * Some details about the theme. 
 * Also notice key Fields. It stores the above field and flags it as enabled for this theme. All other themes will display this field disabled.
 */
function fa_theme_details( $defaults ){
	
	$description = "Full background image theme, suitable for video and image slider. Comes with various layour variations that allow different displays of the same theme. Video enabled and responsive.";
	
	$defaults = array(
		'author' 		=> 'CodeFlavors',
		'author_uri' 	=> 'http://www.codeflavors.com',
		'copyright' 	=> 'author',
		'compatibility' => '3.0',
		'version'		=> '1.0',
		'name'			=> 'Cristal',
		'fields'		=> array(
			'content-author-show'	=> false,
			'content-author-link'	=> false,
			'js-position-in' 	=> false,
			'js-position-out'	=> false,
			'js-distance-in' 	=> false,
			'js-distance-out'	=> false,
			'layout-show-title'	=> false
		),
		// the main slider can have different extra CSS classes that can slide the layout in a different way
		// all the variations are specified here as css_class => message
		'classes' => array(
			'content-top background' 		=> __('Content displayed top with background', 'fapro'),
			'content-bottom background' 	=> __('Content displayed bottom with background', 'fapro'),
			'content-left background'		=> __('Content on the left side with background', 'fapro'),
			't-up-c-down background'		=> __('Title up right, content bottom', 'fapro'),			
			'background'					=> __('Content centered, full height with background', 'fapro'),
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
add_filter('fa-theme-details-' . fa_get_theme_key( __FILE__ ), 'fa_theme_details', 1);


function fa_theme_css_cristal(){
	$rules = array(
		/* The slider container */
		'container' => array(
			'css_selector' 	=> '.fa_slider_cristal',
			'description' 	=> __( 'Slideshow container', 'fapro' ),
			'properties'	=> array(
				'background-color' 	=> 'none',
				'border-radius'		=> '5px'
			)
		),
		/* Slide title */
		'slide_title' => array(
			'css_selector' 	=> '.fa_slide_content h2',
			'description' 	=> __( 'Slide title', 'fapro' ),
			'properties'	=> array(
				'font-style'	=> 'normal',
				'font-size' 	=> '1.7em',
				'color'			=> '#FFFFFF',
				'font-weight' 	=> 300,
				'text-shadow'	=> '1px 1px 2px #000000',
				'border-bottom-width'	=> '1px',
				'border-bottom-style'	=> 'solid',
				'border-bottom-color'	=> '#999'
			)
		),
		'slide_title_anchor' => array(
			'css_selector' 	=> '.fa_slide_content h2 a',
			'description' 	=> __( 'Slide title link', 'fapro' ),
			'properties'	=> array(
				'color' 			=> '#FFFFFF',
				'text-decoration' 	=> 'none'
			)
		),
		'slide_date' => array(
			'css_selector' 	=> '.fa_slide_content .slide-date',
			'description' 	=> __( 'Slide date', 'fapro' ),
			'properties'	=> array(
				'font-size' 	=> '.9em',
				'color' 		=> '#CCCCCC',
				'text-shadow' 	=> '1px 1px 2px #000000',
				'text-align'	=> 'left'
			)
		),
		'slide_description' => array(
			'css_selector' 	=> '.fa_slide_content div.description',
			'description' 	=> __( 'Slide text', 'fapro' ),
			'properties'	=> array(
				'font-size' 	=> '1em',
				'line-height' 	=> '1.3em',
				'color'			=> '#FFFFFF',
				'font-weight'	=> 400,
				'text-shadow'	=> '0px 0px 2px #000000'
			)
		),
		'read_more' => array(
			'css_selector' 	=> '.fa_slide_content .fa_read_more',
			'description' 	=> __( 'Read more link', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF',
				'font-size' => '1.1em',
				'font-weight' => 300,
				'text-decoration' => 'none',
				'border-width' => '1px',
				'border-style' => 'solid',
				'border-color' => '#FFFFFF',
				'text-shadow' => '1px 1px 2px #000000'
			)
		),
		'play_video' => array(
			'css_selector' 	=> '.fa_slide_content .play-video',
			'description' 	=> __( 'Play video link', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF',
				'text-shadow' => '1px 1px 2px #000000'
			)
		),
		'nav_fwd' => array(
			'css_selector' 	=> '.go-forward',
			'description' 	=> __( 'Forward navigator', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF'
			)
		),
		'nav_fwd_hover' => array(
			'css_selector' 	=> '.go-forward:hover',
			'description' 	=> __( 'Forward navigator hover', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF'
			)
		),
		'nav_back' => array(
			'css_selector' 	=> '.go-back',
			'description' 	=> __( 'Backward navigator', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF'
			)
		),
		'nav_back_hover' => array(
			'css_selector' 	=> '.go-back:hover',
			'description' 	=> __( 'Backward navigator hover', 'fapro' ),
			'properties'	=> array(
				'color' => '#FFFFFF'
			)
		),
		'man_nav' => array(
			'css_selector' 	=> '.main-nav .fa-nav',
			'description' 	=> __( 'Bottom navigation', 'fapro' ),
			'properties'	=> array(
				'color' 		=> '#FFFFFF',
				'line-height' 	=> '1em',
				'font-size'		=> '1em',
				'text-shadow' 	=> '0px 0px 1px #999'
			)
		),
		'man_nav_hover' => array(
			'css_selector' 	=> '.main-nav .fa-nav:hover',
			'description' 	=> __( 'Bottom navigation hover', 'fapro' ),
			'properties'	=> array(
				'color' 		=> '#FFFFFF',
				'line-height' 	=> '1em',
				'font-size'		=> '1em',
				'text-shadow' 	=> '0px 0px 1px #999'
			)
		)
	);
	return $rules;
}