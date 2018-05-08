<?php 
																	/* ************* */
																	/* HOOKS/FILTERS */
																	/* ************* */

// Updates last logged in
function wps_update_last_logged_in($user_login, $user) {
    $last_login = get_user_meta($user->ID, 'wpspro_last_login', true);
    if ($last_login):
        // update to previous login so can track for new activity/forum/whatever
        update_user_meta($user->ID, 'wpspro_previous_login', $last_login);
    else:
        update_user_meta($user->ID, 'wpspro_previous_login', current_time('mysql', 1));
    endif;
    update_user_meta($user->ID, 'wpspro_last_login', current_time('mysql', 1));
}
add_action('wp_login', 'wps_update_last_logged_in', 10, 2);

// Updates last active date via wp_footer hook which every theme should have
function wps_update_last_active() {
    global $current_user;
    update_user_meta($current_user->ID, 'wpspro_last_active', current_time('mysql', 1));
}
if (!is_admin()) add_action('wp_footer', 'wps_update_last_active');		

// work out when the last login was
function wps_get_last_login($user_id) {
    $last_logged_in = get_user_meta($user_id, 'wpspro_last_login', true);
    if ($last_logged_in):
        $last_logged_in_date = strtotime($last_logged_in);
    else:
        $last_logged_in_date = current_time('timestamp', 1);
    endif;

    return $last_logged_in_date;
}

// work out when the previous login was
function wps_get_previous_login($user_id) {
    $previous_logged_in = get_user_meta($user_id, 'wpspro_previous_login', true);
    if ($previous_logged_in):
        $last_logged_in_date = strtotime($previous_logged_in);
    else:
        $last_logged_in = get_user_meta($user_id, 'wpspro_last_login', true);
        if ($last_logged_in):
            $last_logged_in_date = strtotime($last_logged_in);
        else:
            $last_logged_in_date = current_time('timestamp', 1);
        endif;
    endif;

    return $last_logged_in_date;
}

function wps_since_last_logged_in($date_to_compare, $new_seconds) {
	global $current_user;
	$since_last_logged_in = false;
	if (is_user_logged_in()):
        $date_to_compare = strtotime($date_to_compare);
		$now = current_time('timestamp', 1);
		$diff = $now - $date_to_compare; // how old in seconds
        $limit = $new_seconds; // seconds
		if ($diff < $limit) $since_last_logged_in = true;
		//echo $now.'<br />'.$date_to_compare.'<br />'.$diff.'<br />';
	endif; 
	return $since_last_logged_in;
}

// Exclude WPS Forum replies and activity comments from Recent Comments widget
function wps_filter_recent_comments( $array ) {

	if (!get_option('wps_filter_recent_comments')):
		$array = array(
		    'parent' => 0,  
		    'post_type' => 'post',
		    'number' => $array['number'],
		    'status' => $array['status'],
		    'post_status' => $array['post_status']
		);
	endif;
	return $array;
}
add_filter( 'widget_comments_args', 'wps_filter_recent_comments' );


																	/* ********** */
																	/* SHORTCODES */
																	/* ********** */

function wps_display_name($atts) {
    
    
    $values = wps_get_shortcode_options('wps_display_name');  
	extract( shortcode_atts( array(
		'user_id' => wps_get_shortcode_value($values, 'wps_display_name-user_id', ''),
		'link' => wps_get_shortcode_value($values, 'wps_display_name-link', false),
		'before'	=> '',
		'after'		=> '',
	), $atts, 'wps_display_name' ) );
        
    if ($user_id == 'user'):
        global $current_user;
        $user_id = $current_user->ID;
    else:
        if (!$user_id) $user_id = wps_get_user_id();
    endif;    

	$user = get_user_by('id', $user_id);
	$html = '';

	if ($user):

		if (get_option('wpspro_profile_page') && strpos(WPS_CORE_PLUGINS, 'core-profile') !== false):
			if ($link):
				if (function_exists('icl_link_to_element')):
					$icl_object_id = icl_object_id(get_option('wpspro_profile_page'), 'page', true);
					$url = get_permalink($icl_object_id);
					$html = '<a href="'.$url.wps_query_mark($url).'user_id='.$user_id.'">'.$user->display_name.'</a>';
				elseif (get_option('wpspro_profile_permalinks')):
					$url = get_page_link(get_option('wpspro_profile_page'));
					$html = '<a href="'.$url.wps_query_mark($url).'user_id='.$user_id.'">'.$user->display_name.'</a>';
				else:
					$url = get_page_link(get_option('wpspro_profile_page'));
					if ( wps_using_permalinks() ):
						$html = '<a href="'.$url.urlencode($user->user_login).'">'.$user->display_name.'</a>';
					else:
						$html = '<a href="'.$url.wps_query_mark($url).'user_id='.$user_id.'">'.$user->display_name.'</a>';
					endif;
				endif;
			else:
				$html = $user->display_name;
			endif;

		else:
			$html = $user->display_name;
		endif;

	endif;

	if ($html) $html = htmlspecialchars_decode($before).$html.htmlspecialchars_decode($after);
    
    $html = apply_filters( 'wps_display_name', $html );
	return $html;

}
add_shortcode('wps-display-name', 'wps_display_name');


																	/* ********* */
																	/* FUNCTIONS */
																	/* ********* */

// Get link to a user's profile page
function wps_profile_link($user_id) {
    
    if (!$user_id) $user_id = wps_get_user_id();
    $user = get_user_by('id', $user_id);
    $url = false;
    
    if ($user) {
    
        if (function_exists('icl_link_to_element')):
            $icl_object_id = icl_object_id(get_option('wpspro_profile_page'), 'page', true);
            $url = get_permalink($icl_object_id);
            $url = $url.wps_query_mark($url).'user_id='.$user_id;
        elseif (get_option('wpspro_profile_permalinks')):
            $url = get_page_link(get_option('wpspro_profile_page'));
            $url = $url.wps_query_mark($url).'user_id='.$user_id;
        else:
            $url = get_page_link(get_option('wpspro_profile_page'));
            if ( wps_using_permalinks() ):
                $url = $url.urlencode($user->user_login);
            else:
                $url = $url.wps_query_mark($url).'user_id='.$user_id;
            endif;
        endif;    
        
    }
    
    return $url;
}

// Is account closed?
function wps_is_account_closed($user_id) {    
    return get_user_meta($user_id, 'wps_account_closed', true);
}

// Cut to number of words
function wps_get_words($text, $words, $more='...') {
	$array = explode(" ", $text, $words+1);
	if (count($array) > $words):
		unset($array[$words]);
		$text = implode(" ", $array).' '.$more;
	else:
		$text = implode(" ", $array);
	endif;
	return $text;
}


// Display array contents (for debugging only)
function wps_display_array($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {

 $curtab ="";
 $returnvalues = "";

 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value) && strpos($value, $search) !== false) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= wps_display_array($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// Get current URL
function wps_curPageURL() {
 	$pageURL = 'http';
 	$wps_force_https = get_option('wps_force_https');
 	$pageURL_https = '';
 	if (!$wps_force_https) { // Default
 		if (isset($_SERVER["HTTPS"])) $pageURL_https = "s";
 	} else {
 		if ($wps_force_https == 'https') $pageURL_https = "s"; // Force HTTPS
 			// ... else force HTTP (ie. as current set)
 	}
 	$pageURL .= $pageURL_https;
 	$pageURL .= "://";
 	if ($_SERVER["SERVER_PORT"] != "80") {
  		$pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER['REQUEST_URI'];
 	} else {
  		$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
 	}
 	return $pageURL;
}

// Permalinks or not?
function wps_query_mark($url) {
	if ($url):
		$q = (strpos($url, '?') !== FALSE) ? '&' : '?';
		return $q;
	else:
		return $url;
	endif;
}

// BB Code rules
function wps_bbcode_replace($text_to_search) {

	// ... ensure using brackets and not HTML entities
    $text_to_search = str_replace(
      array('&#91;', '&#93;', '&lt;', '&gt;'), 
      array('[', ']', '<', '>'), 
      $text_to_search
    );
    
    $text_to_search = esc_html($text_to_search);

	$text_to_search = str_replace('http://youtu.be/', 'http://www.youtube.com/watch?v=', $text_to_search);

	$search = array(
	        '@\[(?i)quote\](.*?)\[/(?i)quote\]@si',
	        '@\[(?i)center\](.*?)\[/(?i)center\]@si',
	        '@\[(?i)li\](.*?)\[/(?i)li\]@si',
	        '@\[(?i)\*\](.*?)<br \/>@si',
	        '@\[(?i)b\](.*?)\[/(?i)b\]@si',
	        '@\[(?i)i\](.*?)\[/(?i)i\]@si',
	        '@\[(?i)s\](.*?)\[/(?i)s\]@si',
	        '@\[(?i)u\](.*?)\[/(?i)u\]@si',
	        '@\[(?i)img\](.*?)\[/(?i)img\]@si',
	        '@\[(?i)url\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)url=(.*?)\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)code\](.*?)\[/(?i)code\]@si',
			'@\[youtube\].*?(?:v=)?([^?&[]+)(&[^[]*)?\[/youtube\]@is',
	        '@\[(?i)map\](.*?)\[/(?i)map\]@si',
	        '@\[(?i)map zoom=(.*?)\](.*?)\[/(?i)map\]@si',
            '@&lt;pre class=&quot;wps_code&quot;&gt;(.*?)&lt;\/pre&gt;@si',
            '@&lt;p&gt;(.*?)&lt;\/p&gt;@si',
            '@&lt;br \/&gt;@si',
	);
	$search = apply_filters( 'wps_bbcode_search_filter', $search );

	$replace = array(
	        '<div class="wps_bbcode_quote">\\1</div>',
	        '<div class="wps_bbcode_center">\\1</div>',
	        '<li class="wps_bbcode_list_item">\\1</li>',
	        '<li class="wps_bbcode_list_item">\\1</li>',
	        '<strong>\\1</strong>',
	        '<em>\\1</em>',
	        '<s>\\1</s>',
	        '<span style="text-decoration:underline">\\1</span>',
	        '<img src="\\1">',
	        '<a href="\\1">\\1</a>',
	        '<a href="\\1">\\2</a>',
	        '<div class="wps_bbcode_code">\\1</div>',
	        '<iframe title="YouTube video player" width="475" height="290" src="http://www.youtube.com/embed/\\1" frameborder="0" allowfullscreen></iframe>',
	        '<a target="_blank" href="https://www.google.com/maps/preview?q=\\1"><img src="http://maps.google.com/maps/api/staticmap?center=\\1&zoom=11&size=400x200&maptype=roadmap&markers=color:ORANGE|label:A|\\1&sensor=false"></a>',
	        '<a target="_blank" href="https://www.google.com/maps/preview?q=\\2"><img src="http://maps.google.com/maps/api/staticmap?center=\\2&zoom=\\1&size=400x200&maptype=roadmap&markers=color:ORANGE|label:A|\\2&sensor=false"></a>',
            '<pre class="wps_code">\\1</pre>',
            '<p>\\1</p>',
            '<br />',
	);
	$search = apply_filters( 'wps_bbcode_replace_filter', $search );

	$r = preg_replace($search, $replace, $text_to_search);
	return $r;

}
																	/* ********* */
																	/* FUNCTIONS */
																	/* ********* */

function wps_make_clickable($text) {

    $internal_link = strpos($text, get_bloginfo('url')) ? 1 : 0;
    $suffix = get_option('wps_external_links');
    $text = make_clickable(str_replace($suffix, '', $text));

    if ($suffix && !$internal_link):
    
        $text = str_replace('<a ', '<a class="wps_external_link" target="_blank" ', $text);
        if ($suffix != '-' && $suffix != '&newtab;') $text = str_replace('</a>', '</a>'.$suffix, $text);
    
    endif;
    
    return $text;
}

function wps_get_user_id() {

	global $current_user;
	if (get_query_var('user')):
		$username = get_query_var('user');
		$get_user = get_user_by('login', urldecode($username));
		$user_id = $get_user ? $get_user->ID : 0;
	else:
		if (isset($_GET['user_id'])):
			$user_id = $_GET['user_id'];
		else:
			$user_id = $current_user ? $current_user->ID : 0;
		endif;
	endif;
	return $user_id;

}

// Returns instance of WP editor
function wps_get_wp_editor($content,$textarea,$css) {

    $wps_pro_toolbar_icons = ($value = get_option('wps_pro_toolbar_icons')) ? $value : "bold,italic,underline";
    $wps_pro_toolbar_css_file = ($value = get_option('wps_pro_toolbar_css_file')) ? $value : WP_PLUGIN_URL  . '/wp-symposium-pro-extensions/wp-symposium-pro-forum-toolbar/editor-styles.css';  
    $wps_pro_toolbar_height = ($value = get_option('wps_pro_toolbar_height')) ? $value : 200;

    $settings = array(
        'media_buttons' => false,
        'wpautop' => false,
        'editor_height' => $wps_pro_toolbar_height,
        'quicktags' => current_user_can('manage_options') || get_option('wps_pro_toolbar_tabs') ? true : false,
        'tinymce'=> array(
            'toolbar1'=> $wps_pro_toolbar_icons,
            'statusbar' => false,
            'resize' => false,
            'wp_autoresize_on' => true,
            'content_css' => $wps_pro_toolbar_css_file
            )
    );

    ob_start();
	echo '<div style="'.$css.'">';
	wp_editor($content, $textarea, $settings);
	echo '</div>';
	$editor_html_code = ob_get_contents();
	ob_end_clean();

	return $editor_html_code;
}

// Automatically close forum comments older than a certain number of days based
// on setting in admin panel for discussion
function wps_forum_close_comments( $posts ) {
	
	if (sizeof($posts) == 1):

		if ( 'wps_forum_post' == get_post_type($posts[0]->ID) && $posts[0]->comment_status != 'closed'):

			// Get post forum to see if has specific auto-close set for that forum, if not use global default
			$post_terms = get_the_terms( $posts[0]->ID, 'wps_forum' );
			$the_post_terms = $post_terms[0];
			$wps_forum_auto_close = wps_get_term_meta($the_post_terms->term_id, 'wps_forum_auto_close', true) ? wps_get_term_meta($the_post_terms->term_id, 'wps_forum_auto_close', true) : '';
			if (!$wps_forum_auto_close)
				$wps_forum_auto_close = get_option( 'wps_forum_auto_close' ) ? get_option( 'wps_forum_auto_close' ) : '';

			if ($wps_forum_auto_close):

				$passed_time = time() - strtotime( $posts[0]->post_date_gmt ) > ( $wps_forum_auto_close * 24 * 60 * 60 );
				if ( $passed_time ):

					if (!get_post_meta($posts[0]->ID, 'wps_reopened_date', true)):

						$posts[0]->comment_status = 'closed';
						$posts[0]->ping_status    = 'closed';
						wp_update_post( $posts[0] );

						$data = array(
						    'comment_post_ID' => $posts[0]->ID,
						    'comment_content' => __('Closed due to inactivity.', WPS2_TEXT_DOMAIN),
						    'comment_type' => 'wps_forum_comment',
						    'comment_parent' => 0,
						    'comment_author' => 0,
						    'comment_author_email' => '',
						    'user_id' => 0,
						    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
						    'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
						    'comment_approved' => 1,
						);

						$new_id = wp_insert_comment($data);

						if ($new_id):

							// Any further actions?
							do_action( 'wps_forum_auto_close_hook', $posts[0]->ID );

						endif;

					endif;
				
				endif;

			endif;
		
		endif;

	endif;

	return $posts;
}
add_action( 'the_posts', 'wps_forum_close_comments' );

// Checks if a user can see a forum category
function user_can_see_forum($user_id, $term_id) {

	global $current_user;
	$see = false;

    // If public, or logged in, can see
	$public = wps_get_term_meta($term_id, 'wps_forum_public', true);
	if ($public || is_user_logged_in()) $see = true;

    // Final check if logged in only forum
	if (!$public && !is_user_logged_in()) $see = false;

    // Any more checking?
	$see = apply_filters('user_can_see_forum_filter', $see, $user_id, $term_id);

	// Admin can always see
	if (current_user_can('manage_options')) $see = true;
    
	return $see;

}

// Checks if a user can see a post (ie. author/admin only?)
function user_can_see_post($user_id, $post_id) {

	$user_can_see = true;
	$post_terms = get_the_terms( $post_id, 'wps_forum' );
	if( $post_terms && !is_wp_error( $post_terms ) ):

		foreach( $post_terms as $term ):

			if (user_can_see_forum($user_id, $term->term_id)):

				$author = wps_get_term_meta($term->term_id, 'wps_forum_author', true);
				$the_post = get_post($post_id);
				if ($author && ($user_id != $the_post->post_author && !current_user_can('manage_options'))):
					$user_can_see = false;
				endif;

			else:

				$user_can_see = false;

			endif;

		endforeach;

	endif;

	return $user_can_see;

}

// See if WordPress using permalinks (or using root of multisite)
function wps_using_permalinks() {

	if (!get_option( 'permalink_structure' ))
		return false;

	if (is_multisite()):
        $current_blog = get_current_blog_id();
        if ($current_blog > 1 || is_admin()):
        	return true;
        else:
        	return false;
        endif;
    else:
    	return true;
    endif;

}

// Check API security code is correct
function wps_api_correct($code) {
	if ($code == get_option('wps_api')):
		return true;
	else:
		return false;
	endif;
}

// Check API function is permitted
function wps_api_function_permitted($function_code) {
	if (strpos(get_option('wps_api_functions'), $function_code) !== false):
		return true;
	else:
		return false;
	endif;
}

function wps_formatted_content($content, $include_extra_formatting=true, $force=false) {

    if (defined( 'WPS_FORUM_TOOLBAR' ) || $force):
        
        $mode = (defined( 'WPS_FORUM_TOOLBAR' ) && get_option( 'wps_pro_toolbar' ) == 'wysiwyg') ? 'wysiwyg' : 'bbcodes';
    
        // find all code segments (so we can preserve actual HTML wanted)
        preg_match_all('@&lt;code&gt;(.*?)&lt;\/code&gt;@si', trim($content).' ', $matches, PREG_SET_ORDER);
        // ... store segments
        $code_segments = array();
        foreach ($matches as $m) {
            // ... store all code segments
            $code_segments[] = $m[1];
        }
        if ($code_segments):
            // ... if any, then now replace with placeholder
            $count=0;
            foreach ($code_segments as $segment) {
                $count++;
                // ... make code tags hidden from reinterpreting!
                $content = str_replace(
                    array('<code>', '</code>', '[code]', '[/code]'), 
                    array('<pre class="wps_code">', '</pre>', '<pre class="wps_code">', '</pre>'), 
                    $content
                );	

                $content = str_replace($segment, "wps_code_segment_".$count, $content);
            }
            if (strpos($content, '<pre class="wps_code">') !== false && strpos($content, '</pre>') === false) $content .= '</pre>';

        endif;

        // Decode HTML entities...
        $content = htmlspecialchars_decode($content);

        // Any final formatting?
        if ($include_extra_formatting):
            $content = str_replace(
                array('<code>', '</code>', '[code]', '[/code]'), 
                array('<pre class="wps_code">', '</pre>', '<pre class="wps_code">', '</pre>'), 
                $content
            );	
            $content = convert_smilies(wps_make_clickable(wpautop($content)));
        else:
            if ($mode == 'wysiwyg') $content = wpautop($content);
        endif;

        // .... and then put back in any code segments
        if ($code_segments):
            $count=0;
            foreach ($code_segments as $segment) {
                $count++;
                $content = str_replace("wps_code_segment_".$count, $segment, $content);
            }
        endif;

        // Finally, strip out unwanted html
        $content = wp_kses($content, wps_allowed_html_tags());
    
    else:
        // No toolbar or no formatting
    
        if (!defined( 'WPS_FORUM_TOOLBAR' ) && $include_extra_formatting):
            // No toolbar and formatting
            $content = wps_make_clickable(wpautop($content));
        endif;
        
    endif;

    return $content;
    
}

function wps_allowed_html_tags() {

	$allowedposttags = array(
		'a' => array(
			'href' => true,
			'rel' => true,
			'rev' => true,
			'name' => true,
			'target' => true,
		),
		'b' => array(),
		'blockquote' => array(
			'cite' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'br' => array(),
		'code' => array(),
		'del' => array(
			'datetime' => true,
		),
		'div' => array(
			'align' => true,
			'class' => true,
            'data-width' => true,
            'data-height' => true,
            'data-source' => true,
            'data-desc' => true,
			'dir' => true,
			'id' => true,
			'lang' => true,
			'rel' => true,
			'style' => array(),
			'xml:lang' => true,
		),
		'em' => array(),
		'h1' => array(
			'align' => true,
		),
		'h2' => array(
			'align' => true,
		),
		'h3' => array(
			'align' => true,
		),
		'h4' => array(
			'align' => true,
		),
		'h5' => array(
			'align' => true,
		),
		'h6' => array(
			'align' => true,
		),
		'hr' => array(
			'align' => true,
			'noshade' => true,
			'size' => true,
			'width' => true,
		),
		'i' => array(),
		'img' => array(
			'alt' => true,
			'align' => true,
			'border' => true,
			'height' => true,
			'hspace' => true,
			'longdesc' => true,
			'vspace' => true,
			'style' => true,
			'src' => true,
			'title' => true,
			'usemap' => true,
			'width' => true,
		),
		'li' => array(
			'align' => true,
			'value' => true,
		),
		'p' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'style' => array(),
			'xml:lang' => true,
		),
		'pre' => array(
			'class' => true,
			'width' => true,
		),
		'span' => array(
			'class' => true,
			'dir' => true,
			'align' => true,
			'lang' => true,
			'rel' => true,
			'style' => array(),
			'title' => true,
			'xml:lang' => true,
		),
		'strike' => array(),
		'strong' => array(),
		'sub' => array(),
		'sup' => array(),
		'table' => array(
			'align' => true,
			'bgcolor' => true,
			'border' => true,
			'cellpadding' => true,
			'cellspacing' => true,
			'dir' => true,
			'rules' => true,
			'summary' => true,
			'width' => true,
		),
		'tbody' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'td' => array(
			'abbr' => true,
			'align' => true,
			'axis' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'colspan' => true,
			'dir' => true,
			'headers' => true,
			'height' => true,
			'nowrap' => true,
			'rowspan' => true,
			'scope' => true,
			'valign' => true,
			'width' => true,
		),
		'tfoot' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'th' => array(
			'abbr' => true,
			'align' => true,
			'axis' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'colspan' => true,
			'headers' => true,
			'height' => true,
			'nowrap' => true,
			'rowspan' => true,
			'scope' => true,
			'valign' => true,
			'width' => true,
		),
		'thead' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'tr' => array(
			'align' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'u' => array(),
		'ul' => array(
			'type' => true,
		),
		'ol' => array(
			'start' => true,
			'type' => true,
			'reversed' => true,
		),
	);

    $allowedposttags = apply_filters( 'wps_allowed_html_tags_filter', $allowedposttags );

	return $allowedposttags;

}

// ****************** CORE ******************

// Print generic modal box for general use (wp_footer hook must exist in theme)
function wps_add_wait_modal_box() {
	echo '<div class="wps_wait_modal"></div>';
}

// Print internal CSS codes in the head section
function wps_add_custom_css() {
	$css = '';
	if ($value = stripslashes( get_option('wpspro_custom_css') )) $css .= $value;
	echo '<style>/* WP Symposium custom CSS */' . chr(13) . chr(10) . $css . '</style>';
}

// Dismiss an admin tip
function wps_admin_tip($html, $tip, $msg) {
    if (isset($_GET['dismiss']) && $_GET['dismiss'] == $tip):
        $dismissed = get_option('wps_admin_tips');
        if (!$dismissed):
            $dismissed = array($tip);
        else:
            if (!in_array($tip, $dismissed)) array_push($dismissed, $tip);
        endif;
        update_option('wps_admin_tips', $dismissed);
    else:
        $dismissed = get_option('wps_admin_tips');
        if (!$dismissed || !in_array($tip, $dismissed)):
            $url = wps_curPageURL();
            $url .= wps_query_mark($url).'dismiss='.$tip;
            $html .= '<div class="wps_admin_tip">';
            $html .= '<div style="font-size: 0.8em; float:right; margin-left: 20px; margin-bottom; 5px;"><a href="'.$url.'">'.__('Dismiss Tip', WPS2_TEXT_DOMAIN).'</a></div>';
            $html .= __('Tip', WPS2_TEXT_DOMAIN).': '.__($msg, WPS2_TEXT_DOMAIN);
            $html .= '<div style="font-style:italic; margin-top: 15px; font-size:0.8em;">'.sprintf(__('This tip is only visible to site administrators and if dismissed can be re-activated via WPS Pro->Setup-><a href="%s">Core Options</a>.', WPS2_TEXT_DOMAIN), admin_url( 'admin.php?page=wps_pro_setup&wps_expand=wps_admin_getting_started_core#core' )).'</div>';
            $html .= '</div>';
        endif;
    endif;
    return $html;
}

// Global function for site URL, so that it can be filtered by others
function wps_root($url) {
    $url = site_url($url);
    $url = apply_filters( 'wps_pro_root_filter', $url );
	return $url;
}

// Get shortcode options from setup
function wps_get_shortcode_options($function) {    
    return get_option('wps_shortcode_options_'.$function) ? get_option('wps_shortcode_options_'.$function) : array();
}

// Get a single shortcode option from setup
function wps_get_shortcode_option($section, $name, $default) {
    $values = get_option('wps_shortcode_options_'.$section) ? get_option('wps_shortcode_options_'.$section) : array();            
    return wps_get_shortcode_value($values, $section.'-'.$name, $default);
}

// Get shortcode value from setup
function wps_get_shortcode_value($values, $name, $default) {
    
    // Remove function if passed in format function-option
    if (strpos($name, '-')):
        $arr = explode('-',$name);
        $name = $arr[1];
    endif;
    
    if ($default === false || $default === true) {
        $v = isset($values[$name]) && $values[$name] ? $values[$name] : false;
        if ($v) {
            $v = $v == 'on' ? true : false; 
        } else {
            $v = $default;
        }
    } else {
        $v = isset($values[$name]) && $values[$name] ? $values[$name] : $default;
    }
    return html_entity_decode(htmlentities(htmlspecialchars_decode($v, ENT_QUOTES)));
}

// Wrapper for all returned HTML from shortodes, including optional admin styling
add_filter( 'wps_wrap_shortcode_styles_filter', 'wps_wrap_shortcode_styles', 10, 8 );
function wps_wrap_shortcode_styles($html, $function, $before, $after, $styles, $values, $width_force=false, $height_force=false) {
    
    if (!get_option('wpspro_global_styles') || get_option('wpspro_global_styles') == 'off') $styles = false; // global styles flag

    if ($styles):
    
        $margin_top = wps_get_shortcode_value($values, $function.'-margin_top', 0);
        $margin_bottom = wps_get_shortcode_value($values, $function.'-margin_bottom', 0);
        $margin_left = wps_get_shortcode_value($values, $function.'-margin_left', 0);
        $margin_right = wps_get_shortcode_value($values, $function.'-margin_right', 0);
        $padding_top = wps_get_shortcode_value($values, $function.'-padding_top', 0);
        $padding_bottom = wps_get_shortcode_value($values, $function.'-padding_bottom', 0);
        $padding_left = wps_get_shortcode_value($values, $function.'-padding_left', 0);
        $padding_right = wps_get_shortcode_value($values, $function.'-padding_right', 0);
        $clear = wps_get_shortcode_value($values, $function.'-clear', false);
        $border_size = wps_get_shortcode_value($values, $function.'-border_size', 0);
        $border_color = wps_get_shortcode_value($values, $function.'-border_color', '#000');
        $border_radius = wps_get_shortcode_value($values, $function.'-border_radius', 0);
        $border_style = wps_get_shortcode_value($values, $function.'-border_style', 'solid');
        $background_color = wps_get_shortcode_value($values, $function.'-background_color', 'transparent');
        $style_width = 'width:'.wps_get_shortcode_value($values, $function.'-style_width', '100%');
        $style_height = wps_get_shortcode_value($values, $function.'-style_height', '');

        $style_width = $width_force ? 'width:'.($width_force+($border_size*2)).'px;' : $style_width;
        $style_height = $height_force ? 'height:'.($height_force+($border_size*2)).'px;' : $style_height;

        $div_top = '<div style="overflow:hidden;'.$style_width.$style_height.';clear:'.($clear ? 'both' : 'none').';background-color:'.$background_color.'; 
        margin-top:'.$margin_top.'px; margin-bottom:'.$margin_bottom.'px; margin-left:'.$margin_left.'px; margin-right:'.$margin_right.'px; 
        padding-top:'.$padding_top.'px; padding-bottom:'.$padding_bottom.'px; padding-left:'.$padding_left.'px; padding-right:'.$padding_right.'px; 
        border:'.$border_size.'px '.$border_style.' '.$border_color.'; border-radius:'.$border_radius.'px;">';
        $div_bottom = '</div>';

    else:
    
        $div_top = '';
        $div_bottom = '';

    endif;

    $html = $div_top.htmlspecialchars_decode($before).$html.htmlspecialchars_decode($after).$div_bottom;

    return $html;
}

function wps_licence_code() {
	
	$saved_code = get_option('wps_licence_code') ? get_option('wps_licence_code') : false;
	$code_chars = preg_replace('#[^VIP]#','',strtoupper($saved_code));
	if ( strpos($code_chars, 'VIP') !== false ) {
		$days = 365; // Forced for a year (i.e. will always be valid)
		$text = __('This is a lifetime WP Symposium Pro Extensions plugin licence. You must not share this within anyone.', WPS2_TEXT_DOMAIN);
	} else {

		if ($saved_code) {
        
			$code = preg_replace('#[^0-9]#','',$saved_code);	        
	        $diff = $code - time();
		
			$days = floor($diff / (60*60*24));
			$diff = $diff - ($days*60*60*24);
			
			$hours = floor($diff / (60*60));
			$diff = $diff - ($hours*60*60);
		
			$minutes = floor($diff / (60));
			$seconds = $diff - ($minutes*60);
			
			$minutes = $minutes > 9 ? $minutes : '0'.(string)$minutes;
			$seconds = $seconds > 9 ? $seconds : '0'.(string)$seconds;

			if ($days > 0 && $days < 366) {
				$text = sprintf(__('Your current WP Symposium Pro Extensions plugin licence expires at %s on %s', WPS2_TEXT_DOMAIN), @date('H:i', $code), @date('l d F Y', $code)).' ('.sprintf(__('%d days', WPS2_TEXT_DOMAIN), $days).')'.', '.sprintf(__('<a href="%s" target="_new">update your licence code</a> before it runs out.', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/licence-code');
			} else {
				$days = 0;
				$text = __('<strong>Invalid WP Symposium Pro Extensions plugin licence</strong> - <a href="http://www.wpsymposiumpro.com/licence-code" target="_new">please get a valid licence code</a>', WPS2_TEXT_DOMAIN);
			}

		} else {
			$days = 0;
			$text = __('<strong>No WP Symposium Pro Extensions plugin licence entered</strong> - <a href="http://www.wpsymposiumpro.com/licence-code" target="_new">click here to get your licence code</a>', WPS2_TEXT_DOMAIN);
		}
		
	}

	if ($days>0) {
		$text = '<p>'.$text.'</p>';
	} else {
		$text = '<p class="wps_error">'.$text.'</p>';
	}

	return array($days, $text, $saved_code);
}

?>