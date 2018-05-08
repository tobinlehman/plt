<?php

function sfccWidgets(){
	register_sidebar(array(
		'name'          => __('Top Bar', 'bootstrap-basic-child'),
		'id'            => 'top-bar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));
	register_sidebar(array(
		'name'          => __('Footer Menu', 'bootstrap-basic-child'),
		'id'            => 'footer-menu',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));
	
	register_sidebar(array(
		'name'          => __('Footer Social Icons', 'bootstrap-basic-child'),
		'id'            => 'footer-social-icons',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));

	register_sidebar(array(
		'name'          => __('Footer left', 'bootstrap-basic-child'),
		'id'            => 'footer-left',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));

	register_sidebar(array(
		'name'          => __('Footer right', 'bootstrap-basic-child'),
		'id'            => 'footer-right',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));

}
add_action('widgets_init', 'sfccWidgets');

//* Enqueue scripts
add_action( 'wp_enqueue_scripts', 'minimum_enqueue_scripts' );


function minimum_enqueue_scripts() {
	
	wp_enqueue_style('sfcc', get_bloginfo( 'stylesheet_directory' ) . '/sfcc.css' , null, 1.1);

	// wp_enqueue_script( 'minimum-responsive-menu', get_bloginfo( 'stylesheet_directory' ) . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0' );
	// wp_enqueue_script( 'eunit-main', get_bloginfo( 'stylesheet_directory' ) . '/js/main.js', array( 'jquery' ), '1.1.0' );

	// wp_enqueue_style( 'dashicons' );
	// wp_enqueue_style( 'minimum-google-fonts', '//fonts.googleapis.com/css?family=Roboto:300,400|Roboto+Slab:300,400', array(), CHILD_THEME_VERSION );

}

?>
<?php function my_login_logo() { ?>

    <style type="text/css">

        .login h1 a {

            background-image: url("http://sfcc.plt.org/wp-content/uploads/sites/3/project_learning_tree_logo_white-1.png") !important;
			width: 300px!important;
			height: 190px !important;
			background-size: cover !important;
			margin: 0px !important;
			padding: 10px !important;
			background: white;

        }

    </style>

<?php }


add_action( 'login_enqueue_scripts', 'my_login_logo' );


function my_login_logo_url() {

    return 'http://sfcc.plt.org';

}


add_filter( 'login_headerurl', 'my_login_logo_url' );


function my_login_logo_url_title() {

    return 'Project Learning Tree';

}


add_filter( 'login_headertitle', 'my_login_logo_url_title' );

?>