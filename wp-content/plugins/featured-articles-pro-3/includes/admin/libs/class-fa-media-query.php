<?php
/**
 * Media query abstract class. Must be implemented by child classes.
 */
abstract class FA_Media_Query{
	/**
	 * Store errors
	 */
	private $errors;
	/**
	 * Store the result of the API query
	 */
	private $result;
	
	/**
	 * Must be implemented in child classes. Must return an array containing the sources
	 * that can be queried for content.
	 * 
	 * Array format is:
	 * 
	 * array(
	 * 		'source_key' => array(
	 * 			'details' => array(
	 * 				'name' 	=> 'Display name',
	 * 				'url' 	=> 'source URL'
	 * 			),
	 * 			'query' => 'URL to query the api and get a JSON response',
	 * 			'mapping' => array(
	 * 				// how to map local fields from remote array fields
	 * 				'local_field' => 'remote field'
	 * 			)
	 * 		)
	 * )
	 * 
	 */
	abstract protected function media_sources();
	
	/**
	 * Constructor; takes as argument an array containing the media source key
	 * implemented by child class using above media_sources() method and the query
	 * string (video ID, image URL, etc) to be used when API query is made.
	 */
	public function __construct( $args ){
		
		$defaults = array(
			'source' 	=> false,
			'query'		=> false
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		// if no source, register error and stop
		if( !$source ){
			return $this->register_error( 'fa-media-query-no-source', __('No media source specified', 'fapro') );
		}
		// no video ID specified, register error and stop
		if( !$query ){
			return $this->register_error('fa-media-query-no-id', __('No media query ID/URL specified', 'fapro'));
		}
		
		// source not identified, register error and stop
		$source_data = $this->get_source( $source );
		if( is_wp_error( $source_data ) ){
			return $source_data;
		}
		
		// create the request
		$request_url = sprintf( $source_data['query'], $query );
		$response = wp_remote_get( $request_url );
		
		// query returned WP error, register errors and stop
		if( is_wp_error( $response ) ){
			$codes = $response->get_error_codes();
			foreach( $codes as $code ){
				$this->register_error( $code, $response->get_error_code( $code ) );
			}
			return;
		}
		
		// media not found
		if( 404 == wp_remote_retrieve_response_code( $response ) ){
			return $this->register_error( 'fa-media-not-found', __('The media was not found.', 'fapro'), $response );
		}
		
		// request returned fail error code, register error and stop
		if( 200 != wp_remote_retrieve_response_code( $response ) ){
			return $this->register_error( 'fa-media-request-fail', __('Request failed.', 'fapro'), $response );
		}
		
		$content 		= json_decode( wp_remote_retrieve_body( $response ), true );
		$this->result 	= $this->format_entry( $content, $source, $source_data, $query ) ;		
	}
	
	/**
	 * Formats entries from different sources into a unified array
	 * @param array $content - video details retrieved from video platform
	 * @param string $source - source name
	 * @param array $source_data - source details
	 */
	protected function format_entry( $content, $source, $source_data, $query = false ){
		// start the result
		$result = array(
			'source' => $source
		);
		foreach( $source_data['mapping'] as $key => $result_key ){
			// missing keys are set by default to false
			if( !$result_key ){
				$result[ $key ] = false;
				continue;
			}
			
			// some info might be stored into a multidimensional array, get it here
			if( is_array( $result_key ) ){
				$result[ $key ] = $this->get_array_value( $result_key, $content );
				continue;
			}
			
			// default processing
			$result[ $key ] = isset( $content[ $result_key ] ) ? $content[ $result_key ] : false;
		}
		
		return $result;
	}
	
	/**
	 * Given an array of keys $keys, it will return the coresponding value from 
	 * the multidimensional array $raw_entry
	 * 
	 * @param array $keys - array of keys from a multidimensional array
	 * @param array $raw_entry - multidimensional array
	 */
	protected function get_array_value( $keys, $raw_entry ){
		
		$r = array();
		if( isset( $raw_entry[ $keys[0] ] ) ){
			$r = $raw_entry[ $keys[0] ];
			unset( $keys[0] );
		}else{
			return false;
		}
		
		foreach( $keys as $key ){
			if( isset( $r[ $key ] ) ){
				$r = $r[ $key ];
			}
		}
		
		return $r;	
	}
	
	/**
	 * Registers an error
	 * @param string $code - error code
	 * @param string $message - error message
	 * 
	 * @return WP_Error
	 */
	protected function register_error( $code, $message, $data = false ){
		if( !is_wp_error( $this->errors ) ){
			$this->errors = new WP_Error();
		}
		$this->errors->add($code, $message, $data);
		return $this->errors;
	}
	
	/**
	 * Get the details of a given video source
	 * @param string $source - a registered video source ( vimeo, youtube, etc )
	 */
	private function get_source( $source ){
		
		$sources = (array) $this->media_sources();
		if( !array_key_exists( $source , $sources ) ){
			return $this->register_error('fa-media-query', __('Source not available', 'fapro'));
		}
		
		return $sources[ $source ];		
	}
	
	/**
	 * Returns the result or WP error in case of failing
	 */
	public function get_result(){
		if( is_wp_error( $this->errors ) ){
			return $this->errors;
		}
		return $this->result;
	}
	
	/**
	 * Get the registered video sources
	 */
	public function get_sources(){
		return $this->media_sources();
	}	
}

/**
 * Query various video APIs
 */
class FA_Video_Query extends FA_Media_Query{
	/**
	 * Constructor. Arguments array must have this form:
	 * 
	 * array(
	 * 		'source' 	=> 'source key from media_sources() method',
	 * 		'video_id' 	=> 'query parameter value'
	 * )
	 */	
	public function __construct( $args ){
		$defaults = array(
			'source' 	=> false, // the source to query videos from
			'video_id' 	=> false, // ID of the video 
		);
		$args = wp_parse_args( $args, $defaults );
		
		// check if YouTube API key was entered and issue error if empty
		if( 'youtube' == $args['source'] ){
			$api_keys = fa_get_options( 'apis' );
			if( empty( $api_keys['youtube_key'] ) ){
				parent::register_error( 'fapro-no-youtube-api-key', __( 'Before making YouTube video queries you must enter your YouTube API key in plugin Settings page.' , 'fapro' ) );
				return;
			}
		}
		
		$params = array(
			'source' => $args['source'],
			'query' => $args['video_id'] 
		);
		parent::__construct( $params );
	}
	
	/**
	 * Set video media sources
	 */
	protected function media_sources(){
		
		$api_keys = fa_get_options( 'apis' );
		
		// registered sources
		$sources = array(
			'vimeo' => array(
				'details' => array(
					'name' => 'Vimeo',
					'url' => 'http://www.vimeo.com'
				),
				'query' => 'http://vimeo.com/api/v2/video/%s.json', // the URL from where to request the video details 		
				'mapping' => array(
					'video_id' 		=> 'id',
					'title' 		=> 'title',
					'description' 	=> 'description',
					'uploaded' 		=> 'upload_date',
					'image' 		=> 'thumbnail_large',
					'duration' 		=> 'duration',
					'width' 		=> 'width',
					'height' 		=> 'height'
				)			
			),
			'youtube' => array(
				'details' => array(
					'name' => 'YouTube',
					'url' => 'http://www.youtube.com'
				),
				// API V3 query
				'query' => 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,status&id=%s&key=' . $api_keys['youtube_key'],
				'mapping' => array(
					'video_id' 		=> 'id',
					'title' 		=> array( 'snippet', 'title' ),
					'description' 	=> array( 'snippet', 'description' ),
					'uploaded' 		=> array( 'snippet', 'publishedAt' ),
					'image' 		=> array( 'snippet', 'thumbnails', 'standard', 'url' ),
					'duration' 		=> array( 'contentDetails', 'duration' ),
					'width' 		=> false,
					'height' 		=> false
				)
			)
		);
		
		/**
		 * Filter to add extra video sources.
		 * @var array - source should be returned as array
		 */
		$extra_sources = (array) apply_filters( 'fa-add-video-source', array() );
				
		return array_merge( $sources, $extra_sources );	
	}
	
	/**
	 * Formats entries from different sources into a unified array
	 * @param array $content - video details retrieved from video platform
	 * @param string $source - source name
	 * @param array $source_data - source details
	 */
	protected function format_entry( $content, $source, $source_data, $video_id = false ){
		switch( $source ){
			case 'vimeo':
				$content = end( $content );
			break;
			case 'youtube':
				if( isset( $content['items'][0] ) ){
					$content = $content['items'][0];
				}else{
					return new WP_Error( 'fa-pro-video-not-found', __('Your video couldn\'t be found', 'fapro') );
				}	
			break;	
		}
		// start the result
		$result = array(
			'source' => $source
		);
		foreach( $source_data['mapping'] as $key => $result_key ){
			// some feeds might not return the video ID. Pass it from the params
			if( 'video_id' == $key && !$result_key ){
				$result[ $key ] = $video_id;
				continue;
			}
			
			// missing keys are set by default to false
			if( !$result_key ){
				$result[ $key ] = false;
				continue;
			}
			
			// some info might be stored into a multidimensional array, get it here
			if( is_array( $result_key ) ){
				$result[ $key ] = $this->get_array_value( $result_key, $content );
				continue;
			}
			
			// default processing
			$result[ $key ] = $content[ $result_key ];
		}
		
		return $result;
	}
	
	/**
	 * Returns registered video sources
	 */
	public function get_video_sources(){
		return parent::get_sources();
	}
}