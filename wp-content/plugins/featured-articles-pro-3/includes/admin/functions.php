<?php 
/**
 * Displays admin messages
 */
function fa_display_admin_message(){
	
	$messages = array(
		701 => __('Preview settings saved.', 'fapro'),
		801 => __('Plugin settings saved.', 'fapro')
		
	);
	
	if( isset( $_GET['message'] ) ){
		$message_id = absint( $_GET['message'] );		
		if( array_key_exists( $message_id , $messages ) ){
?>			
<div id="message" class="updated"><p><?php echo $messages[ $message_id ];?></p></div>
<?php			
		}		
	}	
}

/**
 * Sanitizes a string key.
 *
 * Keys are used as internal identifiers. Lowercase alphanumeric characters, dashes and underscores are allowed.
 * Characters not allowed are converted to underscores.
 *
 *
 * @param string $key String key
 * @return string Sanitized key
 */
function fa_sanitize_hook_id( $key ){
	$key = strtolower( $key );
	$key = preg_replace( '/[^a-z0-9_\-]/', '_', $key );
	return $key;
}

/**
 * Generates the absolute path to an administration page template.
 * Templates are named: template-$template.php
 * Only NAME should be passed to this function.
 * 
 * @uses fa_view_path
 * 
 * @param string $template - template name without template- prefix and .php extension
 * @return string - absolute path to template location 
 */
function fa_template_path( $template ){	
	$file = 'template-' .  $template  . '.php'; 
	return fa_view_path($file);	
}

/**
 * Generates the absolute path to an administration metabox template.
 * Templates are named: metabox-$template.php
 * Only NAME should be passed to this function.
 * 
 * @uses fa_view_path()
 * 
 * @param string $template - template name without metabox- prefix and .php extension
 * @return string - absolute path to template location 
 */
function fa_metabox_path( $template ){
	$file = 'metabox-' .  $template . '.php';
	return fa_view_path($file);	
}

/**
 * Generates the absolute path to an administration modal template.
 * Templates are named: modal-{$template}.php
 * Only NAME should be passed to this function.
 * 
 * @uses fa_view_path()
 * 
 * @param string $template - template name without metabox- prefix and .php extension
 * @return string - absolute path to template location 
 */
function fa_modal_path( $template ){
	$file = 'modal-' . $template . '.php';
	return fa_view_path( $file );
}

/**
 * Returns absolute path for a file in plugin views folder
 * @param string $file
 */
function fa_view_path( $file ){
	$rel_path = 'views/' . sanitize_file_name( $file ); 
	$path = wp_normalize_path( path_join( FA_PATH, $rel_path ) );
	if( !is_file( $path ) ){
		trigger_error( sprintf( __('Template %s does not exist.', 'fapro'), $path), E_USER_WARNING );
	}else{
		return $path;
	}	
}

/**
 * Enqueues a given admin stylesheet. Parameter should
 * be without .css extension 
 * 
 * @param string $stylesheet - stylesheet filename from within folder assets/admin/css without .css extension
 */
function fa_load_admin_style( $stylesheet ){
	
	$url = fa_get_uri( 'assets/admin/css/' . $stylesheet . '.css' );
	wp_enqueue_style(
		'fa-style-' . $stylesheet,
		$url,
		false,
		FA_VERSION
	);
	return 'fa-style-' . $stylesheet;
}

/**
 * Enqueues the stylesheet for a given template. Stylesheet should be inside plugin folder:
 * assets/admin/css and should be named template-$template.css
 * 
 * @param string $template
 */
function fa_load_template_style( $template ){	
	return fa_load_admin_style( 'template-' . $template );	
} 

/**
 * Enqueues a given admin script. File name should not have .js extension.
 * An array of dependencies can be passed to it.
 * 
 * @param string $script - filename from within plugin folder assets/admin/js without the .js extension
 * @param array $dependency - array of dependencies. Defaults to jquery
 * 
 * @return string - script handle
 */
function fa_load_admin_script( $script, $dependency = array( 'jquery' ) ){	
	
	if( defined('FA_SCRIPT_DEBUG_ADMIN') && FA_SCRIPT_DEBUG_ADMIN ){
		$script .= '.dev';
	}else{
		$script .= '.min';
	}
	
	$url = fa_get_uri( 'assets/admin/js/' . $script . '.js' );
	wp_enqueue_script(
		'fa-script-' . $script,
		$url,
		$dependency		
	);	
	return 'fa-script-' . $script;
}

/**
 * Function to load a tinymce js plugin file. 
 * Tinymce plugins are located inside folder assets/js/tinymce/PLUGIN_NAME
 * Only pass the plugin folder name to the function. Actual js file should always be named plugin.js
 * 
 * @param string $plugin
 * @param array $dependency
 */
function fa_tinymce_plugin_url( $plugin ){
	$rel_path = 'assets/admin/js/tinymce/' . $plugin . '/plugin.js';
	return fa_get_uri( $rel_path );
}

/**
 * Function to load a tinymce plugin styling.
 * Tinymce plugins are located inside folder assets/js/tinymce/PLUGIN_NAME
 * Only pass the plugin folder name to the function. Actual css file should always be named style.css
 * @param string $plugin
 */
function fa_tinymce_plugin_style( $plugin ){
	$rel_path = 'assets/admin/js/tinymce/' . $plugin . '/style.css';
	return fa_get_uri( $rel_path );
}

/**
 * Processes the allowed post types that slides can be made from.
 * Uses the settings from plugin Settings page.
 * @return array - array of allowed post types
 */
function fa_allowed_post_types(){
	// get the allowed post types from plugin settings
	$options = fa_get_options('settings');
	// merge the default post type with the allowed post types
	$allowed = array_unique( array_merge( array( 'post', 'page', fa_post_type_slide() ), (array)$options['custom_posts'] ) );
	
	// start the result
	$result	 = array();
	foreach( $allowed as $k => $post_type ){
		if( post_type_exists( $post_type ) ){
			$result[] = $post_type;
		}
	}
	return $result;	
}

/**
 * Retrieves the posts attached to a slider that are used to
 * create the slides. Used when slides are made of manually
 * selected mixed posts, pages and custom types
 * 
 * @param $post_id - slider ID
 * @param $post_status - the status of the posts
 */
function fa_get_slider_posts( $post_id, $post_status = 'publish' ){
	
	$settings = fa_get_options('settings');
	$slider_options = fa_get_slider_options( $post_id, 'slides' );
	
	$post_types = (array) $settings['custom_posts'];
	array_unshift( $post_types, fa_post_type_slide(), 'post', 'page' );
	
	$post_ids = (array) $slider_options['posts'];
	if( !$post_ids ){
		return;
	}
	
	$args = array(
		'posts_per_page' 		=> -1,
		'nopaging'				=> true,
		'ignore_sticky_posts' 	=> true,
		'offset'				=> 0,
		'post__in'				=> $post_ids,
		'post_type'				=> $post_types,
		'post_status'			=> $post_status,
	    'suppress_filters'      => true
	);    	
	$query = new WP_Query( $args );
	$posts = $query->get_posts();
	
	if( !$posts ){
		return;		
	}
	
	if( $posts ){
		foreach( $posts as $post ){
			$key = array_search( $post->ID, $post_ids );
			$result[ $key ] = $post;
		}					
	}
	// arrange the values according to settings
	ksort($result);	
	// regenerate the keys to start from 0 ascending
	$result = array_values( $result );
	
	/**
	 * Filter manual mixed slider slides.
	 * @var array $result
	 * @var int $slider_id
	 */
	$result = apply_filters( 'fa_manual_slider_admin_posts' , $result, $post_id );
	if( !is_array( $result ) ){
		$result = array();
	}else{
		$result = array_values( $result );
	}
	
	return $result;
}

/**
 * Retrieves the images attached to a slider that are used to
 * create the slides. Used when slides are made of manually
 * selected images
 * 
 * @param $post_id - slider ID
 */
function fa_get_slider_images( $post_id ){
	$settings = fa_get_options('settings');
	$slider_options = fa_get_slider_options( $post_id, 'slides' );
	
	$image_ids = (array) $slider_options['images'];
	if( !$image_ids ){
		return;
	}
	
	$args = array(
		'posts_per_page' 		=> -1,
		'nopaging'				=> true,
		'ignore_sticky_posts' 	=> true,
		'offset'				=> 0,
		'post__in'				=> $image_ids,
		'post_type'				=> 'attachment',
		'post_status'			=> 'inherit',
		'post_mime_type'		=> 'image',
	    'suppress_filters'      => true
	);
	$query = new WP_Query( $args );
	$images = $query->get_posts();
	
	$result = array();
	foreach( $image_ids as $image_id ){
		foreach( $images as $image ){
			if( $image_id == $image->ID ){
				$result[] = $image;
				break;
			}
		}		
	}
	return $result;
}

/**
 * Returns setting for slide edit on post edit page
 */
function fa_allowed_slide_edit(){
	// get the allowed post types from plugin settings
	$options = fa_get_options('settings');
	if( isset( $options['post_slide_edit'] ) ){
		return $options['post_slide_edit'];
	}
	return false;	
}

/**
 * Returns a list of checkboxes for a given set of options
 * @param array $attr - attributes for displaying the checkboxes
 */
function fa_checkboxes( $attr ){
	$defaults = array(
		'name' 		=> false,
		'id'		=> '',
		'selected' 	=> array(),
		'options' 	=> array(),
		'separator' => '<br />',
		'echo'		=> true
	);
	extract( wp_parse_args($attr, $defaults), EXTR_SKIP );
	
	if( !$options ){
		return false;
	}
	if( !is_array( $selected ) ){
		$seelected = array();
	}
	if( empty( $id ) ){
		$id = $name;
	}
	
	$output = '';
	foreach( $options as $value => $label ){
		$checked = in_array($value, $selected) ? 'checked="checked"' : '';
		$el_id = esc_attr( $id . $value );
		$output .= sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" %4$s /><label for="%3$s">%5$s</label>%6$s',
			$name . '[]',
			$value,
			$el_id,
			$checked,
			$label,
			$separator
		);
	}
	
	if( $echo ){
		echo $output;
	}
	return $output;
}

/**
 * Return a list of checkboxes corresponding to all current registered post types
 * @param array $attr
 */
function fa_all_post_types_checkboxes( $attr = array() ){
	
	$defaults = array(
		'name' 		=> 'custom_posts',
		'selected' 	=> array(),
		'echo' 		=> true
	);
	
	$args = wp_parse_args($attr, $defaults);	
	$cpt = get_post_types( array(
		'public' 	=> true,
		'_builtin' 	=> false
	), 'objects' );
	
	if( !$cpt ){
		return false;
	}
	
	$options = array();
	foreach( $cpt as $post_type => $obj ){
		$options[ $post_type ] = $obj->label;
	}
	
	$args['options'] = $options;
	return fa_checkboxes($args);	
}

/**
 * Return a list of checkboxes allowed by the plugin
 * @param array $attr
 */
function fa_post_types_checkboxes( $attr = array() ){
	// get plugin options
	$plugin_opt = fa_get_options('settings');
	if( !isset( $plugin_opt['custom_posts'] ) ){
		return;		
	}
	// check if the already allowed post types exist
	$post_types = array();
	foreach( $plugin_opt['custom_posts'] as $post_type ){
		if( post_type_exists($post_type) ){
			$post_types[] = $post_type;
		}		
	}
	// if no post type is registered, return
	if( !$post_types ){
		return;		
	}
	
	// add post post_type to array
	array_unshift( $post_types, 'post' );
	
	$defaults = array(
		'name' 		=> 'custom_posts',
		'id'		=> 'custom-posts-',
		'selected' 	=> array(),
		'echo' 		=> true
	);
	$args = wp_parse_args( $attr, $defaults );
	
	foreach( $post_types as $post_type ){
		$obj = get_post_type_object( $post_type );
		$options[ $post_type ] = $obj->label;
	}
	
	$args['options'] = $options;
	return fa_checkboxes($args);	
} 

/**
 * Displays checked argument in checkbox
 * @param bool $val
 * @param bool $echo
 */
function fa_checked( $val, $echo = true ){
	$checked = '';
	if( is_bool($val) && $val ){
		$checked = ' checked="checked"';
	}
	if( $echo ){
		echo $checked;
	}else{
		return $checked;
	}	
}

/**
 * Displays a style="hidden" on an element if $val is bool true
 *  
 * @param bool $val - value to evaluate
 * @param bool $include_style - include style="" or just display the css
 * @param bool $echo
 */
function fa_hide( $val, $include_style = true, $echo = true ){
	if( !is_bool( $val ) ){
		if( defined( 'FA_SCRIPT_DEBUG' ) && FA_SCRIPT_DEBUG ){
			$trace = debug_backtrace();
			trigger_error( sprintf('Value passed to function should be type BOOL ( function called from %s line %d ).', $trace[0]['file'], $trace[0]['line']), E_USER_ERROR );
			return;
		}
	}
	
	$output = '';
	if( !$val ){
		return $output; 
	}else{
		$output = 'display:none;';
		if( $include_style ){
			$output = 'style="' . $output . '"';
		}
	}
	if( $echo ){
		echo $output;
	}
	return $output;	
}

/**
 * Display select box
 * @param array $args - see $defaults in function
 * @param bool $echo
 */
function fa_dropdown( $args = array() ){
	
	$defaults = array(
		'options' 	=> array(),
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'use_keys'	=> true,
		'hide_if_empty' => true,
		'show_option_none' => __('No options', 'fapro'),
		'select_opt'	=> __('Choose', 'fapro'),
		'select_opt_style' => false,
		'attrs'	=> '',
		'echo' => true
	);
	
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	if( $hide_if_empty  && !$options && !$show_option_none){
		return;
	}
	
	if( !$id ){
		$id = $name;		
	}
	
	$output = sprintf( '<select autocomplete="off" name="%1$s" id="%2$s" class="%3$s" %4$s>', esc_attr( $name ), esc_attr( $id ), esc_attr( $class ), $attrs );
	if( !$options && $show_option_none ){
		$output .= '<option value="">' . $show_option_none . '</option>';	
	}elseif( $select_opt ){		
		$output .= '<option value=""'. ( $select_opt_style ? ' style="' . $select_opt_style . '"' : '' ) .'>' . $select_opt . '</option>';	
	}	
	
	foreach( $options as $val => $text ){
		$opt = '<option value="%1$s"%2$s>%3$s</option>';
		$value = $use_keys ? $val : $text;
		$c = $use_keys ? $val == $selected : $text == $selected;
		$checked = $c ? ' selected="selected"' : '';		
		$output .= sprintf($opt, $value, $checked, $text);		
	}
	
	$output .= '</select>';
	
	if( $echo ){
		echo $output;
	}
	
	return $output;
}

/**
 * Display a dropdown of all dynamic areas
 * @param array $args
 */
function fa_dynamic_areas_dropdown( $args = array() ){
	$areas 		= fa_get_options( 'hooks' );
	$options 	= array();
	foreach( $areas as $area => $details ){
		$options[ $area ] = $details['name'];		
	}
	
	$defaults = array(
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'show_option_none' => false
	);
	$args = wp_parse_args( $args, $defaults );
	$args['options'] = $options;
	fa_dropdown( $args );	
}

/**
 * For a given theme, outputs a dropdown containing the 
 * CSS layout variations implemented in theme functions.php file.
 * 
 * @param string $theme - the theme identifier
 * @param array $args
 */
function fa_theme_layouts_dropdown( $theme, $args ){
	if( !$theme ){
		return false;
	}	
	
	$theme = fa_get_theme( $theme );
	if( !$theme['theme_config']['classes'] ){
		return false;
	}
	
	$defaults = array(
		'name'		=> false,
		'id'		=> false,
		'selected'	=> false,
		'use_keys'	=> true,
		'select_opt'	=> __('Choose', 'fapro')
	);
	$args = wp_parse_args( $args, $defaults );
	$args['options'] = $theme['theme_config']['classes'];
	return fa_dropdown( $args );
}

/**
 * Display a dropdown of slide effects
 * @param unknown_type $args
 */
function fa_slide_effect_dropdown( $args = array() ){
	// the effects
	$options = array(
		'squares' 		 => __( 'Moving rectangles', 'fapro' ),
		'zipper'		 => __( 'Zipper', 'fapro' ),
		'ripple'		 => __( 'Ripple', 'fapro' ),
		'fade'			 => __( 'Progressive fade', 'fapro' ),
		'simple_squares' => __( 'Fading rectangles', 'fapro' ),
		'flip'			 => __( 'Flip', 'fapro' ),
		'swirl'			 => __( 'Swirl', 'fapro' ),
		'wave'			 => __( 'Wave', 'fapro' ),
		'horizontal_slices' => __( 'Horizontal slices', 'fapro' ),
		'vertical_slices' => __( 'Vertical slices', 'fapro' ),
		'out_right' => __( 'Slide out right', 'fapro' ),
		'out_left' => __( 'Slide out left', 'fapro' ),
		'out_top' => __( 'Slide out top', 'fapro' ),
		'out_bottom' => __( 'Slide out bottom', 'fapro' ),
		'random' => __( 'Random each slide', 'fapro' ),
	);
	
	$defaults = array(
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'select_opt'	=> __('Select effect', 'fapro'),
		'echo' => true
	);
	$args = wp_parse_args( $args, $defaults );
	$args['options'] = $options;
	$output = fa_dropdown( $args );
	return $output;
}

/**
 * Display a select box with folders from within wp-content folder
 * @param array $args
 */
function fa_select_extra_dir( $args = array() ){
	$default = array(
		'name' 		=> 'themes_dir',
		'id'		=> false,
		'selected'	=> false,
		'echo'		=> true,
		'select_opt'=> __('Choose folder', 'fapro'),
		'hide_if_empty' => true,
		'show_option_none' => __('Nothing found', 'fapro'),
		'use_keys' => false
	);
	$args = wp_parse_args($args, $default);
	$args['options'] = read_wp_content_dir();
	
	$output = fa_dropdown( $args );	
	return $output;
}

/**
 * Return a select box with aspect ratios
 * @param array $args
 */
function fa_select_aspect_ratio( $args = array() ){
	
	$default = array(
		'name' 	=> 'aspect_ratio',
		'id' 	=> false,
		'selected' => false,
		'echo' => true,
		'select' => false,
		'select_opt'=> false,
		'class' => 'fa_video_aspect_ratio'
	);
	$args = wp_parse_args($args, $default);
	$args['options'] = array(
		'16x9' 		=> '16:9',
		'4x3' 		=> '4:3',
		'2.35x1' 	=> '2.35:1'
	);
	$output = fa_dropdown( $args );	
	return $output;
}

/**
 * Check if user has set the external directory for slider themes.
 */
function fa_is_extra_dir_set(){
	$options = fa_get_options('settings');
	if( isset( $options['themes_dir'] ) && !empty( $options['themes_dir'] ) ){
		return true;
	}
	return false;
}

/**
 * Output a dropdown with all registered WP image sizes
 * @param array $args
 */
function fa_wp_image_size_dropdown( $args = array() ){
	global $_wp_additional_image_sizes;
	
	$sizes = get_intermediate_image_sizes();
	$sizes[] = 'full';
	$options = array();
	foreach( $sizes as $size ){
		$w = $h = 0;
		switch( $size ){
			case 'thumbnail':
				$w = intval( get_option('thumbnail_size_w') );
				$h = intval( get_option('thumbnail_size_h') );				
			break;
			case 'medium':
				$w = intval(get_option('medium_size_w'));
				$h = intval(get_option('medium_size_h'));
			break;
			case 'large':
				$w = intval(get_option('large_size_w'));
				$h = intval(get_option('large_size_h'));	
			break;
			case 'full':
				// nothing
			break;
			default:
				$w = isset( $_wp_additional_image_sizes[ $size ] ) ? $_wp_additional_image_sizes[ $size ]['width'] : 0;
				$h = isset( $_wp_additional_image_sizes[ $size ] ) ? $_wp_additional_image_sizes[ $size ]['height'] : 0;			
			break;
		}
		$options[ $size ] = ucfirst( str_replace( array('-', '_'), ' ', $size ) ) . ( $w && $h ? ' - max. '. $w . 'x' . $h . 'px' : '' );		
	}
	
	$default = array(
		'name' 			=> 'image_size',
		'id' 			=> false,
		'selected' 		=> false,
		'echo' 			=> true,
		'select' 		=> false,
		'select_opt'	=> false,
		'use_keys'		=> true
	);
	$args 				= wp_parse_args( $args, $default );
	$args['options'] 	= $options;
	
	$output = fa_dropdown( $args );
	return $output;
}

/**
 * Displays a dropdown of sliders
 * @param string $name
 * @param string $id
 * @param int $selected
 */
function fa_sliders_dropdown( $name, $id = false, $selected = false, $class = '', $status = 'publish', $echo = true ){
	
	$sliders = fa_get_sliders( $status );
	if( !$sliders ){
		return false;	
	}
		
	$options = array();
	foreach( $sliders as $slider ){
		$options[ $slider->ID ] = !empty( $slider->post_title ) ? esc_attr( $slider->post_title ) : '(' . esc_attr( __('no title', 'fapro')) . ')';
	}
	
	$args = array(
		'options' 	=> $options,
		'name'		=> $name,
		'id'		=> $id,
		'class'		=> $class,
		'selected'	=> $selected,
		'select_opt'	=> __('Choose slider', 'fapro'),
		'echo' 		=> false
	);
	$result = fa_dropdown( $args );
	if( $echo ){
		echo $result;
	}
	return $result;
}

/**
 * Reads the folders inside wp_content folder to help the user choose the folder where to store 
 * extra fa themes
 */
function read_wp_content_dir(){	
	$content_dir = @ opendir( WP_CONTENT_DIR );
	$folders = array();
	if ( $content_dir ) {
		while (($file = readdir( $content_dir ) ) !== false ) {
			if ( substr($file, 0, 1) == '.' )
				continue;
			if ( is_dir( WP_CONTENT_DIR.'/'.$file ) ) {
				$folders[] = $file;
			}
		}
		closedir( $content_dir );
	}
	return $folders;	
}

/**
 * Query the video details for a given video ID on a given source.
 * $args
 * 	- source: the video source ( youtube or vimeo )
 * 	- video_id: the ID of the video
 * 
 * @param array $args
 * @return mixed - WP_Error or array of video details
 */
function fa_query_video( $args ){	
	if( !class_exists('FA_Video_Query') ){
		require_once fa_get_path( 'includes/admin/libs/class-fa-media-query.php' );
	}
	
	$video_query = new FA_Video_Query( $args );
	return $video_query->get_result();	
}

/**
 * Displays a list of radio boxes for all registered video sources
 * @param array $args
 */
function fa_video_sources_checkboxes( $args ){
	if( !class_exists('FA_Video_Query') ){
		require_once fa_get_path( 'includes/admin/libs/class-fa-media-query.php' );
	}
	
	$defaults = array(
		'name' => 'fa_video_source',
		'id' => false,
		'selected' => false,
		'echo' => true,
		'separator' => '<br />'
	);
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	$video_query = new FA_Video_Query( false );
	$sources = $video_query->get_video_sources();
	
	if( !$id ){
		$id = $name;		
	}
	
	$output = '';	
	foreach( $sources as $source_id => $source ){
		$checked = $source_id == $selected ? ' checked="checked"' : false;
		$el_id = $id . '-' . $source_id;
		
		$output .= sprintf( '<input type="radio" name="%1$s" id="%2$s" value="%3$s"  /> <label for="%2$s">%4$s</label>%5$s',
			$name,
			$el_id,
			$source_id,
			$source['details']['name'],
			$separator 
		);		
	}
	
	if( $echo ){
		echo $output;
	}else{
		return $output;
	}	
}

/**
 * Color picker
 * @param array $args
 */
function fa_color_picker( $args = array() ){
	
	$defaults = array(
		'name' 			=> 'fa_color_picker',
		'id'			=> false,
		'value'			=> '',
		'attr'			=> false,
		'autoload'		=> true,
		'class'			=> false
	);
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	if( !$id ){
		$id = $name;		
	}
	// load the script that starts the color pickers
	if( $autoload ){
		// load assets
		fa_load_admin_script(
			'color-picker',
			array(
				'wp-color-picker'
			)
		);
	}else{
		wp_enqueue_script('wp-color-picker');
	}	
	wp_enqueue_style('wp-color-picker');	
?>
<input class="fapro-color-picker-hex <?php echo $class;?>" type="text" maxlength="7" <?php echo $attr;?> name="<?php echo esc_attr( $name );?>" id="<?php echo esc_attr( $id );?>" value="<?php echo $value;?>" placeholder="<?php _e('Hex value', 'fapro')?>" />
<?php	
}

/**
 * Display upload button
 * @param string $name
 * @param string $id
 */
function fa_media_gallery( $args = array() ){
	
	$defaults = array(
		'name' 				=> 'fa_upload',
		'id'				=> 'fa_upload',
		'page_title' 		=> __('Select images (multiple image select enabled)', 'fapro'),
		'button_text' 		=> __('Set images', 'fapro'),
		'select_multiple' 	=> true, // allows multiple images selection
		'class'				=> 'button',
		'update_elem'		=> '#fa-selected-images', // the element ID that should be updated with the response from Ajax after selecting images
		'append_response'	=> true, // append response to $update_elem element ID
		'attributes'		=> false // extra attributes
	);
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	
	if( !$id ){
		$id = $name;		
	}
	wp_enqueue_media();
	fa_load_admin_script('media-gallery');
?>
<div class="uploader stag-metabox-table" style="display:inline;">
	<a href="#" class="fa_upload_image_button <?php echo $class?>" <?php echo $attributes;?> data-title="<?php echo $page_title;?>" data-multiple="<?php echo (bool) $select_multiple;?>" data-update="<?php echo $update_elem;?>" data-append="<?php echo $append_response;?>" id="<?php echo $id;?>_button">
		 <?php echo $button_text;?>
	</a>
</div>
<?php 	
}

/**
 * Display a dropdown to select positions (top, left, right, bottom)
 * @param array $args
 */
function fa_sliding_positions_dropdown( $args ){
	
	$options = array(
		'left' 		=> __( 'Left', 'fapro' ),
		'right' 	=> __( 'Right', 'fapro' ),
		'top' 		=> __( 'Top', 'fapro' ),
		'bottom' 	=> __( 'Bottom', 'fapro' ),
	);
	
	$defaults = array(
		'name'			=> false,
		'id'			=> false,
		'class'			=> '',
		'selected'		=> false,
		'use_keys'		=> true,
		'select_opt'	=> false,
		'echo' 			=> true
	);
	
	if( isset( $args['options'] ) )
		unset( $args['options'] );
	
	$defaults['options'] = $options;
	
	$args = wp_parse_args( $args, $defaults );
	$dropdown = fa_dropdown( $args );
	
	return $dropdown;	
}

/**
 * Output theme colors dropdown
 * @param array $theme_details - theme details as returned by theme manager
 * @param array $args
 */
function fa_theme_colors_dropdown( $theme_details, $args = array() ){
	
	$colors = $theme_details['colors'];
	if( !$colors ){
		return;
	}
	
	$options = array();
	foreach( $colors as $c => $d ){
		$options[ $c ] = $d['name'];
	}
	if( isset($args['options']) ){
		unset( $args['options'] );
	}
	
	$defaults = array(
		'name' 		=> false,
		'id'		=> false,
		'multiple'	=> true,
		'label'		=> false,
		'selected' 	=> false,
		'options' 	=> $options,
		'use_keys' 	=> true,
		'select_opt'=> false
	);
	$args = wp_parse_args( $args, $defaults );
	if( $args['name'] ){
		if( $args['multiple'] ){
			$args['name'] .= '[' . $theme_details['dir'] . ']';
		}	
	}
	if( $args['id'] ){
		$args['id'] .= '-' . $theme_details['dir'];
	}
	$args['echo'] = false;
	$dropdown = fa_dropdown( $args );
	$label = '';
	
	if( $args['label'] ){
		$label = sprintf('<label for="%s">%s</label>: ',
			$args['id'],
			$args['label']
		);
	}	
	echo $label . $dropdown;
}

/**
 * Display a dropdown of mouse events
 * @param array $args
 */
function fa_sliding_event_dropdown( $args ){
	$options = array(
		'click' 		=> __( 'Click', 'fapro' ),
		'mouseenter' 	=> __( 'Mouse hover', 'fapro' )
	);
	
	$defaults = array(
		'name'			=> false,
		'id'			=> false,
		'class'			=> '',
		'selected'		=> false,
		'use_keys'		=> true,
		'select_opt' 	=> false,
		'echo' 			=> true
	);
	
	if( isset( $args['options'] ) )
		unset( $args['options'] );
	
	$defaults['options'] = $options;
	
	$args = wp_parse_args( $args, $defaults );
	$dropdown = fa_dropdown( $args );
	
	return $dropdown;	
}

/**
 * Print out HTML form date elements for editing sliders.
 *
 * @uses $post - the post variable of the current post being edited
 */
function fa_touch_time(){
	global $post, $wp_locale;
	if( !$post ){
		return;
	}
	
	$options = fa_get_slider_options( $post->ID );
	$expires = $options['slider']['expires'];
	if( '0000-00-00 00:00:00' != $expires ){
		$dd = mysql2date( 'd', $expires, false );
		$mm = mysql2date( 'm', $expires, false );
		$yy = mysql2date( 'Y', $expires, false );
		$hh = mysql2date( 'H', $expires, false );
		$ii = mysql2date( 'i', $expires, false );
		$ss = mysql2date( 's', $expires, false );		
	}else{
		$time_adj = current_time('timestamp');		
		$dd = gmdate( 'd', $time_adj ); // day
		$mm = gmdate( 'm', $time_adj ); // month
		$yy = gmdate( 'Y', $time_adj ); // year
		$hh = gmdate( 'H', $time_adj ); // hour
		$ii = gmdate( 'i', $time_adj ); // minute
		$ss = gmdate( 's', $time_adj ); // second
	}
	
	$month = "<select " . 'id="exp_mm" ' . "name=\"exp_mm\">\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		$month .= "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm, false ) . '>';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		$month .= sprintf( __( '%1$s-%2$s', 'fapro' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" id="exp_dd" name="exp_dd" value="' . $dd . '" size="2" maxlength="2"' . ' autocomplete="off" />';
	$year = '<input type="text" id="exp_yy" name="exp_yy" value="' . $yy . '" size="4" maxlength="4"' . ' autocomplete="off" />';
	$hour = '<input type="text" id="exp_hh" name="exp_hh" value="' . $hh . '" size="2" maxlength="2"' . ' autocomplete="off" />';
	$minute = '<input type="text" id="exp_ii" name="exp_ii" value="' . $ii . '" size="2" maxlength="2"' . ' autocomplete="off" />';

	echo '<div class="timestamp-wrap">';
	/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
	printf( __( '%1$s %2$s, %3$s @ %4$s : %5$s', 'fapro' ), $month, $day, $year, $hour, $minute );

	echo '</div><input type="hidden" name="exp_ss" value="' . $ss . '" />';

	echo "\n\n";
	foreach ( array('mm', 'dd', 'yy', 'hh', 'ii') as $timeunit ) {
		echo '<input type="hidden" id="curr_exp_' . $timeunit . '" name="curr_exp_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";		
	}
?>
<input type="checkbox" value="1" name="exp_ignore" id="exp_ignore" /><label for="exp_ignore" title="<?php echo esc_attr( __('If checked, will remove the slider expiration date.', 'fapro') );?>"><?php _e('No expiration date', 'fapro');?></label>
<p>
<a href="#edit_exp_timestamp" class="save-exp-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
<a href="#edit_exp_timestamp" class="cancel-exp-timestamp hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
</p>
<?php 	
}

/**
 * Generates an iframe link request from a given admin menu slug
 * @param string $slug - admin menu slug
 * @param bool $echo
 */
function fa_iframe_admin_page_url( $slug, $args = array(), $echo = true ){
	
	$args = array_merge( array(
		'fapro_inline' => 'true'
	), $args );
	
	$page_url = menu_page_url( $slug, false );
	if( defined('DOING_AJAX') && DOING_AJAX ){
		$page_url = 'admin.php?page=' . $slug;		
	}
	
	$url = add_query_arg( $args, $page_url);
	
	if( $echo ){
		echo $url;
	}	
	return $url;
	
}

/**
 * Outputs a formatted post status from given DB post status
 * @param string $status - status of post as returned from database
 */
function fa_output_post_status( $status ){
	switch ( $status ) {
		case 'private':
			_e('Privately Published', 'fapro');
			break;
		case 'publish':
			_e('Published', 'fapro');
			break;
		case 'expired':
			_e('Expired', 'fapro');
			break;	
		case 'future':
			_e('Scheduled', 'fapro');
			break;
		case 'pending':
			_e('Pending Review', 'fapro');
			break;
		case 'draft':
		case 'auto-draft':
			_e('Draft', 'fapro');
			break;
	}
}

/**
 * Outputs a formatted post date from the database date
 * @param string $date - mysql date
 */
function fa_output_post_date( $date, $echo = true ){
	$datef = __( 'M j, Y @ G:i' );
	$d = date_i18n( $datef, strtotime( $date ) );
	if( $echo ){
		echo $d;
	}
	return $d;
}

/**
 * Outputs the registered post name of a given post type
 * @param string $post_type
 */
function fa_output_post_type( $post_type ){
	if( !post_type_exists( $post_type ) ){
		return;
	}
	$obj = get_post_type_object( $post_type );
	echo $obj->labels->singular_name;
}

/**
 * Display an icon based on the post status
 * @param string $post_status - DB post status
 */
function fa_video_icon( $options ){
	if( isset( $options['video']['video_id'] ) && isset( $options['video']['source'] ) ){
		if( !empty( $options['video']['video_id'] ) && !empty( $options['video']['source'] ) ){
			$class = 'dashicons-format-video';
		}		
	}
	
	if( isset( $class ) ){
	?>
	<i class="dashicons <?php echo $class?>"></i>
	<?php 
	}	
}

/**
 * Display a warning for post statuses pending, future and draft
 * 
 * @param obj $post
 * @param string $before
 * @param string $after
 */
function fa_post_status_message( $post, $before, $after ){
	$message = false;
	switch( $post->post_status ){
		case 'pending':
			$message = __('<strong>Warning! </strong> Slide not visible until post is published.', 'fapro');
		break;
		case 'future':
			$message = sprintf( __('<strong>Warning!</strong> Slide not visible until %s.', 'fapro'), fa_output_post_date( $post->post_date, false ) );
		break;	
		case 'draft':
			$message = __('<strong>Warning!</strong> Slide not visible until post published.', 'fapro');
		break;
		case fa_status_expired():
			$message = __('<strong>Warning!</strong> Slide is expired.', 'fapro');
		break;	
	}
	if( $message ){
		echo $before . $message . $after;	
	}	
}

/**
 * Displays the HTML used in admin area to display the manually
 * selected mixed posts.
 * 
 * @param int/obj $post
 */
function fa_slide_panel( $post, $slider_id ){
	if( is_numeric( $post ) ){
		$post = get_post( $post );
	}
	
	if( !$post ){
		return ;
	}
	
	$options = fa_get_slide_options( $post->ID );
	$slide_title = $post->post_title;
	$slide_content = $post->post_content;
	if( $post->post_type != fa_post_type_slide() ){
		if( !empty( $options['title'] ) && $post->post_title != $options['title'] ){
			$slide_title = $options['title'];
			$post_title = $post->post_title;			
		}
		if( !empty( $options['content'] ) ){
			$slide_content = $options['content'];
		}		
	}
?>
<div class="fa-slide <?php echo esc_attr( $post->post_status );?> " id="post-<?php echo $post->ID;?>" data-post_id="<?php echo $post->ID;?>">
	<a href="<?php fa_iframe_admin_page_url( 'fa-post-slide-edit', array('post_id' => $post->ID, 'slider_id' => $slider_id ) );?>" id="fa-slide-edit-<?php echo $post->ID;?>" class="fapro-modal-trigger fa-slide-edit" data-target="fapro-modal" data-slide_id="<?php echo $post->ID;?>" data-slider_id="<?php echo $slider_id;?>" data-type="mixed"><i class="dashicons dashicons-admin-generic"></i></a>
	<a href="#" id="fa-slide-remove-<?php echo $post->ID;?>" class="fa-slide-remove"><i class="dashicons dashicons-dismiss"></i></a>
	<div class="slide-inside">
		<h3>
			<?php fa_video_icon( $options );?>	
			<a href="<?php echo get_edit_post_link( $post->ID, '' );?>" target="_blank"><?php echo wp_trim_words( $slide_title, 6, '...' );?></a>
		</h3>
		<div class="slide-details">
			<ul>
				<?php if( isset( $post_title ) ):?>
				<li><strong><?php _e('Post title', 'fapro');?>:</strong> <?php echo wp_trim_words( $post_title, 3, '...' );?></li>
				<?php endif;?>
				<li><strong><?php _e('Post status', 'fapro');?>:</strong> <?php fa_output_post_status( $post->post_status );?></li>
				<li><strong><?php _e('Post date', 'fapro');?>:</strong> <?php fa_output_post_date( $post->post_date );?></li>
				<li><strong><?php _e('Post type', 'fapro');?>:</strong> <?php fa_output_post_type( $post->post_type );?></li>
				<?php 
    			    /**
    			     * Allow display of other slide details
    			     * @param $post - current post object
    			     */
    			    do_action( '_fa_slide_panel', $post );
    			?>
				<li>
					<strong><?php _e('Image', 'fapro');?>:</strong>
					<?php 
						require_once fa_get_path( 'includes/templating.php' );
						$image_id = get_the_fa_image_id( $post->ID );
						if( !$image_id ){
							if( !empty( $options['temp_image_url'] ) ){
								$image_url = $options['temp_image_url'];
							}			
						}else{
							$image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							if( $image ){
								$image_url = $image[0];					
							}		
						}
						if( isset( $image_url ) && $image_url ):
					?>
					<img src="<?php echo $image_url?>" />
					<?php else:?>
					<?php _e('none', 'fapro');?>	
					<?php endif;?>
				</li>
				<?php fa_post_status_message( $post, '<li class="warning">', '</li>' );?>			
			</ul>
			<input type="hidden" name="slides[posts][]" value="<?php echo $post->ID;?>" />
		</div>	
	</div>		
</div>
<?php
}

/**
 * Displays the HTML used in admin area to display the manually
 * selected images.
 * 
 * @param int/obj $post
 */
function fa_image_panel( $post, $slider_id ){
	if( is_numeric( $post ) ){
		$post = get_post( $post );
	}
	
	if( !$post || 'attachment' != $post->post_type || 'image' != substr( $post->post_mime_type, 0, 5 ) ){
		return;
	}
	// get image thumbnail
	$thumbnail = wp_get_attachment_image_src( $post->ID, 'thumbnail' );
	if( !$thumbnail ){
		return;
	}
	
	$options = fa_get_slide_options( $post->ID );
	$slide_title = $post->post_title;
	if( $post->post_type == 'attachment' ){
		if( !empty( $options['title'] ) && $post->post_title != $options['title'] ){
			$slide_title = $options['title'];					
		}				
	}
	
	// get full image
	$full = wp_get_attachment_image_src( $post->ID, 'full' );	
	?>
<div class="fa-slide-image" id="attachment-<?php echo $post->ID;?>" data-post_id="<?php echo $post->ID;?>">
	<a href="<?php fa_iframe_admin_page_url( 'fa-post-slide-edit', array( 'post_id' => $post->ID, 'slider_id' => $slider_id ) );?>" id="fa-slide-edit-<?php echo $post->ID;?>" class="fapro-modal-trigger fa-slide-edit" data-target="fapro-modal" data-slide_id="<?php echo $post->ID;?>" data-slider_id="<?php echo $slider_id;?>" data-type="image"><i class="dashicons dashicons-admin-generic"></i></a>
	<a href="#" id="fa-image-remove-<?php echo $post->ID;?>" class="fa-image-remove"><i class="dashicons dashicons-dismiss"></i></a>
	<h3>
		<?php if( !empty( $slide_title ) ):?>
			<a href="<?php echo get_edit_post_link( $post->ID, '' );?>" target="_blank"><?php echo wp_trim_words( $slide_title, 6, '...');?></a>
		<?php else:?>
			<span class="no-image-title"><i><?php _e('no title...', 'fapro');?></i></span>
		<?php endif;?>	
	</h3>
	<img src="<?php echo $thumbnail[0];?>" width="150" height="150" />	
	<div class="slide-details">
		<ul>
			<li><strong><?php _e('Size', 'fapro');?></strong>: <?php echo $full[1] . 'x' . $full[2];?> </li>		
		</ul>
		<input type="hidden" name="slides[images][]" value="<?php echo $post->ID;?>" />
	</div>
</div>
	<?php
}

/**
 * Outputs the HTML for a slider under Automatic placement
 * @param int/object $slider
 */
function fa_slider_area_output( $slider ){
	if( is_numeric( $slider ) ){
		$slider = get_post( $slider );
	}
	// allowed post statuses
	$statuses = array('publish');
	// check the slider
	if( !$slider || !in_array( $slider->post_status, $statuses ) ){
		return;		
	}
	
	$areas = fa_get_slider_options( $slider->ID, 'display' );

	$title = empty( $slider->post_title ) ? '<strong class="no-name">('. __('no title', 'fapro') .')</strong>' : $slider->post_title;
	
	$output = array();
	if( $areas['everywhere'] ){
		$output[] = __('Everywhere', 'fapro');
	}else{
		if( $areas['home'] ){
			$output[] = __( 'Homepage', 'fapro' );
		}
		
		if( $areas['all_pages'] ){
			$output[] = __( 'All single post/pages', 'fapro' );
		}else{
			if( $areas['posts'] ){
				$count = 0;
				foreach( $areas['posts'] as $posts ){
					$count += count( $posts );
				}					
				$output[] = sprintf( __( '%d posts/pages', 'fapro' ), $count );
			}
		}

		if( $areas['all_categories'] ){
			$output[] = __( 'All archive pages', 'fapro' );
		}else{
			if( $areas['tax'] ){
				$count = 0;
				foreach( $areas['tax'] as $categories ){
					$count += count( $categories );
				}					
				$output[] = sprintf( __( '%d category pages', 'fapro' ), $count );
			}
		}						
	}
	
?>
<div class="widget" id="fa_slider-<?php echo $slider->ID;?>">	
	<div class="widget-top">
		<div class="widget-title-action">
			<a class="widget-action hide-if-no-js" href="#available-widgets" style="outline:0;"></a>			
		</div>
		<div class="widget-title"><h4><?php echo $title;?><span class="in-widget-title"></span></h4></div>
	</div>	
	<div class="widget-inside">
		<input class="add_new" type="hidden" value="multi" name="add_new" />
		<input class="multi_number" type="hidden" value="0" name="multi_number">
		<div class="widget-content">
			<p><?php _e('Slider set to display on', 'fapro');?>:</p>
			<ul>
			<?php if( $output ):?>
				<li><?php echo implode( '</li><li>' , $output);?></li>
			<?php else:?>
				<li><span style="color:red"><?php _e('Slider not visible. Choose some pages/posts or archive pages to display it on.', 'fapro');?></span></li>
			<?php endif;?>
			</ul>			
		</div>
		<div class="widget-control-actions">
			<div class="alignleft">
				<a class="widget-control-remove" href="#remove"><?php _e('Delete', 'fapro'); ?></a> |
				<a class="widget-control-close" href="#close"><?php _e('Close', 'fapro'); ?></a> |
				<a href="<?php echo get_edit_post_link( $slider->ID );?>" target="_self"><?php _e('Edit slider', 'fapro');?></a>
			</div>
			<br class="clear" />
		</div>
	</div>						
	<div class="widget-description">
		<?php $options = fa_get_slider_options( $slider->ID );?>
		<?php _e('Slider type', 'fapro');?> : <?php echo $options['slides']['type'];?>
	</div>						
</div>
<?php	
}

/**
 * Output the image attached to a slide
 * @param int $post_id
 */
function the_fa_slide_image( $post_id ){
	$post_id = absint( $post_id );
	$options = fa_get_slide_options( $post_id );
	
	$image = false;
	if( $options['image'] ){
		$image = wp_get_attachment_image( $options['image'], 'thumbnail' );		
	}
	// always enqueue the media gallery scripts
	wp_enqueue_media();
	fa_load_admin_script('media-gallery');	
?>	
<div id="fa-selected-images">
<?php if( $image ):?>
	<div class="fa-slide-image" data-post_id="<?php echo $options['image']?>">		
		<?php
			$args = array(
				'page_title' => __('Select image to use in slide', 'fapro'),
				'button_text' => $image,
				'select_multiple' => false,
				'class'				=> 'fa-img'
			);
			fa_media_gallery( $args );
		?>			
	</div>
	<a href="#" id="fa-remove-slide-image" data-post_id=<?php echo $post_id;?>><?php _e('Remove image', 'fapro');?></a>	
<?php else:// show the image select button?>
	<?php 
		$args = array(
			'page_title' => __('Select image to use in slide', 'fapro'),
			'button_text' => __('Select image', 'fapro'),
			'select_multiple' => false
		);
		fa_media_gallery( $args );
	?>
<?php endif;// if($image)?>
</div>
<?php	
}// the_fa_slide_image

/**
 * Output the image attached to a slide
 * @param int $post_id
 */
function the_default_slider_image( $post_id, $image_id = false ){
	$post_id = absint( $post_id );
	$options = fa_get_slider_options( $post_id, 'content_image' );
	
	$image = false;
	$image_id = is_numeric( $image_id ) ? $image_id : $options['default_image'];
	
	if( $image_id != 0 ){
		$image = wp_get_attachment_image( $image_id, 'thumbnail' );		
	}
	// always enqueue the media gallery scripts
	wp_enqueue_media();
	fa_load_admin_script('media-gallery');	
?>	
<div class="fa-default-slide-image" data-post_id="<?php echo $image_id?>">		
	<?php
		$args = array(
			'name' 				=> 'default_image',
			'id'				=> 'default-image',
			'page_title' 		=> __('Select default image', 'fapro'),
			'button_text' 		=> $image ? $image : __('Set image', 'fapro'),
			'select_multiple' 	=> false, // allows multiple images selection
			'class'				=> $image ? '' : 'button',
			'update_elem'		=> '#fa-slides-default-image', // the element ID that should be updated with the response from Ajax after selecting images
			'append_response'	=> true, // append response to $update_elem element ID
			'attributes'		=> 'data-ajax_action="fa_default_slides_image"' // extra attributes
		);
		fa_media_gallery( $args );
	?>
	<input type="hidden" name="content_image[default_image]" value="<?php echo $image_id;?>" />				
</div>
<?php if( $image ):?>
	<a href="#" id="fa-remove-default-slider-image" data-post_id=<?php echo $post_id;?>><?php _e('Remove image', 'fapro');?></a>	
<?php endif;?>	

<?php	
}// the_fa_slide_image


/***********************************************************************
 * Slideshow themes management
 ***********************************************************************/
/**
 * Get registered slideshow themes
 */
function fa_get_themes(){
	global $fa_theme_manager;
	if( !class_exists('FA_Themes_Manager') ){
		require_once fa_get_path('includes/admin/libs/class-fa-themes-manager.php');
		$fa_theme_manager = new FA_Themes_Manager();
	}
	if( !$fa_theme_manager ){
		$fa_theme_manager = new FA_Themes_Manager();
	}
	
	$themes = $fa_theme_manager->get_themes();
	return $themes;
}

/**
 * Get a theme details
 * @param string $theme
 */
function fa_get_theme( $theme ){
	$themes = fa_get_themes();
	if( array_key_exists( $theme , $themes) ){
		return $themes[ $theme ];
	}
	return false;
}

/**
 * Get theme settings for plugin optional fields
 * @param string $theme
 */
function fa_get_themes_fields(){
	$themes = fa_get_themes();
	$result = array();
	
	foreach( $themes as $theme => $details ){
		$result[ $theme ] = isset( $details['theme_config']['fields'] ) ? (array) $details['theme_config']['fields'] : array();
	}
	return $result;	
}

/**
 * Displays data attributes for a given field stating which themes should enable/disable the field by default
 * @param string $field
 */
function fa_optional_field_data( $field ){
	$themes = fa_get_themes_fields();
	$disable = array();
	$enable = array();
	foreach( $themes as $theme => $fields ){
		if( isset( $fields[ $field ] ) ){
			if( $fields[ $field ] ){
				$enable[] = $theme;
			}else{
				$disable[] = $theme;
			}
		}else{
			$enable[] = $theme;
		}
	}
	
	$output = ' data-theme_enable="' . implode(',', $enable) . '"';
	$output.= ' data-theme_disable="' . implode(',', $disable) . '"';
	echo $output;
}

/**
 * Check if a field is enabled/disabled by the slider theme
 * @param string $theme
 * @param string $field
 */
function fa_theme_field_enabled( $theme, $field ){
	$theme = fa_get_theme( $theme );
	if( !$theme ){
		return true;
	}
	
	if( isset( $theme['theme_config']['fields'][ $field ] ) ){
		return (bool) $theme['theme_config']['fields'][ $field ] ;
	}	
	return true;
}

/**
 * Displays a dropdown of available slideshow themes
 * @param array $args
 */
function fa_themes_dropdown( $args = array() ){
	$defaults = array(
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'use_keys'	=> true,
		'hide_if_empty' => true,
		'show_option_none' => __('No themes', 'fapro'),
		'select_opt'	=> __('Choose theme', 'fapro'),
		'echo' => true
	);
	
	if( isset( $args['options'] ) )
		unset( $args['options'] );
	
	$themes = fa_get_themes();
	$options = array();
	foreach( $themes as $theme => $theme_data ){
		$options[ $theme ] = ucfirst( str_replace('_', ' ', $theme) );
	}
	$defaults['options'] = $options;
	
	$args = wp_parse_args( $args, $defaults );
	$dropdown = fa_dropdown( $args );
	
	return $dropdown;	
}

/************************************************************
 * Slider preview functionality
 ************************************************************/
/**
 * Outputs a preview link to homepage URL
 * @param array $args
 */
function fa_slider_preview_homepage( $args ){
	$defaults = array(
		'post_id' 	=> false,
		'theme' 	=> false,
		'vars'		=> array(),
		'echo'		=> true
	);
	
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );
	if( !$post_id ){
		echo '#';
	}
	
	$params = array(
		'slider_id' 		=> $post_id,
		'theme'				=> $theme,
		'fa_slider_preview' => true
	);
	if( is_array( $vars ) ){
		$params = array_merge( $params, $vars );
	}
	
	$homepage_url = add_query_arg( $params, home_url() );
	
	$homepage_url = wp_nonce_url( $homepage_url, 'fa-slider-theme-preview', 'fa-preview-nonce' );	

	if( $echo ){
		echo $homepage_url;
	}
	return $homepage_url;	
}

/**
 * Returns the slider post ID set for theme editor preview
 */
function theme_editor_preview_post_id(){
	$options = fa_get_options('theme_editor');
	$post_id = false;
	if( $options['slider_id'] ){
		$post = get_post( $options['slider_id'] );
		if( $post ){
			$post_id = $options['slider_id'];						
		}				
	}
				
	if( !$post_id ){			
		$post = get_posts( 
			array(
				'post_type' 	=> fa_post_type_slider(), 
				'post_status' 	=> 'any',
				'numberposts'	=> 1
			) 
		);
		if( $post ){
			$post_id = $post[0]->ID;
		}
	}
	return $post_id;
}

/**
 * Returns the area ID set for theme editor preview
 */
function theme_editor_preview_area_id(){
	$area = 'loop_start';
	$options = fa_get_options('theme_editor');
	if( $options['area_id'] ){
		$areas = fa_get_options('hooks');
		if( is_array( $areas ) && array_key_exists( $options['area_id'] , $areas) ){
			$area = $options['area_id'];
		}
	}
	return $area;
}

/************************************************************
 * Slider theme functions.php specific functions
 * These functions are designed to be used only in
 * slider themes functions.php file.
 ************************************************************/
/**
 * This function should only be called from slider theme folder.
 * Returns a specific key for a given slider theme functions file path.
 * The key is the actual name of the folder that contains all slider theme files.
 * 
 * @param string $file - absolute path to slider theme functions.php file
 */
function fa_get_theme_key( $file ){
	$key = basename( dirname( $file ) );
	return $key;
}

/**
 * Gets the options implemented by a theme based on the absolute path
 * of the functions.php slider theme file
 * 
 * @param string $file - absolute path to slider theme functions.php file
 */
function fa_get_theme_options( $file, $post ){
	$key = fa_get_theme_key( $file );
	if( is_object( $post ) ){
		$post_id = $post->ID;
	}else{
		$post_id = absint( $post );
	}
	
	$options = fa_get_slider_options( $post_id, 'themes_params' );
	if( $options ){
		if( isset( $options[ $key ] ) ){
			return $options[ $key ];
		}
	}	
	return false;	
}

/**
 * Returns the variable name that is compatible with the plugin way of saving variables
 * @param string $var_name
 * @param string $file
 */
function fa_theme_var_name( $var_name, $file, $echo = true ){
	$name = esc_attr( $var_name );
	$key = fa_get_theme_key( $file );
	$output = 'themes_params[' . $key . '][' . $name . ']';
	if( $echo ){
		echo $output;
	}
	
	return $output;	
}

/**
 * Check the existance of the theme CSS customization function.
 * @param string $theme - theme name
 */
function fa_theme_is_customizable( $theme ){
	return function_exists( 'fa_theme_css_' . $theme );
}

/***********************************
 * SLIDERS
 ***********************************/

/**
 * Returns all sliders
 */
function fa_get_sliders( $status = 'publish' ){	
	$args = array(
		'post_type' => fa_post_type_slider(),
		'post_status' => $status,
	    'posts_per_page' => -1
	);
	$sliders = get_posts( $args );	
	return $sliders;	
}
 

/************************************
 * Plugin update requests
 ************************************/
/**
 * Make requests to retrieve information 
 * @param string $request_url
 * @param array $post_vars
 */
function fa_make_request( $request_url, $post_vars = false ){
	if( !defined( 'CODEFLAVORS_CLIENT_CODE' ) || !defined( 'CODEFLAVORS_UPDATES_URL' ) ){
		$error = new WP_Error();
		$error->add(
			'fa-missing-data', 
			__('There is some data missing from your installation. Please download a fresh copy from CodeFlavors and reinstall the plugin.', 'fapro')
		);		
		return $error; 	
	}
	
	$options = array(
		'method' 	=> 'POST', 
		'timeout' 	=> 3, 
		'body' 		=> $post_vars
	);
   	$options['headers'] = array(
    	'Content-Type' 	=> 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
        'User-Agent' 	=> 'WordPress/' . get_bloginfo('version'),
        'Referer' 		=> get_bloginfo('url'),
   		'Post'			=>''
    );
   	$response = wp_remote_request( $request_url, $options ); 
   	return $response;
}

/**
 * Register license
 */
function register_fa_license( $license_code ){
	if( !defined( 'CODEFLAVORS_CLIENT_CODE' ) || !defined( 'CODEFLAVORS_UPDATES_URL' ) ){
		$error = new WP_Error();
		$error->add(
			'fa-missing-data', 
			__('Plugin license activation could not be made because your installation is missing some data. Please download a fresh copy from CodeFlavors and reinstall the plugin.', 'fapro')
		);		
		return $error; 	
	}
	
	$params = array(
		'code'			=> $license_code,
		'blog'			=> home_url(),
		'client_code'	=> CODEFLAVORS_CLIENT_CODE
	);
	$response = fa_make_request(
		CODEFLAVORS_UPDATES_URL.'/notifications/license-activation/', 
		$params
	);
	
	return $response;
}

/**
 * Check for plugin updates
 */
function fa_check_details( $force = false ){
	
	if( !defined( 'CODEFLAVORS_CLIENT_CODE' ) || !defined( 'CODEFLAVORS_UPDATES_URL' ) ){
		$error = new WP_Error();
		$error->add(
			'fa-missing-data', 
			__('Plugin automatic updates are disabled because your installation is missing some data. Please download a fresh copy from CodeFlavors and reinstall the plugin.', 'fapro')
		);		
		return $error; 	
	}
	
	$raw_response = get_transient( 'fa_version' );	
	if( $force ){
		$raw_response = false;
	}	
	if( !$raw_response ){
    	$settings = fa_get_options( 'license' );
		$request_params = array(
			'version'		=> FA_VERSION,
			'url'			=> home_url(),
			'key'			=> $settings['license_key'],
			'client_code'	=> CODEFLAVORS_CLIENT_CODE
		);		
		$request_url 	= CODEFLAVORS_UPDATES_URL . '/notifications/update-notifications/';
		$raw_response 	= fa_make_request( $request_url, $request_params );
		
		if( is_wp_error( $raw_response ) ){
			return $raw_response;
		}	
		set_transient( 'fa_version', $raw_response, 43200 ); //caching for 12 hours
    }
	
    if ( !$raw_response ){
    	return array(
    		'ok' 		=> '', 
    		'version' 	=> '',
    		'url' 		=> ''
    	);
    }else{
    	$response = wp_remote_retrieve_body( $raw_response );
    	$arr = explode( '||', $response );
        return array(
        	'ok' 		=> $arr[0], 
        	'version' 	=> $arr[1], 
        	'url' 		=> $arr[2],
        	'id'		=> $arr[3]
        );
    }	
}