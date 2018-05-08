<?php

																	/* **** */
																	/* INIT */
																	/* **** */

function wps_friends_init() {
	wp_enqueue_script('wps-friendship-js', plugins_url('wps_friends.js', __FILE__), array('jquery'));	
	wp_localize_script('wps-friendship-js', 'wps_friendships_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
	wp_enqueue_style('wps-friends', plugins_url('wps_friends.css', __FILE__), 'css');
	// Anything else?
	do_action('wps_friends_init_hook');
}
																	/* ********** */
																	/* SHORTCODES */
																	/* ********** */
function wps_friends_status($atts) {

	// Init
	add_action('wp_footer', 'wps_friends_init');

	$html = '';
	global $current_user;

	if (is_user_logged_in()):

		// Shortcode parameters
        $values = wps_get_shortcode_options('wps_friends_status');
		extract( shortcode_atts( array(
			'user_id' => '',
			'friends_yes' => wps_get_shortcode_value($values, 'wps_friends_status-friends_yes', __('You are friends', WPS2_TEXT_DOMAIN)),
			'friends_pending' => wps_get_shortcode_value($values, 'wps_friends_status-friends_pending', __('You have requested to be friends', WPS2_TEXT_DOMAIN)),
			'friend_request' => wps_get_shortcode_value($values, 'wps_friends_status-friend_request', __('You have a friends request', WPS2_TEXT_DOMAIN)),
			'friends_no' => wps_get_shortcode_value($values, 'wps_friends_status-friends_no', __('You are not friends', WPS2_TEXT_DOMAIN)),
			'before' => '',
			'styles' => true,
            'after' => '',
		), $atts, 'wps_friends_status' ) );

		if (!$user_id) $user_id = wps_get_user_id();

		if ($user_id != $current_user->ID):

			$friends = wps_are_friends($current_user->ID, $user_id);

			if ($friends['status']):
				if ($friends['status'] == 'publish'):
					$html .= $friends_yes;
				else:
					if ($friends['direction'] == 'to'):
						$html .= $friends_pending;
					else:
						$html .= $friend_request;
					endif;
				endif;
			else:
				$html .= $friends_no;
			endif;

		endif;

	endif;

	if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_friends_status', $before, $after, $styles, $values);

	return $html;

}

function wps_friends_add_button($atts) {

	// Init
	add_action('wp_footer', 'wps_friends_init');

	$html = '';
	global $current_user;

	if (is_user_logged_in() && !get_option('wps_friendships_all')):

		// Shortcode parameters
        $values = wps_get_shortcode_options('wps_friends_add_button');
		extract( shortcode_atts( array(
			'user_id' => 0,
			'label' => wps_get_shortcode_value($values, 'wps_friends_add_button-label', __('Make friends', WPS2_TEXT_DOMAIN)),
			'cancel_label' => wps_get_shortcode_value($values, 'wps_friends_add_button-cancel_label', __('Cancel friendship', WPS2_TEXT_DOMAIN)),
			'cancel_request_label' => wps_get_shortcode_value($values, 'wps_friends_add_button-cancel_request_label', __('Cancel friendship request', WPS2_TEXT_DOMAIN)),
			'class' => wps_get_shortcode_value($values, 'wps_friends_add_button-class', ''),
			'before' => '',
			'styles' => true,
            'after' => '',
		), $atts, 'wps_friends_add' ) );

		if (!$user_id) $user_id = wps_get_user_id();

		if ($user_id && $user_id != $current_user->ID):

			$html .= '<div class="wps_friends_add_button">';

				$html .= '<input type="hidden" id="plugins_url" value="'.plugins_url( '', __FILE__ ).'" />';

				$friends = wps_are_friends($current_user->ID, $user_id);
				if (!$friends['status']):

					$html .= '<button type="submit" rel="'.$user_id.'" class="wps_button wps_friends_add '.$class.'">'.$label.'</button>';

				else:

					if ($friends['status'] == 'publish'):
						$html .= '<button type="submit" rel="'.$friends['ID'].'" class="wps_button wps_friends_cancel '.$class.'">'.$cancel_label.'</button>';
					else:
						$html .= '<button type="submit" rel="'.$friends['ID'].'" class="wps_button wps_pending_friends_reject '.$class.'">'.$cancel_request_label.'</button>';
					endif;

				endif;

			$html .= '</div>';

		endif;


	endif;

	if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_friends_add_button', $before, $after, $styles, $values);

	return $html;

}

function wps_friends($atts) {

	// Init
	add_action('wp_footer', 'wps_friends_init');

	$html = '';
	global $current_user;

	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_friends');
	extract( shortcode_atts( array(
		'user_id' => false,
		'count' => wps_get_shortcode_value($values, 'wps_friends-count', 100),
		'size' => wps_get_shortcode_value($values, 'wps_friends-size', 64),
		'link' => wps_get_shortcode_value($values, 'wps_friends-link', true),
		'show_last_active' => wps_get_shortcode_value($values, 'wps_friends-show_last_active', true),
		'last_active_text' => wps_get_shortcode_value($values, 'wps_friends-last_active_text', __('Last seen:', WPS2_TEXT_DOMAIN)),
		'last_active_format' => wps_get_shortcode_value($values, 'wps_friends-last_active_format', __('%s ago', WPS2_TEXT_DOMAIN)),
		'private' => wps_get_shortcode_value($values, 'wps_friends-private', __('Private information', WPS2_TEXT_DOMAIN)),
		'none' => wps_get_shortcode_value($values, 'wps_friends-none', __('No friends', WPS2_TEXT_DOMAIN)),
		'layout' => wps_get_shortcode_value($values, 'wps_friends-layout', 'list'), // list|fluid
        'logged_out_msg' => wps_get_shortcode_value($values, 'wps_friends-logged_out_msg', __('You must be logged in to view this page.', WPS2_TEXT_DOMAIN)),
		'remove_all_friends' => wps_get_shortcode_value($values, 'wps_friends-remove_all_friends', true),
        'remove_all_friends_msg' => wps_get_shortcode_value($values, 'wps_friends-remove_all_friends_msg', __('Remove all friends', WPS2_TEXT_DOMAIN)),
        'remove_all_friends_sure_msg' => wps_get_shortcode_value($values, 'wps_friends-remove_all_friends_sure_msg', __('Are you sure? This cannot be undone!', WPS2_TEXT_DOMAIN)),
        'login_url' => wps_get_shortcode_value($values, 'wps_friends-login_url', ''),        
		'before' => '',
		'styles' => true,
        'after' => '',
	), $atts, 'wps_friends' ) );

	if (!$user_id)
		$user_id = wps_get_user_id();
    
    if (isset($_GET['user_id'])) $user_id = $_GET['user_id'];

    if (is_user_logged_in()) {
        
        if (current_user_can('manage_options') && !$login_url && function_exists('wps_login_init')):
            $html = wps_admin_tip($html, 'wps_friends', __('Add login_url="/example" to the [wps-friends] shortcode to let users login and redirect back here when not logged in.', WPS2_TEXT_DOMAIN));
        endif;                
    
        $friends = wps_are_friends($current_user->ID, $user_id);
        // By default same user, and friends of user, can see profile
        $user_can_see_friends = ($current_user->ID == $user_id || $friends['status'] == 'publish') ? true : false;
        $user_can_see_friends = apply_filters( 'wps_check_friends_security_filter', $user_can_see_friends, $user_id, $current_user->ID );

        if ($user_can_see_friends):

            global $wpdb;
            if (!get_option('wps_friendships_all')):
                $sql = "SELECT p.ID, m1.meta_value as wps_member1, m2.meta_value as wps_member2
                    FROM ".$wpdb->prefix."posts p 
                    LEFT JOIN ".$wpdb->prefix."postmeta m1 ON p.ID = m1.post_id
                    LEFT JOIN ".$wpdb->prefix."postmeta m2 ON p.ID = m2.post_id
                    WHERE p.post_type='wps_friendship'
                      AND p.post_status='publish'
                      AND m1.meta_key = 'wps_member1'
                      AND m2.meta_key = 'wps_member2'
                      AND (m1.meta_value = %d OR m2.meta_value = %d)";
                $get_friends = $wpdb->get_results($wpdb->prepare($sql, $user_id, $user_id));
            else:
                $site_members = get_users( 'blog_id='.get_current_blog_id() );
                $get_friends = array();
                foreach ($site_members as $member):
                    $row_array['wps_member1'] = $user_id;
                    $row_array['wps_member2'] = $member->ID;
                    array_push($get_friends,$row_array);
                endforeach;
            endif;

            if ($get_friends):
        
                // Show remove all friends option, if on their own page
                if ($remove_all_friends && $user_id == $current_user->ID)
                	$html .= '<p><a id="wps_remove_all_friends" data-sure="'.$remove_all_friends_sure_msg.'" href="javascript:void(0);">'.$remove_all_friends_msg.'</a></p>';

                // Put into array so they can be sorted
                $friends = array();
                foreach ($get_friends as $friend):
        
                    $row_array = array();
                            
                    if (is_array($friend)):
                        $other_member = $friend['wps_member1'] == $user_id ? $friend['wps_member2'] : $friend['wps_member1'];
                    else:
                        $other_member = $friend->wps_member1 == $user_id ? $friend->wps_member2 : $friend->wps_member1;
                    endif;
        
                    if (!wps_is_account_closed($other_member)):
                        $row_array['friend_id'] = $other_member;
                        $row_array['last_active'] = strtotime(get_user_meta($other_member, 'wpspro_last_active', true));
                        array_push($friends,$row_array);
                    endif;
                endforeach;

                // Sort friends by when last active
                $sort = array();
                $order = 'last_active';
                $orderby = 'DESC';
                foreach($friends as $k=>$v) {
                    $sort[$order][$k] = $v[$order];
                }
                $orderby = $orderby == "ASC" ? SORT_ASC : SORT_DESC;
                array_multisort($sort[$order], $orderby, $friends);

                // Show $count number of friends
                $c=0;
                foreach ($friends as $friend):

                    $the_friend = get_user_by('id', $friend['friend_id']);
                    if ($the_friend):

                        // Get profile_security of the_friend
                        $user_can_see_friend = true;
                        $user_can_see_friend = apply_filters( 'wps_check_friends_security_filter', $user_can_see_friend, $friend['friend_id'], $current_user->ID );

                        if ($user_can_see_friend):

                            $html .= '<div id="wps_friends"';
                                if ($layout == 'fluid') $html .= ' style="min-width: 235px; float:left;"';
                                $html .= '>';

                                $html .= '<div class="wps_friends_friend" style="position:relative;padding-left: '.($size+10).'px">';
                                if ($size):
                                    $html .= '<div class="wps_friends_friend_avatar" style="margin-left: -'.($size+10).'px">';
                                        $html .= wps_friend_avatar($friend['friend_id'], $size, $link);
                                    $html .= '</div>';
                                endif;
                                $html .= '<div class="wps_friends_friend_avatar_display_name">';
                                    $html .= wps_display_name(array('user_id'=>$friend['friend_id'], 'link'=>$link));
                                $html .= '</div>';
                                if ($show_last_active && $friend['last_active']):
                                    $html .= '<div class="wps_friends_friend_avatar_last_active">';
                                        $html .= html_entity_decode($last_active_text).' ';
                                        $html .= sprintf(html_entity_decode($last_active_format), human_time_diff($friend['last_active'], current_time('timestamp', 1)), WPS2_TEXT_DOMAIN);
                                    $html .= '</div>';
                                endif;
                                $html .= '</div>';

                            $html .= '</div>';

                        endif;

                    endif;

                    $c++;
                    if ($c == $count) break;		
                endforeach;
            else:
                if ($user_id) $html .= '<div id="wps_friends_none_msg">'.$none.'</div>';
            endif;

        else:

            if ($user_id) $html .= '<div id="wps_friends_private_msg">'.$private.'</div>';

        endif;

    } else {
        
        if (!is_user_logged_in() && $logged_out_msg):
            $query = wps_query_mark(get_bloginfo('url').$login_url);
            if ($login_url) $html .= sprintf('<a href="%s%s%sredirect=%s">', get_bloginfo('url'), $login_url, $query, wps_root( $_SERVER['REQUEST_URI'] ));
            $html .= $logged_out_msg;
            if ($login_url) $html .= '</a>';
        endif;
        
    }
    
	if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_friends', $before, $after, $styles, $values);

	return $html;
}

function wps_friends_pending($atts) {

	// Init
	add_action('wp_footer', 'wps_friends_init');

	$html = '';
	global $current_user;

	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_friends_pending');
	extract( shortcode_atts( array(
        'user_id' => false,
		'count' => wps_get_shortcode_value($values, 'wps_friends_pending-count', 10),
		'size' => wps_get_shortcode_value($values, 'wps_friends_pending-size', 64),
		'link' => wps_get_shortcode_value($values, 'wps_friends_pending-link', true),
		'class' => wps_get_shortcode_value($values, 'wps_friends_pending-class', ''),
		'accept_request_label' => wps_get_shortcode_value($values, 'wps_friends_pending-accept_request_label', __('Accept', WPS2_TEXT_DOMAIN)),
		'reject_request_label' => wps_get_shortcode_value($values, 'wps_friends_pending-reject_request_label', __('Reject', WPS2_TEXT_DOMAIN)),
		'none' => wps_get_shortcode_value($values, 'wps_friends_pending-none', ''),
		'before' => '',
		'styles' => true,
        'after' => '',
	), $atts, 'wps_friends' ) );

    if (!$user_id) $user_id = wps_get_user_id();

    if ($user_id):

		if (isset($_POST['wps_friends_pending'])):

			if ($_POST['wps_friends_pending'] == 'reject'):

				$post = get_post ($_POST['wps_friends_post_id']);
				if ($post):
					$member1 = get_post_meta($post->ID, 'wps_member1', true);
					$member2 = get_post_meta($post->ID, 'wps_member2', true);
					if ($member1 == $current_user->ID || $member2 == $current_user->ID)
						wp_delete_post( $post->ID, true );
				endif;

			endif;		

		endif;

		if ($current_user->ID == $user_id):

			$args = array (
				'post_type'              => 'wps_friendship',
				'posts_per_page'         => $count,
				'post_status'			 => 'pending',
				'meta_query' => array(
					array(
						'key'       => 'wps_member2', // recipient of request is second user meta field
						'compare'   => '=',
						'value'     => $user_id,
					),
				),		
			);


			global $post;
			$loop = new WP_Query( $args );
			if ($loop->have_posts()) {
				$html .= '<div class="wps_pending_friends">';
				while ( $loop->have_posts() ) : $loop->the_post();
					$member1 = get_post_meta( $post->ID, 'wps_member1', true );
	                
	                $html .= '<div class="wps_pending_friends_friend">';
	                    if ($size):
	                        $html .= '<div class="wps_pending_friends_friend_avatar">';
	                            $html .= wps_friend_avatar($member1, $size, $link);
	                        $html .= '</div>';
	                    endif;
	                    $html .= '<div class="wps_pending_friends_friend_display_name">';
	                        $html .= wps_display_name(array('user_id'=>$member1, 'link'=>$link));
	                        $html .= '<div class="wps_pending_friends_accept_reject">';
	                        $html .= '<button type="submit" rel="'.$post->ID.'" class="wps_button wps_pending_friends_accept '.$class.'">'.$accept_request_label.'</button>';
	                        $html .= '<button type="submit" rel="'.$post->ID.'" class="wps_button wps_pending_friends_reject '.$class.'">'.$reject_request_label.'</button>';
	                        $html .= '<input type="hidden" id="plugins_url" value="'.plugins_url( '', __FILE__ ).'" />';
	                        $html .= '</div>';
	                    $html .= '</div>';
	                $html .= '</div>';

				endwhile; 
				$html .= '</div>';		
			} else {
				$html .= $none;
			}
			wp_reset_query();

			if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_friends_pending', $before, $after, $styles, $values);
	    
		endif;

	endif;
	
	return $html;

}

function wps_friends_count($atts) {

    // Init
    add_action('wp_footer', 'wps_friends_init');

    $html = '';
    global $current_user;

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_friends_count');        
    extract( shortcode_atts( array(
    	'user_id' => wps_get_shortcode_value($values, 'wps_friends_count-user_id', ''),
        'status' => wps_get_shortcode_value($values, 'wps_friends_count-status', 'accepted'),
        'url' => wps_get_shortcode_value($values, 'wps_friends_count-url', ''),
        'before' => '',
        'styles' => true,
        'after' => '',
    ), $atts, 'wps_friends_count' ) );    
    
    $html = '';

    if (is_user_logged_in()) {	

		if (!$user_id) {
			$user_id = wps_get_user_id();
		} else {
			if ($user_id == 'user') $user_id = $current_user->ID;
		}

		if ($status == 'accepted'):
	    	$friends = wps_get_friends($user_id, false);
	    else:
	    	$friends = wps_get_pending_friends($user_id, false);
	    endif;
        if ($url) $html .= '<a class="wps_friends_count_link" href="'.$url.wps_query_mark($url).'user_id='.$user_id.'">';
    	$html .= '<span class="wps_friends_count">'.count($friends).'</span>';
        if ($url) $html .= '</a>';

        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_friends_count', $before, $after, $styles, $values);

    }
    
    return $html;

}

function wps_alerts_friends($atts) {

    // Init
    add_action('wp_footer', 'wps_friends_init');

    $html = '';
    global $current_user;

    if (is_user_logged_in()) {	
        
        // Shortcode parameters
        $values = wps_get_shortcode_options('wps_alerts_friends');        
        extract( shortcode_atts( array(
            'flag_size' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_size', 24),
            'flag_pending_size' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_pending_size', 10),
            'flag_pending_top' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_pending_top', 6),
            'flag_pending_left' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_pending_left', 8),
            'flag_pending_radius' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_pending_radius', 8),
            'flag_url' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_url', ''),
            'flag_src' => wps_get_shortcode_value($values, 'wps_alerts_friends-flag_src', ''),
            'before' => '',
            'styles' => true,
            'after' => '',
        ), $atts, 'wps_alerts_friends' ) );

        $args = array (
            'post_type'              => 'wps_friendship',
            'posts_per_page'         => -1,
            'post_status'			 => 'pending',
            'meta_query' => array(
                array(
                    'key'       => 'wps_member2', // recipient of request is second user meta field
                    'compare'   => '=',
                    'value'     => $current_user->ID
                ),
            ),		
        );


        global $post;
        $loop = new WP_Query( $args );
        $unread_count = $loop->found_posts;

        wp_reset_query();

        $html .= '<div id="wps_alerts_friends_flag" style="width:'.$flag_size.'px; height:'.$flag_size.'px;" >';
        $html .= '<a href="'.$flag_url.'">';
        $src = (!$flag_src) ? plugins_url('images/friends'.get_option('wpspro_flag_colors').'.png', __FILE__) : $flag_src;
        $html .= '<img style="width:'.$flag_size.'px; height:'.$flag_size.'px;" src="'.$src.'" />';
        if ($unread_count):
            $html .= '<div id="wps_alerts_friends_flag_unread" style="position: absolute; padding-top: '.($flag_pending_size*0.2).'px; line-height:'.($flag_pending_size*0.8).'px; font-size:'.($flag_pending_size*0.8).'px; border-radius: '.$flag_pending_radius.'px; top:'.$flag_pending_top.'px; left:'.$flag_pending_left.'px; width:'.$flag_pending_size.'px; height:'.$flag_pending_size.'px;">'.$unread_count.'</div>';
        endif;
        $html .= '</a></div>';
        if (!$flag_url) $html .= '<div class="wps_error">'.__('Set flag_url in WPS Pro->Setup->Default Shortcode Settings (Friends), or in the shortcode, in shortcode for the link, probably to the page with [wps-friends] on it.', WPS2_TEXT_DOMAIN).'</div>';
        
        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_alerts_friends', $before, $after, $styles, $values);

    }

    return $html;
    
}


if (!is_admin()) add_shortcode(WPS_PREFIX.'-friends', 'wps_friends');
if (!is_admin()) add_shortcode(WPS_PREFIX.'-friends-status', 'wps_friends_status');
if (!is_admin()) add_shortcode(WPS_PREFIX.'-friends-pending', 'wps_friends_pending');
if (!is_admin()) add_shortcode(WPS_PREFIX.'-friends-add-button', 'wps_friends_add_button');
if (!is_admin()) add_shortcode(WPS_PREFIX.'-friends-count', 'wps_friends_count');
if (!is_admin()) add_shortcode(WPS_PREFIX.'-alerts-friends', 'wps_alerts_friends');


?>