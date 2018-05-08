<?php
/*
Plugin Name: Insert or Embed Articulate Content into Wordpress Premium
Plugin URI: http://www.elearningfreak.com/presenter/insert-or-embed-articulate-content-into-wordpress-plugin-premium/ ?
Description: Enables unlimited uploads for the Articulate plugin
Version: 4.7
Author: Brian Batt
Author URI: http://www.elearningfreak.com
*/

require_once dirname( __FILE__ ) . '/classes/tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'articulate_register_required_plugins' );
add_filter('quiz_embeder_count', 'quiz_embeder_count_premium');
define("ARTICULATE_PREMIUM_VER", '1.0.0');

function articulate_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

	
		// This is an example of how to include a plugin from the WordPress Plugin Repository.
		array(
			'name'      => 'Insert or Embed Articulate Content into WordPress',
			'slug'      => 'insert-or-embed-articulate-content-into-wordpress',
			'required'  => true,
		),

	

	);

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'articulate',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'articulate-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.

	);

	tgmpa( $plugins, $config );
}


function quiz_embeder_count_premium($count){
	
	$count = 100 * 100;
	
	return $count;
	
}
?>