<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

/* Modified by wilcosource - Start */
session_start();
$request_uri = $_SERVER['REQUEST_URI'];
//exit($request_uri);
/* Modified by wilcosource - End */

if ( !isset($wp_did_header) ) {

	$wp_did_header = true;

	// Load the WordPress library.
	require_once( dirname(__FILE__) . '/wp-load.php' );

    /* Modified by wilcosource - Start */
    
    echo "User id ".$_SESSION['user_id'];
    exit();
    
    
    
    /* Modified by wilcosource - End */
    
    
	// Set up the WordPress query.
	wp();
	// Load the theme template.
	require_once( ABSPATH . WPINC . '/template-loader.php' );
}
