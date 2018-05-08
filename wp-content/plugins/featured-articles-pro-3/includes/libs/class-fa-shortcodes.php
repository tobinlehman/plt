<?php
/**
 * Class that implements all plugin shortcodes
 */
class FA_Shortcodes{
	
	private $slider_id 	= false;
	private $atts		= array();
	
	/**
	 * Constructor; starts all plugin shortcodes
	 */
	public function __construct(){
		$shortcodes = $this->shortcodes();
		foreach( $shortcodes as $tag => $data ){
			add_shortcode( $tag , $data['callback'] );
		}		
	}
	
	/**
	 * Contains all shortcodes implemented by the plugin
	 */
	private function shortcodes( $shortcode = false ){
		$shortcodes = array(
			'fa_slider' => array(
				'callback' => array( $this, 'shortcode_fa_slider' ),
				'atts' => array(
					'id' 		=> 0, // slider if
					'singular' 	=> true, // show only if post is displayed singular
					'title'		=> '', // slider title
					'show_title'=> true, // show slider title
					'width'		=> '900', // slider width
					'height'	=> 300, // slider height
					'font_size' => '100%', // slider font size
					'full_width'=> true, // slider is allowed to go fullwidth
					'top'		=> 0, // top margin
					'bottom'	=> 0, // bottom margin
					'show_slide_title'	=> true,
					'show_content'		=> true,
					'show_date'			=> true,
					'show_read_more'  	=> true,
					'show_play_video' 	=> true,
					'img_click'			=> false,
					'auto_slide'		=> false,
				)
			)
		);	
		
		$shortcodes['FA_Lite'] = $shortcodes['fa_slider'];
		
		if( $shortcode ){
			if( array_key_exists( $shortcode , $shortcodes ) ){
				return $shortcodes[ $shortcode ];
			}else{
				return false;
			}
		}		
		return $shortcodes;
	}
	
	/**
	 * Shortcode fa_slider callback function.
	 * Displays a slider from shortcode.
	 */
	public function shortcode_fa_slider( $atts ){		
		// if displaying a slider, don't allow the shortcodes to avoid an infinite loop
		global $fa_slider;
		if( $fa_slider ){
			return;
		}
		
		$data = $this->shortcodes('fa_slider');
		$this->atts = shortcode_atts( 
			$data['atts'], 
			$atts 
		);
		
		foreach( $this->atts as $key => $value ){
			if( 'false' == $value && is_bool( $data['atts'][ $key ] ) ){
				$this->atts[ $key ] = false;
			}
		}
		
		extract( $this->atts , EXTR_SKIP );
		$show = true;	
		
		// show only on single post page if set	
		if( $singular ){
			if( !is_singular() ){
				$show = false;
			}
		}
		
		if( $show ){
			// On theme editor, only display a message but don't display the slider
			if( fa_is_theme_edit_preview() ){
				$output  = '<div style="padding:1em; border:1px red solid;">';
				$output .= __('Shortcode sliders disabled on theme editor.', 'fapro');
				$output .= '</div>';
				return $output;
			}
			
			// store the current slider id
			$this->slider_id = $id;
			
			// Lite shortcode only has one attribute: ID; if more than one attribute exists, overwrite the slider options
			if( count( $atts ) > 1 ){			
				// add a filter on slider display to allow overwrite of slider options
				add_filter( 'fa_display_slider' , array( $this, 'overwrite_options' ), 999, 3 );
			}				
			
			// display the slider. Assign as area shortcode_area
			ob_start();	
			fa_display_slider( $id, 'shortcode_area' );
			$output = ob_get_clean();
			
			
			// clear the slider id class variable
			$this->slider_id 	= false;
			$this->atts			= array();
			// remove the filter to avoid messing the display of the slider on other areas
			remove_filter( 'fa_get_slider_options', array( $this, 'options' ), 999 );
			// remove show filter
			remove_filter( 'fa_display_slider' , array( $this, 'overwrite_options' ), 999 );
			// remove title filter
			remove_filter( 'the_fa_slider_title', array( $this, 'the_title' ), 999 );

			
			// return the slider output
			return $output;
		}			
	}
	
	/**
	 * Callback function on filter fa_display_slider used in shortcode_fa_slider().
	 * Implements the slider options filter to allow the shortcode to overwrite the 
	 * slider options with the shortcode attributes.
	 *  
	 * @param bool $show
	 * @param int $slider_id
	 * @param string $dynamic_area
	 */
	public function overwrite_options( $show, $slider_id, $dynamic_area ){
		// check that area is shortcode
		if( 'shortcode_area' == $dynamic_area ){
			// check the slider ID to be the same as the one currently processed by the shortcode
			if( $slider_id == $this->slider_id ){
				// filter the options
				add_filter( 'fa_get_slider_options', array( $this, 'options' ), 999, 3 );
			}
		}		
		return $show;
	}
	
	/**
	 * Overwrite slider options with shortcode atts
	 * @param array $options
	 * @param string $key
	 * @param int $slider_id
	 */
	public function options( $options, $key, $slider_id ){
		// check the ID to be the same as the one processed
		if( $slider_id != $this->slider_id ){
			return $options;
		}
				
		$layout_options = array(
			'show_title'		=> $this->atts['show_title'],
			'width'				=> $this->atts['width'],
			'height'			=> $this->atts['height'],
			'font_size'			=> absint( $this->atts['font_size'] ),
			'full_width'		=> $this->atts['full_width'],
			'margin_top'		=> $this->atts['top'],
			'margin_bottom'		=> $this->atts['bottom']	
		);

		if( !$key ){
			$options['layout'] 						= wp_parse_args( $layout_options, $options['layout'] );	
			$options['content_title']['show'] 		= $this->atts['show_slide_title'];
			$options['content_text']['show'] 		= $this->atts['show_content'];
			$options['content_read_more']['show'] 	= $this->atts['show_read_more'];
			$options['content_play_video']['show'] 	= $this->atts['show_play_video'];
			$options['content_date']['show'] 		= $this->atts['show_date'];
			$options['content_image']['clickable'] 	= $this->atts['img_click'];	
			$options['js']['auto_slide'] 			= $this->atts['auto_slide'];		
		}else{
			switch( $key ){
				case 'layout':
					$options = wp_parse_args( $layout_options, $options );
				break;
				case 'content_title':
					$options['show'] = $this->atts['show_slide_title'];
				break;
				case 'content_text':
					$options['show'] =  $this->atts['show_content'];
				break;
				case 'content_read_more':
					$options['show'] =  $this->atts['show_read_more'];
				break;
				case 'content_play_video':
					$options['show'] =  $this->atts['show_play_video'];
				break;	
				case 'content_date':
					$options['show'] = $this->atts['show_date'];
				break;
				case 'content_image':
					$options['clickable'] = $this->atts['img_click'];
				break;
				case 'js':
					$options['auto_slide'] = $this->atts['auto_slide'];	
				break;	
			}			
		}
		
		return $options;
	}
	
	/**
	 * Filter the_fa_slider_title callback. Overwrites the default slider title
	 * with the one set on shortcode atts
	 * 
	 * @param string $slider_title
	 * @param int $slider_id
	 */
	public function the_title( $slider_title, $slider_id ){
		// check the ID to be the same as the one processed
		if( $slider_id != $this->slider_id ){
			return $slider_title;
		}	
		return $this->atts['title'];		
	}
	
}