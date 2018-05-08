<?php
/**
 * This file adds the Landing template to the Minimum Pro Theme.
 *
 * @author StudioPress
 * @package Minimum Pro
 * @subpackage Customizations
 */


get_header();

do_action( 'genesis_before_content_sidebar_wrap' );



	// do_action( 'genesis_before_content' );
	// genesis_markup( array(
	// 	'open'   => '<main %s>',
	// 	'context' => 'content',
	// ) );
	// 	do_action( 'genesis_before_loop' );
	// 	do_action( 'genesis_loop' );
	// 	do_action( 'genesis_after_loop' );
	// genesis_markup( array(
	// 	'close' => '</main>', // End .content.
	// 	'context' => 'content',
	// ) );
	// do_action( 'genesis_after_content' );

	if ( have_posts() ) : while ( have_posts() ) : the_post();
	
	    the_content();
	     
	endwhile; else :
	endif; ?>


<?php

get_template_part('eunit', 'footer');

?>