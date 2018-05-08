<?php 
	$favicon = IMG . '/favicon.png';
	$touch_icon = IMG . '/icons/apple-touch-icon-152x152-precomposed.png';
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	require(dirname(__FILE__)."/templates/inc/check-session.inc.php");
	require(dirname(__FILE__)."/templates/inc/validate-user.inc.php");
	require(dirname(__FILE__)."/templates/inc/global-variables.inc.php");

	if(get_current_user_auth_type() == "none")
	    header("Location: $single_signin_url");

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>;?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">

	<!-- mobile specific metas -->
	<meta name="viewport" content = "width = device-width, initial-scale = 1, minimum-scale = 1, maximum-scale = 1" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	
	<title><?php bloginfo('name'); ?> | <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>

	<!-- favicons and apple icons -->
	<link rel="shortcut icon" href="<?php echo $favicon; ?>">
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?php  echo $touch_icon; ?>">

	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel='stylesheet'  href='<?php echo ROOT; ?>/style.css' type='text/css' media='all' />
	<link rel='stylesheet'  href='<?php echo CSS; ?>/ieEdge.css' type='text/css' media='all' />
	<link rel='stylesheet'  href='<?php echo CSS; ?>/ie11.css' type='text/css' media='all' />
	<link rel='stylesheet'  href='<?php echo CSS; ?>/ie10.css' type='text/css' media='all' />
	<!--[if lt IE 10]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<link rel='stylesheet'  href='<?php echo CSS; ?>/ie9.css' type='text/css' media='all' />
	<![endif]-->

	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<link rel='stylesheet'  href='<?php echo CSS; ?>/ie8.css' type='text/css' media='all' />
	<![endif]-->


	<?php wp_head(); ?>
	<script type='text/javascript'>
	(function (d, t) {
	  var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
	  bh.type = 'text/javascript';
	  bh.src = 'https://www.bugherd.com/sidebarv2.js?apikey=9sdh6ni3xo7vo127rceuvq';
	  s.parentNode.insertBefore(bh, s);
	  })(document, 'script');
	</script>

</head>
<body <?php body_class(); ?>>
<?php
	get_template_part('templates/header', 'member');
?> 
<div class="container header">
	<div class="row top-header-row">
		<div class="col-sm-3 col-md-3">
			<h3>
				<a class="logo" href="<?php echo get_option('home'); ?>/">
					<span style="display:none;">
						<?php bloginfo('name'); ?>
					</span>	
					<img src="<?php echo IMG; ?>/logo@2x-01.png" />
				</a>
			</h3>
		</div>
		<div class="col-sm-9 col-md-9 no-p-m">
			<h1 class="pull-right textalign-right site-title">
				<a href="<?php echo SITE; ?>">Coordinators' Corner</a>
			</h1>
			<div class="nav pull-left">
				<div class="btn">
					<i class="glyphicon glyphicon-menu-hamburger
" aria-hidden="true"></i>
					<span class="sr-only">Menu</span>
				</div>

				
			</div>
		</div>
	</div>
	<div class="row bottom-header-row">
		<?php wp_nav_menu( array('theme_location'  => 'main-menu')); ?>
		<div class="menu-underline" style="height:5px;border-bottom: 5px solid #7C439A;width: 99.75%;"></div>
	</div>
</div>	
