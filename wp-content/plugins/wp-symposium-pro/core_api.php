<?php
																	/* ****************** */
																	/* CORE API FUNCTIONS */
																	/* ****************** */
/**
 * Gets last active timestamp for a user, optionally formatted
 *
 * @since 14.12.2
 *
 * @param   int     $user_id       The WordPress user ID
 * @param   mixed   $format        Set to false to return date/time value, or a string for formatting, eg: "Last active: %s ago"
 *
 * @return  mixed   Date/time value, formatted string, or false if no last active value available
 */
function wps_api_user_last_active($user_id, $format=false) {
    $datetime = false;
    $last_active = get_user_meta($user_id, 'wpspro_last_active', true);
    if ($last_active):
        if (!$format):
            $datetime = $last_active;
        else:
			      $datetime = sprintf($format, human_time_diff(strtotime($last_active), current_time('timestamp', 1)));
        endif;
    endif;
    return $datetime;
}


/**
 * Inserts a WordPress post of type wps_activity
 *
 * @since 14.12.2
 *
 * @param   string  $activity_post The activity post to be inserted
 * @param   int     $the_author_id ID of a WordPress member as the author of the activity post
 * @param   int     $the_target_id ID of a WordPress member as the target of the activity post (use $the_author_id for post to self/friends)
 * @param   array   $the_post      Optional $_POST to be further processed by wps_activity_post_add_hook
 * @param   array   $the_files     Optional $_FILES to be further processed by wps_activity_post_add_hook
 *
 * @return  int     ID of new WordPress post, or false if insert failed
 *
 * Note: This includes the wps_activity_post_add_hook hook, so alerts can be generated
 */
function wps_api_insert_activity_post($activity_post, $the_author_id, $the_target_id, $the_post=null, $the_files=null) {
                                  
	global $current_user;
    
    $new_id = false;

	if ( is_user_logged_in() ) {

        $post = array(
          'post_title'     => $activity_post,
          'post_status'    => 'publish',
          'post_type'      => 'wps_activity',
          'post_author'    => $the_author_id,
          'ping_status'    => 'closed',
          'comment_status' => 'open',
        );  
        $new_id = wp_insert_post( $post );

        if ($new_id):
            update_post_meta( $new_id, 'wps_target', $the_target_id );
            do_action( 'wps_activity_post_add_hook', $the_post, $the_files, $new_id );
        endif;

	}

    return $new_id;
}

?>