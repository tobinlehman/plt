<?php
ob_start();

require(dirname(__FILE__)."/inc/global-variables.inc.php");

add_filter('show_admin_bar', '__return_false');

$current_user_auth_type = get_current_user_auth_type();
$is_subscriber = get_active_user_role(get_active_username()) == "subscriber" ? true : false;
?>
<?php if(!$is_subscriber){ 
    add_filter('show_admin_bar', '__return_true'); ?>
<?php } ?>

<div class="container-full header-member no-p-m">
	<div class="row no-p-m">
		<div class="container no-p-m-auto">
			<ul class="pull-right list-horizontal">
				<li><a href="<?php echo $auto_login."/?username=".get_active_username(); ?>">Forums</a></li>
                <?php if($current_user_auth_type != 'none'){ ?>
                    <?php if($current_user_auth_type != 'wp-auth'){ ?>
                        <li><a href="<?php echo $plt_login_url.'/?target_page=account_home'; ?>">My Account</a></li>
                    <?php } ?> 
                
                    <?php if(is_bbpress()){ ?>
                    	<li><a href="<?php echo bp_loggedin_user_domain(); ?>">My Profile</a></li>
                    <?php } ?>
    
                    <li><a href="<?php echo $single_signout_url.'/?http_referer=cc-plt';  ?>">Log Out</a></li>
                    
                <?php }else{ ?>
                    <li><a href="<?php echo $plt_login_url; ?>">Log In</a></li>
                <?php } ?>
				<li>
					<a href="search" class="search-btn">
						<img src="<?php echo IMG; ?>/search-white@2x.png">
					</a>
					<div class="search-form pull-right display-none">
							<?php get_search_form(); ?>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>
