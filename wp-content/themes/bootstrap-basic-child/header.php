<?php
/**
 * The theme header
 * 
 * @package bootstrap-basic
 */
require(get_template_directory()."/inc/check-session.php");
require(get_template_directory()."/inc/global-variables.inc.php");
?>


<!DOCTYPE html>
<!--[if lt IE 7]>  <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>     <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>     <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php wp_title('|', true, 'right'); ?></title>
		<meta name="viewport" content="width=device-width">

		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
		<link href="//fonts.googleapis.com/css?family=Open+Sans:400,700|Oswald:400,700" rel="stylesheet" type="text/css">

		<style>
			
		</style>

		
		<!--wordpress head-->
		<?php wp_head(); ?>
		<script type="text/javascript">
		var _ss = _ss || [];
		_ss.push(['_setDomain', 'https://koi-3QNBEY4NF8.marketingautomation.services/net']);
		_ss.push(['_setAccount', 'KOI-3WT2V19482']);
		_ss.push(['_trackPageView']);
		(function() {
		    var ss = document.createElement('script');
		    ss.type = 'text/javascript'; ss.async = true;
		 
		    ss.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'koi-3QNBEY4NF8.marketingautomation.services/client/ss.js?ver=1.1.1';
		    var scr = document.getElementsByTagName('script')[0];
		    scr.parentNode.insertBefore(ss, scr);
		})();
		</script>
	</head>
	<body <?php body_class(); ?>>
		<!--[if lt IE 8]>
			<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->
		
		<div class="topbar">
			<?php 
                $url = '/login';
                $text = 'Login';
            
                if ( !is_user_logged_in() && !is_session_exists() ) {
                    $url = $plt_login_url;
                    $text = 'Login';
                }
                else{
                    if( is_session_exists() || is_user_logged_in() )
                        $url = $single_signout_url."/?http_referer=plt";
                    $text = 'Logout';
                }
                echo ('<div class="widget"><ul class="memberlink">');
                echo "<li><a href=".$url." id='link'>$text</a></li>";
                echo "<li><a href='$plt_login_url/?target_page=account_home' id='account_home' style='display:none'>Account Home</a></li>";
                echo ("</ul></div>");
				dynamic_sidebar('top-bar');
			?>
			
			
		</div><!-- /.topbar -->
		
		<div class="container page-container">
			<?php do_action('before'); ?> 
			<header role="banner">
				<div class="row row-with-vspace site-branding">
					<div class="col-md-2 col-sm-12 site-title">
						<?php dynamic_sidebar('logo'); ?>
						<div class="sr-only">
							<a href="#content" title="<?php esc_attr_e('Skip to content', 'bootstrap-basic'); ?>"><?php _e('Skip to content', 'bootstrap-basic'); ?></a>
						</div> 
					</div>
					<div class="col-md-10 col-sm-12 page-header-top-right">
						
						<nav class="navbar navbar-default" role="navigation">
							<div class="container">
							<div class="navbar-header">
								<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-primary-collapse">
									<span class="sr-only"><?php _e('Toggle navigation', 'bootstrap-basic'); ?></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
							</div>
							
							<div class="collapse navbar-collapse navbar-primary-collapse">
								<?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav navbar-nav', 'walker' => new BootstrapBasicMyWalkerNavMenu())); ?> 
								<?php dynamic_sidebar('navbar-right');  ?>
								
								
							</div><!--.navbar-collapse-->
						</div>
						</nav>
						
						<div class="clearfix"></div>
						
					</div>
				</div><!--.site-branding-->
				
			</header>
		</div><!-- /.container -->
			
			
		<div id="content" class="row row-with-vspace site-content">
			<div class="container-ed">