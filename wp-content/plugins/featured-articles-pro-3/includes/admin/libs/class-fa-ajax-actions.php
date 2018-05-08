<?php
/**
 * Registers and manages all admin AJAX calls.
 * Used in class FA_Admin()
 */
class FA_Ajax_Actions{
	
	/**
	 * Constructor. Sets all registered ajax actions.
	 */
	public function __construct(){
		// get the actions
		$actions = $this->actions();
		// add wp actions
		foreach( $actions as $action ){
			add_action('wp_ajax_' . $action['action'], $action['callback']);
		}		
	}
	
	/**
	 * Video query callback action.
	 * Queries a video on YouTube or Vimeo and returns its data.
	 */
	public function query_video(){
		$action = $this->get_action_data( 'video_query' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;		
		
		if( !current_user_can( 'edit_post', $_POST['post_id'] ) ){
			wp_die( -1 );
		}
		
		// query the video
		$args = array(
			'source' 	=> !isset( $_POST['video_source'] ) 	? false : $_POST['video_source'],
			'video_id' 	=> !isset( $_POST['video_id'] ) 		? false : $_POST['video_id']
		);
		
		$video = fa_query_video($args);
		// if anything went wrong, get the error message
		if( is_wp_error( $video ) ){
			$message = $video->get_error_message();
			wp_send_json_error( $message );
			die();
		}
		
		// create the response
		$response = array( 'video' => $video );
		
		// set the post thumbnail
		if( ( isset( $_POST['set_thumbnail'] ) && $_POST['set_thumbnail'] ) || ( isset( $_POST['set_slide_img'] ) && $_POST['set_slide_img'] ) ){			
			$attachment_id = $this->import_thumbnail( $post_id, $video );
			
			if( $attachment_id && $_POST['set_thumbnail'] ){
				// set image as featured for current post
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
				// create the return output
				remove_all_filters( 'admin_post_thumbnail_html' );
				$response['thumbnail'] = _wp_post_thumbnail_html( $attachment_id, $post_id );
			}
				
			if( $attachment_id && $_POST['set_slide_img'] ){
				$result = fa_update_slide_options( $post_id , array(
					'image' => $attachment_id					
				));
				
				ob_start();	
				the_fa_slide_image( $post_id );
				$output = ob_get_clean();
				$response['slide_img'] = $output;
			}		
		}

		if( isset( $_POST['set_video'] ) && $_POST['set_video'] ){
			$result = fa_update_slide_options( $_POST['post_id'], array(
				'video' => array(
					'source' 	=> $video['source'],
					'video_id' 	=> $video['video_id'],
					'duration'	=> $video['duration']
				)
			));
			
			if( $result ){
				$options = fa_get_slide_options( $post_id );
				
				ob_start();				
				include fa_metabox_path('slide-video-settings');				
				$output = ob_get_clean();
				$response['video_settings'] = $output;
				
				ob_start();				
				include fa_metabox_path('slide-video-query');				
				$output = ob_get_clean();
				$response['video_query'] = $output;
				
			}			
		};
		
		wp_send_json_success( $response );		
		die();
	}
	
	/**
	 * AJAX callback to import a video image
	 */
	/*
	public function ajax_import_image(){
		$action = $this->get_action_data( 'import_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;		
		
		if( !current_user_can( 'edit_post', $_POST['post_id'] ) ){
			wp_die( -1 );
		}
		
		$options = fa_get_slide_options( $post_id );
		if( isset( $options['video']['video_id'] ) && isset( $options['video']['source'] ) ){
			if( !empty( $options['video']['video_id'] ) && !empty( $options['video']['source'] ) ){
				// query the video
				$args = array(
					'source' 	=> $options['video']['source'],
					'video_id' 	=> $options['video']['video_id']
				);
				
				$video = fa_query_video($args);
				// if anything went wrong, get the error message
				if( is_wp_error( $video ) ){
					$message = $video->get_error_message();
					wp_send_json_error( $message );
					die();
				}
				
				$thumbnail_html = $this->import_thumbnail( $post_id, $video );
				$response = $thumbnail_html;
				wp_send_json_success( $response );
				die();
			}
		}
		
		wp_send_json_error(__('Video not found', 'fapro'));
		
		die();
	}
	*/
	
	/**
	 * Imports an image from a given URL into WP Media and sets it as featured image
	 * for the given post id.
	 * 
	 * @param int $post_id
	 * @param array $video
	 */
	private function import_thumbnail( $post_id, $video ){
		
		/**
		 * Filter on import start. Can be used to set a different attachment rather 
		 * than importing the image. Useful to avoid duplicates.
		 * 
		 * @param int $post_id - id of the post that will have the image attached to it
		 * @param array $video - array of video details
		 */
		$attachment_html = apply_filters('fa-image-imported', false, $post_id, $video);
		if( $attachment_html ){
			return $attachment_html;
		}
		
		/**
		 * Filter the image URL. Allows for example getting images of different sizes than the ones 
		 * that the plugin registers.
		 * 
		 * @param image url - url of image that will be imported
		 * $param int $post_id - id of the post that will have the image attached to it
		 * @param array $video - array of video details
		 */
		$image_url = apply_filters('fa-remote-image-url', $video['image'], $post_id, $video);
		
		// if max resolution query wasn't successful, try to get the registered image size
		$response = wp_remote_get( $image_url, array( 'sslverify' => false ) );	
		if( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$image_contents = $response['body'];
		$image_type 	= wp_remote_retrieve_header( $response, 'content-type' );
		
		// Translate MIME type into an extension
		if ( $image_type == 'image/jpeg' ){
			$image_extension = '.jpg';
		}elseif ( $image_type == 'image/png' ){
			$image_extension = '.png';
		}
		
		if( !isset( $image_extension ) ){
			return false;
		}
		
		// Construct a file name using post slug and extension
		$fn = sanitize_file_name( ( basename( remove_accents( preg_replace('/[^a-z0-9A-Z\-\s]/u', '', $video['title']) ) ) ) . $image_extension );
		
		/**
		 * Imported image filename filter.
		 * 
		 * @param string $fn - filename
		 * @param string $image_extension - image extension (.png, .jpg ...)
		 * @param int $post_id - id of post that will have the image attached to it
		 * @param array $video - array containing all video detais
		 */
		$new_filename = apply_filters('fa-remote-image-filename', $fn, $image_extension, $post_id, $video);
		
		// Save the image bits using the new filename
		$upload = wp_upload_bits( $new_filename, null, $image_contents );
		if ( $upload['error'] ) {
			return false;
		}
			
		$filename 	= $upload['file'];
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type'	=> $wp_filetype['type'],
			'post_title'		=> apply_filters( 'fa-remote-image-post-title', $video['title'], $post_id, $video ),
			'post_content'		=> '',
			'post_status'		=> 'inherit',
			'guid'				=> $upload['url']
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	
		/**
		 * Action on remote image import finish.
		 * 
		 * @param int $attach_id - attachment image ID
		 * @param int $post_id - id of the post having the image attached to it
		 * @param array $video - array of video details
		 */
		do_action('fa-remote-image-processed', $attach_id, $post_id, $video);

		return $attach_id;		
	}
	
	/**
	 * Ajax callback function that removes the attached video from
	 * a post.
	 */
	public function remove_video(){
		$action = $this->get_action_data( 'remove_video' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;		
		
		if( !current_user_can( 'edit_post', $post_id ) ){
			wp_die( -1 );
		}
		
		$result = fa_update_slide_options( $post_id, array(
			'video' => array( 
				'source' 	=> '',
				'video_id' 	=> '',
				'duration'	=> 0
			)
		));
		
		
		$options = fa_get_slide_options( $post_id );
		ob_start();				
		include fa_metabox_path('slide-video-query');				
		$output = ob_get_clean();
		$response['video_query'] = $output;					
				
		wp_send_json_success( $response );		
		die();
	}
	
	/**
	 * Returns the output for a slide assigned to a slider
	 */
	public function assign_slide(){
		$action = $this->get_action_data( 'assign_slide' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;
		
		if( !current_user_can( 'edit_fa_items', $post_id ) ){
			wp_die( -1 );
		}
		
		$slider_id = absint( $_POST['slider_id'] );
		
		$post = get_post( $post_id );
		$output = '';
		if( $post ){
			if( 'auto-draft' != $post->post_status ){
				ob_start();
				fa_slide_panel( $post_id, $slider_id );
				$output = ob_get_clean();
			}			
		}		
		
		/**
		 * Action on post assignment to slider. Will run every time a post is
		 * set as a slide to a given slider.
		 * 
		 * @param int $post_id - ID of post being assigned to slider 
		 */
		do_action('fa_assign_post_to_slider', $post_id, $slider_id);
		
		wp_send_json_success( $output );
		
		die();
	}
	
	/**
	 * Assigns images to sliders and returns the output for administration
	 */
	public function assign_images(){
		$action = $this->get_action_data( 'assign_images' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;
		
		if( !current_user_can( 'edit_fa_items', $post_id ) ){
			wp_die( -1 );
		}
		
		$settings = fa_get_slider_options( $post_id, 'slides' );
		$images = isset( $_POST['images'] ) ? (array) $_POST['images'] : array();
		
		ob_start();
		foreach( $images as $image ){
			fa_image_panel( $image, $post_id );
		}
		$output = ob_get_clean();
		
		/**
		 * Action on post assignment to slider. Will run every time a post is
		 * set as a slide to a given slider.
		 * 
		 * @param int $post_id - ID of post being assigned to slider 
		 */
		do_action('fa_assign_image_to_slider', $post_id, $images);
		
		wp_send_json_success( $output );
		die();
	}
	
	/**
	 * Assigns an image from the media gallery to be used as slide image
	 */
	public function assign_slide_image(){
		$action = $this->get_action_data( 'assign_slide_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;
		
		if( !current_user_can( 'edit_fa_items', $post_id ) ){
			wp_die( -1 );
		}
		
		$image_id = isset( $_POST['images'][0] ) ? absint( $_POST['images'][0] ) : false;
		if( !$image_id ){
			wp_send_json_error(__('No image selected.', 'fapro'));
		}
		// update the image option
		fa_update_slide_options( $post_id , array( 'image' => $image_id ) );
		ob_start();
		// get the image output
		the_fa_slide_image( $post_id );
		// capture the output
		$output = ob_get_clean();
		wp_send_json_success( $output );		
		die();
	}
	
	/**
	 * Removes a previously set slide custom image
	 */
	public function remove_slide_image(){
		$action = $this->get_action_data( 'remove_slide_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;
		
		if( !current_user_can( 'edit_fa_items', $post_id ) ){
			wp_die( -1 );
		}
		
		// update the image option
		fa_update_slide_options( $post_id , array( 'image' => '' ) );
		ob_start();
		// get the image output
		the_fa_slide_image( $post_id );
		// capture the output
		$output = ob_get_clean();
		wp_send_json_success( $output );		
		die();		
	}
	
	/**
	 * Assigns an image from the media gallery to be used as slide image
	 */
	public function assign_theme_image(){
		$action = $this->get_action_data( 'assign_theme_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		if( !current_user_can( 'edit_fa_items' ) ){
			wp_die( -1 );
		}
		
		$image_id = isset( $_POST['images'][0] ) ? absint( $_POST['images'][0] ) : false;
		if( !$image_id ){
			wp_send_json_error(__('No image selected.', 'fapro'));
		}
		
		$image = wp_get_attachment_image_src( $image_id, 'full' );
		if( isset( $image[0] ) ){
			$thumb = wp_get_attachment_image_src( $image_id, 'thumbnail' );
			$output = '<div class="fa_slide_image" data-post_id="' . $image_id . '">';
			$output.= sprintf( '<img src="%s">', $thumb[0] );
			$output.= '</div>';			
			wp_send_json_success( array( 'html' => $output, 'image_url' => $image[0] ) );
		}else{
			wp_send_json_error(__('Image not found', 'fapro'));
		}		
				
		die();
	}
	
	/**
	 * Assign sliders to dynamic areas
	 */
	public function slider_to_area(){
		$action = $this->get_action_data( 'assign_to_area' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		if( !current_user_can( 'edit_fa_items' ) ){
			wp_die( -1 );
		}
		if( !isset( $_POST['areas'] ) ){
			wp_die( -1 );
		}
		
		$settings = fa_get_options('hooks');
		foreach( $_POST['areas'] as $area => $sliders ){
			// if area isn't found in stored areas, skip it
			if( !array_key_exists( $area , $settings ) ){
				continue;
			}
			$result = array();
			// empty the area if nothing is set
			if( empty( $sliders ) ){
				$settigs[ $area ]['sliders'] = $result;
			}
			
			$sliders = explode(',', $sliders);
			foreach( $sliders as $slider ){
				$slider_id = absint( str_replace( 'fa_slider-', '', $slider ) );
				$result[] = $slider_id;
			}
			$settings[ $area ]['sliders'] = $result;			
		}
		
		fa_update_options( 'hooks' , $settings );
		die();
	}
	
	/**
	 * Assigns a default image for slides without image from a given slider
	 */
	public function set_default_slides_image(){
		$action = $this->get_action_data( 'default_slides_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		if( !current_user_can( 'edit_fa_items' ) ){
			wp_die( -1 );
		}
		
		$image_id = isset( $_POST['images'][0] ) ? absint( $_POST['images'][0] ) : false;
		if( !$image_id ){
			wp_send_json_error(__('No image selected.', 'fapro'));
		}
		
		$slider_id = absint( $_POST['post_id'] );
		$post = get_post( $slider_id );
		if( !$post || $post->post_type != fa_post_type_slider() ){
			wp_send_json_error(__('This is available only for FA sliders.', 'fapro'));
		}
		
		ob_start();
		// get the image output
		the_default_slider_image( $slider_id, $image_id );
		// capture the output
		$output = ob_get_clean();
		wp_send_json_success( $output );
		die();
	}
	
	/**
	 * Remove the default image set on slider to be used for slides without image
	 */
	public function remove_default_slides_image(){
		$action = $this->get_action_data( 'rem_default_slides_image' );
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		if( !current_user_can( 'edit_fa_items' ) ){
			wp_die( -1 );
		}
		
		$slider_id = absint( $_POST['post_id'] );
		$post = get_post( $slider_id );
		if( !$post || $post->post_type != fa_post_type_slider() ){
			wp_send_json_error(__('This is available only for FA sliders.', 'fapro'));
		}
		
		ob_start();
		// get the image output
		the_default_slider_image( $slider_id, -1 );
		// capture the output
		$output = ob_get_clean();
		wp_send_json_success( $output );
		die();
	}
	
	/**
	 * Stores all ajax actions references.
	 * This is where all ajax actions are added.
	 */	
	private function actions(){
		$actions = array(
			/**
			 * Queries for videos. Used on slides
			 */
			'video_query' => array(
				'action' 	=> 'fa-query-video',
				'callback' 	=> array( $this, 'query_video' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-video-query'
				)
			),
			/**
			 * Remove video. Used on slides
			 */
			'remove_video' => array(
				'action' 	=> 'fa-remove-video',
				'callback' 	=> array( $this, 'remove_video' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-remove-video'
				)
			),
			/**
			 * Adds a new slide to slider.
			 */
			'assign_slide' => array(
				'action' 	=> 'fa-add-slide',
				'callback' 	=> array( $this, 'assign_slide' ),
				'nonce' 	=> array(
					'name'		=> 'fa_ajax_nonce',
					'action'	=> 'fa-assign-slide'
				) 
			),
			/**
			 * Imports an image for an attached video
			 */
			/*
			'import_image' => array(
				'action' 	=> 'fa-import-image',
				'callback' 	=> array( $this, 'ajax_import_image' ),
				'nonce' => array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-import-video-image'
				)
			),
			]*/
			/**
			 * Adds images to a slider
			 */
			'assign_images' => array(
				'action' 	=> 'fa-add-images',
				'callback' 	=> array( $this, 'assign_images' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-assign-slider-images'
				)
			),
			
			'assign_slide_image' => array(
				'action' 	=> 'fa-assign-slide-image',
				'callback' 	=> array( $this, 'assign_slide_image' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa_assign_slide_image' 
				)
			),
			'remove_slide_image' => array(
				'action' 	=> 'fa-remove-slide-image',
				'callback' 	=> array( $this, 'remove_slide_image' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa_remove_slide_image' 
				)
			),
			'assign_theme_image' => array(
				'action' 	=> 'fa-assign-theme-image',
				'callback' 	=> array( $this, 'assign_theme_image' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa_assign_theme_image' 
				)
			),
			// assign sliders to dynamic areas
			'assign_to_area' => array(
				'action' 	=> 'fa-assign-to-area',
				'callback' 	=> array( $this, 'slider_to_area' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa_assign_slider_to_area'
				)
			),
			// set default image for slides without image on slider options
			// action and nonce are set manually in file includes/admin/functions.php in function fa_default_slides_image()
			'default_slides_image' => array(
				'action' => 'fa_default_slides_image',
				'callback' => array( $this, 'set_default_slides_image' ),
				'nonce' 	=> array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-assign-slider-images'
				)
			),
			// remove default image for slides without image from slider options
			'rem_default_slides_image' => array(
				'action' => 'fa_remove_default_slides_image',
				'callback' => array( $this, 'remove_default_slides_image' ),
				'nonce' => array(
					'name' 		=> 'fa_ajax_nonce',
					'action' 	=> 'fa-assign-slider-images'
				)
			)
		);
		
		return $actions;
	}
	
	/**
	 * Get the wp action name for a given action
	 * @param string $key
	 */
	public function get_action( $key ){
		$action = $this->get_action_data( $key );
		return $action['action'];
	}
	
	/**
	 * Get the wp action nonce for a given action
	 * @param string $key
	 */
	public function get_nonce( $key ){
		$action = $this->get_action_data( $key );
		
		$nonce = wp_create_nonce( $action['nonce']['action'] );
		$result = array(
			'name' => $action['nonce']['name'],
			'nonce' => $nonce
		);		
		return $result;
	}
	
	/**
	 * Gets all details of a given action from registered actions
	 * @param string $key
	 */
	private function get_action_data( $key ){
		$actions = $this->actions();
		if( array_key_exists( $key, $actions ) ){
			return $actions[ $key ];
		}else{
			trigger_error( sprintf( __( 'Action %s not found.'), $key ), E_USER_WARNING);
		}
	}	
}