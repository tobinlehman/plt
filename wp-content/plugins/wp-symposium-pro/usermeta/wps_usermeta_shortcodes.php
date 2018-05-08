<?php

																	/* **** */
																	/* INIT */
																	/* **** */

function wps_usermeta_init() {

    $tabs_array = get_option('wps_profile_tabs');
    $wps_profile_tab_animation = (isset($tabs_array['wps_profile_tab_animation'])) ? $tabs_array['wps_profile_tab_animation'] : 'slide';
    
	// JS and CSS
	wp_enqueue_style('wps-usermeta-css', plugins_url('wps_usermeta.css', __FILE__), 'css');
	wp_enqueue_script('wps-usermeta-js', plugins_url('wps_usermeta.js', __FILE__), array('jquery'));	
    $wps_strength_array = get_option('wps_strength_array');
    if (!$wps_strength_array) $wps_strength_array = array('Weak','Poor','Good','Strong','Mismatch');
	wp_localize_script('wps-usermeta-js', 'wps_usermeta', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'animation' => $wps_profile_tab_animation,
        'score1' => stripslashes($wps_strength_array[0]),
        'score2' => stripslashes($wps_strength_array[1]),
        'score3' => stripslashes($wps_strength_array[2]),
        'score4' => stripslashes($wps_strength_array[3]),
        'score5' => stripslashes($wps_strength_array[4])
    ));    	
    // Password security
    wp_enqueue_script('password-strength-meter');
    
	// Anything else?
	do_action('wps_usermeta_init_hook');

}

function wps_add_tab_css(){    
    
    $tabs_array = get_option('wps_profile_tabs');
    $wps_profile_tab_active_color = (isset($tabs_array['wps_profile_tab_active_color'])) ? $tabs_array['wps_profile_tab_active_color'] : '#fff';
    $wps_profile_tab_inactive_color = (isset($tabs_array['wps_profile_tab_inactive_color'])) ? $tabs_array['wps_profile_tab_inactive_color'] : '#d2d2d2';
    $wps_profile_tab_active_text_color = (isset($tabs_array['wps_profile_tab_active_text_color'])) ? $tabs_array['wps_profile_tab_active_text_color'] : '#000';
    $wps_profile_tab_inactive_text_color = (isset($tabs_array['wps_profile_tab_inactive_text_color'])) ? $tabs_array['wps_profile_tab_inactive_text_color'] : '#000';

    echo '<style>';
    
    echo '.wps-tab-links a:hover {';
    echo 'background-color:'.$wps_profile_tab_active_color.';';
    echo 'color:'.$wps_profile_tab_active_text_color.';';    
    echo 'border-bottom: 1px solid '.$wps_profile_tab_inactive_color.';';
    echo '}';

    echo '.wps-tab-links li.active a:hover {';
    echo 'border-bottom: 1px solid transparent;';
    echo '}';
    
    echo '.wps-tab-content {';
    echo 'background-color:'.$wps_profile_tab_active_color.';';
    echo 'border: 1px solid '.$wps_profile_tab_inactive_color.';';
    echo '}';
    
    echo '.wps-tab-links li a, .wps-tab-links li a:visited {';
    echo 'border-top: 1px solid '.$wps_profile_tab_inactive_color.';';
    echo 'border-left: 1px solid '.$wps_profile_tab_inactive_color.';';
    echo 'border-right: 1px solid '.$wps_profile_tab_inactive_color.';';
    echo 'border-bottom: 1px solid transparent;';
    echo 'background-color:'.$wps_profile_tab_inactive_color.';';
    echo 'color:'.$wps_profile_tab_inactive_text_color.';';
    echo 'text-decoration:none;';
    echo '}';
    
    echo '.wps-tab-links li.active a {';
    echo 'background-color:'.$wps_profile_tab_active_color.' !important;';
    echo 'color:'.$wps_profile_tab_active_text_color.' !important;';
    echo '}';
        
    echo '</style>';
    
    
}   


																	/* ********** */
																	/* SHORTCODES */
																	/* ********** */

function wps_user_id($atts) {
    global $current_user;
    $html = '';
    $user_id = wps_get_user_id();
    $html .= $user_id;
    return $html;
}

function wps_usermeta_button($atts) {

	// Init
	add_action('wp_footer', 'wps_usermeta_init');

	global $current_user;
	$html = '';

	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_usermeta_button');        
	extract( shortcode_atts( array(
		'user_id' => false,
		'url' => wps_get_shortcode_value($values, 'wps_usermeta_button-url', ''),
		'value' => wps_get_shortcode_value($values, 'wps_usermeta_button-value', __('Go', WPS2_TEXT_DOMAIN)),
		'class' => wps_get_shortcode_value($values, 'wps_usermeta_button-class', ''),
		'styles' => true,
        'after' => '',
		'before' => '',
	), $atts, 'wps_usermeta_button' ) );

	if (!$user_id) $user_id = wps_get_user_id();

	if (!$url):

		$html .= '<div class="wps_error">'.__('Please set URL option in the shortcode.', WPS2_TEXT_DOMAIN).'</div>';

	else:

		$html .= '<form action="" method="POST">';
		$url .= wps_query_mark($url).'user_id='.$user_id;
		$html .= '<input class="wps_user_button '.$class.'" rel="'.$url.'" type="submit" class="wps_button '.$class.'" value="'.$value.'" />';
		$html .= '</form>';

	endif;

	if ($html) $html = htmlspecialchars_decode($before).$html.htmlspecialchars_decode($after);

	return $html;	
}

function wps_usermeta($atts) {

	// Init
	add_action('wp_footer', 'wps_usermeta_init');

	global $current_user;
	$html = '';

	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_usermeta');    
	extract( shortcode_atts( array(
		'user_id' => false,
		'meta' => wps_get_shortcode_value($values, 'wps_usermeta-meta', 'wpspro_home'),
		'label' => wps_get_shortcode_value($values, 'wps_usermeta-label', ''),
		'size' => wps_get_shortcode_value($values, 'wps_usermeta-size', '250,250'),
		'map_style' => wps_get_shortcode_value($values, 'wps_usermeta-map_style', 'dynamic'),
		'zoom' => wps_get_shortcode_value($values, 'wps_usermeta-zoom', 5),
        'link' => wps_get_shortcode_value($values, 'wps_usermeta-link', true),
		'styles' => true,
        'after' => '',
		'before' => '',
	), $atts, 'wps_usermeta' ) );
	$size = explode(',', $size);

	if (!$user_id) $user_id = wps_get_user_id();

    if ($user_id):

    	if (strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false):
            $friends = wps_are_friends($current_user->ID, $user_id);
            // By default same user, and friends of user, can see profile
            $user_can_see_profile = ($current_user->ID == $user_id || $friends['status'] == 'publish') ? true : false;
            $user_can_see_profile = apply_filters( 'wps_check_profile_security_filter', $user_can_see_profile, $user_id, $current_user->ID );
        else:
            $user_can_see_profile = $current_user->ID == $user_id ? true : false;
        endif;

    	if ($user_can_see_profile):

    		$user = get_user_by('id', $user_id);
    		if ($user):
    			if ($meta != 'wpspro_map'):
        
                    $user_values = array('ID', 'display_name', 'user_firstname', 'user_lastname', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_status');
                    if (in_array($meta, $user_values)) {
        
                        if ($label) $html .= '<span class="wps_usermeta_label">'.$label.'</span> ';    
                        if ($meta == 'user_email' && $link) {
                            $html .= '<a href="mailto:'.$user->$meta.'">'.$user->$meta.'</a>';
                        } else {
                            $html .= $user->$meta;
                        }
        
                    } else {

                        if ($value = get_user_meta( $user_id, $meta, true )) {
                            if ($label) $html .= '<span class="wps_usermeta_label">'.$label.'</span> ';
                        } else {
                            if ($value = get_user_meta( $user_id, 'wps_'.$meta, true )):
                                // Filter for value
                                $value = apply_filters( 'wps_usermeta_value_filter', $value, $atts, $user_id );
                            endif;
                        }
                        $html .= $value;
        
                    }
        
                else:
        
    				$city = get_user_meta( $user_id, 'wpspro_home', true );
    				$country = get_user_meta( $user_id, 'wpspro_country', true );
    				if ($city && $country):
    					if ($map_style == "static"):
    						$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to enlarge">';
    						$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&size='.$size[0].'x'.$size[1].'&zoom='.$zoom.'&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
    						$html .= "</a>";
    					else:
    						$html .= "<iframe width='".$size[0]."' height='".$size[1]."' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.co.uk/maps?q=".$city.",+".$country."&amp;z=".$zoom."&amp;output=embed&amp;iwloc=near'></iframe>";
    					endif;
                    else:
                        $html .= "<div id='wps_no_map_available' style='width:".$size[0]."px; height:".$size[1]."px;'></div>";
    				endif;

    			endif;
    		endif;

    	endif;

    endif;
    
	if ($html) $html = htmlspecialchars_decode($before).$html.htmlspecialchars_decode($after);
    
	return $html;

}

function wps_usermeta_change($atts) {

	// Init
	add_action('wp_footer', 'wps_usermeta_init');
    add_action('wp_footer', 'wps_add_tab_css');

	global $current_user;
	$html = '';

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_usermeta_change');    
    extract( shortcode_atts( array(
        'user_id' => 0,
        'meta_class' => 'wps_usermeta_change_label',
        'show_town' => wps_get_shortcode_value($values, 'wps_usermeta_change-show_town', true),
        'show_country' => wps_get_shortcode_value($values, 'wps_usermeta_change-show_country', true),
        'show_name' => wps_get_shortcode_value($values, 'wps_usermeta_change-show_name', true),
        'class' => wps_get_shortcode_value($values, 'wps_usermeta_change-class', ''),
        'label' => wps_get_shortcode_value($values, 'wps_usermeta_change-label', __('Update', WPS2_TEXT_DOMAIN)),
        'town' => wps_get_shortcode_value($values, 'wps_usermeta_change-town', __('Town/City', WPS2_TEXT_DOMAIN)),
        'town_default' => wps_get_shortcode_value($values, 'wps_usermeta_change-town_default', ''),
        'town_mandatory' => wps_get_shortcode_value($values, 'wps_usermeta_change-town_mandatory', false),
        'country' => wps_get_shortcode_value($values, 'wps_usermeta_change-country', __('Country', WPS2_TEXT_DOMAIN)),
        'country_default' => wps_get_shortcode_value($values, 'wps_usermeta_change-country_default', ''),
        'country_mandatory' => wps_get_shortcode_value($values, 'wps_usermeta_change-country_mandatory', false),
        'displayname' => wps_get_shortcode_value($values, 'wps_usermeta_change-displayname', __('Display Name', WPS2_TEXT_DOMAIN)),
        'name' => wps_get_shortcode_value($values, 'wps_usermeta_change-name', __('Your first name and family name', WPS2_TEXT_DOMAIN)),
        'language' => wps_get_shortcode_value($values, 'wps_usermeta_change-language', __('Select your language', WPS2_TEXT_DOMAIN)),
        'password' => wps_get_shortcode_value($values, 'wps_usermeta_change-password', __('Change your password', WPS2_TEXT_DOMAIN)),
        'password2' => wps_get_shortcode_value($values, 'wps_usermeta_change-password2', __('Re-type your password', WPS2_TEXT_DOMAIN)),
        'password_msg' => wps_get_shortcode_value($values, 'wps_usermeta_change-password_msg', __('Password changed, please log in again.', WPS2_TEXT_DOMAIN)),
        'email' => wps_get_shortcode_value($values, 'wps_usermeta_change-email', __('Email address', WPS2_TEXT_DOMAIN)),
        'logged_out_msg' => wps_get_shortcode_value($values, 'wps_usermeta_change-logged_out_msg', __('You must be logged in to view this page.', WPS2_TEXT_DOMAIN)),
        'mandatory' => wps_get_shortcode_value($values, 'wps_usermeta_change-mandatory', '<span style="color:red;"> *</span>'),        
        'login_url' => wps_get_shortcode_value($values, 'wps_usermeta_change-login_url', ''),
        'required_msg' => wps_get_shortcode_value($values, 'wps_usermeta_change-required_msg', __('Please check for required fields', WPS2_TEXT_DOMAIN)),
        'styles' => true,
        'after' => '',
        'before' => '',

    ), $atts, 'wps_usermeta' ) );

	if (is_user_logged_in()) {
    
		if (!$user_id)
			$user_id = wps_get_user_id();

		$user_can_see_profile = ($current_user->ID == $user_id || current_user_can('manage_options')) ? true : false;

        if (current_user_can('manage_options') && !$login_url && function_exists('wps_login_init')):
            $html = wps_admin_tip($html, 'wps_usermeta_change', __('Add login_url="/example" to the [wps-usermeta-change] shortcode to let users login and redirect back here when not logged in.', WPS2_TEXT_DOMAIN));
        endif;    
        
		if ($user_can_see_profile):
        
            $mandatory = html_entity_decode($mandatory, ENT_QUOTES);

			// Start building tabs array
			$tabs = array();
            $tabs_array = get_option('wps_profile_tabs');        
                
			// Update if POSTing
			if (isset($_POST['wps_usermeta_change_update'])):
        
                // First do nonce check to ensure being posted from trusted source
                if ( 
                    ! isset( $_POST['wps_usermeta_change_nonce_field'] ) 
                    || ! wp_verify_nonce( $_POST['wps_usermeta_change_nonce_field'], 'wps_usermeta_change_nonce' ) 
                ) {        
                    
                    $html .= '<div class="wps_error">'.__('Security field did not validate').'</div>';
                    
                } else {

                    if ($display_name = $_POST['wpspro_display_name'])
                        wp_update_user( array ( 'ID' => $user_id, 'display_name' => $display_name ) ) ;

                    if ($first_name = $_POST['wpspro_firstname'])
                        wp_update_user( array ( 'ID' => $user_id, 'first_name' => $first_name ) ) ;
                    if ($last_name = $_POST['wpspro_lastname'])
                        wp_update_user( array ( 'ID' => $user_id, 'last_name' => $last_name ) ) ;

                    if ($user_email = $_POST['wpspro_email'])
                        wp_update_user( array ( 'ID' => $user_id, 'user_email' => $user_email ) ) ;

                    if (isset($_POST['wpspro_home'])):
                        $home_form = str_replace('"', '', (str_replace('\'', '', (str_replace('<', '', (str_replace('>', '', strip_tags($_POST['wpspro_home']))))))));
                        update_user_meta( $user_id, 'wpspro_home', $home_form);		        
                    endif;

                    if (isset($_POST['wpspro_country'])):
                        $country_form = str_replace('"', '', (str_replace('\'', '', (str_replace('<', '', (str_replace('>', '', strip_tags($_POST['wpspro_country']))))))));
                        update_user_meta( $user_id, 'wpspro_country', $country_form);
                    endif;

                    // Update lat and long from location fields?
                    if (isset($_POST['wpspro_home']) && isset($_POST['wpspro_country'])):

                        // Change spaces to %20 for Google maps API and geo-code
                        $city = str_replace(' ','%20',$_POST['wpspro_home']);
                        $country_value = str_replace(' ','%20',$_POST['wpspro_country']);
                        $fgc = 'http://maps.googleapis.com/maps/api/geocode/json?address='.$city.'+'.$country_value.'&sensor=false';

                        if ($json = @file_get_contents($fgc) ):
                            $json_output = json_decode($json, true);
                            
                            @$lat = $json_output['results'][0]['geometry']['location']['lat'];
                            @$lng = $json_output['results'][0]['geometry']['location']['lng'];

                            update_user_meta($user_id, 'wpspro_lat', $lat);
                            update_user_meta($user_id, 'wpspro_long', $lng);

                        else:

                            update_user_meta($user_id, 'wpspro_lat', 0);
                            update_user_meta($user_id, 'wpspro_long', 0);

                        endif;

                    else:
                        // can't find out lat and long so save as 0 so (for example) they still appear in the directory feature
                        update_user_meta($user_id, 'wpspro_lat', 0);
                        update_user_meta($user_id, 'wpspro_long', 0);                        
                    endif;                    

                    if (isset($_POST['wpspro_password']) && $_POST['wpspro_password'] != ''):
                        $pw = $_POST['wpspro_password'];
                        wp_set_password($pw, $user_id);
                        $html .= '<div class="wps_success password_msg">'.$password_msg.'</div>';
                    endif;

                    $refresh = false;
                    if (isset($_POST['wpspro_lang'])):
                        $user_lang = get_user_meta($user_id, 'wpspro_lang', true);
                        if ($user_lang != $_POST['wpspro_lang']) $refresh = true;
                        if ($_POST['wpspro_lang']):
                            update_user_meta( $user_id, 'wpspro_lang', $_POST['wpspro_lang']);
                        else:
                            delete_user_meta($user_id, 'wpspro_lang');
                        endif;             
                    endif;

                    do_action( 'wps_usermeta_change_hook', $user_id, $atts, $_POST, $_FILES );

                    if ($refresh):
                        // .. need a refresh to change language (wait 1 second for page to load)
                        echo '<script>';
                            echo "window.setTimeout(wps_reload_page,1000);";
                            echo "function wps_reload_page() {";
                                echo "alert('".__('Page will be refreshed for your chosen language.', WPS2_TEXT_DOMAIN)."');";
                                echo "window.location.reload();"; 
                            echo "}";
                        echo '</script>';
                    endif;
                } // End of nonce check

			endif;

			if (!isset($_POST['wpspro_password']) || $_POST['wpspro_password'] == ''):

                $the_user = get_user_by('id', $user_id);

                $value = isset($_POST['wpspro_display_name']) ? stripslashes($_POST['wpspro_display_name']) : $the_user->display_name;
                    $form_html = '<div class="wps_usermeta_change_item">';
                    $form_html .= '<div class="'.$meta_class.'">'.$displayname.'</div>';
                    $form_html .= '<input type="text" id="wpspro_display_name" class="wps_mandatory_field" name="wpspro_display_name" value="'.$value.'" />';
                    $form_html .= $mandatory;
                    $form_html .= '</div>';
                    $tab_row['tab'] = isset($tabs_array['wps_profile_tab_names']) ? $tabs_array['wps_profile_tab_names'] : 1;
                    $tab_row['html'] = $form_html;        
                    $tab_row['mandatory'] = false;     
                    array_push($tabs,$tab_row);        

                if ($name && $show_name):
                    $firstname = isset($_POST['wpspro_firstname']) ? $_POST['wpspro_firstname'] : $the_user->first_name;
                    $lastname = isset($_POST['wpspro_lastname']) ? $_POST['wpspro_lastname'] : $the_user->last_name;
                    $form_html = '<div class="wps_usermeta_change_item">';
                        $form_html .= '<div class="'.$meta_class.'">'.$name.'</div>';
                        $form_html .= '<div class="wps_usermeta_change_name"><input type="text" name="wpspro_firstname" class="wps_mandatory_field" value="'.$firstname.'"> ';
                        $form_html .= '<input type="text" name="wpspro_lastname" class="wps_mandatory_field" value="'.$lastname.'">'.$mandatory.'</div>';
                    $form_html .= '</div>';
                    $tab_row['tab'] = isset($tabs_array['wps_profile_tab_names']) ? $tabs_array['wps_profile_tab_names'] : 1;
                    $tab_row['html'] = $form_html;        
                    $tab_row['mandatory'] = false;     
                    array_push($tabs,$tab_row);                
                endif;

                $value = isset($_POST['wpspro_email']) ? $_POST['wpspro_email'] : $the_user->user_email;
                    $form_html = '<div class="wps_usermeta_change_item">';
                    $form_html .= '<div class="'.$meta_class.'">'.$email.'</div>';
                    $form_html .= '<input type="text" id="wpspro_email" class="wps_mandatory_field" name="wpspro_email" value="'.$value.'" />';
                    $form_html .= $mandatory;
                    $form_html .= '</div>';
                    $tab_row['tab'] = isset($tabs_array['wps_profile_tab_email']) ? $tabs_array['wps_profile_tab_email'] : 1;
                    $tab_row['html'] = $form_html;        
                    $tab_row['mandatory'] = false;     
                    array_push($tabs,$tab_row);                

                $value = get_user_meta( $user_id, 'wpspro_home', true );
                    if ($town && $show_town):
                        $form_html = '<div id="wpspro_home" class="wps_usermeta_change_item">';
                        $form_html .= '<div class="'.$meta_class.'">'.$town.'</div>';
                        if (!$value && $town_default) $value = $town_default;
                        $form_html .= '<input type="text" id="wpspro_home" ';
                            if ($town_mandatory) $form_html .= 'class="wps_mandatory_field" ';
                            $form_html .= 'name="wpspro_home" value="'.$value.'" />';
                        if ($town_mandatory) $form_html .= $mandatory;
                        $form_html .= '</div>';
                        $tab_row['tab'] = isset($tabs_array['wps_profile_tab_location']) ? $tabs_array['wps_profile_tab_location'] : 1;
                        $tab_row['html'] = $form_html; 
                        $tab_row['mandatory'] = $town_mandatory;
                        array_push($tabs,$tab_row);                
                    endif;

                $value = get_user_meta( $user_id, 'wpspro_country', true );
                    if ($country && $show_country):
                        $form_html = '<div id="wpspro_country" class="wps_usermeta_change_item">';
                        $form_html .= '<div class="'.$meta_class.'">'.$country.'</div>';
                        if (!$value && $country_default) $value = $country_default;
                        $form_html .= '<input type="text" id="wpspro_country" ';
                            if ($country_mandatory) $form_html .= 'class="wps_mandatory_field" ';        
                            $form_html .= 'name="wpspro_country" value="'.$value.'" />';
                        if ($country_mandatory) $form_html .= $mandatory;
                        $form_html .= '</div>';
                        $tab_row['tab'] = isset($tabs_array['wps_profile_tab_location']) ? $tabs_array['wps_profile_tab_location'] : 1;
                        //$form_html .= '<div id="wpspro_geo" class="wps_usermeta_change_item">'.__('Geo co-ordinates:', WPS2_TEXT_DOMAIN).' '.get_user_meta($user_id, 'wpspro_lat', true).'/'.get_user_meta($user_id, 'wpspro_long', true).'</div>';
                        $tab_row['html'] = $form_html;        
                        $tab_row['mandatory'] = $country_mandatory;
                        array_push($tabs,$tab_row);                
                    endif;
        
                // Language select
                    $wps_pro_lang = ($l = get_option('wps_pro_lang')) ? $l : false;
                    if ($wps_pro_lang):
                        $user_lang = get_user_meta($user_id, 'wpspro_lang', true);

                        $form_html = '<div class="wps_usermeta_change_item">';
                        $form_html .= '<div class="'.$meta_class.'">'.$language.'</div>';
                        $form_html .= '<select name="wpspro_lang" id="wpspro_lang" style="width:200px">';
                        $options = '';

                        $langs = explode("\n", str_replace("\r", "", $wps_pro_lang));
                        foreach ($langs as $lang):
                            if (strpos($lang, ',') !== false):
                                list($text, $value) = explode(',', $lang);
                            else:
                                $text = $lang;
                                $value = '';
                            endif;
                            $options .= '<option value="'.$value.'"';
                                if ($user_lang == $value) $options .= ' SELECTED';
                                $options .= '>'.$text.'</option>';
                        endforeach;

                        $form_html .= $options;
                        $form_html .= '</select>';
                        $form_html .= '</div>';
                        $tab_row['tab'] = isset($tabs_array['wps_profile_tab_lang']) ? $tabs_array['wps_profile_tab_lang'] : 1;
                        $tab_row['html'] = $form_html;        
                        $tab_row['mandatory'] = false;     
                        array_push($tabs,$tab_row);
                    endif;                

                // Password change
                    $form_html = '<div class="wps_usermeta_change_item">';
                    $form_html .= '<div class="'.$meta_class.'">'.$password.'</div>';
                    $form_html .= '<input type="password" name="wpspro_password" id="wpspro_password" />';
                    $form_html .= '<div class="'.$meta_class.'">'.$password2.'</div>';
                    $form_html .= '<input type="password" name="wpspro_password2" id="wpspro_password2" />';
                    if (!get_option('wps_password_strength_meter')) $form_html .= '<div id="wps_password_strength_result" style="display:none"></div>';
                    $form_html .= '</div>';
                    $tab_row['tab'] = isset($tabs_array['wps_profile_tab_password']) ? $tabs_array['wps_profile_tab_password'] : 1;
                    $tab_row['html'] = $form_html;        
                    $tab_row['mandatory'] = false;     
                    array_push($tabs,$tab_row);                

                // Anything else?
                $tabs = apply_filters( 'wps_usermeta_change_filter', $tabs, $atts, $user_id );

			endif;

            // Build output
            $wps_profile_tab1 = (isset($tabs_array['wps_profile_tab1'])) ? $tabs_array['wps_profile_tab1'] : false;
            $wps_profile_tab2 = (isset($tabs_array['wps_profile_tab2'])) ? $tabs_array['wps_profile_tab2'] : false;
            $wps_profile_tab3 = (isset($tabs_array['wps_profile_tab3'])) ? $tabs_array['wps_profile_tab3'] : false;
            $wps_profile_tab4 = (isset($tabs_array['wps_profile_tab4'])) ? $tabs_array['wps_profile_tab4'] : false;
            $wps_profile_tab5 = (isset($tabs_array['wps_profile_tab5'])) ? $tabs_array['wps_profile_tab5'] : false;
            $wps_profile_tab6 = (isset($tabs_array['wps_profile_tab6'])) ? $tabs_array['wps_profile_tab6'] : false;
            $wps_profile_tab7 = (isset($tabs_array['wps_profile_tab7'])) ? $tabs_array['wps_profile_tab7'] : false;
            $wps_profile_tab8 = (isset($tabs_array['wps_profile_tab8'])) ? $tabs_array['wps_profile_tab8'] : false;
            $wps_profile_tab9 = (isset($tabs_array['wps_profile_tab9'])) ? $tabs_array['wps_profile_tab9'] : false;
            $wps_profile_tab10 = (isset($tabs_array['wps_profile_tab10'])) ? $tabs_array['wps_profile_tab10'] : false;

            $tab_ptr = 1;
            $max_tabs = false;
            if ($wps_profile_tab1) $max_tabs = 1;
            if ($wps_profile_tab2) $max_tabs = 2;
            if ($wps_profile_tab3) $max_tabs = 3;
            if ($wps_profile_tab4) $max_tabs = 4;
            if ($wps_profile_tab5) $max_tabs = 5;
            if ($wps_profile_tab6) $max_tabs = 6;
            if ($wps_profile_tab7) $max_tabs = 7;
            if ($wps_profile_tab8) $max_tabs = 8;
            if ($wps_profile_tab9) $max_tabs = 9;
            if ($wps_profile_tab10) $max_tabs = 10;
        
            // Show form
            $html .= '<form enctype="multipart/form-data" id="wps_usermeta_change" action="#" method="POST">';
                $html .= '<input type="hidden" name="wps_usermeta_change_update" value="yes" />';
                $html .= wp_nonce_field( 'wps_usermeta_change_nonce', 'wps_usermeta_change_nonce_field' );

                if ($max_tabs):
        
                    $html .= '<div class="wps-tabs">';
                        $html .= '<ul class="wps-tab-links">';
                            $html .= '<li id="wps-tab1" class="active"><a href="#tab1">'.$wps_profile_tab1.'</a></li>';
                            if ($wps_profile_tab2) $html .= '<li id="wps-tab2" ><a href="#tab2">'.$wps_profile_tab2.'</a></li>';
                            if ($wps_profile_tab3) $html .= '<li id="wps-tab3" ><a href="#tab3">'.$wps_profile_tab3.'</a></li>';
                            if ($wps_profile_tab4) $html .= '<li id="wps-tab4" ><a href="#tab4">'.$wps_profile_tab4.'</a></li>';
                            if ($wps_profile_tab5) $html .= '<li id="wps-tab5" ><a href="#tab5">'.$wps_profile_tab5.'</a></li>';
                            if ($wps_profile_tab6) $html .= '<li id="wps-tab6" ><a href="#tab6">'.$wps_profile_tab6.'</a></li>';
                            if ($wps_profile_tab7) $html .= '<li id="wps-tab7" ><a href="#tab7">'.$wps_profile_tab7.'</a></li>';
                            if ($wps_profile_tab8) $html .= '<li id="wps-tab8" ><a href="#tab8">'.$wps_profile_tab8.'</a></li>';
                            if ($wps_profile_tab9) $html .= '<li id="wps-tab9" ><a href="#tab9">'.$wps_profile_tab9.'</a></li>';
                            if ($wps_profile_tab10) $html .= '<li id="wps-tab10" ><a href="#tab10">'.$wps_profile_tab10.'</a></li>';
                        $html .= '</ul>';

                        $html .= '<div class="wps-tab-content">';

                            while ($tab_ptr <= $max_tabs)
                            {
                                $html .= '<div id="tab'.$tab_ptr.'" class="wps-tab ';
                                if ($tab_ptr == 1) $html .= 'active';
                                $html .= '"><div id="wps-tab-content-'.$tab_ptr.'" class="wps-tab-content-inner">';
                                foreach ($tabs as $tab):
                                    if ($tab['tab'] == $tab_ptr):
                                        $html .= '<p>'.$tab['html'].'</p>';     
                                    endif;
                                endforeach;
                                $html .= '</div></div>';
                                $tab_ptr++;
                            }


                        $html .= '</div>';
                    $html .= '</div>';
        
                else:
        
                    while ($tab_ptr <= 10)
                    {
                        foreach ($tabs as $tab):
                            if ($tab['tab'] == $tab_ptr):
                                $html .= $tab['html'];  
                            endif;
                        endforeach;
                        $tab_ptr++;
                    }
        
                endif;

                $html .= '<div id="wps_required_msg" class="wps_error" style="display:none">'.$required_msg.'</div>';
                $html .= '<button type="submit" id="wps_usermeta_change_submit" class="wps_button '.$class.'">'.$label.'</button>';
            $html .= '</form>';
        

		endif;

		if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_usermeta_change', $before, $after, $styles, $values);

    } else {

        if (!is_user_logged_in() && $logged_out_msg):
            $query = wps_query_mark(get_bloginfo('url').$login_url);
            if ($login_url) $html .= sprintf('<a href="%s%s%sredirect=%s">', get_bloginfo('url'), $login_url, $query, wps_root( $_SERVER['REQUEST_URI'] ));
            $html .= $logged_out_msg;
            if ($login_url) $html .= '</a>';
        endif;
    
    }
    
	return $html;

}

function wps_usermeta_change_link($atts) {

	// Init
	add_action('wp_footer', 'wps_usermeta_init');

	global $current_user;
	$html = '';

	if (is_user_logged_in()) {

		// Shortcode parameters
        $values = wps_get_shortcode_options('wps_usermeta_change_link');    
		extract( shortcode_atts( array(
			'text' => wps_get_shortcode_value($values, 'wps_usermeta_change_link-text', __('Edit Profile', WPS2_TEXT_DOMAIN)),
			'user_id' => 0,
			'styles' => true,
            'after' => '',
			'before' => '',
		), $atts, 'wps_usermeta_change_link' ) );

		if (!$user_id)
			$user_id = wps_get_user_id();

        if ($user_id):

    		if ($current_user->ID == $user_id || current_user_can('manage_options')):
    			$url = get_page_link(get_option('wpspro_edit_profile_page'));
    			$html .= '<a href="'.$url.wps_query_mark($url).'user_id='.$user_id.'">'.$text.'</a>';
    		endif;

        endif;
        
		if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_usermeta_change_link', $before, $after, $styles, $values);

	}

	return $html;

}

function wps_close_account($atts) {

	// Init
	add_action('wp_footer', 'wps_usermeta_init');

	global $current_user;
	$html = '';

	if (is_user_logged_in()) {
        
		// Shortcode parameters
        $values = wps_get_shortcode_options('wps_close_account');    
		extract( shortcode_atts( array(
			'class' => wps_get_shortcode_value($values, 'wps_close_account-class', ''),
			'label' => wps_get_shortcode_value($values, 'wps_close_account-label', __('Close account', WPS2_TEXT_DOMAIN)),
			'are_you_sure_text' => wps_get_shortcode_value($values, 'wps_close_account-are_you_sure_text', __('Are you sure? You cannot re-open a closed account.', WPS2_TEXT_DOMAIN)),
			'logout_text' => wps_get_shortcode_value($values, 'wps_close_account-logout_text', __('Your account has been closed.', WPS2_TEXT_DOMAIN)),
            'url' => wps_get_shortcode_value($values, 'wps_close_account-url', '/'), // set URL to go to after de-activation, probably a logout page, or '' for current page
			'styles' => true,
            'after' => '',
			'before' => '',

		), $atts, 'wps_usermeta' ) );
		
        $user_id = wps_get_user_id();
        if ($user_id == $current_user->ID || current_user_can('manage_options')):

            $html .= '<input type="button" data-sure="'.$are_you_sure_text.'" data-url="'.$url.'" data-logout="'.$logout_text.'" id="wps_close_account" data-user="'.$user_id.'" class="wps_button '.$class.'" value="'.$label.'" />';

            if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_close_account', $before, $after, $styles, $values);

        endif;
        
            
    }

    return $html;
}

function wps_join_site($atts) {
    
    $html = '';

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_join_site');    
    extract( shortcode_atts( array(
        'class' => wps_get_shortcode_value($values, 'wps_join_site-label', ''),
        'label' => wps_get_shortcode_value($values, 'wps_join_site-label', __('Join this site', WPS2_TEXT_DOMAIN)),
        'style' => wps_get_shortcode_value($values, 'wps_join_site-label', 'button'), // button|text
        'styles' => true,
        'after' => '',
        'before' => '',
    ), $atts, 'wps_join_site' ) );
    
    if (is_multisite()):
    
        if ($style == 'button'):
            $html .= '<input type="button" class="wps_button '.$class.'" id="wps_join_site" value="'.$label.'" />';
        else:
            $html .= '<a href="javascript:void(0);" id="wps_join_site">'.$label.'</a>';
        endif;

        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_join_site', $before, $after, $styles, $values);

    endif;
    
    return $html;
    
}


function wps_no_user_check($atts){

    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    global $current_user;
    $html = '';
    
    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_no_user_check');
    extract( shortcode_atts( array(
        'not_found_msg' => wps_get_shortcode_value($values, 'wps_no_user_check-not_found_msg', __('User does not exist!', WPS2_TEXT_DOMAIN)),
        'styles' => true,
        'after' => '',
        'before' => '',        
    ), $atts, 'wps_no_user_check' ) );
    
    if (get_query_var('user')):
        $username = get_query_var('user');
        $get_user = get_user_by('login', urldecode($username));
        $user_id = $get_user ? $get_user->ID : 0;
    else:
        $username = false;
        if (isset($_GET['user_id'])):
            $user_id = $_GET['user_id'];
        else:
            $user_id = $current_user ? $current_user->ID : 0;
        endif;
    endif;

    $user_id = wps_get_user_id();

    if (!$user_id):
        $html .= '<div id="wps_user_not_found">';
        $html .= $not_found_msg;
        if ($username) $html .= ' ('.$username.')';
        $html .= '</div>';
    endif;

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_no_user_check', $before, $after, $styles, $values);    

    return $html;

}

// Show content if users are friends
function wps_is_friend_content($atts, $content="") {

    if (strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false):

        // Init
        add_action('wp_footer', 'wps_usermeta_init');

        $html = '';
        global $current_user;

        // Shortcode parameters
        $values = wps_get_shortcode_options('wps_is_friend_content');
        extract( shortcode_atts( array(
            'not_friends_msg' => wps_get_shortcode_value($values, 'wps_is_friend_content-not_friends_msg', __('Sorry, you are not friends.', WPS2_TEXT_DOMAIN)),
            'include_friendship_action' => wps_get_shortcode_value($values, 'wps_is_friend_content-include_friendship_action', true),
            'friend_add_label' => wps_get_shortcode_value($values, 'wps_is_friend_content-friend_add_label', __('Make friends', WPS2_TEXT_DOMAIN)),
            'friend_cancel_request_label' => wps_get_shortcode_value($values, 'wps_is_friend_content-friend_cancel_request_label', __('Cancel Request', WPS2_TEXT_DOMAIN)),     
            'accept_request_label' => wps_get_shortcode_value($values, 'wps_is_friend_content-accept_request_label', __('Accept Friendship', WPS2_TEXT_DOMAIN)),
            'reject_request_label' => wps_get_shortcode_value($values, 'wps_is_friend_content-reject_request_label', __('Reject', WPS2_TEXT_DOMAIN)),
            'reject_request_label' => wps_get_shortcode_value($values, 'wps_is_friend_content-reject_request_label', __('Reject', WPS2_TEXT_DOMAIN)),
            'request_made_msg' => wps_get_shortcode_value($values, 'wps_is_friend_content-request_made_msg', __('You have received a friend request from this user.', WPS2_TEXT_DOMAIN)),
            'friendship_class' => wps_get_shortcode_value($values, 'wps_is_friend_content-friendship_class', ''),
            'styles' => true,
            'after' => '',
            'before' => '',        
        ), $atts, 'wps_is_friend_content' ) );

        $user_id = wps_get_user_id();    

        if ($user_id):

            $friends = wps_are_friends($current_user->ID, $user_id);

            if ($friends['status'] == 'publish'):

                // Shortcode parameters
                extract( shortcode_atts( array(
                    'before' => '',
                    'after' => '',
                ), $atts, 'wps_user_exists_content' ) );

                $html .= do_shortcode($content);

            else:

                $html .= '<div id="wps_is_friend_content">'.$not_friends_msg.'</div>';

                if ($include_friendship_action):
                    $friend_cancel_label = '';

                    $item_html = '<div class="wps_is_friend_content_item_friends_status">';
                        if ($friends['status']):
                            if ($friends['status'] == 'pending' && $friends['direction'] == 'from'):
                                if ($user_id != $current_user->ID):
                                    // Request made to this user
                                    $item_html .= '<div id="wps_friendship_request_made">'.$request_made_msg.'</div>';
                                    $item_html .= '<button type="submit" rel="'.$friends['ID'].'" class="wps_button wps_pending_friends_accept '.$friendship_class.'">'.$accept_request_label.'</button>';
                                    $item_html .= '<button type="submit" rel="'.$friends['ID'].'" class="wps_button wps_pending_friends_reject '.$friendship_class.'">'.$reject_request_label.'</button>';
                                else:
                                    $item_html .= wps_friends_add_button(array('user_id' => $user_id, 'label' => $friend_add_label, 'cancel_label' => $friend_cancel_label, 'cancel_request_label' => $friend_cancel_request_label));
                                endif;
                            else:
                                // Request made from this user
                                $item_html .= wps_friends_add_button(array('user_id' => $user_id, 'label' => $friend_add_label, 'cancel_label' => $friend_cancel_label, 'cancel_request_label' => $friend_cancel_request_label));
                            endif;
                        else:
                            // Not friends
                            $item_html .= wps_friends_add_button(array('user_id' => $user_id, 'label' => $friend_add_label, 'cancel_label' => $friend_cancel_label, 'cancel_request_label' => $friend_cancel_request_label));
                        endif;
                    $item_html .= '</div>';
                    $html .= $item_html;
                endif;


            endif;

        endif;
    
    endif;

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_is_friend_content', $before, $after, $styles, $values);        

    return $html;    

}

// Show content if user exists
function wps_user_exists_content($atts, $content="") {

    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    $html = '';
    global $current_user;

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_user_exists_content');
    extract( shortcode_atts( array(
        'not_found_msg' => wps_get_shortcode_value($values, 'wps_user_exists_content-not_found_msg', __('User does not exist!', WPS2_TEXT_DOMAIN)),
        'styles' => true,
        'after' => '',
        'before' => '',        
    ), $atts, 'wps_user_exists_content' ) );

    $user_id = wps_get_user_id();    

    if ( $user_id ):
    
        $html .= do_shortcode($content);

    else:

        $html .= '<div id="wps_user_exists_content">'.$not_found_msg.'</div>';

    endif;

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_user_exists_content', $before, $after, $styles, $values);        

    return $html;    

}

// Show content if no user logged in
function wps_not_logged_in($atts, $content="") {

    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_not_logged_in');
    extract( shortcode_atts( array(
        'styles' => true,
        'after' => '',
        'before' => '',        
    ), $atts, 'wps_not_logged_in' ) );    

    $html = '';
    global $current_user;

    if ( !is_user_logged_in() )    
        $html .= do_shortcode($content);

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_not_logged_in', $before, $after, $styles, $values);        

    return $html;    

}

// Show content if user is logged in
function wps_is_logged_in($atts, $content="") {

    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    // Shortcode parameters
    $values = wps_get_shortcode_options('wps_is_logged_in');
    extract( shortcode_atts( array(
        'styles' => true,
        'after' => '',
        'before' => '',        
    ), $atts, 'wps_is_logged_in' ) );        

    $html = '';
    global $current_user;

    if ( is_user_logged_in() )    
        $html .= do_shortcode($content);

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_is_logged_in', $before, $after, $styles, $values);        

    return $html;    

}

// Backup for [wps-activity-page] if activity is not enabled
function wps_backup_activity_page($atts){

	// Init
	add_action('wp_footer', 'wps_usermeta_init');

    global $current_user;
	$html = '';
    
	// Shortcode parameters
    $values = wps_get_shortcode_options('wps_activity_page');
	extract( shortcode_atts( array(
		'user_id' => false,
        'mimic_user_id' => false,
		'user_avatar_size' => wps_get_shortcode_value($values, 'wps_activity_page-user_avatar_size', 150),
		'map_style' => wps_get_shortcode_value($values, 'wps_activity_page-map_style', 'static'),
		'map_size' => wps_get_shortcode_value($values, 'wps_activity_page-map_size', '150,150'),
		'map_zoom' => wps_get_shortcode_value($values, 'wps_activity_page-map_zoom', 4),
		'town_label' => wps_get_shortcode_value($values, 'wps_activity_page-town_label', __('Town/City', WPS2_TEXT_DOMAIN)),
        'country_label' => wps_get_shortcode_value($values, 'wps_activity_page-country_label', __('Country', WPS2_TEXT_DOMAIN)),
        'requests_label' => wps_get_shortcode_value($values, 'wps_activity_page-requests_label', __('Friend Requests', WPS2_TEXT_DOMAIN)),
        'styles' => true,
	), $atts, 'wps_activity_page' ) );
    
	if (!$user_id):
        $user_id = wps_get_user_id();
        $this_user = $current_user->ID;
    else:
        if ($mimic_user_id):
            $this_user = $user_id;
        else:
            $this_user = $current_user->ID;
        endif;
    endif;

	$html .= '<style>.wps_avatar img { border-radius:0px; }</style>';
	$html .= wps_display_name(array('user_id'=>$user_id, 'before'=>'<div id="wps_display_name" style="font-size:2.5em; line-height:2.5em; margin-bottom:20px;">', 'after'=>'</div>'));
	$html .= '<div style="overflow:auto;overflow-y:hidden;margin-bottom:15px">';
    $html .= '<div id="wps_activity_page_avatar" style="float: left; margin-right: 20px;">';
    if (strpos(WPS_CORE_PLUGINS, 'core-avatar') !== false):
        $html .= wps_avatar(array('user_id'=>$user_id, 'change_link'=>1, 'size'=>$user_avatar_size, 'before'=>'<div id="wps_display_avatar" style="float:left; margin-right:15px;">', 'after'=>'</div>'));
    else:
        $html .= '<div id="wps_display_avatar" style="float:left; margin-right:15px;">';
            $html .= get_avatar($user_id, $user_avatar_size);
        $html .= '</div>';
    endif;
    if (strpos(WPS_CORE_PLUGINS, 'core-profile') !== false):
        $html .= wps_usermeta(array('user_id'=>$user_id, 'meta'=>'wpspro_map', 'map_style'=>$map_style, 'size'=>$map_size, 'zoom'=>$map_zoom, 'before'=>'<div id="wps_display_map" style="float:left;margin-right:15px;">', 'after'=>'</div>'));
        $html .= '<div style="float:left;margin-right:15px;">';
        $html .= wps_usermeta(array('user_id'=>$user_id, 'meta'=>'wpspro_home', 'before'=>'<strong>'.$town_label.'</strong><br />', 'after'=>'<br />'));
        $html .= wps_usermeta(array('user_id'=>$user_id, 'meta'=>'wpspro_country', 'before'=>'<strong>'.$country_label.'</strong><br />', 'after'=>'<br />'));
        $html .= wps_usermeta_change_link($atts);
    endif;
	$html .= '</div>';
    if (strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false):
        $html .= '<div id="wps_display_friend_requests" style="margin-left:10px;float:left;min-width:200px;">';
        $html .= wps_friends_pending(array('user_id'=>$user_id, 'count' => 10, 'before'=>'<div class="wps_20px_gap"><div style="font-weight:bold;margin-bottom: 10px">'.$requests_label.'</div>', 'after'=>'</div>'));
        $html .= wps_friends_add_button(array());
        $html .= '</div>';
    endif;
	$html .= '</div>';

    if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_activity_page', '', '', $styles, $values);    
    
	return $html;

}

// Displays when last active
function wps_last_active($atts) {
    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    $html = '';
    if (is_user_logged_in()) {
    
        // Shortcode parameters
        $values = wps_get_shortcode_options('wps_last_active');
        extract( shortcode_atts( array(
            'user_id' => wps_get_shortcode_value($values, 'wps_last_active-user_id', ''),
            'show_as_date' => wps_get_shortcode_value($values, 'wps_last_active-show_as_date', false),                    
            'date_format' => wps_get_shortcode_value($values, 'wps_last_active-date_format', __('%s ago', WPS2_TEXT_DOMAIN)),
            'not_active_msg' => wps_get_shortcode_value($values, 'wps_last_active-not_logged_in_msg', __('Not active recently.', WPS2_TEXT_DOMAIN)),                    
            'after' => '',
            'before' => '',            
            'styles' => true,
        ), $atts, 'wps_last_active' ) );
        
        if ($user_id == 'user'):
            global $current_user;
            $user_id = $current_user->ID;
        else:
            if (!$user_id) $user_id = wps_get_user_id();
        endif;    

        $last_active = get_user_meta($user_id, 'wpspro_last_active', true);
        $html .= '<span class="wps_last_active">';
        if ($last_active):
            $last_active_date = new DateTime();
            $last_active_date->setTimestamp(strtotime($last_active));
            if ($show_as_date):
                $html .= date_format($last_active_date, $date_format);
            else:
                $from = strtotime($last_active_date->format('Y-m-d H:i:s'));
                $to = current_time('timestamp', 1);
                $html .= sprintf($date_format, human_time_diff($from, $to));
            endif;
        else:
            $html .= $not_active_msg;
        endif;
        $html .= '</span>';

        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_last_active', $before, $after, $styles, $values);
        
    }
    
    return $html;
}

// Displays when last logged in
function wps_last_logged_in($atts) {
    // Init
    add_action('wp_footer', 'wps_usermeta_init');

    $html = '';
    
    if (is_user_logged_in()) {
    
        // Shortcode parameters
        $values = wps_get_shortcode_options('wps_last_logged_in');
        extract( shortcode_atts( array(
            'user_id' => wps_get_shortcode_value($values, 'wps_last_logged_in-user_id', ''),
            'show_as_date' => wps_get_shortcode_value($values, 'wps_last_logged_in-show_as_date', false),                    
            'date_format' => wps_get_shortcode_value($values, 'wps_last_logged_in-date_format', __('%s ago', WPS2_TEXT_DOMAIN)),
            'previous' => wps_get_shortcode_value($values, 'wps_last_logged_in-previous', false),                    
            'not_logged_in_msg' => wps_get_shortcode_value($values, 'wps_last_logged_in-not_logged_in_msg', __('Not logged in recently.', WPS2_TEXT_DOMAIN)),
            'after' => '',
            'before' => '',            
            'styles' => true,
        ), $atts, 'wps_last_logged_in' ) );

        if ($user_id == 'user'):
            global $current_user;
            $user_id = $current_user->ID;
        else:
            if (!$user_id) $user_id = wps_get_user_id();
        endif;    

        if (!$user_id) $user_id = wps_get_user_id();

        $last_logged_in = !$previous ? get_user_meta($user_id, 'wpspro_last_login', true) : get_user_meta($user_id, 'wpspro_previous_login', true);
        $html .= '<span class="wps_last_logged_in">';
        if ($last_logged_in):
            $last_logged_in_date = new DateTime();
            $last_logged_in_date->setTimestamp(strtotime($last_logged_in));
            if ($show_as_date):
                $html .= date_format($last_logged_in_date, $date_format);
            else:
                $from = strtotime($last_logged_in_date->format('Y-m-d H:i:s'));
                $to = current_time('timestamp', 1);
                $html .= sprintf($date_format, human_time_diff($from, $to));
            endif;
        else:
            $html .= $not_logged_in_msg;
        endif;
        $html .= '</span>';

        if ($html) $html = apply_filters ('wps_wrap_shortcode_styles_filter', $html, 'wps_last_logged_in', $before, $after, $styles, $values);
        
    }
    
    return $html;

}

add_shortcode(WPS_PREFIX.'-user-id', 'wps_user_id');
add_shortcode(WPS_PREFIX.'-usermeta', 'wps_usermeta');
add_shortcode(WPS_PREFIX.'-no-user-check', 'wps_no_user_check');
add_shortcode(WPS_PREFIX.'-is-friend-content', 'wps_is_friend_content');
add_shortcode(WPS_PREFIX.'-user-exists-content', 'wps_user_exists_content');
add_shortcode(WPS_PREFIX.'-is-logged-in', 'wps_is_logged_in');
add_shortcode(WPS_PREFIX.'-not-logged-in', 'wps_not_logged_in');
add_shortcode(WPS_PREFIX.'-usermeta-change', 'wps_usermeta_change');
add_shortcode(WPS_PREFIX.'-usermeta-change-link', 'wps_usermeta_change_link');
add_shortcode(WPS_PREFIX.'-usermeta-button', 'wps_usermeta_button');
add_shortcode(WPS_PREFIX.'-close-account', 'wps_close_account');
add_shortcode(WPS_PREFIX.'-join-site', 'wps_join_site');
add_shortcode(WPS_PREFIX.'-last-active', 'wps_last_active');
add_shortcode(WPS_PREFIX.'-last-logged-in', 'wps_last_logged_in');

// Backup for [wps-activity-page]
if (strpos(WPS_CORE_PLUGINS, 'core-activity') === false)
    add_shortcode(WPS_PREFIX.'-activity-page', 'wps_backup_activity_page');

?>
