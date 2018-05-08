<?php
class FA_Update{
	
	private $auto_display = array();
	private $slides = array();
	
	public function __construct(){		
		$this->update_settings();
		$this->import_hooks();
		$this->process_sliders();		
		$this->delete_options();		
	}
	
	/**
	 * Transfer plugin settings from old format to new one
	 */
	private function update_settings(){
		$settings 	= get_option( 'feat_art_options', array() );
		$details 	= get_option( 'fa_plugin_details', array() );
		$license 	= get_option( 'fa_plugin_license', array() );
		// update new settings format
		if( $settings ){
			$update = array(
				'complete_uninstall' => $settings['complete_uninstall'],
				'load_in_wptouch'	 => $settings['load_in_wptouch'],
				'custom_posts'		 => $settings['custom_posts']
			);
			fa_update_options( 'settings' , $update );
		}
		// update the license key
		if( $license ){
			$update = array(
				'license_key' 		=> $license['license_key'],
				'activation_date' 	=> $license['activation_date']
			);
			fa_update_options( 'license' , $update );
		}		
	}
	
	/**
	 * Import hooks into the new format
	 */
	private function import_hooks(){
		// old option
		$option = get_option( 'fapro_hooks', array() );
		if( $option ){
			$opt = array();
			foreach( $option as $hook ){
				$opt[ $hook['hook'] ] = array(
					'name' => $hook['name'],
					'description' => $hook['desc'],
					'sliders' => array()
				);
			}
			if( $opt ){
				fa_update_options( 'hooks' , $opt );
			}	
		}
	}
	
	/**
	 * Update sliders
	 */
	private function process_sliders(){
		// ge tthe sliders
		$sliders = get_posts(array(
			'post_type' 		=> fa_post_type_slider(),
			'post_status' 		=> 'any',
			'posts_per_page' 	=> -1
		));
		if( !$sliders ){
			return;
		}
		
		// load themes manager to allow options from themes to be merged with the plugin options
		fa_get_themes();
		
		// run sliders
		foreach( $sliders as $slider ){
			$options = $this->process_old_slider_options( $slider->ID );
			
			// get old content option
			$o = get_post_meta( $slider->ID, '_fa_lite_content', true );
			
			// set the slider content
			if( isset( $o['displayed_content'] ) ){
				$options['slides']['type'] = ( 1 == $o['displayed_content'] ? 'post' : 'mixed' );
			}
			
			// set post types
			if( isset( $o['post_types'] ) ){
				if( !is_array( $o['post_types'] ) || !$o['post_types'] ){
					$post_types = array( 'post' );
				}else{
					$post_types = $o['post_types'];
				}
				$options['slides']['post_type'] = $post_types;
			}
			
			// set the categories
			if( isset( $o['display_from_category'] ) || isset( $o['display_from_taxonomies'] ) ){
				$tags = array();
				if( $o['display_from_taxonomies'] ){
					foreach( $o['display_from_taxonomies'] as $taxonomy => $tags ){
						foreach( $tags as $slug ){
							$term = get_term_by( 'slug' , $slug, $taxonomy );
							if( $term && !is_wp_error( $term ) ){
								$tags[ $taxonomy ][] = absint( $term->term_id );
							}
						}
					}
				}				
				if( $o['display_from_category'] ){
					if( 1 == count( $o['display_from_category'] ) && empty( $o['display_from_category'][0] ) ){
						$tags['category'] = array();	
					}else{					
						$tags['category'] = (array) $o['display_from_category'];
					}	
				}
				
				$options['slides']['tags'] = $tags;				
			}
			
			// set orderby
			if( isset( $o['display_order'] ) ){
				switch( $o['display_order'] ){
					case 1;
					default:
						$options['slides']['orderby'] = 'date';
					break;	
					case 2:
						$options['slides']['orderby'] = 'comments';
					break;
					case 3:
						$options['slides']['orderby'] = 'random';
					break;	
				}
			}
			
			// set posts
			if( isset( $o['display_pages'] ) || isset( $o['display_featured'] ) ){
				$options['slides']['posts'] = array_merge( (array)$o['display_pages'], (array)$o['display_featured'] );
				if( $options['slides']['posts'] ){
					foreach( $options['slides']['posts'] as $pid ){
						delete_post_meta( $pid, '_fa_lite_' . $slider->ID . '_featured_ord' );
					}					
					$this->slides = array_merge( $this->slides, $options['slides']['posts'] );
				}
			}
			
			// get old aspect option
			$o = get_post_meta( $slider->ID, '_fa_lite_aspect', true );
			
			// set the content to be displayed
			if( isset( $o['use_custom_text'] ) && $o['use_custom_text'] ){
				$options['content_text']['use'] = 'custom';
			}else if( isset( $o['use_excerpt'] ) && $o['use_excerpt'] ){
				$options['content_text']['use'] = 'excerpt';
			}else{
				$options['content_text']['use'] = 'content';
			}
			
			// set slider fullwidth
			if( isset( $o['slider_width'] ) ){
				if( '100%' == $o['slider_width'] ){
					$options['layout']['full_width'] = true;
				}else{
					$options['layout']['width'] = $o['slider_width'];
				}
			}
			
			// set homepage display - part of automatic display feature
			$o = get_post_meta( $slider->ID, '_fa_lite_home_display', true );
			if( $o ){
				$options['display']['home'] = true;
			}
			
			// set categories display
			$o = get_post_meta( $slider->ID, '_fa_lite_categ_display', true );
			if( $o ){
				if( in_array( 'all', (array) $o ) ){
					$options['display']['all_categories'] = true;
				}elseif( in_array( 'everywhere',  $o ) ){
					$options['display']['everywhere'] = true;
				}else{				
					$args = array(
						'include' => $o,
						'hide_empty' => false,
					);
					$taxonomies = get_taxonomies( array( 'public' => true ) );
					$terms = get_terms( $taxonomies, $args );
					
					$opt = array();
					if( $terms ){
						foreach( $terms as $term ){
							$opt[ $term->taxonomy ][] = $term->term_id;
						}
					}				
					$options['display']['tax'] = $opt;
				}	
			}
			
			// set pages display
			$o = get_post_meta( $slider->ID, '_fa_lite_page_display', true );
			if( $o ){
				$opt = array();
				foreach( $o as $post_id ){
					$post_type = get_post_type( $post_id );
					if( $post_type ){
						$opt[ $post_type ][] = $post_id;
					}
				}			
				$options['display']['posts'] = $opt;
			}		
			
			// get current theme
			$o = get_post_meta( $slider->ID, '_fa_lite_theme', true );
			if( isset( $o['active_theme'] ) ){
				
				$theme = $this->get_new_theme( $o['active_theme'] );
				if( $theme ){
					$options['theme']['active'] = $theme['theme'];
					if( $theme['params'] ){
						foreach( $theme['params'] as $key1 => $values1 ){
							if( is_array( $values1 ) ){
								foreach( $values1 as $key2 => $values2 ){
									if( is_array( $values2 ) ){
										foreach( $values2 as $key3 => $values3 ){
											$options[ $key1 ][ $key2 ][ $key3 ] = $values3;
										}
									}else{
										$options[ $key1 ][ $key2 ] = $values2;
									}
								}
							}else{
								$options[ $key1 ] = $values1;
							}
						}
					}
				}
			}
			
			// set slider expiration date
			$o = get_post_meta( $slider->ID, '_fa_lite_aspect', true );
			if( isset( $o['end_publish_date'] ) ){
				$expires = '0000-00-00 00:00:00';
				if( !empty( $o['end_publish_date'] ) ){
					$expires = $o['end_publish_date'];
				}
				$options['slider']['expires'] = $expires;
			}
			if( isset( $o['start_publish_date'] ) ){
				if( !empty( $o['start_publish_date'] ) ){
					$timestamp = strtotime( $o['start_publish_date'] );
					if( time() < $timestamp ){					
						$data = array(
							'post_date' 	=> date( 'Y-m-d', strtotime( $o['start_publish_date'] ) ),
							'post_date_gmt' => date( 'Y-m-d', strtotime( $o['start_publish_date'] ) ),
							'edit_date' 	=> true,
							'ID' 			=> $slider->ID
						);
						wp_update_post( $data );
					}
				}
			}
			
			// set the new option
			$result = fa_update_slider_options( $slider->ID , $options );			
			
			// setup automatic display
			$automatic_display =  get_post_meta( $slider->ID, '_fa_lite_display', true );
			if( $automatic_display ){
				if( isset( $automatic_display['hook_display'] ) ){
					$this->auto_display[ $automatic_display['hook_display'] ][] = $slider->ID;
				}
			}
			
			// delete the old options
			delete_post_meta( $slider->ID , '_fa_lite_content');
			delete_post_meta( $slider->ID , '_fa_lite_aspect');
			delete_post_meta( $slider->ID , '_fa_lite_display');
			delete_post_meta( $slider->ID , '_fa_lite_js');
			delete_post_meta( $slider->ID , '_fa_lite_theme');
			delete_post_meta( $slider->ID , '_fa_lite_theme_details');
			delete_post_meta( $slider->ID , '_fa_lite_home_display');
			delete_post_meta( $slider->ID , '_fa_lite_categ_display');
			delete_post_meta( $slider->ID , '_fa_lite_page_display');		
		}

		$this->store_auto_displays();
		$this->process_slides();
		$this->process_custom_slides();
	}
	
	/**
	 * Set sliders that should auto display on hooks
	 */
	private function store_auto_displays(){
		if( $this->auto_display ){
			$option = fa_get_options( 'hooks' );
			foreach( $this->auto_display as $hook => $sliders ){
				if( array_key_exists( $hook ,  $option ) ){
					$option[ $hook ]['sliders'] = $sliders;
				}
			}
			fa_update_options( 'hooks' , $option );
		}	
	}
	
	/**
	 * Processes slides
	 */
	private function process_slides(){
		if( !$this->slides ){
			return;
		}
		
		$posts = get_posts(array(
			'include' 			=> $this->slides,
			'posts_per_page' 	=> -1,
			'post_status' 		=> 'any',
			'post_type'			=> 'any'
		));
		
		if( $posts ){
			foreach( $posts as $post ){
				// custom slides are processed later
				if( fa_post_type_slide() == $post->post_type ){
					continue;
				}
				
				$slide_options = array(
					'link_text' 	=> get_post_meta( $post->ID, '_fa_cust_link', true ),
					'class'			=> get_post_meta( $post->ID, '_fa_cust_class', true ),
					'link_target' 	=> '_self',
					'background'	=> get_post_meta( $post->ID, '_fa_bg_color', true ),
					'title'			=> get_post_meta( $post->ID, '_fa_cust_title', true ),
					'content'		=> get_post_meta( $post->ID, '_fa_cust_txt', true ),
					'image'			=> get_post_meta( $post->ID, '_fa_image', true ),
					'temp_image_id' => get_post_meta( $post->ID, '_fa_image_autodetect', true )
				);
				
				fa_update_slide_options( $post->ID ,  $slide_options );
				
				// delete old meta
				delete_post_meta( $post->ID , '_fa_cust_link' );
				delete_post_meta( $post->ID , '_fa_cust_class' );
				delete_post_meta( $post->ID , '_fa_bg_color' );
				delete_post_meta( $post->ID , '_fa_cust_title' );
				delete_post_meta( $post->ID , '_fa_cust_txt' );
				delete_post_meta( $post->ID , '_fa_image' );
				delete_post_meta( $post->ID, '_fa_image_autodetect' );
				//delete_post_meta( $post->ID, '_fa_lite_' . $slider->ID . '_featured_ord' );
			}
		}		
	}
	
	/**
	 * Process custom slides
	 */
	private function process_custom_slides(){
		$slides = get_posts(array(
			'post_type' 		=> fa_post_type_slide(),
			'post_status' 		=> 'any',
			'posts_per_page' 	=> -1
		));
		
		if( $slides ){
			foreach( $slides as $slide ){
				$options = array(
					'link_text' 	=> get_post_meta( $slide->ID, '_fa_cust_link', true ),
					'class'			=> get_post_meta( $slide->ID, '_fa_cust_class', true ),
					'url'			=> get_post_meta( $slide->ID, '_fa_cust_url', true ),
					'link_target' 	=> get_post_meta( $slide->ID, '_fa_cust_url_blank', true ) ? '_blank' : '_self',
					'background'	=> get_post_meta( $slide->ID, '_fa_bg_color', true ),
					'image'			=> get_post_meta( $slide->ID, '_fa_image', true ),
				);
				
				$video = get_post_meta( $slide->ID, '_fa_video_settings', true );
				if( $video ){
					unset( $video['height'] );
					unset( $video['hd'] );
					$video['video_id'] = $video['id'];
					unset( $video['id'] );
					$options['video'] = $video;
				}
				
				fa_update_slide_options( $slide->ID ,  $options );
				
				delete_post_meta( $slide->ID , '_fa_cust_link');
				delete_post_meta( $slide->ID , '_fa_cust_class');
				delete_post_meta( $slide->ID , '_fa_cust_url');
				delete_post_meta( $slide->ID , '_fa_cust_url_blank');
				delete_post_meta( $slide->ID , '_fa_bg_color');
				delete_post_meta( $slide->ID , '_fa_video_settings');	
				delete_post_meta( $slide->ID , '_fa_cust_title');
				delete_post_meta( $slide->ID , '_fa_cust_txt');
				delete_post_meta( $slide->ID , '_fa_image');
				delete_post_meta( $slide->ID , '_fa_media_type');
			}
		}		
	}
	
	private function delete_options(){
		// remove old options
		delete_option( 'feat_art_options' );
		delete_option( 'fa_plugin_details' );
		delete_option( 'fa_plugin_license' );
		delete_option( 'fapro_hooks' );
		delete_option( 'fa_lite_categories' );
		delete_option( 'fa_lite_home' );
		delete_option( 'fa_lite_pages' );
	}
	
	private function process_old_slider_options( $slider_id ){
		// map some of the old options into the new options structure
		$map = array(
			'slides' => array(
				'option' => '_fa_lite_content',
				'mapping' => array(
					/* new key => old key */
					'post_type' 	=> 'post_types',			
					'limit' 		=> 'num_articles',
					'author' 		=> 'author'
				)
			),
			'content_image' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show' 			=> 'thumbnail_display',
					'preload' 		=> 'thumbnail_preloader',
					'show_width' 	=> 'thumbnail_width',
					'show_height' 	=> 'thumbnail_height',
					'clickable'		=> 'thumbnail_click',
					'sizing' 		=> 'fa_image_source',
					'width' 		=> 'custom_image_width',
					'height' 		=> 'custom_image_height',
					'wp_size' 		=> 'th_size'
				)
			),
			'content_title' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show' 			=> 'show_title',
					'use_custom' 	=> 'title_custom',
					'clickable' 	=> 'title_click'
				)
			),
			'content_text' => array(
				'option' 	=> '_fa_lite_aspect',
				'mapping' 	=> array(
					'show' 				=> 'show_text',
					'allow_tags' 		=> 'allowed_tags',
					'allow_all_tags' 	=> 'allow_all_tags',
					'strip_shortcodes' 	=> 'strip_shortcodes',
					'max_length' 		=> 'desc_truncate',
					'max_length_noimg' 	=> 'desc_truncate_noimg',
					'end_truncate' 		=> 'end_truncate'
				)
			),
			'content_read_more' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show' 	=> 'show_read_more',
					'text'	=> 'read_more'
				)
			),
			'content_date' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show' => 'show_date'
				)
			),
			'content_author' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show' => 'show_post_author',
					'link' => 'link_post_author'
				)
			),
			'layout' => array(
				'option' => '_fa_lite_aspect',
				'mapping' => array(
					'show_title' 	=> 'section_display',
					'height' 		=> 'slider_height',
					'show_main_nav' => 'bottom_nav',
					'show_side_nav' => 'sideways_nav'
				)
			),
			'js' => array(
				'option' => '_fa_lite_js',
				'mapping' => array(
					'auto_slide' 		=> 'autoSlide',
					'slide_duration' 	=> 'slideDuration',
					'effect_duration' 	=> 'effectDuration',
					'click_stop' 		=> 'stopSlideOnClick',
					'distance_in' 		=> 'fadeDist',
					'position_in' 		=> 'fadePosition',
					'event' 			=> 'navEvent'
				)
			)
		);
		
		// new slider options defaults
		$defaults = fa_get_slider_default_options();
		
		foreach ( $map as $option_key => $details ){
			$old_option = get_post_meta( $slider_id, $details['option'], true );
			foreach ( $details['mapping'] as $key => $old_key ){
				if( isset( $old_option[ $old_key ] ) ){
					$defaults[ $option_key ][ $key ] = $old_option[ $old_key ];
				}
			}
		}
		
		return $defaults;
	}
	
	/**
	 * Mapping of old slider themes to set up correctly the new slider themes
	 * @param string $old_theme
	 */
	private function get_new_theme( $old_theme ){
		
		$themes = array(
			'accordion' => array(
				'theme' 	=> 'accordion',
				'params' 	=> array()
			),
			'carousel' => array(
				'theme' 	=> 'cristal',
				'params'	=> array(
					'themes_params' => array(
						'cristal' => array(
							'navigation' => 'carousel'
						)
					),
					'layout' => array(
						'class' 		=> 't-up-c-down background',
						'show_main_nav' => true
					),
					'content_text' => array(
						'show' => false
					),
					'content_date' => array(
						'show' => false
					),
					'content_author' => array(
						'show' => false
					)
				)
			),
			'classic' => array(
				'theme' => 'simple',
				'params' => array()
			),
			'minimal' => array(
				'theme' => 'cristal',
				'params' => array(
					'themes_params' => array(
						'cristal' => array(
							'navigation' => 'dots'
						)
					),
					'layout' => array(
						'class' => 'content-bottom background'
					)
				)
			),
			'smoke' => array(
				'theme' => 'cristal',
				'params' => array(
					'themes_params' => array(
						'cristal' => array(
							'navigation' => 'dots'
						)
					),
					'layout' => array(
						'class' => 'content-left background',
						'show_side_nav' => false,
						'show_main_nav' => true
					)
				)
			),
			'title_navigation' => array(
				'theme' =>  'list',
				'params' => array()
			),
			'navobar' => array(
				'theme' => 'navobar',
				'params' => array()
			),
			// theme removed, defaults to simple
			'strips' => array(
				'theme' => 'simple',
				'params' => array(
					'layout' => array(
						'show_main_nav' => true,
						'show_side_nav' => true
					),
					'js' => array(
						'effect_duration' => .6
					)
				)
			),
			// theme removed, defaults to simple
			'ribbons' => array(
				'theme' => 'simple',
				'params' => array(
					'layout' => array(
						'show_main_nav' => true,
						'show_side_nav' => true
					)
				)
			),
		);
		
		if( array_key_exists( $old_theme ,  $themes ) ){
			return $themes[ $old_theme ];
		}
		return false;
	}
	
}