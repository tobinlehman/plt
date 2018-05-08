<?php
class FA_Slider{
	/**
	 * Stores the post slider
	 */
	private $slider = false;
	/**
	 * Stores the options of the slider
	 */
	private $options = false;
	/**
	 * Transient expiration. For sliders made from posts, the cache
	 * is set by the script to 5 minutes
	 * @var int - expiration time in seconds
	 */
	private $cache_time = 43200; // 12H	
	/**
	 * Transients prefix to identify the slides transient.
	 * If changing the prefix, also change it in class FA_Admin 
	 * that stores it in variable $slider_transient_prefix.
	 * 
	 * @var string - prefix for slider transient
	 */
	private $transient_prefix = 'fa_slider_';
	/**
	 * Store the slider transient details.
	 * @var array(
	 * 		$slider - post object for slider post
	 * 		$slider_output - the HTML output for the slider
	 * 		$options - the slider options
	 * 		$time - mysql time of the cache
	 * 		$time_gmt - mysql GMT time of the cache
	 * 		$timespamt - timestamp of cache
	 * )
	 */
	private $transient;
	/**
	 * Stores retrieved slides 
	 */
	private $slides = false;
	
	/**
	 * Constructor. Takes as argument the slider ID
	 * @param int $slider_id
	 */
	public function __construct( $slider_id ){
		$this->timer_start = microtime( true );
		//* Check if cache is enabled and see if there's an available cache for the slider
		$settings = fa_get_options('settings');
		if( $settings['cache'] && !fa_is_preview() ){
			$transient = get_transient( $this->transient_prefix . $slider_id );
			if( $transient && $transient['options']['slider']['cached'] && !fa_is_preview() ){
				$this->transient = $transient;
				$this->slider  	= $transient['slider'];
				$this->options 	= $transient['options'];
				return;
			}
		}	
		
		//*/		
		// get the slider post
		$post = get_post( $slider_id );
		
		if( !$post || $post->post_type != fa_post_type_slider() ){
			return;
		}
		// check post status, allow any if preview
		if( !fa_is_preview() && 'publish' != $post->post_status ){
			return;
		}
		
		// if preview, check other sliders status that might be displayed into the page and allow only published ones 
		if( fa_is_preview() ){
			$preview_id = absint( $_GET['slider_id'] );
			if( $slider_id != $preview_id && 'publish' != $post->post_status ){
				return false;
			}						
		}
		
		// store the slider
		$this->slider = $post;	
		
		// If preview, get the options from the revision, if any is available
		if( fa_is_preview() && !fa_is_theme_edit_preview() ){
			$statuses = array( 'future', 'publish', 'draft' );
			if( in_array( $post->post_status, $statuses ) ){
				$children = get_children( array(
					'post_parent' 		=> $slider_id,
					'post_type' 		=> 'revision',
					'orderby'          	=> 'post_date',
					'order'            	=> 'DESC',
					'posts_per_page'	=> 1
				));
				if( $children ){
					$revision	= array_pop( $children );
					$options	= fa_get_slider_options( $revision->ID );
					$this->options = $options;
				}
			}
		}		
		
		// filter the options to push the theme from preview
		if( fa_is_preview() ){
			if( isset( $_GET['slider_id'] ) && $slider_id == $_GET['slider_id'] ){			
				// filter the slider options to set the theme from preview
				add_filter('fa_get_slider_options', array( $this, 'modify_preview_options' ), 1, 3);
			}	
			// change the transient to preview - will force the slider to display not cached
			$this->transient = 'preview';
		}
		
		// if options aren't already set by a preview, set them now
		if( !$this->options ){
			// get the slider options
			$this->options = fa_get_slider_options( $post->ID );
		}
		
		// cache slides made from posts only for 5 minutes
		if( 'post' == $this->options['slides']['type'] ){
			$this->cache_time = 5 * MINUTE_IN_SECONDS;
		}
		
		// get the slides
		$this->slides = $this->get_slides();		
	}
	
	/**
	 * Returns the slides according to slider content settings
	 */
	private function get_slides(){
		$result 	= array();
		// content selection is stored on key slides
		$options 	= $this->options['slides'];
		switch( $options['type'] ){
			// posts query
			case 'post':
				$post_types = $this->_allowed_post_types();				
				$args = array(
					'post_status' 			=> 'publish',
					'post_type'				=> $post_types,
					'numberposts'			=> absint( $options['limit'] ),
					'order'					=> 'DESC',
					'ignore_sticky_posts' 	=> true			
				);
				
				/**
				 * Allow extra query arguments to be set on post query
				 */
				$extra_args = apply_filters( '_fa_slider_posts_query_args' , array() );
				// recreate args array
				$args = array_merge( $extra_args, $args );
				
				if( !$post_types ){
					break;
				}
				
				// tax query
				foreach( $args['post_type'] as $post_type ){
					$taxonomies = get_object_taxonomies( $post_type );
					foreach( $options['tags'] as $tax => $terms  ){
						if( !in_array( $tax , $taxonomies) || !$terms ){
							continue;
						}						
						// add the taxonomies
						$args['tax_query'][] = array(
							'taxonomy' 	=> $tax,
							'field'		=> 'id',
							'terms'		=> $terms
						);						
					}					
				}
				
				// add relation parameter only if query is made for more than one taxonomy
				if( isset( $args['tax_query'] ) && count( $args['tax_query'] ) > 1 ){
					$args['tax_query']['relation'] = 'OR';
				}
				
				// set orderby parameter
				switch( $options['orderby'] ){
					case 'date':
					default:	
						$args['orderby'] = 'post_date';						
					break;
					case 'comments':
						$args['orderby'] = 'comment_count post_date';
					break;
					case 'random':
						$args['orderby'] = 'rand';
					break;						
				}
				// set author
				if( isset( $options['author'] ) && 0 != $options['author'] ){
					$args['author'] = absint( $options['author'] );
				}	

				$slides = get_posts( $args );
				
				/**
				 * Filter on found slides
				 *
				 * @param $result - array of posts that the slider is made of
				 * @param $this->slider->ID - slider ID being processed
				 */
				$result = apply_filters( 'fa_filter_slider_post_slides' , $slides, $this->slider->ID );
				
				if( !is_array( $result ) ){
				    $result = array();
				}else{
				    $result = array_values( $result );
				}
				
				/**
				 * Action when retrieving posts. Useful for third party compatibility.
				 * 
				 * @param $result - array of posts that the slider is made of
				 * @param $this->slider->ID - slider ID being processed
				 */
				do_action( 'fa_slider_post_slides', $result, $this->slider->ID );
				
			break;
			// mixed content query
			case 'mixed':
				// if no posts selected, break
				if( !$options['posts'] ){
					break;					
				}
				$args = array(
					'post_status'			=> 'publish',
					'post_type'				=> $this->_allowed_post_types(),
					'posts_per_page' 		=> -1,
					'nopaging'				=> true,
					'ignore_sticky_posts' 	=> true,
					'offset'				=> 0,
					'include'				=> (array) $options['posts']					
				);
				
				/**
				 * Allow extra query arguments to be set on post query
				 */
				$extra_args = apply_filters( '_fa_slider_mixed_content_query_args' , array() );
				// recreate args array
				$args = array_merge( $extra_args, $args );
				
				$posts = get_posts( $args );
				
				$result = array();
				if( $posts ){
					foreach( $posts as $post ){
						$key = array_search( $post->ID, $options['posts'] );
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
				$result = apply_filters( 'fa_manual_slider_posts' , $result, $this->slider->ID );
				if( !is_array( $result ) ){
					$result = array();
				}else{
					$result = array_values( $result );
				}
				
			break;
			// images query
			case 'image':
				// if no images selected, break
				if( !$options['images'] ){
					break;
				}
				
				$args = array(
					'post_status' 			=> 'inherit',
					'post_type' 			=> 'attachment',
					'posts_per_page'		=> -1,
					'nopaging'				=> true,
					'ignore_sticky_posts' 	=> true,
					'offset'				=> 0,
					'include'				=> (array) $options['images'],
					'suppress_filters'		=> true
				);
				$posts = get_posts( $args );
				if( $posts ){
					$result = array();
					foreach( $posts as $post ){
						$key = array_search( $post->ID, $options['images'] );
						$result[ $key ] = $post;
					}
				}
				// arrange the values according to settings
				ksort($result);	
				// regenerate the keys to start from 0 ascending
				$result = array_values( $result );			
			break;	
		}		
		return $result;
	}
	
	/**
	 * Returns the current slider allowed post type.
	 * Verifies the slider settings against the allowed post type in plugin setting.
	 * Verifies that post types exist.
	 * 
	 * @return array
	 */
	private function _allowed_post_types(){
		// get the allowed post types from plugin settings
		$options = fa_get_options('settings');
		// merge the default post type with the allowed post types
		$allowed = array_unique( array_merge( array( 'post' ), (array)$options['custom_posts'] ) );
		// get the post types set on slider
		$set 	 = (array)$this->options['slides']['post_type'];
		// start the result
		$result	 = array();
		foreach( $set as $k => $post_type ){
			if( post_type_exists( $post_type ) && in_array( $post_type, $allowed ) ){
				$result[] = $post_type;
			}
		}
		// for mixed content sliders, add the slide post type and page post type
		if( 'mixed' == $this->options['slides']['type'] ){
			$result   = (array)$allowed;
			$result[] = fa_post_type_slide();
			$result[] = 'page';
		}
		
		return $result;
	}
	
	/**
	 * Displays the slider. If $echo, it will output the contents 
	 * 
	 * @param bool $echo
	 */
	public function display( $echo = true ){
		if( ( !$this->slider || ( !$this->slides ) && ( !$this->transient || 'preview' == $this->transient ) ) ){
			return;
		}
		
		// set the expiration
		if( '0000-00-00 00:00:00' != $this->options['slider']['expires'] ){
			$post_date 			= $this->slider->post_date;
			$expiration_date 	= $this->options['slider']['expires'];
			$status				= $this->slider->post_status;
			
			// make the slider expire
			if( strtotime( $expiration_date ) < current_time('timestamp') && fa_status_expired() != $status ){
				$args = array(
					'ID' => $this->slider->ID,
					'post_status' => fa_status_expired()
				);
				wp_update_post( $args );
				return;
			}
		}
				
		$theme = $this->options['theme'];
		$theme_file = $theme['details']['display'];
		if( !file_exists( $theme_file ) ){
			// trigger error just for admins
			if( current_user_can( 'manage_options' ) ){
				trigger_error( sprintf( __('Slider theme <strong>%s</strong> display file could not be found.', 'fapro'), $theme['details']['theme_config']['name']) );
			}	
			return;
		}
		// make ssl friendly theme URL
		if( is_ssl() ){
			$theme['details']['url'] = str_replace( 'http://', 'https://', $theme['details']['url'] );
		}
		
		// load minified stylesheet
		$suffix = defined('FA_CSS_DEBUG') && FA_CSS_DEBUG ? '' : '.min';
		wp_enqueue_style(
			'fa-theme-' . $theme['active'],
			path_join( $theme['details']['url'] , 'stylesheet' . $suffix . '.css'),
			false,
			FA_VERSION
		);
		
		if( isset( $theme['details']['theme_config']['stylesheets'] ) ){
			$extra_styles = (array) $theme['details']['theme_config']['stylesheets'];
			
			/**
			 * Filter that can be used in themes to prevent FontAwesome from being loaded
			 * if the theme already uses it.
			 * @var bool
			 */
			$allow_fa = apply_filters( 'fa-load-font-awesome-css' , true );
			
			// prevent Font Awesome to be loaded if set by the user in admin area
			$options = fa_get_options( 'settings' );
			if( isset( $options['load_font_awesome'] ) && !$options['load_font_awesome'] ){
				$allow_fa = false;
			}
			
			// enqueue font awesome if specified by the theme settings
			if( $allow_fa && isset( $extra_styles['font-awesome'] ) && $extra_styles['font-awesome'] ){
				// enqueue font awesome only if specified by the theme settings
				wp_enqueue_style(
					'font-awesome',
					( is_ssl() ? 'https://' : 'http://' ) . 'maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css',
					array(),
					FA_VERSION
				);
			}
			
			/**
			 * Filter that can be used in themes to prevent jquery ui dialog styling from being loaded
			 * if the theme already implements it.
			 * @var bool
			 */
			$allow_ui_dialog = apply_filters( 'fa-load-jquery-ui-dialog-css' , true );			
			// enqueue jquery ui dialog styling if specified by theme settings
			if( $allow_ui_dialog && isset( $extra_styles['jquery-ui-dialog'] ) && $extra_styles['jquery-ui-dialog'] ){
				fa_load_style('jquery-ui-dialog');
			}	
		}
		
		if( !empty( $this->options['theme']['color'] ) ){
			wp_enqueue_style(
				'fa-theme-' . $theme['active'] . '-' . $theme['color'],
				path_join( $theme['details']['url'] , 'colors/' . str_replace('.min', '', $theme['color'] ) . '.css'),
				array( 'fa-theme-' . $theme['active'] ),
				FA_VERSION
			);
		}
		
		/**
		 * Theme starter dependencies
		 */
		$dependencies = array( 'jquery' );		
		$theme_scripts = isset( $theme['details']['theme_config']['scripts'] ) ? (array) $theme['details']['theme_config']['scripts'] : array();
		/**
		 * Load extra scripts specified in theme details
		 */
		if( $theme_scripts ){
			if( isset( $theme_scripts['jquery-ui-dialog'] ) && $theme_scripts['jquery-ui-dialog'] ){
				// if jquery-ui-dialog is already enqueued, we use that
				if( wp_script_is( 'jquery-ui-dialog', 'enqueued' ) ){
					$jquery_dialog_handle = 'jquery-ui-dialog';
				}else{
					// register our own jquery-ui-dialog
					if( !wp_script_is( 'jquery-ui-dialog-fa', 'registered' ) ){
						// register dialog without 'jquery-ui-resizable', 'jquery-ui-draggable' and 'jquery-ui-position' since they are not used
						global $wp_version;
						if( version_compare($wp_version, '4.1', '>=') ){
							wp_register_script( 'jquery-ui-dialog-fapro' , '/wp-includes/js/jquery/ui/dialog.min.js', array( 'jquery-ui-button' ) );
						}else{
							wp_register_script( 'jquery-ui-dialog-fapro' , '/wp-includes/js/jquery/ui/jquery.ui.dialog.min.js', array( 'jquery-ui-button' ) );	
						}						
					}
					$jquery_dialog_handle = 'jquery-ui-dialog-fapro';
				}
				$dependencies[] = $jquery_dialog_handle;	
			}			
		}
		
		wp_enqueue_script('jquery-ui-widget');
		$script_handles = array('slider', 'accordion', 'carousel', 'jquery-mobile', 'jquery-transit', 'round-timer', /*'froogaloop',*/ 'video-player2' /*'video-player'*/);
		
		// extra theme handles implemented by the theme
		$extra_handles = isset( $theme['details']['theme_config']['extra_scripts']['handles'] ) ? (array)$theme['details']['theme_config']['extra_scripts']['handles'] : array();
		if( $extra_handles ){
			foreach( $extra_handles as $handle ){
				$dependencies[] = $handle;
			}
		}
		
		// theme scripts that should be enqueued
		$enqueue_scripts = isset( $theme['details']['theme_config']['extra_scripts']['enqueue'] ) ? (array)$theme['details']['theme_config']['extra_scripts']['enqueue'] : array();
		if( $enqueue_scripts ){
			foreach( $enqueue_scripts as $handle => $rel_path ){
				wp_register_script(
					$handle,
					$theme['details']['url'] .'/'. ltrim( $rel_path, '/\\' ),
					array( 'jquery' )
				);
				$dependencies[] = $handle;
			}
		}
		
		// when debug is on, load each individual .dev file
		if( defined('FA_SCRIPT_DEBUG') && FA_SCRIPT_DEBUG ){
			/**
			 * Following handles are enqueued by themes that use regular slider script.
			 * These script can be skipped by themes by specifying in theme functions.php
			 * file inside function that passes the details not to embed them.
			 * Dissalowing embed for certain themes can be done with an array like
			 * 'scripts' => array( 'slider' => false )
			 */
			foreach ( $script_handles as $handle ){
				if( !isset( $theme_scripts[ $handle ] ) || $theme_scripts[ $handle ] ){
					$dependencies[] = fa_load_script( $handle );
				}
			}			
		}else{
			/**
			 * Iterate all handles and if one isn't set or is set true, load minimized
			 * scripts file.
			 */
			$load_scripts = false;
			foreach ( $script_handles as $handle ){
				if( !isset( $theme_scripts[ $handle ] ) || $theme_scripts[ $handle ] ){
					$load_scripts = true;
					break;
				}
			}
			if( $load_scripts ){
				// load only the minified file containing all scripts
				$dependencies[] = fa_load_script( '_scripts.min' );
			}
		}		
		
		/**
		 * Filter that allows loading of extra slider scripts. 
		 * Callback functions should pass only the script handle to be loaded.
		 * All script enqueue is done by the callback function.
		 * @var array - array of script handles
		 */
		$load_scripts = apply_filters( 'fa_extra_slider_scripts', array() );
		if( is_array( $load_scripts ) && $load_scripts ){
			$dependencies = array_merge( $load_scripts, $dependencies );
		}
		
		// load theme starter
		$suffix = defined('FA_SCRIPT_DEBUG') && FA_SCRIPT_DEBUG ? '.dev' : '.min';
		wp_enqueue_script(
			'fa-theme-' . $theme['active'] . '-starter',
			path_join( $theme['details']['url'] , 'starter' . $suffix . '.js'),
			$dependencies,
			'',
			true
		);
		
		
		if( $this->transient && 'preview' != $this->transient ){
			$output = $this->transient['slider_output'];
			// include some cache stats on previews
			if( fa_is_preview() || current_user_can( 'manage_options' ) ){
				$output.= '<!-- Slider generated from cache -->';
			}				
		}else{		
			global $slider_id, $fa_slider;
			// global $post
			$slider_id = $this->slider->ID;
			/**
			 * Set the global $fa_slider variable that will be used
			 * in templating functions and other functions.
			 * 
			 * @var object - the post object of the current slider
			 */
			$fa_slider = $this->slider;
			// store the posts on the slider global variable
			$fa_slider->slides = $this->slides;
			// set the current to 0
			$fa_slider->current_slide = -1;
			// set the number of slides
			$fa_slider->slide_count = count( $this->slides );
			
			// create a backup copy of the current post			
			//$p = $post;
			
			/**
			 * @deprecated
			 * For backwards compatibitily, create a variable that holds the slider HTML ID
			 */
			$FA_slider_id = 'fa-slider-' . $this->slider->ID;
			
			// prepare the variables for the theme to use
			//$postslist = $this->slides;
			// include the templating functions
			include_once fa_get_path( 'includes/templating.php' );
			// capture the output
			ob_start();
			include( $theme_file );
			$output = ob_get_clean();
			// give back the initial value of $post
			//$post = $p;			
		}
		
		if( !$this->transient ){
			$settings = fa_get_options('settings');
			if( $settings['cache'] && $this->options['slider']['cached'] ){
				set_transient( $this->transient_prefix . $this->slider->ID , array(
					'slider'		=> $this->slider,
					'slider_output' => $output,
					'options' 		=> $this->options,
					'time'			=> current_time( 'mysql' ),
					'time_gmt'		=> current_time( 'mysql', true ),
					'timestamp'		=> time()
				), $this->cache_time );
			}			
		}
		// include some cache stats on previews
		if( fa_is_preview() || current_user_can( 'manage_options' ) ){
			$output .= '<!-- Slider generated in '.number_format( microtime(true) - $this->timer_start, 5 ).' seconds -->';
		}
		
		// show the edit link on slider output
		if( current_user_can( 'edit_fa_items', $this->slider->ID ) ){
			$settings = fa_get_options( 'settings' );
			$show = (bool) $settings['edit_links'];
			/**
			 * Show slider edit link in front-end output.
			 * 
			 * @var bool - if callback returns false, edit link will be hidden
			 * @var slider_id - id of slider
			 */
			$show = apply_filters( 'fa_show_slider_edit_link', $show, $this->slider->ID );
			if( $show ){
				$edit_link = get_edit_post_link( $this->slider->ID );
				$output .= sprintf( '<a href="%s" title="%s">%s</a>',
					$edit_link,
					esc_attr( __( 'Edit slider', 'fapro' ) ),
					__('Edit slider', 'fapro')
				);
			}	
		}
		
		if( $echo ){
			echo $output;
		}
		
		return $output;
	}
	
	/**
	 * Callback for filter fa_get_slider_options.
	 * When a preview is displayed, the function will overwrite
	 * the slider options with the options passed over $_GET
	 */
	public function modify_preview_options( $options, $key, $slider_id ){
		// make this work only for previews
		if( !fa_is_preview() ){
			return $options;
		}
		// check that is the same slider ID
		if( $slider_id != $this->slider->ID ){
			return $options;
		}
		
		// for theme editor, overwrite some defaults
		if( fa_is_theme_edit_preview() ){
			if( !$key ){
				$options['layout']['class'] = ''; // reset the variation class if any
				$options['layout']['show_title'] = true; // show the slider title if any
				$options['layout']['show_main_nav'] = true;	// display main navigation
				$options['layout']['show_side_nav'] = true; // display second navigation
				$options['js']['auto_slide'] = false; // no autoslide
				$options['slider']['cached'] = false; // no cache
				// show all content
				$options['content_image']['show'] = true;
				$options['content_video']['show'] = true;
				$options['content_title']['show'] = true;
				$options['content_text']['show'] = true;
				$options['content_read_more']['show'] = true;
				$options['content_play_video']['show'] = true;
				$options['content_date']['show'] = true;
				$options['content_author']['show'] = true;
				
				$load_theme = $_GET['theme'];
				$theme 		= fa_get_theme( $load_theme );
				// change the active theme and its details
				$options['theme']['active'] 	= $load_theme;
				$options['theme']['details'] 	= $theme;
				if( isset( $_GET['color'] ) ){
					$options['theme']['color'] = $_GET['color'];
				}
			}else{
				switch( $key ){
					case 'theme':
						$load_theme = $_GET['theme'];
						$theme 		= fa_get_theme( $load_theme );
						// change the active theme and its details
						$options['active'] 	= $load_theme;
						$options['details'] 	= $theme;
						if( isset( $_GET['color'] ) ){
							$options['color'] = $_GET['color'];
						}
					break;	
					case 'layout':
						$options['class'] = '';
						$options['show_title'] = true;
						$options['show_main_nav'] = true;
						$options['show_side_nav'] = true;
					break;
					case 'js':
						$options['auto_slide'] = false;
					break;
					case 'slider':
						$options['cached'] = false;
					break;
					case 'content_image':
					case 'content_video':
					case 'content_title':
					case 'content_text':
					case 'content_read_more':
					case 'content_play_video':
					case 'content_date':
					case 'content_author':
						$options['show'] = true;
					break;	
				}
			}
		}else{
			// On preview, force the review options instead of slider options
			if( !$key ){
				return $this->options;
			}else{
				return $this->options[ $key ];
			}
		}		
		return $options;
	}
	
	/**
	 * Class destructor.
	 */
	public function __destruct(){
		global $fa_slider;
		$fa_slider = false;
	}	
}