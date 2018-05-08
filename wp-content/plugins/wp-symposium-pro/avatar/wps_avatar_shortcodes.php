<?php

																	/* **** */
																	/* INIT */
																	/* **** */

function wps_avatar_init() {

	wp_enqueue_script("thickbox");
	wp_enqueue_style("thickbox");
	wp_enqueue_script('wps-avatar-js', plugins_url('wps_avatar.js', __FILE__), array('jquery'));	
	wp_enqueue_style('user-avatar', plugins_url('user-avatar.css', __FILE__), 'css');
	wp_enqueue_style('imgareaselect');
	wp_enqueue_script('imgareaselect');
	// Anything else?
	do_action('wps_avatar_init_hook');
}

																	/* ********** */
																	/* SHORTCODES */
																	/* ********** */

function wps_avatar($atts) {

	// Init
	add_action('wp_footer', 'wps_avatar_init');

	global $current_user;
	$html = '';

	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_avatar');  
	extract( shortcode_atts( array(
		'user_id' => wps_get_shortcode_value($values, 'wps_avatar-user_id', ''),
		'size' => wps_get_shortcode_value($values, 'wps_avatar-size', 256),
		'change_link' => wps_get_shortcode_value($values, 'wps_avatar-change_link', false),
        'profile_link' => wps_get_shortcode_value($values, 'wps_avatar-profile_link', false), // only if avatar is NOT current user
		'styles' => true,
		'check_privacy' => false,
        'after' => '',
		'before' => '',
	), $atts, 'wps_avatar' ) );

    if ($user_id == 'user'):
        $user_id = $current_user->ID;
    else:
        if (!$user_id) $user_id = wps_get_user_id();
    endif;

    if ($user_id):
    
	    if ($check_privacy && strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false):
			$friends = wps_are_friends($current_user->ID, $user_id);
			// By default same user, and friends of user, can see profile
			$user_can_see_profile = ($current_user->ID == $user_id || $friends['status'] == 'publish') ? true : false;
			$user_can_see_profile = apply_filters( 'wps_check_profile_security_filter', $user_can_see_profile, $user_id, $current_user->ID );
		else:
			$user_can_see_profile = true;
		endif;
		
		if ($user_can_see_profile):

			if ($user_id != $current_user->ID) {
	            if ($profile_link)
	                $html .= '<a href="'.get_page_link(get_option('wpspro_profile_page')).'?user_id='.$user_id.'">';
				$html .= user_avatar_get_avatar( $user_id, $size );
	            if ($profile_link)
	                $html .= '</a>';
			} else {
				$profile = get_user_by('id', $user_id);
				global $current_user;

                if (!strpos($size, '%')):
				    $html .= sprintf('<div class="wps_avatar" style="width: %dpx; height: %dpx;">', $size, $size);
                else:
				    $html .= sprintf('<div class="wps_avatar" style="width: %d; height: %d;">', $size, $size);
                endif;
				$html .= user_avatar_get_avatar( $user_id, $size );
				if ($change_link) $html .= '<a id="user-avatar-link" style="text-decoration: none;opacity:0.7;background-color: #000; color:#fff !important; padding: 3px 8px 3px 8px; position:absolute; bottom:18px; left: 10px;" href="'.get_page_link(get_option('wpspro_change_avatar_page')).'?user_id='.$user_id.'&action=change_avatar" title="'.__('Upload and Crop an Image to be Displayed', WPS2_TEXT_DOMAIN).'" >'.__('Change Picture', WPS2_TEXT_DOMAIN).'</a>';
				$html .= '</div>';
			}
	    
	        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_avatar', $before, $after, $styles, $values, $size, $size);

		endif;

	endif;
	
	return $html;

}


function wps_avatar_change_link($atts) {

	// Init
	add_action('wp_footer', 'wps_avatar_init');

	global $current_user;
	$html = '';

	if (is_user_logged_in()) {
        
        // Shortcode parameters
		$values = wps_get_shortcode_options('wps_avatar_change_link');
		extract( shortcode_atts( array(
			'text' => wps_get_shortcode_value($values, 'wps_avatar_change_link-text', __('Change Picture', WPS2_TEXT_DOMAIN)),
			'styles' => true,
            'after' => '',
			'before' => '',
		), $atts, 'wps_avatar_change' ) );

		$user_id = wps_get_user_id();

		if ($current_user->ID == $user_id)
			$html .= '<a href="'.get_page_link(get_option('wpspro_change_avatar_page')).'?user_id='.$user_id.'">'.$text.'</a>';

	}

	if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_avatar_change_link', $before, $after, $styles, $values);
	return $html;

}

function wps_avatar_change($atts) {

	// Init
	add_action('wp_footer', 'wps_avatar_init');

	global $current_user;
	$html = '';

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_avatar_change');
    extract( shortcode_atts( array(
        'label' => wps_get_shortcode_value($values, 'wps_avatar_change-label', __('Upload', WPS2_TEXT_DOMAIN)),
        'step1' => wps_get_shortcode_value($values, 'wps_avatar_change-step1', __('Step 1: Click on this link to choose an image and afterwards click the button below.', WPS2_TEXT_DOMAIN)),
        'step2' => wps_get_shortcode_value($values, 'wps_avatar_change-step2', __('Step 2: First select an area on your uploaded image, and then click the crop button.', WPS2_TEXT_DOMAIN)),
        'choose' => wps_get_shortcode_value($values, 'wps_avatar_change-choose', __('Click here to choose an image... (maximum %dKB)', WPS2_TEXT_DOMAIN)),
        'try_again_msg' => wps_get_shortcode_value($values, 'wps_avatar_change-try_again_msg', __('Try again...', WPS2_TEXT_DOMAIN)),
        'file_types_msg' => wps_get_shortcode_value($values, 'wps_avatar_change-file_types_msg', __("Please upload an image file (.jpeg, .gif, .png).", WPS2_TEXT_DOMAIN)),
        'not_permitted' => wps_get_shortcode_value($values, 'wps_avatar_change-not_permitted', __('You are not allowed to change this avatar.', WPS2_TEXT_DOMAIN)),
        'file_too_big_msg' => wps_get_shortcode_value($values, 'wps_avatar_change-file_too_big_msg', __('Please upload an image file no larger than %dKB, yours was %dKB.', WPS2_TEXT_DOMAIN)),
        'max_file_size' => wps_get_shortcode_value($values, 'wps_avatar_change-max_file_size', 500),
        'crop' => wps_get_shortcode_value($values, 'wps_avatar_change-crop', true),
        'effects' => wps_get_shortcode_value($values, 'wps_avatar_change-effects', false),
        'logged_out_msg' => wps_get_shortcode_value($values, 'wps_avatar_change-logged_out_msg', __('You must be logged in to view this page.', WPS2_TEXT_DOMAIN)),
        'login_url' => wps_get_shortcode_value($values, 'wps_avatar_change-login_url', ''),
        'flip' => wps_get_shortcode_value($values, 'wps_avatar_change-flip', __('Flip', WPS2_TEXT_DOMAIN)),
        'rotate' => wps_get_shortcode_value($values, 'wps_avatar_change-rotate', __('Rotate', WPS2_TEXT_DOMAIN)),
        'invert' => wps_get_shortcode_value($values, 'wps_avatar_change-invert', __('Invert', WPS2_TEXT_DOMAIN)),
        'sketch' => wps_get_shortcode_value($values, 'wps_avatar_change-sketch', __('Sketch', WPS2_TEXT_DOMAIN)),
        'pixelate' => wps_get_shortcode_value($values, 'wps_avatar_change-pixelate', __('Pixelate', WPS2_TEXT_DOMAIN)),
        'sepia' => wps_get_shortcode_value($values, 'wps_avatar_change-sepia', __('Sepia', WPS2_TEXT_DOMAIN)),
        'emboss' => wps_get_shortcode_value($values, 'wps_avatar_change-emboss', __('Emboss', WPS2_TEXT_DOMAIN)),
        'styles' => true,
    ), $atts, 'wps_avatar_change' ) );
    
	if (is_user_logged_in()):

        $user_id = wps_get_user_id();

        if (current_user_can('manage_options') && !$login_url && function_exists('wps_login_init')):
            $html = wps_admin_tip($html, 'wps_avatar_change', __('Add login_url="/example" to the [wps-avatar-change] shortcode to let users login and redirect back here when not logged in.', WPS2_TEXT_DOMAIN));
        endif;        
    
        $useragent=$_SERVER['HTTP_USER_AGENT'];

        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            $crop = false;
        }

		if ($current_user->ID == $user_id || current_user_can('manage_options') || is_super_admin($current_user->ID) ):

			include_once ABSPATH . 'wp-admin/includes/media.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/image.php';

            $step = 1;
            if (isset($_POST['wps_avatar_change_step'])):
                $step = $_POST['wps_avatar_change_step'];
            elseif (isset($_GET['wps_avatar_change_step'])):
                $step = $_GET['wps_avatar_change_step'];
            endif;
    
			if ($step == 1):

                $html .= '<div id="wps_avatar_change_step_1">'.$step1.'</div>';
				$html .= '<form enctype="multipart/form-data" id="avatarUploadForm" method="POST" action="#" >';
					$html .= '<input type="hidden" name="wps_avatar_change_step" value="2" />';
                    $choose = sprintf($choose, $max_file_size);
					$html .= '<input title="'.$choose.'" type="file" id="avatar_file_upload" name="uploadedfile" style="display:none" /><br /><br />';
					wp_nonce_field('user-avatar');
					$html .= '<button class="wps_button">'.$label.'</button>';
				$html .= '</form>';

			elseif ($step == '2' && $crop):

                $img_action = isset($_GET['flip_file']) || isset($_GET['rotate_file']) || isset($_GET['invert_file']) || isset($_GET['sketch_file']) || isset($_GET['pixelate_file']) || isset($_GET['sepia_file']) || isset($_GET['emboss_file']);
				if ( (!$img_action) && (!(($_FILES["uploadedfile"]["type"] == "image/gif") || ($_FILES["uploadedfile"]["type"] == "image/jpeg") || ($_FILES["uploadedfile"]["type"] == "image/png") || ($_FILES["uploadedfile"]["type"] == "image/pjpeg") || ($_FILES["uploadedfile"]["type"] == "image/x-png"))) ):
					
					$html .= "<div class='wps_error'>".$file_types_msg." (".$_FILES["uploadedfile"]["type"].")</div>";
					$html .= "<p><a href=''>".$try_again_msg.'</a></p>';

				else:

                    // check file size
                    if (!$img_action):
                        $file_size = $_FILES["uploadedfile"]["size"];
                    else:
                        $file_size = $_GET['file_size'];
                    endif;
                    $file_size = $file_size / 1024; // KB
                    if ($file_size > $max_file_size):
                        $html .= "<div class='wps_error'>".sprintf($file_too_big_msg, $max_file_size, $file_size)."</div>";
                        $html .= "<p><a href=''>".$try_again_msg.'</a></p>';
                    else:    

                        if (!$img_action):
                            $overrides = array('test_form' => false);
                            $file = wp_handle_upload($_FILES['uploadedfile'], $overrides);

                            if ( isset($file['error']) ){
                                die( $file['error'] );
                            }

                            $url = $file['url'];
                            $type = $file['type'];
                            $file = $file['file'];
                            $filename = basename($file);
                            set_transient( 'avatar_file_'.$user_id, $file, 60 * 60 * 5 );
                
                        else:

                            if (!class_exists('SimpleImage')) require_once('SimpleImage.php');
    
                            if (isset($_GET['flip_file'])):
                                // flip...
                                $file = stripslashes($_GET['flip_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->flip('y');
                                $image->save($file);
                            endif;

                            if (isset($_GET['rotate_file'])):
                                // rotating...
                                $file = stripslashes($_GET['rotate_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->rotate(90);
                                $image->save($file);
                            endif;

                            if (isset($_GET['invert_file'])):
                                // inverting...
                                $file = stripslashes($_GET['invert_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->invert();
                                $image->save($file);
                            endif;

                            if (isset($_GET['sketch_file'])):
                                // sketch...
                                $file = stripslashes($_GET['sketch_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->sketch();
                                $image->save($file);
                            endif;

                            if (isset($_GET['pixelate_file'])):
                                // pixelate...
                                $file = stripslashes($_GET['pixelate_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->pixelate(4);
                                $image->save($file);
                            endif;

                            if (isset($_GET['sepia_file'])):
                                // sepia...
                                $file = stripslashes($_GET['sepia_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->sepia();
                                $image->save($file);
                            endif;

                            if (isset($_GET['emboss_file'])):
                                // emboss...
                                $file = stripslashes($_GET['emboss_file']);
                                $url = stripslashes($_GET['url']);
                                // update the files 
                                $image = new SimpleImage();
                                $image->load($file);
                                $image->emboss();
                                $image->save($file);
                            endif;    

                        endif;

                        // Save the data
                        list($width, $height, $type, $attr) = getimagesize( $file );

                        if ( $width > 420 ) {

                            $oitar = $width / 420;
                            if (!class_exists('SimpleImage')) require_once('SimpleImage.php');
                            $image = new SimpleImage();
                            $image->load($file);
                            $image->fit_to_width(420);
                            $image->save($file);        

                            $url = str_replace(basename($url), basename($file), $url);

                            $width = $width / $oitar;
                            $height = $height / $oitar;

                        } else {
                            $oitar = 1;
                        }

                        $div_width = esc_attr($width) + 20;
                        $html .= '<div style="padding:0 !important;overflow:auto !important; min-width: '.$div_width.'px !important;">';
    
                            $html .= '<form id="iframe-crop-form" method="POST" action="#">';
                            $html .= '<input type="hidden" name="wps_avatar_change_step" value="3" />';
                            $html .= '<div id="wps_avatar_change_step_2">'.$step2.'</div>';                        

                            $page_id = isset($_GET['page_id']) ? '?page_id='.$_GET['page_id'] : '';
                            $page_url = strtok(wps_curPageURL(), '?').$page_id;

                            $wps_change_avatar_effects = get_option('wps_change_avatar_effects');
                            if (!$wps_change_avatar_effects) $wps_change_avatar_effects = 'flip,rotate,invert,sketch,pixelate,sepia,emboss';   
                            if ($effects):
                                $effects = explode(',', $wps_change_avatar_effects);
                            else:
                                $effects = array();
                            endif;
                            $page_url = $page_url.wps_query_mark($page_url);
                            if (in_array('flip', $effects)) $html .= '<a class="wps_avatar_upload_effect"  href="'.$page_url.'wps_avatar_change_step=2&flip_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$flip.'</a>';
                            if (in_array('rotate', $effects)) $html .= '<a class="wps_avatar_upload_effect"  href="'.$page_url.'wps_avatar_change_step=2&rotate_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$rotate.'</a>';
                            if (in_array('invert', $effects)) $html .= '<a class="wps_avatar_upload_effect" href="'.$page_url.'wps_avatar_change_step=2&invert_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$invert.'</a>';
                            if (in_array('sketch', $effects)) $html .= '<a class="wps_avatar_upload_effect" href="'.$page_url.'wps_avatar_change_step=2&sketch_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$sketch.'</a>';
                            if (in_array('pixelate', $effects)) $html .= '<a class="wps_avatar_upload_effect" href="'.$page_url.'wps_avatar_change_step=2&pixelate_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$pixelate.'</a>';
                            if (in_array('sepia', $effects)) $html .= '<a class="wps_avatar_upload_effect" href="'.$page_url.'wps_avatar_change_step=2&sepia_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$sepia.'</a>';
                            if (in_array('emboss', $effects)) $html .= '<a class="wps_avatar_upload_effect" href="'.$page_url.'wps_avatar_change_step=2&emboss_file='.$file.'&url='.$url.'&file_size='.$file_size.'">'.$emboss.'</a>';
                            $html .= '<div id="wps_uploaded_avatar_to_crop">';
                            $html .= '<img src="'.$url.'" id="wps_upload" width="'.esc_attr($width).'" height="'.esc_attr($height).'" />';
                            $html .= '</div>';

                            $html .= '<div id="wps_preview" style="float:left; margin-top:20px !important; width: 150px; height: 150px; overflow: hidden;">';
                            $html .= '<img src="'.esc_url_raw($url).'" width="'.esc_attr($width).'" height="'.esc_attr($height).'" style="max-width:none" />';
                            $html .= '</div>';

                            $html .= '<input type="hidden" name="x1" id="x1" value="0" />';
                            $html .= '<input type="hidden" name="y1" id="y1" value="0" />';
                            $html .= '<input type="hidden" name="x2" id="x2" />';
                            $html .= '<input type="hidden" name="y2" id="y2" />';
                            $html .= '<input type="hidden" name="width" id="width" value="'.esc_attr($width).'" />';
                            $html .= '<input type="hidden" name="height" id="height" value="'.esc_attr($height).'" />';
                            $html .= '<input type="hidden" id="init_width" value="'.esc_attr($width).'" />';
                            $html .= '<input type="hidden" id="init_height" value="'.esc_attr($height).'" />';

                            $html .= '<input type="hidden" name="oitar" id="oitar" value="'.esc_attr($oitar).'" />';
                            wp_nonce_field('user-avatar');
                            $html .= '<button class="wps_button" style="clear:both; margin-top:20px !important; margin-left:20px !important;" id="user-avatar-crop-button">'.__('Crop', WPS2_TEXT_DOMAIN).'</button>';

                            $html .= '</form>';

                        $html .= '</div>';
    
                    endif;

				endif;

			else: // $step == 3

                if (isset($_POST['oitar'])):
    
                    // Doing crop
                    $x1_post = floatval($_POST['x1']);
                    $y1_post = floatval($_POST['y1']);
                    $oitar_post = floatval($_POST['oitar']);
                    $width_post = floatval($_POST['width']);
                    $height_post = floatval($_POST['height']);
    
                    /* removed in 16.08 
                    if ( false && $oitar_post > 1 ):
                        $x1_post =$x1_post * $oitar_post;
                        $y1_post = $y1_post * $oitar_post;
                        $width_post = $width_post * $oitar_post;
                        $height_post = $height_post * $oitar_post;
                    endif;
                    */

                    $original_file = get_transient( 'avatar_file_'.$user_id );
                    delete_transient('avatar_file_'.$user_id );

                    $time_now = time();

                    if( !file_exists($original_file) ):

                        $html .= "<div class='error'><p>". __('Sorry, no file available', WPS2_TEXT_DOMAIN)."</p></div>";

                    else:

                        // Create avatar folder if not already existing
                        $continue = true;
                        if( !file_exists(WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/") ):
    
                            if (!mkdir(WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/", 0777 ,true)):
                                $error = error_get_last();
                                $html .= $error['message'].'<br />';
                                $html .= WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/<br>";
                                $continue = false;
                            else:
                                $path = WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/";
                                $cropped_full = $path.$time_now."-wpsfull.jpg";
                                $cropped_thumb = $path.$time_now."-wpsthumb.jpg";
                            endif;
                        else:
                            $cropped_full = user_avatar_core_avatar_upload_path($user_id).$time_now."-wpsfull.jpg";
                            $cropped_thumb = user_avatar_core_avatar_upload_path($user_id).$time_now."-wpsthumb.jpg";
                        endif;

                        if ($continue):

                            // delete the previous files
                            user_avatar_delete_files($user_id);
                            @mkdir(WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/", 0777 ,true);

                            if (!class_exists('SimpleImage')) require_once('SimpleImage.php');

                            // update the files 
                            $img = $original_file;
	                            $image = new SimpleImage();
	                            $image->load($img);
	                            $image->crop($x1_post, $y1_post, $x1_post+$width_post, $y1_post+$height_post);
	                            $image->save($cropped_full);
    
                            $img = $original_file;
	                            $image = new SimpleImage();
	                            $image->load($img);
	                            $image->crop($x1_post, $y1_post, $x1_post+$width_post, $y1_post+$height_post);
                                $image->resize(100,100);
	                            $image->save($cropped_thumb);

							/* Update user's meta data for quick reference */
							update_user_meta( $user_id, 'wps_pro_avatar', "/wps-pro-content/members/".$user_id."/avatar/".$time_now."-wpsfull.jpg" );	 
    
                            if ( is_wp_error( $cropped_full ) ):
                                $html .= __( 'Image could not be processed. Please try again.', WPS2_TEXT_DOMAIN);	
                                //var_dump($cropped_full);	
                            else:
                                /* Remove the original */
                                @unlink( $original_file );
                                $html .= '<script>window.location.replace("'.get_page_link(get_option('wpspro_profile_page')).'?user_id='.$user_id.'");</script>';
                            endif;

                        endif;

                    endif;
    
                else:
    
                    // Skip crop
    
					$overrides = array('test_form' => false);
					$file = wp_handle_upload($_FILES['uploadedfile'], $overrides);

					if ( isset($file['error']) ){
						die( $file['error'] );
					}

					$url = $file['url'];
					$type = $file['type'];
					$original_file = $file['file'];
					$filename = basename($original_file);

					$time_now = time();

                    if( !file_exists($original_file) ):

                        $html .= "<div class='error'><p>". __('Sorry, no file available', WPS2_TEXT_DOMAIN)."</p></div>";

                    else:

                        // Create avatar folder if not already existing
                        $continue = true;
                        if( !file_exists(WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/") ):
                            if (!mkdir(WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/", 0777 ,true)):
                                $error = error_get_last();
                                $html .= $error['message'].'<br />';
                                $html .= WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/<br>";
                                $continue = false;
                            else:
                                $path = WP_CONTENT_DIR."/wps-pro-content/members/".$user_id."/avatar/";
                                $cropped_full = $path.$time_now."-wpsfull.jpg";
                                $cropped_thumb = $path.$time_now."-wpsthumb.jpg";
                            endif;
                        else:
                            $cropped_full = user_avatar_core_avatar_upload_path($user_id).$time_now."-wpsfull.jpg";
                            $cropped_thumb = user_avatar_core_avatar_upload_path($user_id).$time_now."-wpsthumb.jpg";
                        endif;

                        if ($continue):

                            // delete the previous files
                            user_avatar_delete_files($user_id);

                            // update the files 
                            list($width, $height, $type, $attr) = getimagesize( $original_file );    
                            $cropped_full = wp_crop_image( $original_file, 0, 0, $width, $height, 300, 300, false, $cropped_full );
                            $cropped_thumb = wp_crop_image( $original_file, 0, 0, $width, $height, 300, 300, false, $cropped_thumb );

							/* Update user's meta data for quick reference */
							update_user_meta( $user_id, 'wps_pro_avatar', "/wps-pro-content/members/".$user_id."/avatar/".$time_now."-wpsfull.jpg" );	 

                            if ( is_wp_error( $cropped_full ) ):
                                $html .= __( 'Image could not be processed. Please try again.', WPS2_TEXT_DOMAIN);	
                                //var_dump($cropped_full);	
                            else:
                                /* Remove the original */
                                @unlink( $original_file );
                                $html .= '<script>window.location.replace("'.get_page_link(get_option('wpspro_profile_page')).'?user_id='.$user_id.'");</script>';
                            endif;

                        endif;

                    endif;
    
                endif;


			endif;

		else:

			$html .= $not_permitted;

		endif;

    else:

        if (!is_user_logged_in() && $logged_out_msg):
            $query = wps_query_mark(get_bloginfo('url').$login_url);
            if ($login_url) $html .= sprintf('<a href="%s%s%sredirect=%s">', get_bloginfo('url'), $login_url, $query, wps_root( $_SERVER['REQUEST_URI'] ));
            $html .= $logged_out_msg;
            if ($login_url) $html .= '</a>';
        endif;
    
    endif;

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_avatar_change', '', '', $styles, $values);
    
	return $html;
}



add_shortcode(WPS_PREFIX.'-avatar', 'wps_avatar');
add_shortcode(WPS_PREFIX.'-avatar-change-link', 'wps_avatar_change_link');
add_shortcode(WPS_PREFIX.'-avatar-change', 'wps_avatar_change');
?>