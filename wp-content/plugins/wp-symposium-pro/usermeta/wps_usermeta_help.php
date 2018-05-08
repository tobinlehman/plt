<?php
// Quick Start
add_action('wps_admin_quick_start_hook', 'wps_admin_quick_start_profile');
function wps_admin_quick_start_profile() {

	global $wpdb;
	$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_content LIKE '%s'";
	if (!($wpdb->get_results($wpdb->prepare($sql, '%[wps_activity%')))):

		echo '<div style="margin-right:10px; float:left">';
		echo '<form action="" method="POST">';
		echo '<input type="hidden" name="wpspro_quick_start" value="profile" />';
		echo '<input type="submit" class="button-secondary" value="'.__('Add Profile Pages', WPS2_TEXT_DOMAIN).'" />';
		echo '</form></div>';

	endif;
}

add_action('wps_admin_quick_start_form_save_hook', 'wps_admin_quick_start_profile_save', 10, 1);
function wps_admin_quick_start_profile_save($the_post) {

	if (isset($the_post['wpspro_quick_start']) && $the_post['wpspro_quick_start'] == 'profile'):

		// Profile Page
		$post_content = '['.WPS_PREFIX.'-user-exists-content]['.WPS_PREFIX.'-is-friend-content]['.WPS_PREFIX.'-activity-page][/'.WPS_PREFIX.'-is-friend-content][/'.WPS_PREFIX.'-user-exists-content]';

		$post = array(
		  'post_content'   => $post_content,
		  'post_name'      => 'profile',
		  'post_title'     => __('Profile', WPS2_TEXT_DOMAIN),
		  'post_status'    => 'publish',
		  'post_type'      => 'page',
		  'ping_status'    => 'closed',
		  'comment_status' => 'closed',
		);  

		$new_id = wp_insert_post( $post );
		update_option('wpspro_profile_page', $new_id);

		// Edit Profile Page
		$post = array(
		  'post_content'   => '['.WPS_PREFIX.'-usermeta-change]',
		  'post_name'      => 'edit-profile',
		  'post_title'     => __('Edit Profile', WPS2_TEXT_DOMAIN),
		  'post_status'    => 'publish',
		  'post_type'      => 'page',
		  'ping_status'    => 'closed',
		  'comment_status' => 'closed',
		);  

		$new_edit_profile_id = wp_insert_post( $post );
		update_option('wpspro_edit_profile_page', $new_edit_profile_id);

		// Change Avatar Page
		$post = array(
		  'post_content'   => '['.WPS_PREFIX.'-avatar-change]',
		  'post_name'      => 'change-avatar',
		  'post_title'     => __('Change Avatar', WPS2_TEXT_DOMAIN),
		  'post_status'    => 'publish',
		  'post_type'      => 'page',
		  'ping_status'    => 'closed',
		  'comment_status' => 'closed',
		);  

		$new_change_avatar_id = wp_insert_post( $post );
		update_option('wpspro_change_avatar_page', $new_change_avatar_id);		

		// Friends Page
		$post = array(
		  'post_content'   => '['.WPS_PREFIX.'-friends-pending]['.WPS_PREFIX.'-friends count="100"]',
		  'post_name'      => 'friends',
		  'post_title'     => __('Friends', WPS2_TEXT_DOMAIN),
		  'post_status'    => 'publish',
		  'post_type'      => 'page',
		  'ping_status'    => 'closed',
		  'comment_status' => 'closed',
		);  

		$new_friends_id = wp_insert_post( $post );

		echo '<div class="wps_success">'.sprintf(__('Profile Page (%s) added. [<a href="%s">view</a>]', WPS2_TEXT_DOMAIN), get_permalink($new_id), get_permalink($new_id)).'<br />';
		echo sprintf(__('Edit Profile Page (%s) added. [<a href="%s">view</a>]', WPS2_TEXT_DOMAIN), get_permalink($new_edit_profile_id), get_permalink($new_edit_profile_id)).'<br />';
		echo sprintf(__('Change Avatar Page (%s) added. [<a href="%s">view</a>]', WPS2_TEXT_DOMAIN), get_permalink($new_change_avatar_id), get_permalink($new_change_avatar_id)).'<br />';
		echo sprintf(__('Friends Page (%s) added. [<a href="%s">view</a>]', WPS2_TEXT_DOMAIN), get_permalink($new_friends_id), get_permalink($new_friends_id)).'<br /><br />';

		echo sprintf(__('You might want to add them to your <a href="%s">WordPress menu</a>.', WPS2_TEXT_DOMAIN), "nav-menus.php").'</div>';

	endif;

}

// Settings
add_action('wps_admin_getting_started_hook', 'wps_admin_getting_started_profile', 4);
function wps_admin_getting_started_profile() {

	// Show menu item	
    $css = isset($_POST['wps_expand']) && $_POST['wps_expand'] == 'wps_admin_getting_started_profile' ? 'wps_admin_getting_started_menu_item_remove_icon ' : '';    
  	echo '<div class="'.$css.'wps_admin_getting_started_menu_item" id="wps_admin_getting_started_profile_div" rel="wps_admin_getting_started_profile">'.__('Profile Page', WPS2_TEXT_DOMAIN).'</div>';

  	// Show setup/help content
  	$display = isset($_POST['wps_expand']) && $_POST['wps_expand'] == 'wps_admin_getting_started_profile' ? 'block' : 'none';
  	echo '<div class="wps_admin_getting_started_content" id="wps_admin_getting_started_profile" style="display:'.$display.'">';
	?>

		<table class="form-table">
			<tr valign="top"> 
			<td scope="row"><label for="profile_page"><?php echo __('Profile Page', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<p style="margin-bottom:5px"><strong><?php echo __('Your profile page must not have a parent page.', WPS2_TEXT_DOMAIN); ?></strong></p>
				<select name="profile_page">
				 <?php 
				  $profile_page = get_option('wpspro_profile_page');
				  if (!$profile_page) echo '<option value="0">'.__('Select page...', WPS2_TEXT_DOMAIN).'</option>';
				  if ($profile_page) echo '<option value="0">'.__('Reset...', WPS2_TEXT_DOMAIN).'</option>';						
				  $pages = get_pages(); 
				  foreach ( $pages as $page ) {
				  	$option = '<option value="' . $page->ID . '"';
				  		if ($page->ID == $profile_page) $option .= ' SELECTED';
				  		$option .= '>';
					$option .= $page->post_title;
					$option .= '</option>';
					echo $option;
				  }
				 ?>						
				</select>
				<span class="description"><?php echo __('WordPress page that profile links go to.', WPS2_TEXT_DOMAIN); ?>
				<?php if ($profile_page) {
					echo ' [<a href="post.php?post='.$profile_page.'&action=edit">'.__('edit', WPS2_TEXT_DOMAIN).'</a>';
					echo '|<a href="'.get_permalink($profile_page).'">'.__('view', WPS2_TEXT_DOMAIN).'</a>]';
				}
				?>
				</span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="edit_profile_page"><?php echo __('Edit Profile Page', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<select name="edit_profile_page">
				 <?php 
				  $profile_page = get_option('wpspro_edit_profile_page');
				  if (!$profile_page) echo '<option value="0">'.__('Select page...', WPS2_TEXT_DOMAIN).'</option>';
				  if ($profile_page) echo '<option value="0">'.__('Reset...', WPS2_TEXT_DOMAIN).'</option>';						
				  $pages = get_pages(); 
				  foreach ( $pages as $page ) {
				  	$option = '<option value="' . $page->ID . '"';
				  		if ($page->ID == $profile_page) $option .= ' SELECTED';
				  		$option .= '>';
					$option .= $page->post_title;
					$option .= '</option>';
					echo $option;
				  }
				 ?>						
				</select>
				<span class="description"><?php echo __('WordPress page that allows user to edit their profile.', WPS2_TEXT_DOMAIN); ?>
				<?php if ($profile_page) {
					echo ' [<a href="post.php?post='.$profile_page.'&action=edit">'.__('edit', WPS2_TEXT_DOMAIN).'</a>';
					echo '|<a href="'.get_permalink($profile_page).'">'.__('view', WPS2_TEXT_DOMAIN).'</a>]';
				 } ?>
				</span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="change_avatar_page"><?php echo __('Change Avatar Page', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<select name="change_avatar_page">
				 <?php 
				  $profile_page = get_option('wpspro_change_avatar_page');
				  if (!$profile_page) echo '<option value="0">'.__('Select page...', WPS2_TEXT_DOMAIN).'</option>';
				  if ($profile_page) echo '<option value="0">'.__('Reset...', WPS2_TEXT_DOMAIN).'</option>';						
				  $pages = get_pages(); 
				  foreach ( $pages as $page ) {
				  	$option = '<option value="' . $page->ID . '"';
				  		if ($page->ID == $profile_page) $option .= ' SELECTED';
				  		$option .= '>';
					$option .= $page->post_title;
					$option .= '</option>';
					echo $option;
				  }
				 ?>						
				</select>
				<span class="description"><?php echo __('WordPress page that allows user to change their avatar.', WPS2_TEXT_DOMAIN); ?>
				<?php if ($profile_page) {
					echo ' [<a href="post.php?post='.$profile_page.'&action=edit">'.__('edit', WPS2_TEXT_DOMAIN).'</a>';
					echo '|<a href="'.get_permalink($profile_page).'">'.__('view', WPS2_TEXT_DOMAIN).'</a>]';
				} ?>
				</span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="profile_permalinks"><?php echo __('Profile Parameter', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="wpspro_profile_permalinks" id="wpspro_profile_permalinks" type="checkbox" <?php if ( get_option('wpspro_profile_permalinks') ) echo 'CHECKED'; ?> style="width:10px" />
   				<span class="description"><?php _e('Do not use usernames for profile page links', WPS2_TEXT_DOMAIN); ?></span>
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="all_friends_alerts"><?php echo __('Posts to all friends', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="wpspro_all_friends_alerts" id="wpspro_all_friends_alerts" type="checkbox" <?php if ( get_option('wpspro_all_friends_alerts') ) echo 'CHECKED'; ?> style="width:10px" />
   				<span class="description"><?php _e('When an activity post will be sent to all friends, should an alert be generated?', WPS2_TEXT_DOMAIN); ?></span>
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="activity_set_focus"><?php echo __('Activity post focus', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="wpspro_activity_set_focus" id="wpspro_activity_set_focus" type="checkbox" <?php if ( get_option('wpspro_activity_set_focus') ) echo 'CHECKED'; ?> style="width:10px" />
   				<span class="description"><?php _e('When profile page is loaded, set focus to activity post textarea.', WPS2_TEXT_DOMAIN); ?></span>
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="activity_sticky_admin_only"><?php echo __('Sticky posts', WPS2_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="activity_sticky_admin_only" id="activity_sticky_admin_only" type="checkbox" <?php if ( get_option('activity_sticky_admin_only') ) echo 'CHECKED'; ?> style="width:10px" />
   				<span class="description"><?php _e('Limit sticky post option to site administrator only.', WPS2_TEXT_DOMAIN); ?></span>
			</tr> 

		</table>

		<?php

  		echo '<h2>'.__('Getting Started', WPS2_TEXT_DOMAIN).'</h2>';

  		echo '<p><em>'.__('Either click on "<a href="#">Add Profile Pages</a>" at the top of this page, or...', WPS2_TEXT_DOMAIN).'</em></p>';

  		echo '<div style="border:1px dashed #333; background-color:#efefef; margin-bottom:10px; padding-left:15px">';

		  	echo '<h3>'.__('Profile Page', WPS2_TEXT_DOMAIN).'</h3>';

		  	if (!$profile_page = get_option('wpspro_profile_page')):
			  	echo '<p>'.sprintf(__('<a href="%s">Create a WordPress page</a>, then select it above, and save. When you have done that, some example shortcodes will be shown here that you can copy into that page.', WPS2_TEXT_DOMAIN), 'post-new.php?post_type=page').'</p>';
		  	else:
		  		echo '<p>'.__('Copy the following shortcode', WPS2_TEXT_DOMAIN).', <a href="post.php?post='.$profile_page.'&action=edit">'.__('edit your "Profile" page', WPS2_TEXT_DOMAIN).'</a> '.__('and paste the shortcodes to get started.', WPS2_TEXT_DOMAIN).'</p>';
		  		echo '<p>';
			  	echo '<strong>['.WPS_PREFIX.'-activity-page]</strong> <span class="description">'.__("Creates a profile page with key elements", WPS2_TEXT_DOMAIN).'</span><br />';
			  	echo '<span class="description"><a href="http://www.wpsymposiumpro.com/shortcodes" target="_blank">'.__('more examples...', WPS2_TEXT_DOMAIN).'</a></span>';
			  	echo '</p>';
		  	endif;

		  	echo '<h3>'.__('Edit Profile Page', WPS2_TEXT_DOMAIN).'</h3>';

		  	if (!$profile_page = get_option('wpspro_edit_profile_page')):
			  	echo '<p>'.sprintf(__('<a href="%s">Create a WordPress page</a>, then select it above, and save. When you have done that, some example shortcodes will be shown here that you can copy into that page.', WPS2_TEXT_DOMAIN), 'post-new.php?post_type=page').'</p>';
		  	else:
		  		echo '<p>'.__('Copy the following shortcodes', WPS2_TEXT_DOMAIN).', <a href="post.php?post='.$profile_page.'&action=edit">'.__('edit your "Edit Profile" page', WPS2_TEXT_DOMAIN).'</a> '.__('and paste the shortcodes to get started.', WPS2_TEXT_DOMAIN).'</p>';
		  		echo '<p>';
			  	echo '<strong>['.WPS_PREFIX.'-usermeta-change]</strong> <span class="description">'.__("Let's the user change their profile details", WPS2_TEXT_DOMAIN).'</span><br />';
			  	echo '<span class="description"><a href="http://www.wpsymposiumpro.com/shortcodes" target="_blank">'.__('more examples...', WPS2_TEXT_DOMAIN).'</a></span>';
			  	echo '</p>';
		  	endif;

		  	echo '<h3>'.__('Change Avatar Page', WPS2_TEXT_DOMAIN).'</h3>';

		  	if (!$profile_page = get_option('wpspro_change_avatar_page')):
			  	echo '<p>'.sprintf(__('<a href="%s">Create a WordPress page</a>, then select it above, and save. When you have done that, some example shortcodes will be shown here that you can copy into that page.', WPS2_TEXT_DOMAIN), 'post-new.php?post_type=page').'</p>';
		  	else:
		  		echo '<p>'.__('Copy the following shortcodes', WPS2_TEXT_DOMAIN).', <a href="post.php?post='.$profile_page.'&action=edit">'.__('edit your "Change Avatar" page', WPS2_TEXT_DOMAIN).'</a> '.__('and paste the shortcodes to get started.', WPS2_TEXT_DOMAIN).'</p>';
		  		echo '<p>';
			  	echo '<strong>['.WPS_PREFIX.'-avatar-change]</strong> <span class="description">'.__("Let's the user upload and crop their avatar", WPS2_TEXT_DOMAIN).'</span><br />';
			  	echo '<span class="description"><a href="http://www.wpsymposiumpro.com/shortcodes" target="_blank">'.__('more examples...', WPS2_TEXT_DOMAIN).'</a></span>';
			  	echo '</p>';
		  	endif;

		  	echo '<h3>'.__('Adding the Pages to your Site', WPS2_TEXT_DOMAIN).'</h3>';
		  	echo '<p>'.sprintf(__('Once you have created your pages, you may want to add them to your <a href="%s">site menu</a>.', WPS2_TEXT_DOMAIN), 'nav-menus.php').'</p>';

		echo '</div>';

	echo '</div>';

}

add_action( 'wps_admin_setup_form_save_hook', 'wps_profile_admin_options_save', 10, 1 );
function wps_profile_admin_options_save ($the_post) {

	if (isset($the_post['profile_page']) && $the_post['profile_page'] > 0):
		update_option('wpspro_profile_page', $the_post['profile_page']);
	else:
		delete_option('wpspro_profile_page');
	endif;

	if (isset($the_post['change_avatar_page']) && $the_post['change_avatar_page'] > 0):
		update_option('wpspro_change_avatar_page', $the_post['change_avatar_page']);
	else:
		delete_option('wpspro_change_avatar_page');
	endif;		

	if (isset($the_post['edit_profile_page']) && $the_post['edit_profile_page'] > 0):
		update_option('wpspro_edit_profile_page', $the_post['edit_profile_page']);
	else:
		delete_option('wpspro_edit_profile_page');
	endif;		

	if (isset($the_post['wpspro_profile_permalinks'])):
		update_option('wpspro_profile_permalinks', true);
	else:
		delete_option('wpspro_profile_permalinks');
	endif;

	if (isset($the_post['wpspro_all_friends_alerts'])):
		update_option('wpspro_all_friends_alerts', true);
	else:
		delete_option('wpspro_all_friends_alerts');
	endif;

	if (isset($the_post['wpspro_activity_set_focus'])):
		update_option('wpspro_activity_set_focus', true);
	else:
		delete_option('wpspro_activity_set_focus');
	endif;
    
	if (isset($the_post['activity_sticky_admin_only'])):
		update_option('activity_sticky_admin_only', true);
	else:
		delete_option('activity_sticky_admin_only');
	endif;
    
}


// Settings
add_action('wps_admin_getting_started_hook', 'wps_admin_getting_started_edit_profile', 4);
function wps_admin_getting_started_edit_profile() {

	// Show menu item	
    $css = isset($_POST['wps_expand']) && $_POST['wps_expand'] == 'wps_admin_getting_started_edit_profile' ? 'wps_admin_getting_started_menu_item_remove_icon ' : '';    
  	echo '<div class="'.$css.'wps_admin_getting_started_menu_item" id="wps_admin_getting_started_edit_profile_div" rel="wps_admin_getting_started_edit_profile">'.__('Edit Profile Page', WPS2_TEXT_DOMAIN).'</div>';

  	// Show setup/help content
  	$display = isset($_POST['wps_expand']) && $_POST['wps_expand'] == 'wps_admin_getting_started_edit_profile' ? 'block' : 'none';
  	echo '<div class="wps_admin_getting_started_content" id="wps_admin_getting_started_edit_profile" style="display:'.$display.'">';

        echo '<h3>'.__('Options available to user', WPS2_TEXT_DOMAIN).'</h3>';
    
        $wps_password_strength_meter = get_option('wps_password_strength_meter');
        $wps_strength_array = get_option('wps_strength_array');
        if (!$wps_strength_array) $wps_strength_array = array('Weak','Poor','Good','Strong','Mismatch');    
        $wps_strength = stripslashes(implode(',',$wps_strength_array));
        if (!$wps_strength) $wps_strength = 'Weak,Poor,Good,Strong,Mismatch';
        $wps_change_avatar_effects = get_option('wps_change_avatar_effects');
        if (!$wps_change_avatar_effects) $wps_change_avatar_effects = 'flip,rotate,invert,sketch,pixelate,sepia,emboss';
        $hide_email_notifications_for_activity = get_option('hide_email_notifications_for_activity');
        echo '<table>';
        echo '<tr><td style="width:300px">'.__('Password strength meter', WPS2_TEXT_DOMAIN).'</td><td><input type="checkbox" name="wps_password_strength_meter"'.($wps_password_strength_meter ? ' CHECKED' : '').' /> '.__('Check to hide on Edit Profile page', WPS2_TEXT_DOMAIN).'</td></tr>';
        echo '<tr><td style="width:300px">'.__('Password strength options', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_strength_array" style="width:300px" value="'.$wps_strength.'" /> '.__('comma seperated, all 5 required', WPS2_TEXT_DOMAIN),'</td></tr>';
        echo '<tr><td style="width:300px">'.__('Change avatar effects', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_change_avatar_effects" style="width:300px" value="'.$wps_change_avatar_effects.'" /> '.__('comma seperated (delete to reset)', WPS2_TEXT_DOMAIN),'</td></tr>';
        echo '<tr><td style="width:300px"></td><td style="font-style:italic">'.sprintf(__('Enable, and change the labels via <a href="%s">Shortcodes</a>->Avatar->[wps-avatar-change]', WPS2_TEXT_DOMAIN), admin_url( 'admin.php?page=wps_pro_shortcodes' )).'</td></tr>';
        echo '<tr><td style="width:300px">'.__('Receive email notifications for activity', WPS2_TEXT_DOMAIN).'</td><td><input type="checkbox" name="hide_email_notifications_for_activity"'.($hide_email_notifications_for_activity ? ' CHECKED' : '').' /> '.__('Check to hide on Edit Profile page', WPS2_TEXT_DOMAIN).'</td></tr>';
        echo '</table>';
    
        echo '<h3>'.__('Tabs', WPS2_TEXT_DOMAIN).'</h3>';

        echo '<p>'.__('You can have up to 10 tabs on the edit profile page. Set them up by entering a description (blank tabs are not displayed).<br />', WPS2_TEXT_DOMAIN);
        echo sprintf(__('Then below, after saving, you can choose which tab various items appear on (including <a href="%s" target="_blank">profile extensions</a> if you are using that feature).', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/browse-plugins/').'</p>';

        $tabs_array = get_option('wps_profile_tabs');
    
        $wps_profile_tab1 = (isset($tabs_array['wps_profile_tab1'])) ? $tabs_array['wps_profile_tab1'] : '';
        $wps_profile_tab2 = (isset($tabs_array['wps_profile_tab2'])) ? $tabs_array['wps_profile_tab2'] : '';
        $wps_profile_tab3 = (isset($tabs_array['wps_profile_tab3'])) ? $tabs_array['wps_profile_tab3'] : '';
        $wps_profile_tab4 = (isset($tabs_array['wps_profile_tab4'])) ? $tabs_array['wps_profile_tab4'] : '';
        $wps_profile_tab5 = (isset($tabs_array['wps_profile_tab5'])) ? $tabs_array['wps_profile_tab5'] : '';
        $wps_profile_tab6 = (isset($tabs_array['wps_profile_tab6'])) ? $tabs_array['wps_profile_tab6'] : '';
        $wps_profile_tab7 = (isset($tabs_array['wps_profile_tab7'])) ? $tabs_array['wps_profile_tab7'] : '';
        $wps_profile_tab8 = (isset($tabs_array['wps_profile_tab8'])) ? $tabs_array['wps_profile_tab8'] : '';
        $wps_profile_tab9 = (isset($tabs_array['wps_profile_tab9'])) ? $tabs_array['wps_profile_tab9'] : '';
        $wps_profile_tab10 = (isset($tabs_array['wps_profile_tab10'])) ? $tabs_array['wps_profile_tab10'] : '';
    
        $wps_profile_tab_active_color = (isset($tabs_array['wps_profile_tab_active_color'])) ? $tabs_array['wps_profile_tab_active_color'] : '#fff';
        $wps_profile_tab_inactive_color = (isset($tabs_array['wps_profile_tab_inactive_color'])) ? $tabs_array['wps_profile_tab_inactive_color'] : '#d2d2d2';
        $wps_profile_tab_active_text_color = (isset($tabs_array['wps_profile_tab_active_text_color'])) ? $tabs_array['wps_profile_tab_active_text_color'] : '#000';
        $wps_profile_tab_inactive_text_color = (isset($tabs_array['wps_profile_tab_inactive_text_color'])) ? $tabs_array['wps_profile_tab_inactive_text_color'] : '#000';
        $wps_profile_tab_animation = (isset($tabs_array['wps_profile_tab_animation'])) ? $tabs_array['wps_profile_tab_animation'] : 'slide';

        echo '<table style="margin-top:20px">';
        echo '<tr><td style="width:300px">'.__('Tab 1:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab1" style="width:300px" value="'.$wps_profile_tab1.'" /><br />';
        echo '<tr><td>'.__('Tab 2:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab2" style="width:300px" value="'.$wps_profile_tab2.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 3:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab3" style="width:300px" value="'.$wps_profile_tab3.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 4:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab4" style="width:300px" value="'.$wps_profile_tab4.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 5:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab5" style="width:300px" value="'.$wps_profile_tab5.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 6:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab6" style="width:300px" value="'.$wps_profile_tab6.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 7:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab7" style="width:300px" value="'.$wps_profile_tab7.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 8:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab8" style="width:300px" value="'.$wps_profile_tab8.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 9:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab9" style="width:300px" value="'.$wps_profile_tab9.'" /></td></tr>';
        echo '<tr><td>'.__('Tab 10:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab10" style="width:300px" value="'.$wps_profile_tab10.'" /></td></tr>';
        echo '<tr><td>'.__('Active Tab Color:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab_active_color" value="'.$wps_profile_tab_active_color.'" class="wps-color-picker" data-default-color="#fff" /></td></tr>';
        echo '<tr><td>'.__('Active Tab Text Color:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab_active_text_color" value="'.$wps_profile_tab_active_text_color.'" class="wps-color-picker" data-default-color="#000" /></td></tr>';
        echo '<tr><td>'.__('Inactive Tab Color:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab_inactive_color" value="'.$wps_profile_tab_inactive_color.'" class="wps-color-picker" data-default-color="#d2d2d2" /></td></tr>';
        echo '<tr><td>'.__('Inactive Tab Text Color:', WPS2_TEXT_DOMAIN).'</td><td><input type="text" name="wps_profile_tab_inactive_text_color" value="'.$wps_profile_tab_inactive_text_color.'" class="wps-color-picker" data-default-color="#000" /></td></tr>';
        echo '<tr><td>'.__('Tab Animation:', WPS2_TEXT_DOMAIN).'</td><td>';
            echo '<select name="wps_profile_tab_animation">';
            echo '<option value="slide"';
                if ($wps_profile_tab_animation == 'slide') echo ' SELECTED';
                echo '>'.__('Slide', WPS2_TEXT_DOMAIN).'</option>';
            echo '<option value="fade"';
                if ($wps_profile_tab_animation == 'fade') echo ' SELECTED';
                echo '>'.__('Fade', WPS2_TEXT_DOMAIN).'</option>';
            echo '<option value="none"';
                if ($wps_profile_tab_animation == 'none') echo ' SELECTED';
                echo '>'.__('None', WPS2_TEXT_DOMAIN).'</option>';
        echo '</td></tr>';

        if ($wps_profile_tab1 || $wps_profile_tab2 || $wps_profile_tab3 || $wps_profile_tab4 || $wps_profile_tab5 || $wps_profile_tab6 || $wps_profile_tab7 || $wps_profile_tab8 || $wps_profile_tab9 || $wps_profile_tab10):
            echo wps_show_edit_profile_tabs('Name/Display Name', 'names');
            echo wps_show_edit_profile_tabs('Email Address', 'email');
            echo wps_show_edit_profile_tabs('Town/City/Country', 'location');
            echo wps_show_edit_profile_tabs('Change Password', 'password');
            if ($lang = get_option('wps_pro_lang')):
	            echo wps_show_edit_profile_tabs('Languages', 'lang');
	        endif;
            echo wps_show_edit_profile_tabs('Activity email alerts', 'activity_alerts');
            do_action( 'wps_show_edit_profile_tabs_hook' );
        endif;

        echo '</table>';    

    echo '</div>';

}

function wps_show_edit_profile_tabs($label, $select_name) {

    $tabs_array = get_option('wps_profile_tabs');
    
    $wps_profile_tab1 = (isset($tabs_array['wps_profile_tab1'])) ? $tabs_array['wps_profile_tab1'] : '';
    $wps_profile_tab2 = (isset($tabs_array['wps_profile_tab2'])) ? $tabs_array['wps_profile_tab2'] : '';
    $wps_profile_tab3 = (isset($tabs_array['wps_profile_tab3'])) ? $tabs_array['wps_profile_tab3'] : '';
    $wps_profile_tab4 = (isset($tabs_array['wps_profile_tab4'])) ? $tabs_array['wps_profile_tab4'] : '';
    $wps_profile_tab5 = (isset($tabs_array['wps_profile_tab5'])) ? $tabs_array['wps_profile_tab5'] : '';
    $wps_profile_tab6 = (isset($tabs_array['wps_profile_tab6'])) ? $tabs_array['wps_profile_tab6'] : '';
    $wps_profile_tab7 = (isset($tabs_array['wps_profile_tab7'])) ? $tabs_array['wps_profile_tab7'] : '';
    $wps_profile_tab8 = (isset($tabs_array['wps_profile_tab8'])) ? $tabs_array['wps_profile_tab8'] : '';
    $wps_profile_tab9 = (isset($tabs_array['wps_profile_tab9'])) ? $tabs_array['wps_profile_tab9'] : '';
    $wps_profile_tab10 = (isset($tabs_array['wps_profile_tab10'])) ? $tabs_array['wps_profile_tab10'] : '';

    $select_name = 'wps_profile_tab_'.$select_name;
    $ret = '<tr><td';
        if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 99) $ret .= ' style="color:#cfcfcf"';
        $ret .= '>'.__($label, WPS2_TEXT_DOMAIN).'</td><td>';
    $ret .= '<select ';
        if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 99) $ret .= ' style="color:#cfcfcf"';                    
        $ret .= sprintf('name="%s">', $select_name);
        if ($wps_profile_tab1) $ret .= '<option value="1"';
            if (!isset($tabs_array[$select_name]) || $tabs_array[$select_name] == 1) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab1.'</option>';
        if ($wps_profile_tab2) $ret .= '<option value="2"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 2) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab2.'</option>';
        if ($wps_profile_tab3) $ret .= '<option value="3"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 3) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab3.'</option>';
        if ($wps_profile_tab4) $ret .= '<option value="4"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 4) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab4.'</option>';
        if ($wps_profile_tab5) $ret .= '<option value="5"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 5) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab5.'</option>';
        if ($wps_profile_tab6) $ret .= '<option value="6"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 6) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab6.'</option>';
        if ($wps_profile_tab7) $ret .= '<option value="7"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 7) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab7.'</option>';
        if ($wps_profile_tab8) $ret .= '<option value="8"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 8) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab8.'</option>';
        if ($wps_profile_tab9) $ret .= '<option value="9"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 9) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab9.'</option>';
        if ($wps_profile_tab10) $ret .= '<option value="10"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 10) $ret .= ' SELECTED';
            $ret .= '>'.$wps_profile_tab10.'</option>';
        $ret .= '<option value="99"';
            if (isset($tabs_array[$select_name]) && $tabs_array[$select_name] == 99) $ret .= ' SELECTED';
            $ret .= '>'.__('Do not show', WPS2_TEXT_DOMAIN).'</option>';
    $ret .= '</select>';
    $ret .= '</td></tr>';
    return $ret;
}

add_action( 'wps_admin_setup_form_save_hook', 'wps_edit_profile_admin_options_save', 10, 1 );
function wps_edit_profile_admin_options_save ($the_post) {
    
    if (isset($the_post['wps_change_avatar_effects'])):
        update_option('wps_change_avatar_effects', strtolower($the_post['wps_change_avatar_effects']));
    else:
        delete_option('wps_change_avatar_effects');
    endif;

    if (isset($the_post['wps_password_strength_meter'])):
        update_option('wps_password_strength_meter', true);
    else:
        delete_option('wps_password_strength_meter');
    endif;
    update_option('wps_strength_array', explode(',', $the_post['wps_strength_array']));

    if (isset($the_post['hide_email_notifications_for_activity'])):
        update_option('hide_email_notifications_for_activity', true);
    else:
        delete_option('hide_email_notifications_for_activity');
    endif;

    if (strpos(WPS_CORE_PLUGINS, 'core-profile') !== false):
    
        $tabs_array = array();
    
        if (isset($the_post['wps_profile_tab1']))
            $tabs_array['wps_profile_tab1'] = stripslashes($the_post['wps_profile_tab1']);

        if (isset($the_post['wps_profile_tab2']))
            $tabs_array['wps_profile_tab2'] = stripslashes($the_post['wps_profile_tab2']);

        if (isset($the_post['wps_profile_tab3']))
            $tabs_array['wps_profile_tab3'] = stripslashes($the_post['wps_profile_tab3']);

        if (isset($the_post['wps_profile_tab4']))
            $tabs_array['wps_profile_tab4'] = stripslashes($the_post['wps_profile_tab4']);

        if (isset($the_post['wps_profile_tab5']))
            $tabs_array['wps_profile_tab5'] = stripslashes($the_post['wps_profile_tab5']);

        if (isset($the_post['wps_profile_tab6']))
            $tabs_array['wps_profile_tab6'] = stripslashes($the_post['wps_profile_tab6']);

        if (isset($the_post['wps_profile_tab7']))
            $tabs_array['wps_profile_tab7'] = stripslashes($the_post['wps_profile_tab7']);

        if (isset($the_post['wps_profile_tab8']))
            $tabs_array['wps_profile_tab8'] = stripslashes($the_post['wps_profile_tab8']);

        if (isset($the_post['wps_profile_tab9']))
            $tabs_array['wps_profile_tab9'] = stripslashes($the_post['wps_profile_tab9']);

        if (isset($the_post['wps_profile_tab10']))
            $tabs_array['wps_profile_tab10'] = stripslashes($the_post['wps_profile_tab10']);

        if (isset($the_post['wps_profile_tab_active_color']))
            $tabs_array['wps_profile_tab_active_color'] = stripslashes($the_post['wps_profile_tab_active_color']);

        if (isset($the_post['wps_profile_tab_inactive_color']))
            $tabs_array['wps_profile_tab_inactive_color'] = stripslashes($the_post['wps_profile_tab_inactive_color']);

        if (isset($the_post['wps_profile_tab_active_text_color']))
            $tabs_array['wps_profile_tab_active_text_color'] = stripslashes($the_post['wps_profile_tab_active_text_color']);

        if (isset($the_post['wps_profile_tab_inactive_text_color']))
            $tabs_array['wps_profile_tab_inactive_text_color'] = stripslashes($the_post['wps_profile_tab_inactive_text_color']);

        if (isset($the_post['wps_profile_tab_animation']))
            $tabs_array['wps_profile_tab_animation'] = stripslashes($the_post['wps_profile_tab_animation']);

        if (isset($the_post['wps_profile_tab_names'])) $tabs_array['wps_profile_tab_names'] = (int)$the_post['wps_profile_tab_names'];
        if (isset($the_post['wps_profile_tab_email'])) $tabs_array['wps_profile_tab_email'] = (int)$the_post['wps_profile_tab_email'];
        if (isset($the_post['wps_profile_tab_location'])) $tabs_array['wps_profile_tab_location'] = (int)$the_post['wps_profile_tab_location'];
        if (isset($the_post['wps_profile_tab_password'])) $tabs_array['wps_profile_tab_password'] = (int)$the_post['wps_profile_tab_password'];
        if (isset($the_post['wps_profile_tab_lang'])) $tabs_array['wps_profile_tab_lang'] = (int)$the_post['wps_profile_tab_lang'];
        if (isset($the_post['wps_profile_tab_activity_alerts'])) $tabs_array['wps_profile_tab_activity_alerts'] = (int)$the_post['wps_profile_tab_activity_alerts'];

        $tabs_array = apply_filters( 'wps_show_edit_profile_tabs_save_filter', $tabs_array, $the_post );
        update_option('wps_profile_tabs', $tabs_array);

    endif;
}

/* [wps-user-exists-content] */

// Add shortcodes to setup options
add_action('wps_options_shortcode_hook', 'wps_options_shortcode_hook_user_exists_content', 10, 2);
function wps_options_shortcode_hook_user_exists_content($wps_expand_tab, $wps_expand_shortcode) {
    echo wps_show_shortcode($wps_expand_tab, $wps_expand_shortcode, 'conditional', 'wps_user_exists_content_tab', WPS_PREFIX.'-user-exists-content');
}

// Add shortcode options to setup options
add_action('wps_options_shortcode_options_hook', 'wps_options_shortcode_options_hook_user_exists_content', 10, 1);
function wps_options_shortcode_options_hook_user_exists_content($wps_expand_shortcode) {

    $values = get_option('wps_shortcode_options_'.'wps_user_exists_content') ? get_option('wps_shortcode_options_'.'wps_user_exists_content') : array();
    echo wps_show_options($wps_expand_shortcode, 'wps_user_exists_content_tab');
        echo '<strong>'.__('Purpose:', WPS2_TEXT_DOMAIN).'</strong> '.__("Hides content if no user found.", WPS2_TEXT_DOMAIN).'<br />';
        echo '<strong>'.__('How to use:', WPS2_TEXT_DOMAIN).'</strong> '.__('Add [wps-user-exists-content]CONTENT[/wps-user-exists-content] to a WordPress Page, Post or Text widget. CONTENT will only be shown if a user is found.', WPS2_TEXT_DOMAIN);
        echo wps_codex_link('http://www.wpspro.com/wps-user-exists-content');
        echo '<p><strong>'.__('Options', WPS2_TEXT_DOMAIN).'</strong><br />';
        echo '<table cellpadding="0" cellspacing="0"  class="wps_shortcode_value_row">';
            echo '<tr><td>'.__('Text shown if no user found', WPS2_TEXT_DOMAIN).'</td><td>';
                $not_found_msg = wps_get_shortcode_default($values, 'wps_user_exists_content-not_found_msg', __('User does not exist!', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_user_exists_content-not_found_msg" value="'.$not_found_msg.'" /></td><td>(not_found_msg="'.$not_found_msg.'")</td></tr>';
    
            do_action('wps_show_styling_options_hook', 'wps_user_exists_content', $values);
    
        echo '</table>';
    
    echo '</div>';      

}

/* [wps-is-friend-content] */

// Add shortcodes to setup options
add_action('wps_options_shortcode_hook', 'wps_options_shortcode_hook_is_friend_content', 10, 2);
function wps_options_shortcode_hook_is_friend_content($wps_expand_tab, $wps_expand_shortcode) {
    echo wps_show_shortcode($wps_expand_tab, $wps_expand_shortcode, 'conditional', 'wps_is_friend_content_tab', WPS_PREFIX.'-is-friend-content');
}

// Add shortcode options to setup options
add_action('wps_options_shortcode_options_hook', 'wps_options_shortcode_options_hook_is_friend_content', 10, 1);
function wps_options_shortcode_options_hook_is_friend_content($wps_expand_shortcode) {

    $values = get_option('wps_shortcode_options_'.'wps_is_friend_content') ? get_option('wps_shortcode_options_'.'wps_is_friend_content') : array();
    echo wps_show_options($wps_expand_shortcode, 'wps_is_friend_content_tab');
        echo '<strong>'.__('Purpose:', WPS2_TEXT_DOMAIN).'</strong> '.__("Hides content if users are not friends.", WPS2_TEXT_DOMAIN).'<br />';
        echo '<strong>'.__('How to use:', WPS2_TEXT_DOMAIN).'</strong> '.__('Add [wps-is-friend-content]CONTENT[/wps-is-friend-content] to a WordPress Page, Post or Text widget. CONTENT will only be shown if users are friends.', WPS2_TEXT_DOMAIN);
        echo wps_codex_link('http://www.wpspro.com/wps-is-friend-content');
        echo '<p><strong>'.__('Options', WPS2_TEXT_DOMAIN).'</strong><br />';
        echo '<table cellpadding="0" cellspacing="0"  class="wps_shortcode_value_row">';
            echo '<tr><td>'.__('Text shown if are not friends', WPS2_TEXT_DOMAIN).'</td><td>';
                $not_friends_msg = wps_get_shortcode_default($values, 'wps_is_friend_content-not_friends_msg', __('Sorry, you are not friends!', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-not_friends_msg" value="'.$not_friends_msg.'" /></td><td>(not_friends_msg="'.$not_friends_msg.'")</td></tr>';
            echo '<tr><td>'.__('Show friendship action buttons', WPS2_TEXT_DOMAIN).'</td><td>';
                $include_friendship_action = wps_get_shortcode_default($values, 'wps_is_friend_content-include_friendship_action', true);
                echo '<input type="checkbox" name="wps_is_friend_content-include_friendship_action"'.($include_friendship_action ? ' CHECKED' : '').'></td><td>(include_friendship_action="'.($include_friendship_action ? '1' : '0').'")</td></tr>';
            echo '<tr><td>'.__('Make friends label', WPS2_TEXT_DOMAIN).'</td><td>';
                $friend_add_label = wps_get_shortcode_default($values, 'wps_is_friend_content-friend_add_label', __('Make friends', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-friend_add_label" value="'.$friend_add_label.'" /></td><td>(friend_add_label="'.$friend_add_label.'")</td></tr>';
            echo '<tr><td>'.__('Cancel friendship request label', WPS2_TEXT_DOMAIN).'</td><td>';
                $friend_cancel_request_label = wps_get_shortcode_default($values, 'wps_is_friend_content-friend_cancel_request_label', __('Cancel Request', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-friend_cancel_request_label" value="'.$friend_cancel_request_label.'" /></td><td>(friend_cancel_request_label="'.$friend_cancel_request_label.'")</td></tr>';
            echo '<tr><td>'.__('Request made text', WPS2_TEXT_DOMAIN).'</td><td>';
                $request_made_msg = wps_get_shortcode_default($values, 'wps_is_friend_content-request_made_msg', __('You have received a friend request from this user.', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-request_made_msg" value="'.$request_made_msg.'" /></td><td>(request_made_msg="'.$request_made_msg.'")</td></tr>';
            echo '<tr><td>'.__('Accept request label', WPS2_TEXT_DOMAIN).'</td><td>';
                $accept_request_label = wps_get_shortcode_default($values, 'wps_is_friend_content-accept_request_label', __('Accept Friendship', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-accept_request_label" value="'.$accept_request_label.'" /></td><td>(accept_request_label="'.$accept_request_label.'")</td></tr>';
            echo '<tr><td>'.__('Reject request label', WPS2_TEXT_DOMAIN).'</td><td>';
                $reject_request_label = wps_get_shortcode_default($values, 'wps_is_friend_content-reject_request_label', __('Reject', WPS2_TEXT_DOMAIN));
                echo '<input type="text" name="wps_is_friend_content-reject_request_label" value="'.$reject_request_label.'" /></td><td>(reject_request_label="'.$reject_request_label.'")</td></tr>';

            do_action('wps_show_styling_options_hook', 'wps_is_friend_content', $values);
    
        echo '</table>';
    
    echo '</div>';      

}

?>