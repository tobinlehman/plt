<?php

																	/* **** */
																	/* INIT */
																	/* **** */

function wps_alerts_init() {
	// JS and CSS
	wp_enqueue_script('wps-alerts-js', plugins_url('wps_alerts.js', __FILE__), array('jquery'));	
	wp_enqueue_style('wps-alerts-css', plugins_url('wps_alerts.css', __FILE__), 'css');
	wp_localize_script('wps-alerts-js', 'wps_alerts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));    	

	// Select2 replacement drop-down list from core
	wp_enqueue_script('wps-select2-js', plugins_url('../../wp-symposium-pro/js/select2.min.js', __FILE__), array('jquery'));	
	wp_enqueue_style('wps-select2-css', plugins_url('../../wp-symposium-pro/js/select2.css', __FILE__), 'css');

	// Anything else?
	do_action('wps_alerts_init_hook');
}


																	/* ********** */
																	/* SHORTCODES */
																	/* ********** */

function wps_alerts_activity($atts) {

	// Init
	add_action('wp_footer', 'wps_alerts_init');

	global $current_user;
	$html = '';

	if ( is_user_logged_in() ):

		// Shortcode parameters
        $values = wps_get_shortcode_options('wps_alerts_activity');
		extract( shortcode_atts( array(
            'style' => wps_get_shortcode_value($values, 'wps_alerts_activity-style', 'dropdown'), // dropdown|flag|list
            'flag_size' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_size', 24),
            'flag_unread_size' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_unread_size', 10),
            'flag_unread_top' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_unread_top', 6),
            'flag_unread_left' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_unread_left', 8),
            'flag_unread_radius' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_unread_radius', 8),
            'flag_url' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_url', ''),
            'flag_src' => wps_get_shortcode_value($values, 'wps_alerts_activity-flag_src', ''),
			'recent_alerts_text' => wps_get_shortcode_value($values, 'wps_alerts_activity-recent_alerts_text', __('Recent alerts...', WPS2_TEXT_DOMAIN)),
			'no_activity_text' => wps_get_shortcode_value($values, 'wps_alerts_activity-no_activity_text', __('No activity alerts', WPS2_TEXT_DOMAIN)),
			'select_activity_text' => wps_get_shortcode_value($values, 'wps_alerts_activity-select_activity_text', __('You have 1 new alert,You have %d new alerts,You have no new alerts', WPS2_TEXT_DOMAIN)),
			'make_all_read_text' => wps_get_shortcode_value($values, 'wps_alerts_activity-make_all_read_text', __('Mark all as read', WPS2_TEXT_DOMAIN)),
            'delete_all_text' => wps_get_shortcode_value($values, 'wps_alerts_activity-delete_all_text', __('Delete all', WPS2_TEXT_DOMAIN)),
			'date_format' => wps_get_shortcode_value($values, 'wps_alerts_activity-date_format', __('%s ago.', WPS2_TEXT_DOMAIN)),
            'delete_on_click' => wps_get_shortcode_value($values, 'wps_alerts_activity-delete_on_click', false), // set to 1 to remove the alert when clicked on
			'styles' => true,
            'after' => '',
			'before' => '',
		), $atts, 'wps_alerts_activity' ) );
    
		// Get all alerts for this user
		$args = array(
			'posts_per_page'   => 100,
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'post_type'        => 'wps_alerts',
			'post_status'      => array('publish', 'pending'),
			'meta_query' => array(
	        	array(
	        		'key' => 'wps_alert_recipient',
	        		'value' => $current_user->user_login,
	        		'compare' => '=='
	        	)
	        )
		);
		$alerts = get_posts($args);	
    
		$list = array();
		$labels = explode(',', $select_activity_text);
		$unread = 0;
		foreach ($alerts as $alert):
			$item['ID'] = $alert->ID;
			$item['excerpt'] = $alert->post_excerpt;
			$item['post_date'] = $alert->post_date;
			$url = get_post_meta($alert->ID, 'wps_alert_url', true);
			if ($url && $alert->post_excerpt):
				if (!get_post_meta($alert->ID, 'wps_alert_read', true)):
					$unread++;
					$item['class'] = 'wps_alerts_unread';
				else:
					$item['class'] = '';
				endif;
				$item['url'] = $url;
				$list[]= $item;
			endif;
		endforeach;

        // Dropdown list
        if ($style == 'dropdown'):
    
            if ($list):

                $html .= '<div style="max-width:100%">';
                    $html .= "<select name='wps_alerts_activity' id='wps_alerts_activity' rel='".$delete_on_click."' style='width:100%'>";
                    if ($unread == 1):
                        $html .= '<option value="count">'.$labels[0].'</option>';
                        $html .= '<option data-url="make_all_read">'.$make_all_read_text.'</option>';
                    elseif ($unread > 1):
                        $html .= '<option value="count">'.sprintf($labels[1], $unread).'</option>';
                        $html .= '<option data-url="make_all_read">'.$make_all_read_text.'</option>';
                    else:
                        $html .= '<option value="count">'.$labels[2].'</option>';
                        $html .= '<option data-url="make_all_read">'.$make_all_read_text.'</option>';
                    endif;
                    if ($delete_all_text) $html .= '<option data-url="delete_all_text">'.$delete_all_text.'</option>';

                    foreach ($list as $alert):
                        $html .= '<option data-url="'.$alert['url'].'" class="'.$alert['class'].' wps_alert_item" value="'.$alert['ID'].'">';
                        $html .= $alert['excerpt'];
                        $html .= ' '.sprintf($date_format, human_time_diff(strtotime($alert['post_date']), current_time('timestamp', 0)), WPS2_TEXT_DOMAIN);
                        $html .= '</option>';
                    endforeach;
                    $html .= "</select>";
                $html .= '</div>';

            else:
                $html .= "<select name='wps_alerts_activity' id='wps_alerts_activity' rel='".$delete_on_click."' style='width:100%'>";
                    $html .= '<option value="">'.$no_activity_text.'</option>';
                $html .= "</select>";
            endif;
    
        endif;
    
        // Flag
        if ($style == 'flag'):
    
            $html .= '<div id="wps_alerts_flag" style="width:'.$flag_size.'px; height:'.$flag_size.'px;" >';
            $html .= '<a href="'.$flag_url.'">';
            $src = (!$flag_src) ? plugins_url('images/flag'.get_option('wpspro_flag_colors').'.png', __FILE__) : $flag_src;
            $html .= '<img style="width:'.$flag_size.'px; height:'.$flag_size.'px;" src="'.$src.'" />';
            if ($unread):
                $html .= '<div id="wps_alerts_flag_unread" style="position: absolute; padding-top: '.($flag_unread_size*0.2).'px; line-height:'.($flag_unread_size*0.8).'px; font-size:'.($flag_unread_size*0.8).'px; border-radius: '.$flag_unread_radius.'px; top:'.$flag_unread_top.'px; left:'.$flag_unread_left.'px; width:'.$flag_unread_size.'px; height:'.$flag_unread_size.'px;">'.$unread.'</div>';
            endif;
            $html .= '</a></div>';
            if (!$flag_url) $html .= '<div class="wps_error">'.__('Set flag_url in shortcode', WPS2_TEXT_DOMAIN).'</div>';
    
        endif;
    
        // List
        if ($style == 'list'):

           if ($list) {

                $html .= '<div id="wps_alerts_list">';

                    if ($unread) $html .= '<div id="wps_mark_all_as_read_div"><a href="javascript:void(0);" id="wps_make_all_read">'.$make_all_read_text.'</a></div>';
                    $html .= '<div id="wps_alerts_delete_all_div"><a href="javascript:void(0);" id="wps_alerts_delete_all">'.$delete_all_text.'</a></div>';

                    foreach ($list as $alert):

                        $html .= '<div class="wps_alerts_list_item '.$alert['class'].'">';
                            $url = $alert['url'];
                            $html .= '<a href="javascript:void(0)" class="wps_alerts_list_item_link" data-id="'.$alert['ID'].'" data-url="'.$url.'">'.$alert['excerpt'].'</a>';
                            $html .= ' '.sprintf($date_format, human_time_diff(strtotime($alert['post_date']), current_time('timestamp', 0)), WPS2_TEXT_DOMAIN);
                            $html .= '<img title="'.__('Delete', WPS2_TEXT_DOMAIN).'" class="wps_alerts_list_item_delete" rel="'.$alert['ID'].'" src="'.plugins_url('../../wp-symposium-pro/forums/images/trash.png', __FILE__).'" />';
                        $html .= '</div>';
    
                    endforeach;

                $html .= '</div>';

            } else {
                $html .= $no_activity_text;
            }
    
        endif;

		if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_alerts_activity', $before, $after, $styles, $values);

	endif;

	return $html;	
}


add_shortcode(WPS_PREFIX.'-alerts-activity', 'wps_alerts_activity');

?>