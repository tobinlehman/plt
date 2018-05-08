<?php 
if( !defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN') ){
	die();
}

$options = get_option( 'fa_plugin_options', array() );
if( isset( $options['settings']['complete_uninstall'] ) && $options['settings']['complete_uninstall'] ){
	// 1. Remove plugin options
	delete_option('fa_plugin_options');
	
	// 2. Remove Custom Post Slider
	$args = array(
		'post_type' => 'fa_slider',
		'post_status' => 'any'
	);
	$sliders = get_posts( $args );	
	if( $sliders ){
		foreach( $sliders as $slider ){
			wp_delete_post( $slider->ID, true );
		}
	}
	
	// 4. Remove slide tax
	global $wpdb;
	$taxonomy = 'fa_slide_categories';
	// Prepare & execute SQL, Delete Terms
	$wpdb->get_results( $wpdb->prepare( "DELETE t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s')", $taxonomy ) );
	// Delete Taxonomy
	$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	
	// 3. Remove Custom Post Slide
	$args = array(
		'post_type' => 'fa_slide',
		'status' 	=> 'any'
	);
	$slides = get_posts( $args );
	if( $slides ){
		foreach( $slides as $slide ){
			wp_delete_post( $slide->ID, true );
		}
	}
	
	// 4. Remove Slide Options from other posts used as slides
	global $wpdb;
	$query = "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_fa_slide_settings'";
	$wpdb->query( $query );
	
	// 5. Delete transients
	delete_transient('fa_version');	
}