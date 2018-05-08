<?php
/*
Plugin Name: Insert or Embed Articulate Content into WordPress Trial
Plugin URI: http://www.elearningfreak.com/presenter/insert-or-embed-articulate-content-into-wordpress-plugin-premium/ ?
Description: Quickly embed or insert Articulate content into a post or page.  Do you need to upload content created in other apps like Captivate, Lectora, Camtasia, iSpring, Elucidat, Gomo, Obisidian Black, MindManager, or any other tool?  The premium plugin now supports them all.  Learn more at www.elearningfreak.com
Version: 4.290
Author: Brian Batt
Author URI: http://www.elearningfreak.com
*/ 
		define ( 'WP_QUIZ_EMBEDER_PLUGIN_DIR_BASENAME',plugin_basename( __FILE__ )); 
		define ( 'WP_QUIZ_EMBEDER_PLUGIN_DIR', dirname(__FILE__)); // Plugin Directory
		define ( 'WP_QUIZ_EMBEDER_PLUGIN_URL', plugin_dir_url(__FILE__)); // Plugin URL (for http requests)
global $wpdb;
require_once("settings_file.php");
require_once("functions.php");
include_once(WP_QUIZ_EMBEDER_PLUGIN_DIR."/include/shortcode.php");
register_activation_hook(__FILE__,'quiz_embeder_install'); 
/*add_action( 'admin_notices', 'quiz_embeder_banner');*/
register_deactivation_hook( __FILE__, 'quiz_embeder_remove' );
// Create a helper function for easy SDK access.
function articulate_fs() {
    global $articulate_fs;

    if ( ! isset( $articulate_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $articulate_fs = fs_dynamic_init( array(
            'id'                  => '1159',
            'slug'                => 'insert-or-embed-articulate-content-into-wordpress',
            'type'                => 'plugin',
            'public_key'          => 'pk_33392c26e487a56795b740ebd9594',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'first-path'     => 'plugins.php',
                'account'        => false,
                'contact'        => false,
                'support'        => false,
            ),
        ) );
    }

    return $articulate_fs;
}

// Init Freemius.
articulate_fs();
// Signal that SDK was initiated.
do_action( 'articulate_fs_loaded' );
function quiz_embeder_count(){
$count = 2;
return apply_filters('quiz_embeder_count', $count);
}
function quiz_embeder_install() {
@mkdir(getUploadsPath());
@file_put_contents(getUploadsPath()."index.html","");
}
function quiz_embeder_remove() {
$qz_upload_path=getUploadsPath();
if(file_exists($qz_upload_path."/.htaccess")){unlink($qz_upload_path."/.htaccess");}
}
add_action( 'wp_ajax_quiz_upload', 'wp_ajax_quiz_upload' );
add_action( 'wp_ajax_del_dir', 'wp_ajax_del_dir' );
add_action( 'wp_ajax_rename_dir', 'wp_ajax_rename_dir');
function wp_myplugin_media_button() {
$wp_myplugin_media_button_image = getPluginUrl().'quiz.png';
$siteurl = get_admin_url();
echo '<a href="'.$siteurl.'media-upload.php?type=articulate-upload&TB_iframe=true&tab=articulate-upload" class="thickbox">
<img src="'.$wp_myplugin_media_button_image.'" width=15 height=15 /></a>';
}
function media_upload_quiz_form()
{	
wp_enqueue_style('materialize-css', WP_QUIZ_EMBEDER_PLUGIN_URL.'css/materialize.css');
wp_enqueue_script('materializejs', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/materialize.js' );
wp_enqueue_script('jshelpers', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/jshelpers.js' );
print_tabs();
echo '<div class="wrap" style="margin-left:20px;  margin-bottom:50px;">';
echo '<div id="icon-upload" class="icon32"><br></div><h2 class="header">Upload File</h2>';
print_upload();
echo "</div>";
}
function media_upload_quiz_content()
{
wp_enqueue_style('materialize-css', WP_QUIZ_EMBEDER_PLUGIN_URL.'css/materialize.css');
wp_enqueue_script('materializejs', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/materialize.js' );
wp_enqueue_script('jshelpers', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/jshelpers.js' );
print_tabs();
echo '<div class="wrap" style="margin-left:20px;  margin-bottom:50px;">';
echo '<div id="icon-upload" class="icon32"><br></div><h2 class="header">Content Library</h2>';
printInsertForm();
echo "</div>";
}
function media_upload_quiz()
{	
wp_enqueue_style('materialize-css', WP_QUIZ_EMBEDER_PLUGIN_URL.'css/materialize.css');
wp_enqueue_script('materializejs', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/materialize.js' );
wp_enqueue_script('jshelpers', WP_QUIZ_EMBEDER_PLUGIN_URL.'js/jshelpers.js' );
wp_iframe( "media_upload_quiz_content" );
}
function media_upload_upload()
{
if ( isset( $_REQUEST[ 'tab' ] ) && strstr( $_REQUEST[ 'tab' ], 'articulate-quiz') ) {
wp_iframe( "media_upload_quiz_content" );
}
else
{
wp_iframe( "media_upload_quiz_form" );
}
}
function print_tabs()
{
function quiz_tabs($tabs) 
{
$newtab1 = array('articulate-upload' => 'Upload File');
$newtab2 = array('articulate-quiz' => 'Content Library');
return array_merge($newtab1,$newtab2);
}
add_filter('media_upload_tabs', 'quiz_tabs');
media_upload_header();
}
if ( ! function_exists ( 'quiz_embeder_register_plugin_links' ) ) {
function quiz_embeder_register_plugin_links( $links, $file ) {
$base = plugin_basename(__FILE__);
if ( $file == $base ) {
if ( ! is_network_admin() )
$links[] = '<a href="https://www.youtube.com/watch?v=AwcIsxpkvM4" target="_blank">' . __( 'How to Use','quiz_embeder' ) . '</a>';
$links[] = '<a href="https://www.elearningfreak.com/presenter/insert-or-embed-articulate-content-into-wordpress-plugin-premium/">' . __( 'Upgrade to Premium','quiz_embeder' ) . '</a>';
$links[] = '<a href="https://www.elearningfreak.com/contact-us/" target="_blank">' . __( 'Support','quiz_embeder' ) . '</a>';
}
return $links;
}
}
add_action('media_upload_articulate-upload','media_upload_upload');
add_action('media_upload_articulate-quiz','media_upload_quiz');
add_action( 'media_buttons', 'wp_myplugin_media_button',100);
add_filter( 'plugin_row_meta', 'quiz_embeder_register_plugin_links', 10, 2 );
function quiz_embeder_enqueue_script() {
wp_enqueue_script('jquery');
}    
add_action('wp_enqueue_scripts', 'quiz_embeder_enqueue_script');
include_once(WP_QUIZ_EMBEDER_PLUGIN_DIR."/admin_page.php");
?>