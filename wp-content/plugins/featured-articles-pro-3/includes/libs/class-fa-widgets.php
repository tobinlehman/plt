<?php

/**
 * Featured Articles slider widget class.
 * Allows multiple instances of the widget.
 */
class FA_Widgets extends WP_Widget{
	
	private $slider_id 	= false;
	private $atts		= array();
	
	/**
	 * Create the slider widget
	 */
	public function __construct(){
		/* Widget settings. */
		$widget_opts = array( 
			'classname' => 'fa_slideshow', 
			'description' => __('Add a Featured Articles slider widget', 'fapro') );

		/* Widget control settings. */
		$control_opts = array( 'id_base' => 'fa-slideshow-widget' );

		/* Create the widget. */
		parent::__construct( 
			'fa-slideshow-widget', 
			__('Featured Articles slider', 'fapro'), 
			$widget_opts, 
			$control_opts 
		);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ){
		if( !$instance || ( !isset( $instance['slider_id'] ) || !$instance['slider_id'] ) || fa_is_theme_edit_preview() ){
			return;			
		}
		
		$slider = get_post( $instance['slider_id'] );
		if( !$slider || ( 'publish' != $slider->post_status && !fa_is_preview() ) ){
			return;
		}
		
		extract( $args, EXTR_SKIP );
		// output HTML before widget as set by sidebar
		echo $before_widget;
		// output the widget title
		$title = apply_filters('widget_title', $instance['title'] );
		if( $instance['title'] ){
			// output the widget title
			echo $before_title . $title . $after_title;			
		}
		
		$this->slider_id = $instance['slider_id'];
		$this->atts = $instance;
		// add a filter on slider display to allow overwrite of slider options
		add_filter( 'fa_display_slider' , array( $this, 'overwrite_options' ), 999, 3 );	
				
		// display the slider; assign it to widget area to be able to check into the display filter (index.php in plugin files).
		fa_display_slider( $instance['slider_id'], 'widget_area' );		
		// output HTML after widget as set by sidebar
		echo $after_widget;
		
		// clear the slider id class variable
		$this->slider_id 	= false;
		$this->atts			= array();
		// remove the filter to avoid messing the display of the slider on other areas
		remove_filter( 'fa_get_slider_options', array( $this, 'options' ), 999 );
		// remove show filter
		remove_filter( 'fa_display_slider' , array( $this, 'overwrite_options' ), 999 );
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
		if( 'widget_area' == $dynamic_area ){
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
			'show_title'		=> false, // don't show the slider title on widgets
			'width'				=> $this->atts['width'],
			'height'			=> $this->atts['height'],
			'full_width'		=> $this->atts['full_width'],
			'font_size'			=> $this->atts['font_size'],
			'margin_top'		=> $this->atts['margin_top'],
			'margin_bottom'		=> $this->atts['margin_bottom']	
		);

		if( !$key ){
			$options['layout'] = wp_parse_args( $layout_options, $options['layout'] );	
			$options['content_title']['show'] = $this->atts['show_title'];
			$options['content_text']['show'] = $this->atts['show_content'];
			$options['content_read_more']['show'] = $this->atts['show_read_more'];
			$options['content_play_video']['show'] = $this->atts['show_play_video'];
			$options['content_date']['show'] = $this->atts['show_date'];
			$options['content_image']['clickable'] = $this->atts['img_click'];	
			$options['js']['auto_slide'] = $this->atts['auto_slide'];		
		}else{
			switch( $key ){
				case 'layout':
					$options = wp_parse_args( $layout_options, $options );
				break;
				case 'content_title':
					$options['show'] = $this->atts['show_title'];
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
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ){
		extract( wp_parse_args( $instance, $this->_defaults() ), EXTR_SKIP );		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' )?>"><?php _e( 'Title', 'fapro' );?>: </label>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'title' );?>" id="<?php echo $this->get_field_id( 'title' );?>" value="<?php echo esc_attr( $title );?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'slider_id' );?>"><?php _e('Slider', 'fapro');?>: </label>
			<?php fa_sliders_dropdown( $this->get_field_name( 'slider_id' ), $this->get_field_id( 'slider_id' ), $slider_id, 'widefat' );?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'width' )?>"><?php _e('Width', 'fapro');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('width');?>" id="<?php echo $this->get_field_id('width');?>" value="<?php echo $width;?>" size="2" /> <?php _e('px', 'fapro');?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'height' )?>"><?php _e('Height', 'fapro');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('height');?>" id="<?php echo $this->get_field_id('height');?>" value="<?php echo $height;?>" size="2" /> px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'full_width' )?>"><?php _e('Display full width', 'fapro');?>: </label>
			<input type="checkbox" name="<?php echo $this->get_field_name('full_width');?>" id="<?php echo $this->get_field_id('full_width');?>" value="1" <?php fa_checked( (bool) $full_width );?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'margin_top' )?>"><?php _e('Top distance', 'fapro');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('margin_top');?>" id="<?php echo $this->get_field_id('margin_top');?>" value="<?php echo $margin_top;?>" size="2" /> px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'margin_bottom' )?>"><?php _e('Bottom distance', 'fapro');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('margin_bottom');?>" id="<?php echo $this->get_field_id('margin_bottom');?>" value="<?php echo $margin_bottom;?>" size="2" /> px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'font_size' )?>"><?php _e('Font size', 'fapro');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('font_size');?>" id="<?php echo $this->get_field_id('font_size');?>" value="<?php echo $font_size;?>" size="2" /> (<?php _e('percentual', 'fapro');?>)
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('show_title');?>" id="<?php echo $this->get_field_id('show_title');?>" value="1" <?php fa_checked( (bool) $show_title );?> />
			<label for="<?php echo $this->get_field_id( 'show_title' )?>"><?php _e('Show titles in slides', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('show_date');?>" id="<?php echo $this->get_field_id('show_date');?>" value="1" <?php fa_checked( (bool) $show_date );?> />
			<label for="<?php echo $this->get_field_id( 'show_date' )?>"><?php _e('Show slides date', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('show_content');?>" id="<?php echo $this->get_field_id('show_content');?>" value="1" <?php fa_checked( (bool) $show_content );?> />
			<label for="<?php echo $this->get_field_id( 'show_content' )?>"><?php _e('Show slide text', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('show_read_more');?>" id="<?php echo $this->get_field_id('show_read_more');?>" value="1" <?php fa_checked( (bool) $show_read_more );?> />
			<label for="<?php echo $this->get_field_id( 'show_read_more' )?>"><?php _e('Show read more link', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('show_play_video');?>" id="<?php echo $this->get_field_id('show_play_video');?>" value="1" <?php fa_checked( (bool) $show_play_video );?> />
			<label for="<?php echo $this->get_field_id( 'show_play_video' )?>"><?php _e('Show play video link', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('img_click');?>" id="<?php echo $this->get_field_id('img_click');?>" value="1" <?php fa_checked( (bool) $img_click );?> />
			<label for="<?php echo $this->get_field_id( 'img_click' )?>"><?php _e('Image is clickable', 'fapro');?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('auto_slide');?>" id="<?php echo $this->get_field_id('auto_slide');?>" value="1" <?php fa_checked( (bool) $auto_slide );?> />
			<label for="<?php echo $this->get_field_id( 'auto_slide' )?>"><?php _e('Autoslide', 'fapro');?></label>
		</p>	
		<?php
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ){
		$instance = array();
		$defaults = $this->_defaults();
		
		foreach( $defaults as $field => $value ){
			$type = gettype( $value );
			switch( $type ){
				case 'integer':
					if( isset( $new_instance[ $field ] ) ){
						$defaults[ $field ] = absint( $new_instance[ $field ] );
					}
				break;
				case 'string':
					$defaults[ $field ] = $new_instance[ $field ];					
				break;
				case 'boolean':
					$defaults[ $field ] = isset( $new_instance[ $field ] );
				break;	
				
			}			
		}
		
		return $defaults;
	}
	
	/**
	 * Widget default values
	 */
	private function _defaults(){
		$defaults = array(
			'title' 		=> __('Featured', 'fapro'),
			'slider_id' 	=> 0,
			'width'			=> 700,
			'height'		=> 500,
			'full_width'	=> true,
			'font_size'		=> '100%',
			'margin_top'	=> 0,
			'margin_bottom' => 0,		
			'show_title'	=> true,
			'show_content'	=> true,
			'show_date'		=> true,
			'show_read_more'  => true,
			'show_play_video' => true,
			'img_click'		=> false,
			'auto_slide'	=> false,
		);
		return $defaults;
	}
}