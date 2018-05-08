<?php

/**
 * When assigning a post to a slider, check if the post has a compatible attached video to it
 * and set it on the post.
 * 
 * @param int $post_id - post object or post ID of post being attached to slider
 */
function third_party_fa_set_video( $post_id ){
	if( is_numeric( $post_id ) ){
		$post = get_post( $post_id );
	}else{
		$post = $post_id;
	}
	
	if( !$post ){
		return;
	}
	
	// get slide options
	$options = fa_get_slide_options( $post_id );
	// if video is already set, don't do anything
	if( !empty( $options['video']['video_id'] ) ){
		return;
	}
	
	// allowed post types
	$post_types = array( 'post', 'vimeo-video', 'video' );
	if( in_array( $post->post_type, $post_types ) ){
		if( function_exists( 'cvm_get_post_video_data' ) ){
			// compatibility with Vimeo Videos PRO
			$video_data = cvm_get_post_video_data( $post->ID );
			if( $video_data ){
				$options['video'] = array(
					'source' 	=> 'vimeo',
					'video_id' 	=> $video_data['video_id'],
					'duration' 	=> $video_data['duration']
				);
				// set video on slide
				fa_update_slide_options( $post->ID, $options );
				return;
			}
		}
		
		// YouTube Video import functionality
		$yt_video = get_post_meta( $post->ID, '__cbc_video_data', true );
		if( $yt_video ){
			$options['video'] = array(
				'source' 	=> 'youtube',
				'video_id' 	=> $yt_video['video_id'],
				'duration' 	=> $yt_video['duration']
			);
			// set video on slide
			fa_update_slide_options( $post->ID, $options );
			return;
		}		
	}
}
add_action('fa_assign_post_to_slider', 'third_party_fa_set_video', 10, 1);

/**
 * Process slides for sliders made from posts and add video details if needed.
 * 
 * @param array $slides - array of slides
 * @param int $slider_id - the slider ID
 */
function third_party_fa_posts_videos( $slides, $slider_id ){
	// if only one post, process it
	if( is_object( $slides ) ){
		third_party_fa_set_video( $slides );
		return;
	}
	// process multiple posts
	foreach( $slides as $post ){
		third_party_fa_set_video( $post );
	}	
}
add_action( 'fa_slider_post_slides', 'third_party_fa_posts_videos', 10, 2 );