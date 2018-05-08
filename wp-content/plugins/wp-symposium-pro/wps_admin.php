<?php
// Admin dependencies
add_action('admin_enqueue_scripts', 'wps_usermeta_admin_init');
function wps_usermeta_admin_init() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wps-usermeta-js', plugins_url('usermeta/wps_usermeta.js', __FILE__), array('wp-color-picker'));	
}

function wps_menu() {

	$menu_label = (defined('WPS_MENU')) ? WPS_MENU : 'WPS Pro';
	add_menu_page($menu_label, $menu_label, 'manage_options', 'wps_pro', 'wpspro_setup', 'none'); 
	add_submenu_page('wps_pro', __('Release Notes', WPS2_TEXT_DOMAIN), __('Release notes', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_release_notes', 'wpspro_release_notes');
	add_submenu_page('wps_pro', __('Setup', WPS2_TEXT_DOMAIN), __('Setup', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_setup', 'wpspro_setup');
	add_submenu_page('wps_pro', __('Shortcodes', WPS2_TEXT_DOMAIN), __('Shortcodes', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_shortcodes', 'wps_pro_shortcodes');
	add_submenu_page(get_option('wps_core_admin_icons') ? 'wps_pro' : '', __('Custom CSS', WPS2_TEXT_DOMAIN), __('Custom CSS', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_custom_css', 'wpspro_custom_css');
	add_submenu_page('wps_pro', __('Licence', WPS2_TEXT_DOMAIN), __('Licence', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_licence', 'wps_pro_licence');
	add_submenu_page(get_option('wps_core_admin_icons') ? 'wps_pro' : '', __('Clear all WPS data', WPS2_TEXT_DOMAIN), __('Clear all WPS data', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_reset', 'wps_pro_reset');
	add_submenu_page('wps_pro', __('Translations', WPS2_TEXT_DOMAIN), __('Translations', WPS2_TEXT_DOMAIN), 'manage_options', 'wps_pro_translations', 'wps_pro_translations');
	remove_submenu_page('wps_pro','wps_pro');

    // Add any favourites to WPS admin menu
    add_action('admin_menu', 'wps_add_external_link_admin_submenu');    

    // Are plugin versions the same (if installed)?
    if (function_exists('__wps__wpspro_extensions_au')):
        $ext = get_plugin_data(__DIR__.'/../wp-symposium-pro-extensions/wp_symposium_pro_extensions.php');
        $ver = $ext['Version'];
        if (get_option('wp_symposium_pro_ver') != $ver)
        echo '<div class="error"><p>'.sprintf(__('Your WP Symposium Pro Extensions plugin version (%s) does not match the core plugin version (%s), please ensure they are both the same! Update on the <a href="%s">Plugins page</a>.', WPS2_TEXT_DOMAIN), $ver, get_option('wp_symposium_pro_ver'), admin_url('plugins.php')).'</p></div>';
    endif;    

}



function wps_add_external_link_admin_submenu() {
    global $submenu;

    // Any admin favourites?
    $admin_favs = get_option('wps_admin_favs');
    if (is_array($admin_favs)):
        // Configure
        if (in_array('custom_css', $admin_favs))
            $submenu['wps_pro'][] = array( 'Custom CSS', 'manage_options', admin_url('admin.php?page=wps_pro_custom_css') );
        if (in_array('profile_extensions', $admin_favs))
            $submenu['wps_pro'][] = array( 'Profile Extensions', 'manage_options', admin_url('edit.php?post_type=wps_extension') );
        if (in_array('setup_rewards', $admin_favs))
            $submenu['wps_pro'][] = array( 'Rewards (setup)', 'manage_options', admin_url('edit.php?post_type=wps_rewards') );
        if (in_array('wps_pro_reset', $admin_favs))
            $submenu['wps_pro'][] = array( 'WPS Pro Reset', 'manage_options', admin_url('admin.php?page=wps_pro_reset') );
        // User
        if (in_array('activity_posts', $admin_favs))
            $submenu['wps_pro'][] = array( 'Activity Posts (all)', 'manage_options', admin_url('edit.php?post_type=wps_activity') );
        if (in_array('friendships', $admin_favs))
            $submenu['wps_pro'][] = array( 'Friendships (all)', 'manage_options', admin_url('edit.php?post_type=wps_friendship') );
        if (in_array('rewards', $admin_favs))
            $submenu['wps_pro'][] = array( 'Rewards (awarded)', 'manage_options', admin_url('edit.php?post_type=wps_reward') );
        if (in_array('whoto', $admin_favs))
            $submenu['wps_pro'][] = array( 'Activity ("whoto" lists)', 'manage_options', admin_url('edit.php?post_type=wps_crowd') );
        if (in_array('reviews', $admin_favs))
            $submenu['wps_pro'][] = array( 'Reviews', 'manage_options', admin_url('edit.php?post_type=wps_review') );
        // Forums
        if (in_array('manage_all_forums', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forums (all)', 'manage_options', admin_url('admin.php?page=wpspro_forum_setup') );
        if (in_array('forum_posts', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forums Posts', 'manage_options', admin_url('edit.php?post_type=wps_forum_post') );
        if (in_array('forum_extensions', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forum Exts (posts)', 'manage_options', admin_url('edit.php?post_type=wps_forum_extension') );
        if (in_array('forum_reply_extensions', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forum Exts (replies)', 'manage_options', admin_url('edit.php?post_type=wps_forum_reply_ext') );
        if (in_array('wps_forum_subs', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forum Subscriptions', 'manage_options', admin_url('edit.php?post_type=wps_forum_subs') );
        if (in_array('forum_reply_extensions', $admin_favs))
            $submenu['wps_pro'][] = array( 'Forum Topic Subs', 'manage_options', admin_url('edit.php?post_type=wps_subs') );
        // Alerts
        if (in_array('manage_alerts', $admin_favs))
            $submenu['wps_pro'][] = array( 'Alerts (manage)', 'manage_options', admin_url('edit.php?post_type=wps_alerts') );
        // Groups
        if (in_array('manage_groups', $admin_favs))
            $submenu['wps_pro'][] = array( 'Groups (manage)', 'manage_options', admin_url('edit.php?post_type=wps_group') );
        if (in_array('group_members', $admin_favs))
            $submenu['wps_pro'][] = array( 'Groups (members)', 'manage_options', admin_url('edit.php?post_type=wps_group_members') );
        // Galleries
        if (in_array('galleries', $admin_favs))
            $submenu['wps_pro'][] = array( 'Galleries', 'manage_options', admin_url('edit.php?post_type=wps_gallery') );
        // Private Messages
        if (in_array('messages', $admin_favs))
            $submenu['wps_pro'][] = array( 'Private Messages', 'manage_options', admin_url('edit.php?post_type=wps_mail') );
        // Lounge
        if (in_array('lounge', $admin_favs))
            $submenu['wps_pro'][] = array( 'Lounge Chat', 'manage_options', admin_url('edit.php?post_type=wps_lounge') );
        // Calendars
        if (in_array('calendars', $admin_favs))
            $submenu['wps_pro'][] = array( 'Calendars', 'manage_options', admin_url('edit.php?post_type=wps_calendar') );
        if (in_array('calendar_events', $admin_favs))
            $submenu['wps_pro'][] = array( 'Calendars (events)', 'manage_options', admin_url('edit.php?post_type=wps_event') );
    endif;

    usort($submenu['wps_pro'], 'compareByName');    
}
function compareByName($a, $b) {
  return strcmp($a[0], $b[0]);
}

function wpspro_manage() {

    if (!get_option('wps_core_admin_icons')):

	$values = get_option('wps_default_extensions');
	$values = $values ? explode(',', $values) : array();

	  	echo '<div id="wps_admin_admin_links">';

		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Configure', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	if (in_array('ext-extended', $values) && function_exists('__wps__wpspro_extensions_au')) 	echo '<li class="wps_icon_profile'.wps_admin_fav('profile_extensions').'"><a href="edit.php?post_type=wps_extension">'.__('Setup Profile Extensions', WPS2_TEXT_DOMAIN).'</a></li>';
			  	if (in_array('ext-rewards', $values) && function_exists('__wps__wpspro_extensions_au'))		echo '<li class="wps_icon_rewards'.wps_admin_fav('setup_rewards').'"><a href="edit.php?post_type=wps_rewards">'.__('Setup Rewards', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '<li class="wps_icon_css'.wps_admin_fav('custom_css').'"><a href="admin.php?page=wps_pro_custom_css">'.__('Custom CSS', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '<li class="wps_icon_reset'.wps_admin_fav('wps_pro_reset').'"><a href="admin.php?page=wps_pro_reset">'.__('Clear all WPS Pro data', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
		  	echo '</div>';

            if (strpos(WPS_CORE_PLUGINS, 'core-activity') !== false || strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false || (strpos(WPS_CORE_PLUGINS, 'core-profile') !== false && ((in_array('ext-rewards', $values) || in_array('ext-rewards', $values)) && function_exists('__wps__wpspro_extensions_au'))) ):
                echo '<div class="wps_manage_left">';
                    echo '<h3>'.__('User', WPS2_TEXT_DOMAIN).'</h3>';
                    echo '<ul class="wps_manage_icons">';
                    if (strpos(WPS_CORE_PLUGINS, 'core-activity') !== false)
                        echo '<li class="wps_icon_activity'.wps_admin_fav('activity_posts').'"><a href="edit.php?post_type=wps_activity">'.__('Manage Activity Posts', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (strpos(WPS_CORE_PLUGINS, 'core-friendships') !== false)
                        echo '<li class="wps_icon_friends'.wps_admin_fav('friendships').'"><a href="edit.php?post_type=wps_friendship">'.__('Manage Friendships', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-rewards', $values) && function_exists('__wps__wpspro_extensions_au'))		echo '<li class="wps_icon_rewards'.wps_admin_fav('rewards').'"><a href="edit.php?post_type=wps_reward">'.__('Manage Rewards Given', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-crowds', $values) && function_exists('__wps__wpspro_extensions_au'))      echo '<li class="wps_icon_whoto'.wps_admin_fav('whoto').'"><a href="edit.php?post_type=wps_crowd">'.__('Manage "Who To" lists', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-reviews', $values) && function_exists('__wps__wpspro_extensions_au'))     echo '<li class="wps_icon_reviews'.wps_admin_fav('reviews').'"><a href="edit.php?post_type=wps_review">'.__('Manage Reviews', WPS2_TEXT_DOMAIN).'</a></li>';
                    echo '</ul>';
                echo '</div>';
            endif;

            if (strpos(WPS_CORE_PLUGINS, 'core-forums') !== false):
                echo '<div class="wps_manage_left">';
                    echo '<h3>'.__('Forums', WPS2_TEXT_DOMAIN).'</h3>';
                    echo '<ul class="wps_manage_icons">';
                    echo '<li class="wps_icon_forums'.wps_admin_fav('manage_all_forums').'"><a href="admin.php?page=wpspro_forum_setup">'.__('Manage All Forums', WPS2_TEXT_DOMAIN).'</a></li>';
                    echo '<li class="wps_icon_forums'.wps_admin_fav('forum_posts').'"><a href="edit.php?post_type=wps_forum_post">'.__('Manage Forum Posts', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-forum-extended', $values) && function_exists('__wps__wpspro_extensions_au'))		echo '<li class="wps_icon_forums'.wps_admin_fav('forum_extensions').'"><a href="edit.php?post_type=wps_forum_extension">'.__('Forum Extensions', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-forum-reply-extended', $values) && function_exists('__wps__wpspro_extensions_au'))echo '<li class="wps_icon_forums'.wps_admin_fav('forum_reply_extensions').'"><a href="edit.php?post_type=wps_forum_reply_ext">'.__('Forum Reply Extensions', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-forum-subs', $values) && function_exists('__wps__wpspro_extensions_au'))			echo '<li class="wps_icon_subs'.wps_admin_fav('wps_forum_subs').'"><a href="edit.php?post_type=wps_forum_subs">'.__('Forum Subscriptions', WPS2_TEXT_DOMAIN).'</a></li>';
                    if (in_array('ext-forum-subs', $values) && function_exists('__wps__wpspro_extensions_au'))			echo '<li class="wps_icon_subs'.wps_admin_fav('wps_subs').'"><a href="edit.php?post_type=wps_subs">'.__('Topic Subscriptions', WPS2_TEXT_DOMAIN).'</a></li>';
                    echo '</ul>';
                echo '</div>';
            endif;

            if (strpos(WPS_CORE_PLUGINS, 'core-alerts') !== false):
                echo '<div class="wps_manage_left">';
                    echo '<h3>'.__('Alerts', WPS2_TEXT_DOMAIN).'</h3>';
                    echo '<ul class="wps_manage_icons">';
                    echo '<li class="wps_icon_alerts'.wps_admin_fav('manage_alerts').'"><a href="edit.php?post_type=wps_alerts">'.__('Manage Alerts', WPS2_TEXT_DOMAIN).'</a></li>';
                    echo '</ul>';
                    echo '<p>'.sprintf(__('Clear out your <a href="%s">sent and pending alerts</a> regularly.', WPS2_TEXT_DOMAIN), admin_url( 'edit.php?post_type=wps_alerts' )).'</p>';
                    if (function_exists('__wps__wpspro_extensions_au'))
                        echo '<p>'.sprintf(__('<a href="%s">Click here</a> to clean up forum subscriptions for non-existent users.', WPS2_TEXT_DOMAIN), admin_url( 'edit.php?post_type=wps_alerts&wps_cleanup=1' )).'</p>';
                echo '</div>';
            endif;

		  	if (in_array('ext-groups', $values) && function_exists('__wps__wpspro_extensions_au')):
		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Groups', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	echo '<li class="wps_icon_groups'.wps_admin_fav('manage_groups').'"><a href="edit.php?post_type=wps_group">'.__('Manage Groups', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '<li class="wps_icon_groups'.wps_admin_fav('group_members').'"><a href="edit.php?post_type=wps_group_members">'.__('Group Members', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
		  	echo '</div>';
		  	endif;

		  	if (in_array('ext-gallery', $values) && function_exists('__wps__wpspro_extensions_au')):
		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Galleries', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	echo '<li class="wps_icon_galleries'.wps_admin_fav('galleries').'"><a href="edit.php?post_type=wps_gallery">'.__('Manage Galleries', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
		  	echo '</div>';
		  	endif;

		  	if (in_array('ext-mail', $values) && function_exists('__wps__wpspro_extensions_au')):
		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Private Messages', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	echo '<li class="wps_icon_mail'.wps_admin_fav('messages').'"><a href="edit.php?post_type=wps_mail">'.__('Manage Messages', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
		  	echo '</div>';
		  	endif;

		  	if (in_array('ext-lounge', $values) && function_exists('__wps__wpspro_extensions_au')):
		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Lounge', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	echo '<li class="wps_icon_lounge'.wps_admin_fav('lounge').'"><a href="edit.php?post_type=wps_lounge">'.__('Manage Lounge Chat', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
                echo '<p>'.sprintf(__('Please note that the Lounge is an unsupported feature, we recommend <a href="%s" target="_new">Simple Ajax Chat</a>.', WPS2_TEXT_DOMAIN), 'https://wordpress.org/plugins/simple-ajax-chat/').'</p>';
		  	echo '</div>';
		  	endif;

		  	if (in_array('ext-calendar', $values) && function_exists('__wps__wpspro_extensions_au')):
		  	echo '<div class="wps_manage_left">';
			  	echo '<h3>'.__('Calendars', WPS2_TEXT_DOMAIN).'</h3>';
			  	echo '<ul class="wps_manage_icons">';
			  	echo '<li class="wps_icon_calendars'.wps_admin_fav('calendars').'"><a href="edit.php?post_type=wps_calendar">'.__('Manage Calendars', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '<li class="wps_icon_calendars'.wps_admin_fav('calendar_events').'"><a href="edit.php?post_type=wps_event">'.__('Manage Calendar Events', WPS2_TEXT_DOMAIN).'</a></li>';
			  	echo '</ul>';
		  	echo '</div>';
		  	endif;

		echo '</div>';

		echo '<div style="clear:both"></div>';

	endif;
}

function wps_admin_fav($item) {
    $admin_favs = get_option('wps_admin_favs');
    if (is_array($admin_favs)):
        if (in_array($item, $admin_favs)):
            return ' wps_admin_fav wps_admin_fav_on'.' wps_fav_'.$item;
        else:
            return ' wps_admin_fav'.' wps_fav_'.$item;
        endif;
    else:
        return ' wps_admin_fav'.' wps_fav_'.$item;
    endif;
}

function wpspro_release_notes() {

  	echo '<div class="wrap">';
        	
	  	echo '<style>';
            echo '.wrap { margin-top: 30px !important; margin-left: 10px !important; }';
	  		echo '#wps_release_notes p, td, ol, a { font-size:14px; line-height: 1.3em; font-family:arial; }';
	  		echo '#wps_release_notes h1 { color: #510051; font-weight: bold; line-height: 1.2em; }';
	  		echo '#wps_release_notes h2 { color: #510051; margin-top: 10px; font-weight: bold; }';
	  		echo '#wps_release_notes h3 { color: #333; }';
	  	echo '</style>';
	  	echo '<div id="wps_release_notes">';
	  		echo '<div id="wps_welcome_bar" style="margin-top: 20px;">';
		  		echo '<img id="wps_welcome_logo" style="width:56px; height:56px; float:left;" src="'.plugins_url('../wp-symposium-pro/css/images/wps_logo.png', __FILE__).'" title="'.__('help', WPS2_TEXT_DOMAIN).'" />';
		  		echo '<div style="font-size:2em; line-height:1em; font-weight:100; color:#fff;">'.__('Welcome to WP Symposium Pro', WPS2_TEXT_DOMAIN).'</div>';
		  		echo '<p style="color:#fff;"><em>'.__('The ultimate social network plugin for WordPress', WPS2_TEXT_DOMAIN).'</em></p>';
	  		echo '</div>';

	  		echo '<div style="font-size:1.4em; margin-top:20px">'.__('Thank you for installing WP Symposium Pro!', WPS2_TEXT_DOMAIN).'</div>';

	  		?>

            <div id="wps_release_notes_facebook" class="wps_release_notes" style="float:right; width:280px;">
                <div style="float:right;width:200px"><?php echo sprintf(__('Please don\'t forget to like our <a href="%s" target="_blank">Facebook page</a>, or follow @wpsymposium on <a href="%s" target="_blank">Twitter</a>, to get all the latest news and release announcements.', WPS2_TEXT_DOMAIN), 'https://www.facebook.com/wpsymposium', 'http://twitter.com/wpsymposium'); ?></div>
            </div>
            <p>
            	<?php echo sprintf(__('If you are new to WP Symposium Pro, you will want to visit the <a href="%s">Setup page</a>...', WPS2_TEXT_DOMAIN), admin_url('admin.php?page=wps_pro_setup')); ?>
            </p>
            <p>
            	<?php echo sprintf(__('If you want to have a look at the current development version, you can <a href="%s" target="_blank">download the latest development build</a>, and follow news on it on the <a href="%s" target="_blank">Development blog</a>.', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/development-build/', 'http://www.wpsymposiumpro.com/category/development/'); ?>
            </p>
            <p>
            	<?php echo __('If you use a cache or a CDN (maybe like CloudFlare), we recommend clearing/purging all your files after upgrading any plugins.', WPS2_TEXT_DOMAIN); ?>
            </p>

            <em><strong>Simon, WP Symposium developer (and tea drinker, drink tea, tea is good)</strong></em></p>
			<br />

            <?php
            $cup_position = 'left';
            if ($cup_position == 'left'):
                $cup_of_tea_left = "background-position: bottom left; background-repeat: no-repeat; background-image: url('".plugins_url( '/css/images/cup_of_tea.png', __FILE__ )."');";
                $cup_of_tea_right = "";
            elseif ($cup_position == 'right'):
                $cup_of_tea_left = "";
                $cup_of_tea_right = "background-position: bottom right; background-repeat: no-repeat; background-image: url('".plugins_url( '/css/images/cup_of_tea.png', __FILE__ )."');";
            else: // center (of left)
                $cup_of_tea_left = "background-position: bottom center; background-repeat: no-repeat; background-image: url('".plugins_url( '/css/images/cup_of_tea.png', __FILE__ )."');";
                $cup_of_tea_right = "";
            endif;
            ?>

            <table><tr>
				<td valign="top" class="wps_release_notes" style="<?php echo $cup_of_tea_left; ?>width:45%;">

					<div style="font-size:1.6em; line-height:1.6em; color: #510051; font-weight: bold;">Core WP Symposium Pro plugin</div>
					<a href="http://www.wordpress.org/plugins/wp-symposium-pro" target-"_blank">Available from the WordPress repository</a><br />

                    <h2 style="font-style:italic; margin-top:20px;">16.11</h2>

                    <h3>Avatar</h3>
                    <p>Can now translate flip/rotate/etc for avatar upload (Shortcodes->Avatar->[wps-avatar-change]).</p>

                    <h3>Edit Profile</h3>
                    <p>Set default/mandatory for town/city and country (Shortcodes->Profile->[wps-usermeta-change]).</p>

                    <h3>Profile</h3>
                    <p>Profile page shows correctly for users who have space in the user login.</p>

                    <div style="width:10px;height:200px"></div> <!-- buffer for cup of tea spacing  -->                                       

                </td>
				<td style="width:1%">&nbsp;</td>
				<td valign="top" class="wps_release_notes" style="<?php echo $cup_of_tea_right; ?>">

                    <div style="font-size:1.6em; line-height:1.6em; color: #510051; font-weight: bold;">WP Symposium Pro Extensions plugin</div>
					<a href="http://www.wpsymposiumpro.com/licenses/" target-"_blank">Available from www.wpsymposiumpro.com</a><br />

                    <h2 style="font-style:italic; margin-top:20px;">16.11</h2>

                    <h3>Profile Extensions</h3>
                    <p>Prefix an option in a list with * to set it as the "clear this choice", follow * with your own words.</p>
                    
                    <h3>Profile Extensions/Directory</h3>
                    <p>To help with adding profile extensions to the directory, added to WPS Pro->Shortcodes->Directory->[wps-directory]</p>

                    <h3>Login/Register</h3>
                    <p>Can now set default values of town/city and country on registration form if used.</p>

                    <h3>Member Directory</h3>
                    <p>Users who have not logged in since installing WPS Pro will not appear in the directory (as never been active in WPS Pro's view). Sort this out via WPS Pro->Setup->Member Directory.</p>

                    <h3>Calendar</h3>
                    <p>Event owners and site admin can now delete events via front-end (click on event and choose Delete).</p>
					
				</td>
			</tr></table>

			<br style="clear:both">
	  	<?php
	  	echo '</div>';
		
	echo '</div>';	

}

function wpspro_setup() {

	// Flush re-write rules, good idea if problem with linking, saves having to re-save permalink
	global $wp_rewrite;
	$wp_rewrite->flush_rules();	
  	echo '<style>';
        echo '.wrap { margin-top: 20px !important; margin-left: 10px !important; }';
  	echo '</style>';

  	echo '<div class="wrap">';
        	
        // Backdoor to de-activate everything
        if (isset($_GET['wps_deactivate_all'])):
            delete_option('wps_default_core');
            delete_option('wps_default_extensions');
            echo '<div class="wps_warning">'.__('All features de-activated', WPS2_TEXT_DOMAIN).'</div>';
        endif;

	  	$show_header = get_option('wps_show_welcome_header') ? ' style="display:none; "' : '';

	  	echo '<div '.$show_header.'id="wps_welcome">';
	  		echo '<div id="wps_welcome_bar">';
		  		echo '<img id="wps_welcome_logo" style="width:56px; height:56px; float:left;" src="'.plugins_url('../wp-symposium-pro/css/images/wps_logo.png', __FILE__).'" title="'.__('help', WPS2_TEXT_DOMAIN).'" />';
		  		echo '<div style="font-size:2em; line-height:1em; font-weight:100; color:#fff;">'.__('Welcome to WP Symposium Pro', WPS2_TEXT_DOMAIN).'</div>';
		  		echo '<p style="color:#fff;"><em>'.__('The ultimate social network plugin for WordPress', WPS2_TEXT_DOMAIN).'</em></p>';
	  		echo '</div>';
	  		echo '<div style="width:30%; min-width:200px; margin-right:10px; float: left;">';
		  		echo '<p style="font-size:1.4em; font-weight:100;">'.__('How to get started...', WPS2_TEXT_DOMAIN).'</p>';
		  		echo '<p style="font-weight:100;">'.__('Use the Quick Start buttons below,', WPS2_TEXT_DOMAIN).'<br />';
		  		echo sprintf(__('add your new pages to your <a href="%s">WordPress menu</a>,  then', WPS2_TEXT_DOMAIN), 'nav-menus.php').'<br />';
		  		echo sprintf(__('customize via <a href="%s">Shortcodes</a> (via the menu).', WPS2_TEXT_DOMAIN), admin_url( 'admin.php?page=wps_pro_shortcodes' )).'</p>';
		  		echo '<p style="font-size:1.4em; font-weight:100;">'.__('How to get support...', WPS2_TEXT_DOMAIN).'</p>';
		  		echo '<p style="font-weight:100;">'.sprintf(__('Support is available at <a target="_blank" href="%s">www.wpsymposiumpro.com</a>', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com').'<br />';
		  		echo sprintf(__('with <a href="%s" target="_blank">forums</a>, <a href="%s" target="_blank">helpdesk</a>, and live chat support.', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/forums/', 'http://www.wpsymposiumpro.com/helpdesk/').'</p>';
		  		echo '<p style="font-weight:100;">'.sprintf(__('We also have an <a target="_blank" href="%s">online admin guide</a> and <a target="_blank" href="%s">video tutorials</a>...', WPS2_TEXT_DOMAIN), 'http://www.wpspro.com', 'http://www.wpspro.com/tag/video').'</p>';
	  		echo '</div>';
	  		echo '<div class="wps_setup_video_div">';
		  		echo '<p style="font-size:1.4em; font-weight:100;">'.__('Setting up WP Symposium Pro', WPS2_TEXT_DOMAIN).'</p>';
	            echo '<div class="wps_video_container" style="margin-bottom:-30px;">';
				echo '<iframe class="wps_setup_video_iframe" src="//www.youtube.com/embed/8beh25UWQOs?feature=player_embedded&showinfo=0&rel=0&autohide=1&vq=hd720" frameborder="0" allowfullscreen></iframe>';
				echo '</div>';
	  		echo '</div>';
	  		echo '<div class="wps_setup_video_div">';
		  		echo '<p style="font-size:1.4em; font-weight:100;">'.__('Installing and Activating more Extensions', WPS2_TEXT_DOMAIN).'</p>';
	            echo '<div class="wps_video_container" style="margin-bottom:-30px;">';
				echo '<iframe class="wps_setup_video_iframe" src="//www.youtube.com/embed/It3bJ0IGy2M?feature=player_embedded&showinfo=0&rel=0&autohide=1&vq=hd720" frameborder="0" allowfullscreen></iframe>';
				echo '</div>';
	  		echo '</div>';
	  	echo '</div>';

		// Do any saving from quick start hook
		if (isset($_POST)):
            if (isset($_POST['wps_expand'])) echo '<input type="hidden" id="wps_expand" value="'.$_POST['wps_expand'].'" />';
			if (isset($_POST['wpspro_quick_start'])):
				do_action( 'wps_admin_quick_start_form_save_hook', $_POST);
			endif;
		endif;

		// Show and hide header
		echo '<div style="float:right"><a id="wps_hide_welcome_header" style="text-decoration:none;" href="javascript:void(0); return false;">'.__('Show/Hide Welcome', WPS2_TEXT_DOMAIN).'</a></div>';

		// Check that profile pages are set up
		if (!get_option('wpspro_profile_page')):
			echo '<div class="wps_error">'.__('You need to set the Profile pages, under "Profile Page" below...', WPS2_TEXT_DOMAIN).'</div>';
		endif;

		// Quick start hook
		echo '<div style="width: 300px; float: left; font-size:1.8em; margin-bottom:15px;">'.__('Quick Start', WPS2_TEXT_DOMAIN).'</div>';
		echo '<div style="clear: both; margin-bottom:15px;overflow:auto;">';
		do_action( 'wps_admin_quick_start_hook' );
		echo '</div>';

		// Admin links
		$hide_icons = get_option('wps_core_admin_icons');
		if ($hide_icons):
			echo '<div style="float:right"><a id="wps_hide_admin_links_show" style="text-decoration:none;" href="javascript:void(0); return false;">'.__('Move admin links here', WPS2_TEXT_DOMAIN).'</a></div>';
		else:
			echo '<div style="float:right; text-align:right;"><a id="wps_hide_admin_links" style="text-decoration:none;" href="javascript:void(0); return false;">'.__('Move admin links to dashboard menu', WPS2_TEXT_DOMAIN).'</a><br />('.__('click on individual icons to move one at a time', WPS2_TEXT_DOMAIN).')</div>';
		endif;
		wpspro_manage();		

		// Option Sections
	  	echo '<p style="clear:both;">'.__('Click on a section title below to see options and help to get started.', WPS2_TEXT_DOMAIN).'</p>';
		if (!function_exists('__wps__wpspro_extensions_au'))
	  		echo '<p>'.sprintf(__('Loads more features are available from <a href="%s">www.wpsymposiumpro.com</a>.', WPS2_TEXT_DOMAIN), "http://www.wpsymposiumpro.com/licenses").'</p>';

		// Do any saving
		if (isset($_POST['wpspro_update']) && $_POST['wpspro_update'] == 'yes'):
			do_action( 'wps_admin_setup_form_save_hook', $_POST);
		endif;
		if ( isset($_GET['wpspro_update']) ):
			do_action( 'wps_admin_setup_form_get_hook', $_GET);
		endif;		
		echo '<form id="wps_setup" action="'.admin_url( 'admin.php?page=wps_pro_setup' ).'" method="POST">';
		echo '<input type="hidden" name="wpspro_update" value="yes" />';

			// Getting Started/Help hook
			do_action( 'wps_admin_getting_started_hook' );

		echo '<p><input type="submit" id="wps_setup_submit" name="Submit" class="button-primary" value="'.__('Save Changes', WPS2_TEXT_DOMAIN).'" /></p>';
			
		echo '</form>';

		
	echo '</div>';	  	

}

function wps_pro_shortcodes() {

	// Flush re-write rules, good idea if problem with linking, saves having to re-save permalink
	global $wp_rewrite;
	$wp_rewrite->flush_rules();	
    
    // Do any saving
    if (isset($_POST['wpspro_update']) && $_POST['wpspro_update'] == 'yes'):
        do_action( 'wps_admin_getting_started_shortcodes_save_hook', $_POST);
    endif;

  	echo '<div class="wrap">';
        	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';

        // Getting Started/Help hook
        do_action( 'wps_admin_getting_started_shortcodes_hook' );
		
	echo '</div>';	  	

}

function wps_pro_translations() {

	if (current_user_can('manage_options')):
		if (isset($_POST['wps_pro_lang'])):
            update_option('wps_pro_lang', $_POST['wps_pro_lang']);
            if (isset($_POST['wps_pro_lang_site'])):
                update_option('wps_pro_lang_site', true);
            else:
                delete_option('wps_pro_lang_site');
            endif;
        endif;
	endif;

  	echo '<div class="wrap">';
        	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
    
	  	echo '<h2>'.__('Translations (requires WordPress version 4.0 or higher)', WPS2_TEXT_DOMAIN).'</h2>';

		$path = WP_CONTENT_DIR.'/languages/plugins/wp-symposium-pro/';

		if (is_admin() && !file_exists($path)) {
			// ... make folder for translation files
	    	@mkdir($path, 0777, true);	
		}

		$locale = get_locale();
		$deprecated = false;
		$domain = WPS2_TEXT_DOMAIN;

		// Load the textdomain according to the plugin first
		$sep = $locale ? '-' : '';
		$mofile = $domain . $sep . $locale . '.mo';
		if ( $loaded = load_textdomain( $domain, $mofile ) )
			return $loaded;

		// Otherwise, load from the languages directory
		$mofile = $path . $mofile;
		$loaded_file = load_textdomain( $domain, $mofile );

		echo '<h3>'.__('Shortcode options', WPS2_TEXT_DOMAIN).'</h3>';
	  	echo '<p>'.sprintf(__('You can change text and labels used by shortcodes on the <a href="%s">Shortcodes</a> admin page', WPS2_TEXT_DOMAIN), admin_url( 'admin.php?page=wps_pro_shortcodes' )).'.</p>';

        echo '<h3>'.__('Default language (locale) as defined in Settings->General', WPS2_TEXT_DOMAIN).'</h3>';
        echo '<p>'.__('This is your default site language:', WPS2_TEXT_DOMAIN).' '.$locale.'</p>';
    
		echo '<h3>'.__('Default .mo file based on default locale', WPS2_TEXT_DOMAIN).'</h3>';
        echo '<p>'.sprintf(__('If you want to change the translations for WP Symposium Pro for your site&apos;s default language/locale, <a href="%s" target="_blank">get the base .pot file</a>, and then the .mo file that you should create (with an application like <a href="%s" target="_blank">PoEdit</a>) is:', WPS2_TEXT_DOMAIN), "http://www.wpsymposiumpro.com/translation/", "http://www.poedit.com").'</p>';
		echo '<p><span style="padding:4px 8px 4px 8px;border-radius: 3px; border: 1px solid #aaa; background-color:white">'.$mofile.'</span></p>';    

		echo '<h3>'.__('Adding languages that user&apos;s can select themselves', WPS2_TEXT_DOMAIN).'</h3>';
		echo '<p>'.sprintf(__('You need to generate a .mo file with PoEdit, based on downloaded .po files from <a href="%s" target="_blank">CrowdIn.Net</a> you offer to your users (or create one as above with an application like PoEdit), for example, wp-symposium-pro-fr_FR.mo would be the .mo file for French. You place the .mo files in the "Translations folder" as shown in the next section below on this page. Read more about it on the <a href="%s" target="_blank">WP Symposium Pro Codex</a>.', WPS2_TEXT_DOMAIN), "https://crowdin.com/project/symposium", "http://www.wpspro.com/translating-wp-symposium-pro/").'</p>';

        echo '<p>'.__('To give users the ability to choice from the alternative languages, enter additional languages and locales below, see the example further down the page.', WPS2_TEXT_DOMAIN).'</p>';
        echo '<p>'.__('For your site&apos;s default language, put it at the top, and do not enter a comma and locale (look at the example for an English site).', WPS2_TEXT_DOMAIN).'</p>';

		echo '<form action="" method="POST">';
        echo '<textarea name="wps_pro_lang" style="border:1px solid black; width:500px;height:100px">';
        echo get_option('wps_pro_lang');
        echo '</textarea><br />';
        echo '<input type="submit" class="button-primary" value="'.__('Save', WPS2_TEXT_DOMAIN).'" />';			

        echo '<p>'.__('Once saved, user&apos;s can select from the languages that you have set above on their Edit Profile page.', WPS2_TEXT_DOMAIN).'</p>';
		echo '<p>'.sprintf(__('Note that if you are using <strong>TABS</strong> for your Edit Profile page on the website, you can select which tab the languages choice appears in, in the Edit Profile section of the <a href="%s">Setup</a> page.', WPS2_TEXT_DOMAIN), admin_url( 'admin.php?page=wps_pro_setup' )).'</p>';

		echo '<p>'.__('As an example, if your site language was English, and you have a .mo file for French (wp-symposium-pro-fr_FR.mo), German (wp-symposium-pro-de_DE.mo), Russian (wp-symposium-pro-ru_RU.mo) and Spanish (wp-symposium-pro-es_ES.mo), you could enter the following in the text area above:', WPS2_TEXT_DOMAIN).'</p>';
		echo '<div style="font-family:courier">English<br />';
		echo 'Français,fr_FR<br />';
		echo 'Deutsche,de_DE<br />';
		echo 'Pусский,ru_RU<br />';
		echo 'Español,es_ES</div>';

		echo '<h3>'.__('Translations folder', WPS2_TEXT_DOMAIN).'</h3>';
        echo '<p>'.__('This is the folder in which you put your translation files (.mo files) if you want to provide alternative languages to your users.', WPS2_TEXT_DOMAIN).'</p>';
		echo '<p><span style="padding:4px 8px 4px 8px;border-radius: 3px; border: 1px solid #aaa; background-color:white">'.$path.'</span></p>';
    
        $files = scandir($path);
        $valid_files = false;
        $list = '<ul>';
        if ($files):
            foreach ($files as $file):
                if ( (strpos($file,  $domain.'-') !== false) && (strpos($file, '.mo') !== false) ):
                    $list .= '<li>'.$file.'</li>';
                    $valid_files = true;
                endif;
            endforeach;
        endif;
        $list .= '</ul>';
        if ($valid_files):
            echo '<p>'.__('Language files found:', WPS2_TEXT_DOMAIN).'</p>';
            echo $list;
        else:
            echo '<p>'.__('There are currently no valid language files in the directory.', WPS2_TEXT_DOMAIN).'</p>';            
        endif;

		echo '<h3>'.__('Changing language of entire site', WPS2_TEXT_DOMAIN).'</h3>';
	  	echo '<p>'.sprintf(__('If you have the corresponding site language for WordPress installed (see <a target="_blank" href="%s">here</a>), it can optionally be used for the user when they select a language too!', WPS2_TEXT_DOMAIN ), "https://codex.wordpress.org/Installing_WordPress_in_Your_Language").'</p>';
        echo '<p><input type="checkbox" ';
        if (get_option('wps_pro_lang_site')) echo 'CHECKED ';
            echo 'name="wps_pro_lang_site" />'.__('Tick to confirm automatic language/locale switch for WordPress installation.', WPS2_TEXT_DOMAIN).'</p>';
        echo '<input type="submit" class="button-primary" value="'.__('Save', WPS2_TEXT_DOMAIN).'" />';			
		echo '</form>';
        
    echo '</div>';	  	

}

function wps_pro_reset() {

  	echo '<div class="wrap">';
        	
	  	echo '<style>';
            echo '.wrap { margin-top: 30px !important; margin-left: 10px !important; }';
	  	echo '</style>';
        	
  		echo '<div id="wps_welcome_bar" style="margin-top: 20px;">';
	  		echo '<img id="wps_welcome_logo" style="width:56px; height:56px; float:left;" src="'.plugins_url('../wp-symposium-pro/css/images/wps_logo.png', __FILE__).'" title="'.__('help', WPS2_TEXT_DOMAIN).'" />';
	  		echo '<div style="font-size:2em; line-height:1em; font-weight:100; color:#fff;">'.__('Welcome to WP Symposium Pro', WPS2_TEXT_DOMAIN).'</div>';
	  		echo '<p style="color:#fff;"><em>'.__('The ultimate social network plugin for WordPress', WPS2_TEXT_DOMAIN).'</em></p>';
  		echo '</div>';

  		echo '<div style="font-size:1.4em; margin-top:20px">'.__('WP Symposium Pro data removal (reset)', WPS2_TEXT_DOMAIN).'</div>';

		echo '<p>'.__('Use this screen to reset WP Symposium Pro, or remove all data before you uninstall the plugin.', WPS2_TEXT_DOMAIN).'</p>';

		// admins only!
		if (current_user_can('manage_options')):

			// ... instructed to reset?
			if (isset($_POST['wps_pro_reset_confirm'])):
				if (wp_verify_nonce( $_POST['wps_pro_reset_nonce'], 'wps_pro_reset' )) {
					// reset!
                    global $wpdb, $wp_rewrite;
                    if (is_multisite()) {
                        $blogs = $wpdb->get_results("SELECT blog_id FROM ".$wpdb->base_prefix."blogs");
                        if ($blogs) {
                            foreach($blogs as $blog) {
                              switch_to_blog($blog->blog_id);
                              echo '<div class="wps_warning">'.sprintf(__('Switching to blog ID %d', WPS2_TEXT_DOMAIN), $blog->blog_id).'</div>';
                                    echo '<div class="wps_warning">';
                                    __wps_pro_uninstall_delete();
                                    echo __('Removing local files', WPS2_TEXT_DOMAIN).'... ';
                                    __wps_pro_uninstall_rrmdir(WP_CONTENT_DIR.'/wps-pro-content');
                                    echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
                                    echo __('Flushing WordPress', WPS2_TEXT_DOMAIN).'... ';                        
                        			$wp_rewrite->flush_rules();
                        			echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
									echo '</div><div class="wps_success">'.__('Complete', WPS2_TEXT_DOMAIN).'</div>';
									echo '<p>'.__('You will need to remove any pages that you created.', WPS2_TEXT_DOMAIN).'</p>';
                            }
                            restore_current_blog();
                        }   
                    } else {
                    	echo '<div class="wps_warning">';
                        __wps_pro_uninstall_delete();
						echo __('Removing local files', WPS2_TEXT_DOMAIN).'... ';                        
                        __wps_pro_uninstall_rrmdir(WP_CONTENT_DIR.'/wps-pro-content');
						echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
						echo __('Flushing WordPress', WPS2_TEXT_DOMAIN).'... ';                        
                        $wp_rewrite->flush_rules();
						echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
						echo '</div><div class="wps_success">'.__('Complete', WPS2_TEXT_DOMAIN).'</div>';
						echo '<p>'.__('You will need to remove any pages that you created.', WPS2_TEXT_DOMAIN).'</p>';
                    }

				} else {
					echo '<div class="wps_error">'.__('NONCE failed - suspicious activity, reset cancelled', WPS2_TEXT_DOMAIN).'</div>';
				}

			else:

				echo '<div class="wps_warning">'.__('This cannot be un-done - please make sure you take a site database backup first (in case of problems or mistake)!', WPS2_TEXT_DOMAIN).'</div>';

			endif;

			echo '<form onsubmit="return confirm(\''.__('Are you sure? Last chance!', WPS2_TEXT_DOMAIN).'\')" action="'.admin_url( 'admin.php?page=wps_pro_reset' ).'" method="POST">';
				wp_nonce_field( 'wps_pro_reset', 'wps_pro_reset_nonce' );				
				echo '<input type="hidden" name="wps_pro_reset_confirm" value="Y" />';
				echo '<input type="submit" class="button-primary" value="'.__('Clear all WPS Pro data', WPS2_TEXT_DOMAIN).'" />';			
			echo '</form>';

		else:

			echo '<div class="wps_error">'.__('Only available to site administrators.', WPS2_TEXT_DOMAIN).'</div>';

		endif;

}

function wps_pro_licence() {

  	echo '<div class="wrap">';
        	
	  	echo '<style>';
            echo '.wrap { margin-top: 30px !important; margin-left: 10px !important; }';
	  	echo '</style>';
        	
  		echo '<div id="wps_welcome_bar" style="margin-top: 20px;">';
	  		echo '<img id="wps_welcome_logo" style="width:56px; height:56px; float:left;" src="'.plugins_url('../wp-symposium-pro/css/images/wps_logo.png', __FILE__).'" title="'.__('help', WPS2_TEXT_DOMAIN).'" />';
	  		echo '<div style="font-size:2em; line-height:1em; font-weight:100; color:#fff;">'.__('Welcome to WP Symposium Pro', WPS2_TEXT_DOMAIN).'</div>';
	  		echo '<p style="color:#fff;"><em>'.__('The ultimate social network plugin for WordPress', WPS2_TEXT_DOMAIN).'</em></p>';
  		echo '</div>';
        
  		echo '<div style="font-size:1.4em; margin-top:20px">'.__('WP Symposium Pro Extensions Plugin Licence', WPS2_TEXT_DOMAIN).'</div>';

		echo '<p>'.sprintf(__('The core WP Symposium Pro plugin is free, available from <a href="%s" target="_blank">wordpress.org</a>. No licence code is required for the core plugin.', WPS2_TEXT_DOMAIN), 'http://www.wordpress.org/plugins/wp-symposium-pro').'</p>';
		echo '<p>'.sprintf(__('If you have the <a href="%s" target="_blank">WP Symposium Pro Extensions plugin</a> activated, you will need to enter a licence code as proof of purchase.<br />Until you do so, a polite notice will be displayed on your site to users (this is not shown if your site is running on a local development server or within the trial period).', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/licenses/').'</p>';
		echo '<p>'.__('We are sorry that we have to do this, unfortunately there are some people who want to use the plugin for free when the vast majority are nice, and purchase the plugin.', WPS2_TEXT_DOMAIN).'</p>';
		echo '<p>'.sprintf(__('If you have purchased an <a href="%s" target="_blank">Enterprise licence</a> or <a href="%s" target="_blank">Lifetime licence</a>, we can provide you with a one-off licence code - please us via the live chat (or leave a message that way).', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/licenses/', 'http://www.wpsymposiumpro.com/licenses/').'</p>';
		echo '<p>'.__('If you are running a trial version of the Extensions Plugin, you have 28 days to try the product. During this time a notice is displayed on your site, but to administrators only.', WPS2_TEXT_DOMAIN).'</p>';

        if (function_exists('__wps__wpspro_extensions_au')):
    
			// ... need to save the code?
			if (isset($_POST['wps_manage_licence_input']) && isset($_POST['wps_manage_licence_input'])):
				update_option('wps_licence_code', $_POST['wps_manage_licence_input']);
			endif;
			if (isset($_POST['wps_manage_licence_clear']) && isset($_POST['wps_manage_licence_clear'])):
				delete_option('wps_licence_code');
			endif;
    
			$licence = wps_licence_code();
			$licenced = $licence[0] ? true : false;

            $install = get_option('wps_extensions_installed');
            if (!$licenced && $install):

                $expiry_date = $install + (28*60*60*24); // 28 days grace period
                $diff_left = $expiry_date - time(); // expired?

                $days = floor($diff_left / (60*60*24));
                $diff = $diff_left - ($days*60*60*24);

                $hours = floor($diff / (60*60));
                $diff = $diff - ($hours*60*60);

                $minutes = floor($diff / (60));
                $seconds = $diff - ($minutes*60);

                if ($diff_left > 0):
                    echo sprintf(__('<strong>You are running a demonstration copy of the Extensions plugin, it was installed on %s, and will expire at %s on %s.</strong>', WPS2_TEXT_DOMAIN), @date('l jS F Y', $install), @date('H:i', $expiry_date), @date('l jS F Y', $expiry_date));
                else:
                    echo sprintf(__('<span style="color:red;font-weight:bold">Your demonstration copy of the Extensions plugin has expired! It was installed on %s at %s, and expired at %s on %s.</span>', WPS2_TEXT_DOMAIN), @date('l jS F Y', $install), @date('H:i', $install), @date('H:i', $expiry_date), @date('l jS F Y', $expiry_date));
                endif;
    
            endif;    
    
			// ... display licence information (and form to enter/update)
			echo '<form action="'.admin_url( 'admin.php?page=wps_pro_licence' ).'" method="POST">';
				if (!$licenced) echo '<br />';
				echo '<div id="wps_manage_licence_code">';
					if ($licence[2]):
						echo '<strong>'.__('Licence code', WPS2_TEXT_DOMAIN).': '.$licence[2];
						if (!$licenced) echo ' - '.__('Invalid!', WPS2_TEXT_DOMAIN);
						echo '</strong>';
					endif;
					echo $licence[1]; // HTML
					echo __('Licence code', WPS2_TEXT_DOMAIN).': ';
					echo '<input type="text" id="wps_manage_licence_input" name="wps_manage_licence_input" value="'.$licence[2].'" />';
					echo '<input type="submit" class="button-primary" value="'.__('Update', WPS2_TEXT_DOMAIN).'" />';			
					echo '<input type="checkbox" style="margin-left:12px" name="wps_manage_licence_clear" />'.__('Clear', WPS2_TEXT_DOMAIN);
				echo '</div>';
			echo '</form>';
		else:
			echo '<p>'.__('The licence is only required if you have the WP Symposium Extensions plugin installed and activated.', WPS2_TEXT_DOMAIN).'</p>';
			echo '<p>'.sprintf(__('To see all the extra features provided by the Extensions Plugin, visit <a href="%s" target="_blank">www.wpsymposiumpro.com</a>', WPS2_TEXT_DOMAIN), 'http://www.wpsymposiumpro.com/licenses/').'</p>';
		endif;		

	echo '</div>';	  	

}

function wpspro_custom_css() {

	// React to POSTed information
	if (isset($_POST['wpspro_update_css'])):

		update_option('wpspro_custom_css', $_POST['wpspro_custom_css']);

		// Re-act to any more options?
		do_action( 'wps_admin_custom_css_form_save_hook', $_POST );

	endif;
	

  	echo '<div class="wrap">';
        	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Custom CSS', WPS2_TEXT_DOMAIN).'</h2>';

	  	echo __('To over-ride theme styles, you may need to add !important to styles.', WPS2_TEXT_DOMAIN);
	  	?>
		<form action="" method="POST">

			<input type="hidden" name="wpspro_update_css" value="yes" />

			<table class="form-table">

				<tr><td colspan="2">

					<textarea name="wpspro_custom_css" id="wpspro_custom_css" style="width:100%; height:500px"><?php echo stripslashes(get_option('wpspro_custom_css')); ?></textarea>

				</td></tr>

				<?php 
				// Any more options?
				do_action( 'wps_admin_custom_css_form_hook' );
				?>

			</table> 
			
			<p style="margin-left:6px"> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Custom CSS', WPS2_TEXT_DOMAIN); ?>" /> 
			</p> 
			
		</form> 
		<?php

	echo '</div>';	  	

}


function __wps_pro_uninstall_delete () {
    global $wpdb;

    // delete shortcode options
    $sql = "DELETE FROM ".$wpdb->prefix."options WHERE option_name like 'wps_shortcode_options%'";
    echo __('Removing shortcode options', WPS2_TEXT_DOMAIN).'... '; 
    $wpdb->query($sql);
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
    // delete other options
    $sql = "DELETE FROM ".$wpdb->prefix."options WHERE option_name like 'wps_%'";
    echo __('Removing application options', WPS2_TEXT_DOMAIN).'... '; 
    $wpdb->query($sql);
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
    // delete user meta data
    echo __('Removing user meta', WPS2_TEXT_DOMAIN).'... ';    
    $wpdb->query("DELETE FROM ".$wpdb->base_prefix."usermeta WHERE meta_key like 'wps_%'");
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
	// removing custom posts (core)
    $sql = "DELETE FROM ".$wpdb->prefix."posts WHERE post_type = 'wps_activity' OR post_type = 'wps_alerts' OR post_type = 'wps_forum_post' OR post_type = 'wps_friendship'";
    echo __('Removing core custom post types', WPS2_TEXT_DOMAIN).'... '; 
    $wpdb->query($sql);
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
	// removing custom posts (extensions)
    $sql = "DELETE FROM ".$wpdb->prefix."posts WHERE post_type = 'wps_calendar' OR post_type = 'wps_event' OR post_type = 'wps_crowd' OR post_type = 'wps_extension' OR post_type = 'wps_forum_extension' OR post_type = 'wps_forum_subs' OR post_type = 'wps_subs' OR post_type = 'wps_gallery' OR post_type = 'wps_group_members' OR post_type = 'wps_group' OR post_type = 'wps_lounge' OR post_type = 'wps_mail' OR post_type = 'wps_reward' OR post_type = 'wps_rewards'";
    echo __('Removing additional custom post types', WPS2_TEXT_DOMAIN).'... '; 
    $wpdb->query($sql);
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
    // clear schedules
    echo __('Removing WordPress schedule', WPS2_TEXT_DOMAIN).'... ';    
    wp_clear_scheduled_hook( 'wps_symposium_pro_alerts_hook' );
	echo __('ok', WPS2_TEXT_DOMAIN).'<br />';
}

function __wps_pro_uninstall_rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") __wps_pro_uninstall_rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
} 
?>