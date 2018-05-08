<?php
// Hook into core get users AJAX function
add_action( 'wp_ajax_wps_get_users', 'wps_get_users_ajax' ); 

// AJAX functions for activity
add_action( 'wp_ajax_wps_activity_comment_add', 'wps_activity_comment_add' ); 
add_action( 'wp_ajax_wps_activity_settings_delete', 'wps_activity_settings_delete' ); 
add_action( 'wp_ajax_wps_activity_settings_sticky', 'wps_activity_settings_sticky' ); 
add_action( 'wp_ajax_wps_activity_settings_unsticky', 'wps_activity_settings_unsticky' ); 
add_action( 'wp_ajax_wps_comment_settings_delete', 'wps_comment_settings_delete' ); 
add_action( 'wp_ajax_wps_activity_unhide_all', 'wps_activity_unhide_all' ); 
add_action( 'wp_ajax_wps_activity_settings_hide', 'wps_activity_settings_hide' ); 
add_action( 'wp_ajax_wps_return_activity_posts', 'wps_return_activity_posts' ); 
add_action( 'wp_ajax_nopriv_wps_return_activity_posts', 'wps_return_activity_posts' );  // Logged out

/* RETURN ACTIVITY */
function wps_return_activity_posts() {

	global $current_user;

    $data = $_POST['data'];
    $arr = $data['arr'];
    $atts = $data['atts'];
    $user_id = $_POST['user_id'];
    $nonce = $_POST['nonce'];
    
    $nonce_check = wp_verify_nonce( $nonce, 'wps_get_activity_nonce_'.$user_id );

    if ($nonce_check):
    
        if ($arr = unserialize(stripslashes($arr))) {

            if ($arr):

                $atts = unserialize(stripslashes($atts));

                // Get shortcode parameters
                $values = wps_get_shortcode_options('wps_activity');    
                extract( shortcode_atts( array(
                    'class' => wps_get_shortcode_value($values, 'wps_activity-class', ''),                    
                    'report' => wps_get_shortcode_value($values, 'wps_activity-report', true),
                    'report_label' => wps_get_shortcode_value($values, 'wps_activity-report_label', __('Report', WPS2_TEXT_DOMAIN)), 
                    'report_email' => wps_get_shortcode_value($values, 'wps_activity-report_email', get_bloginfo('admin_email')), 
                    'sticky_label' => wps_get_shortcode_value($values, 'wps_activity-sticky_label', __('Stick', WPS2_TEXT_DOMAIN)), // blank to hide
                    'unsticky_label' => wps_get_shortcode_value($values, 'wps_activity-unsticky_label', __('Unstick', WPS2_TEXT_DOMAIN)),
                    'delete_label' => wps_get_shortcode_value($values, 'wps_activity-delete_label', __('Delete', WPS2_TEXT_DOMAIN)), // blank to hide
                    'hide_label' => wps_get_shortcode_value($values, 'wps_activity-hide_label', __('Hide', WPS2_TEXT_DOMAIN)), // blank to hide
                    'avatar_size' => wps_get_shortcode_value($values, 'wps_activity-avatar_size', 64),                    
                    'label' => wps_get_shortcode_value($values, 'wps_activity-label', __('Comment', WPS2_TEXT_DOMAIN)),
                    'link' => wps_get_shortcode_value($values, 'wps_activity-link', true),
                    'more' =>  wps_get_shortcode_value($values, 'wps_activity-more', 50),
                    'more_label' =>  wps_get_shortcode_value($values, 'wps_activity-more_label', __('more', WPS2_TEXT_DOMAIN)),    
                    'comment_avatar_size' => wps_get_shortcode_value($values, 'wps_activity-comment_avatar_size', 40),
                    'comment_size' => wps_get_shortcode_value($values, 'wps_activity-comment_size', 5),
                    'comment_size_text_plural' => wps_get_shortcode_value($values, 'wps_activity-comment_size_text_plural', __('Show previous %d comments...', WPS2_TEXT_DOMAIN)),
                    'comment_size_text_singular' => wps_get_shortcode_value($values, 'wps_activity-comment_size_text_singular', __('Show previous comment...', WPS2_TEXT_DOMAIN)),          
                    'date_format' => wps_get_shortcode_value($values, 'wps_activity-date_format', __('%s ago', WPS2_TEXT_DOMAIN)),                    
                    'allow_replies' => wps_get_shortcode_value($values, 'wps_activity-allow_replies', true),                    
                    'load_more_label' => wps_get_shortcode_value($values, 'wps_activity-load_more_label', __('more...', WPS2_TEXT_DOMAIN)),                    
                ), $atts, 'wps_activity' ) );

                // Protect email from tags
                $report_email = str_replace('@', '[@]', $report_email);            

                $shown_count = 0;
                $array_count = 0;
                $page_size = $_POST['page'];
                $start = $_POST['start'];
                $this_user = $_POST['this_user'];

                $shown = array();

                foreach ($arr as $i):

                    $array_count++;

                    if ($array_count >= $start && $shown_count < $page_size && !in_array($i['ID'], $shown) && $i['ID']):

                        // Check not hidden
                        $hidden_list = get_post_meta ($i['ID'], 'wps_activity_hidden', true);    
                        $hidden = ($hidden_list && in_array((int)$user_id, $hidden_list)) ? true : false;

                        if (!$hidden):

                            array_push($shown, $i['ID']);
                            $item = get_post($i['ID']);

                            $item_html = '';
                            $is_sticky = get_post_meta( $item->ID, 'wps_sticky', true );
                            $is_sticky_css = $is_sticky ? ' wps_sticky' : '';

                            $item_html .= '<div class="wps_activity_item'.$is_sticky_css.'" id="wps_activity_'.$item->ID.'" style="position:relative;padding-left: '.($avatar_size+10).'px">';

                                $item_html .= '<div id="wps_activity_'.$item->ID.'_content" class="wps_activity_content">';

                                    // Settings
                                    $settings = '';
                                    
                                    if  ($item->post_author == $this_user || current_user_can('manage_options')):
                                        $settings .= '<div class="wps_activity_settings" style="display:none">';
                                            $settings .= '<img style="height:15px;width:15px;" src="'.plugins_url('images/wrench'.get_option('wpspro_icon_colors').'.png', __FILE__).'" />';
                                        $settings .= '</div>';
                                        $settings .= '<div class="wps_activity_settings_options" style="display:none">';
                                            if (!get_option('activity_sticky_admin_only') || current_user_can('manage_options')):
                                                if (!$is_sticky && $sticky_label) $settings .= '<a class="wps_activity_settings_sticky" rel="'.$item->ID.'" href="javascript:void(0);">'.$sticky_label.'</a>';
                                                if ($is_sticky && $unsticky_label) $settings .= '<a class="wps_activity_settings_unsticky" rel="'.$item->ID.'" href="javascript:void(0);">'.$unsticky_label.'</a>';
                                            endif;
                                            if ($delete_label) $settings .= '<a class="wps_activity_settings_delete" rel="'.$item->ID.'" href="javascript:void(0);">'.$delete_label.'</a>';
                                            if ($hide_label) $settings .= '<a class="wps_activity_settings_hide" rel="'.$item->ID.'" href="javascript:void(0);">'.$hide_label.'</a>';
                                            if ($report) $settings .= '<a class="wps_activity_settings_report" rel="'.$item->ID.'" href="mailto:'.$report_email.'?subject='.wps_curPageURL().wps_query_mark(wps_curPageURL()).'view='.$item->ID.'">'.$report_label.'</a>';
                                            $settings = apply_filters( 'wps_activity_item_setting_filter', $settings, $atts, $item, $user_id, $this_user);
                                        $settings .= '</div>';
                                    endif;
                                    $settings = apply_filters( 'wps_activity_item_settings_filter', $settings, $atts, $item, $user_id, $this_user);
                                    $item_html .= $settings;

                                    // Hide/Report
                                    if ($this_user && $item->post_author != $this_user && !current_user_can('manage_options')):
                                        $item_html .= '<div class="wps_activity_settings" style="display:none">';
                                            $item_html .= '<img style="height:15px;width:15px;" src="'.plugins_url('images/wrench'.get_option('wpspro_icon_colors').'.png', __FILE__).'" />';
                                        $item_html .= '</div>';
                                        $item_html .= '<div class="wps_activity_settings_options" style="display:none">';
                                            if ($hide_label) $item_html .= '<a class="wps_activity_settings_hide" rel="'.$item->ID.'" href="javascript:void(0);">'.$hide_label.'</a>';
                                            if ($report) $item_html .= '<a class="wps_activity_settings_report" rel="'.$item->ID.'" href="mailto:'.$report_email.'?subject='.wps_curPageURL().wps_query_mark(wps_curPageURL()).'view='.$item->ID.'">'.$report_label.'</a>';
                                        $item_html .= '</div>';
                                    endif;            

                                    // Avatar
                                    $item_html .= '<div class="wps_activity_item_avatar" style="float: left; margin-left: -'.($avatar_size+10).'px">';
                                        if (strpos(WPS_CORE_PLUGINS, 'core-avatar') !== false):
                                            $item_html .= user_avatar_get_avatar($item->post_author, $avatar_size, true, 'thumb');
                                        else:
                                            $item_html .= get_avatar($item->post_author, $avatar_size);
                                        endif;
                                    $item_html .= '</div>';            

                                    // Meta
                                    $recipients = '';
                                    $item_html .= '<div class="wps_activity_item_meta">';
                                        $item_html .= wps_display_name(array('user_id'=>$item->post_author, 'link'=>$link));
                                        $target_ids = get_post_meta( $item->ID, 'wps_target', true );
                                        if (is_array($target_ids)):
                                            $c=0;
                                            $recipients = ' &rarr; ';
                                            foreach ($target_ids as $target_id):
                                                if ( $target_id != $item->post_author):
                                                    if ($c) $recipients .= ', ';
                                                    $recipients .= wps_display_name(array('user_id'=>$target_id, 'link'=>$link));
                                                    $c++;
                                                endif;
                                            endforeach;	
                                        else:
                                            if ( $target_ids != $item->post_author):
                                                $recipient_display_name = wps_display_name(array('user_id'=>$target_ids, 'link'=>$link));
                                                if ($recipient_display_name):
                                                    $recipients = ' &rarr; '.$recipient_display_name;
                                                endif;
                                            endif;
                                        endif;

                                        // In case of changes
                                        $recipients = apply_filters( 'wps_activity_item_recipients_filter', $recipients, $atts, $target_ids, $item->ID, $user_id, $this_user );
                                        $item_html .= $recipients;

                                        // Date
                                        $item_html .= '<br />';
                                        $item_html .= '<div class="wps_ago">'.sprintf($date_format, human_time_diff(strtotime($item->post_date), current_time('timestamp', 0)), WPS2_TEXT_DOMAIN).'</div>';

                                        // Any more meta?
                                        // Passes $item_html, shortcodes options ($atts), current post ID ($item->ID), user page ($user_id), current users ID ($this_user)
                                        $item_html = apply_filters( 'wps_activity_item_meta_filter', $item_html, $atts, $item->ID, $user_id, $this_user );

                                    $item_html .= '</div>';                    

                                    /* POST */

                                    $post_words = $item->post_title;

                                    $post_words = str_replace('[a]', '<a', $post_words);
                                    $post_words = str_replace('[a2]', '>', $post_words);
                                    $post_words = str_replace('[/a]', '</a>', $post_words);

                                    if (strpos($post_words, '[q]') !== false && strpos($post_words, '[/q]') === false) $post_words .= '[/q]';
                                    $p = str_replace(': ', '<br />', $post_words);

                                    $p = str_replace('<p>', '', $p);
                                    $p = str_replace('</p>', '', $p);
                                    $p = '<div id="activity_item_'.$item->ID.'">'.$p.'</div>';

                                    // Look for quotes and paragraphs
                                    $p = str_replace('[q]', '<div class="wps_quoted_content">', $p);
                                    $p = str_replace('[/q]', '</div>', $p);
                                    $p = str_replace('[p]', '<div class="wps_p_content">', $p);
                                    $p = str_replace('[/p]', '</div>', $p);

                                    // Format
                                    $p = wps_formatted_content($p, true);

                                    // Check for any items (attachments)
                                    if ($i=strpos($p, '[items]')):
                                        $attachments_list = substr($p, $i+7, strlen($p)-($i+7));
                                        if (strpos($attachments_list, '[')) 
                                            $attachments_list = substr($attachments_list, 0, strpos($attachments_list, '['));
                                        $attachments_list = substr(strip_tags($attachments_list), 0, -1);

                                        $attachments = explode(',', strip_tags($attachments_list));
                                        $attachment_html = '<div class="wps_activity_item_attachments">';
                                        foreach ($attachments as $attachment):
                                            $desc = esc_html(get_post_meta($attachment, '_wps_desc', true));
                                            $source = esc_html(get_post_meta($attachment, '_wps_source', true));
                                            $image_src = wp_get_attachment_image_src( $attachment, 'full' );
                                            $attachment_html .= '<div class="wps_activity_item_attachment wps_activity_item_attachment_item">';
                                            $attr = array( 'title' => get_post_meta($attachment, '_wps_desc', true), 'alt' => get_post_meta($attachment, '_wps_desc', true) );
                                            $img = wp_get_attachment_image($attachment, 'thumbnail', false, $attr );            
                                            $attachment_html .= $img;
                                            $attachment_html .= '<div data-desc="'.$desc.'" data-source="'.$source.'" data-width="'.$image_src[1].'" data-height="'.$image_src[2].'" class="wps_activity_item_attachment_full">'.$image_src[0].'</div>';
                                            $attachment_html .= '</div>'; 
                                        endforeach;
                                        $attachment_html .= '<div style="clear:both"></div></div>';
                                        $p = str_replace('[items]'.$attachments_list, '', $p);
                                        $p .= $attachment_html;
                                    endif;

                                    // Shortern if necessary and applicable
                                    if (strpos($p, '[q]') === false && strpos($post_words, '[items]') === false):
                                        $words = explode(' ', $p, $more + 1);
                                        if (count($words)> $more):
                                            array_pop($words);
                                            array_push($words, '... [<span class="activity_item_more" rel="'.$item->ID.'" title="'.$more_label.'">'.$more_label.'</span>]');
                                            $item_html .= '<div class="wps_activity_item_post" id="activity_item_snippet_'.$item->ID.'">'.implode(' ', $words).'</div></div>';
                                            $item_html .= '<div style="display:none;" id="activity_item_full_'.$item->ID.'">'.$p.'</div>';
                                        else:
                                            $item_html .= '<div class="wps_activity_item_post" id="activity_item_'.$item->ID.'">'.$p.'</div>';
                                        endif;
                                    else:
                                        $item_html .= '<div class="wps_activity_item_post" id="activity_item_'.$item->ID.'">'.$p.'</div>';
                                    endif;

                                    // Final filter for handling anything else
                                    // Passes $item_html, shortcodes options ($atts), current post ID ($item->ID), post title ($item->post_stitle), user page ($user_id), current users ID ($this_user)
                                    $item_html = apply_filters( 'wps_activity_item_filter', $item_html, $atts, $item->ID, $item->post_title, $user_id, $this_user, $shown_count );          

                                    // Existing Comments
                                    $args = array(
                                        'post_id' => $item->ID,
                                        'orderby' => 'ID',
                                        'order' => 'ASC',
                                    );
                                    $comments = get_comments($args);
                                    if ($comments) {
                                        $comment_count = sizeof($comments);
                                        $item_html .= '<div class="wps_activity_comments">';

                                        $comments_shown = 0;
                                        foreach($comments as $comment) :

                                            $item_html .= '<a name="wps_comment_'.$item->ID.'"></a>';

                                            if ($comment_count > $comment_size && $comments_shown == 0):
                                                $previous = $comment_count-$comment_size > 1 ? sprintf($comment_size_text_plural, ($comment_count-$comment_size)) : sprintf($comment_size_text_singular, ($comment_count-$comment_size));
                                                $item_html .= '<div rel="'.$item->ID.'" class="wps_activity_hidden_comments">'.$previous.'</div>';
                                            endif;

                                            $hidden_style = ($comments_shown >= $comment_count - $comment_size) ? '' : 'display:none;';
                                            $item_html .= '<div id="wps_comment_'.$comment->comment_ID.'" class="wps_activity_comment wps_activity_item_'.$item->ID.'" style="'.$hidden_style.'position:relative;padding-left: '.($comment_avatar_size+10).'px">';

                                                // Settings
                                                if ($comment->user_id == $this_user || $item->post_author == $current_user->ID || current_user_can('manage_options')):
                                                    $item_html .= '<div class="wps_comment_settings" style="display:none">';
                                                        $item_html .= '<img style="height:15px;width:15px;" src="'.plugins_url('images/wrench'.get_option('wpspro_icon_colors').'.png', __FILE__).'" />';
                                                    $item_html .= '</div>';
                                                    $item_html .= '<div class="wps_comment_settings_options">';
                                                        $item_html .= '<a class="wps_comment_settings_delete" rel="'.$comment->comment_ID.'" href="javascript:void(0);">'.__('Delete comment', WPS2_TEXT_DOMAIN).'</a>';
                                                        if ($report) $item_html .= '<a class="wps_comment_settings_report" rel="'.$comment->ID.'" href="mailto:'.$report_email.'?subject='.wps_curPageURL().wps_query_mark(wps_curPageURL()).'view='.$item->ID.'">'.$report_label.'</a>';
                                                    $item_html .= '</div>';
                                                endif;

                                                // Avatar
                                                $item_html .= '<div class="wps_activity_post_comment_avatar" style="float:left; margin-left: -'.($comment_avatar_size+10).'px">';
                                                if (strpos(WPS_CORE_PLUGINS, 'core-avatar') !== false):
                                                    $item_html .= user_avatar_get_avatar($comment->user_id, $comment_avatar_size, true, 'thumb');
                                                else:
                                                    $item_html .= get_avatar($comment->user_id, $comment_avatar_size);
                                                endif;
                                                $item_html .= '</div>';

                                                // Name and date
                                                $item_html .= wps_display_name(array('user_id'=>$comment->user_id, 'link'=>$link));
                                                $item_html .= '<br />';
                                                $item_html .= '<div class="wps_ago">'.sprintf($date_format, human_time_diff(strtotime($comment->comment_date), current_time('timestamp', 0)), WPS2_TEXT_DOMAIN).'</div>';

                                                // Any other meta
                                                // Passes $item_html, shortcodes options ($atts), current post ID ($item->ID), current comment ID ($comment->comment_ID), user page ($user_id), current users ID ($this_user)
                                                $item_html = apply_filters( 'wps_activity_comment_meta_filter', $item_html, $atts, $item->ID, $comment->comment_ID, $user_id, $this_user );                                    

                                                // The Comment
                                                $item_html .= wps_formatted_content($comment->comment_content, true);

                                                // Filter to add anything to end of comment
                                                $item_html = apply_filters( 'wps_activity_post_comment_filter', $item_html, $atts, $item->ID, $comment->comment_ID, $user_id, $this_user );                                                

                                            $item_html .= '</div>';

                                            $comments_shown++;

                                        endforeach;

                                        $item_html .= '</div>';
                                    }

                                $item_html .= '</div>';

                                // Add new comment	
                                if (is_user_logged_in() && $allow_replies && !wps_is_account_closed($user_id)):
                                    $add_form = '<div class="wps_activity_post_comment_div">';
                                        $add_form .= '<input type="hidden" id="wps_activity_plugins_url" value="'.plugins_url( '', __FILE__ ).'" />';
                                        $add_form .= '<textarea class="wps_activity_post_comment" id="post_comment_'.$item->ID.'"></textarea>';
                                        $add_form .= '<button class="wps_button wps_activity_post_comment_button '.$class.'" data-link="'.$link.'" data-size="'.$comment_avatar_size.'" rel="'.$item->ID.'">'.$label.'</button>';
                                    $add_form .= '</div>';
                                    $add_form = apply_filters( 'wps_activity_new_comment_filter', $add_form, $atts, $item->ID, $user_id, $this_user );
                                    $item_html .= $add_form;
                                endif;

                            $item_html .= '</div>'; // end of post

                            // Parse for protected email addresses (to handle tags)
                            $item_html = str_replace('[@]', '@', $item_html);

                            $items .= $item_html;

                            $shown_count++;

                            if ($shown_count == $page_size) break;

                        endif;

                    endif;

                endforeach;

                if (count($arr) > $array_count)
                    $items .= '<div id="wps_activity_load_more_div"><button id="wps_activity_load_more" class="wps_button" data-count="'.$array_count.'">'.$load_more_label.'</button></div>';

                echo $items; // return HTML

            else:
                echo 'no activity: ';
                echo $data['arr'];
            endif;

        } else {
            echo 'could not unserialize activity: ';
            echo $data['arr'];
        }
    
    else:
        echo 'could not verify nonce for wps_get_activity_nonce_'.$user_id.': ';
        echo $nonce;
    endif;

	exit;
}

/* ADMIN - UNHIDE ALL POSTS */
function wps_activity_unhide_all() {
    
    global $wpdb;
    $post_id = $_POST['post_id'];
    $sql = "delete from ".$wpdb->prefix."postmeta where meta_key = 'wps_activity_hidden' and post_id = %d";
    $wpdb->query($wpdb->prepare($sql, $post_id));
    echo $post_id;
    exit;
    
}

/* HIDE POST */
function wps_activity_settings_hide() {

    global $current_user;
    
    $hidden = get_post_meta ($_POST['post_id'], 'wps_activity_hidden', true);
    if (!$hidden) $hidden = array();
    array_push($hidden, $current_user->ID);
    
	update_post_meta( $_POST['post_id'], 'wps_activity_hidden', $hidden );
    
	echo $_POST['post_id'];
    exit;

}

/* MAKE POST STICKY */
function wps_activity_settings_sticky() {

	if (update_post_meta( $_POST['post_id'], 'wps_sticky', true )) {
		echo $_POST['post_id'];
	} else {
		echo 0;
	}

}

/* MAKE POST UNSTICKY */
function wps_activity_settings_unsticky() {

	if (delete_post_meta( $_POST['post_id'], 'wps_sticky' )) {
		echo $_POST['post_id'];
	} else {
		echo 0;
	}

}

/* ADD COMMENT */
function wps_activity_comment_add() {

	global $current_user;
	$data = array(
	    'comment_post_ID' => $_POST['post_id'],
	    'comment_content' => strip_tags(trim($_POST['comment_content'])),
	    'comment_type' => 'wps_activity_comment',
	    'comment_parent' => 0,
	    'comment_author' => $current_user->user_login,
	    'user_id' => $current_user->ID,
	    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
	    'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
	    'comment_approved' => 1,
	);

	$new_id = wp_insert_comment($data);

	if ($new_id):

		// Any further actions?
		do_action( 'wps_activity_comment_add_hook', $_POST, $new_id );

        $item_html = '<div class="wps_activity_comment" style="position:relative;padding-left: '.($_POST['size']+10).'px">';

            // Avatar
            $item_html .= '<div class="wps_activity_post_comment_avatar" style="float:left; margin-left: -'.($_POST['size']+10).'px">';
                if (strpos(WPS_CORE_PLUGINS, 'core-avatar') !== false):
                    $item_html .= user_avatar_get_avatar($current_user->ID, $_POST['size'], true, 'thumb');
                else:
                    $item_html .= get_avatar($current_user->ID, $_POST['size']);
                endif;
            $item_html .= '</div>';

            // Name and date
            $item_html .= wps_display_name(array('user_id'=>$current_user->ID, 'link'=>$_POST['link']));
            $item_html .= '<br />';

            // The Comment
            $item_html .= (wps_formatted_content($_POST['comment_content']));

        $item_html .= '</div>';

		echo $item_html;
		
	else:
		echo 0;
	endif;


    exit;
    
}

/* DELETE POST */
function wps_activity_settings_delete() {

	$id = $_POST['id'];
	if ($id):
		global $current_user;
		$post = get_post($id);
		if ($post->post_author == $current_user->ID || current_user_can('manage_options')):
			if (wp_delete_post($id, true)):
                global $wpdb;
                $sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_parent = %d AND post_type = 'wps_alerts'";
                $alerts = $wpdb->get_results($wpdb->prepare($sql, $id));
                if ($alerts):
                    foreach ($alerts as $alert):
                        if (!wp_delete_post($alert->ID, true)) {
                            echo 'failed to delete alert '.$alert->ID.'. ';
                        }
                    endforeach;
                endif;
				echo 'success';
			else:
				echo 'failed to delete post '.$id;
			endif;
		else:
			echo 'not owner';
		endif;
	endif;
    
    exit;

}

/* DELETE COMMENT */
function wps_comment_settings_delete() {

	$id = $_POST['id'];
	if ($id):
		global $current_user;
		$comment = get_comment($id);
        $post = get_post($comment->comment_post_ID);
		if ($comment && $post && ($comment->user_id == $current_user->ID || $post->post_author == $current_user->ID || current_user_can('manage_options'))):
			if (wp_delete_comment($id, false)): // soft delete
                global $wpdb;
                $sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_parent = %d AND post_type = 'wps_alerts'";
                $alerts = $wpdb->get_results($wpdb->prepare($sql, $comment->comment_post_ID));
                if ($alerts):
                    foreach ($alerts as $alert):
                        if (!wp_delete_post($alert->ID, true)) {
                            echo 'failed to delete alert '.$alert->ID.'. ';
                        }
                    endforeach;
                endif;
				echo 'success';
			else:
				echo 'failed to delete comment '.$id;
			endif;
		else:
			echo 'not owner';
		endif;
	endif;
    
    exit;

}

?>
