<?php 
//define constants//
define( 'ROOT', get_template_directory_uri() );
define( 'IMG', ROOT . '/img' );
define( 'CSS', ROOT . '/css' );
define( 'JS', ROOT . '/js' );
define( 'BOWER', ROOT . '/bower_components' );
define( 'SITE', get_site_url() );


//THEME SUPPORT//
//add support for different post formats
add_theme_support( 'post-formats', 
  array(
    'gallery', 
    'link',
    'image',
    'quote', 
    'video',
    'audio'
  )
);
// add support for uploading svg files
function mime_types($mimes){
  $mimes['svg'] = 'image/svg+xml';
  $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
  return $mimes;
} 
add_filter( 'upload_mimes', 'mime_types' );
//add support for automatic feed links
add_theme_support( 'automatic-feed-links' );
//add support for post thumbnails
add_theme_support( 'post-thumbnails' );
//add support for menus
add_theme_support('menus');
//wordpress's html5 search form
add_theme_support( 'html5', array( 'search-form' ) );
//register nav menus
register_nav_menus(
  array(
    'main-menu'                => __( 'Main Menu', 'wmi' ),
    'footer-column-1'          => __( 'Footer Column 1', 'wmi' ),
    'footer-column-2'          => __( 'Footer Column 2', 'wmi' ),
    'coordinators-store'       => __( 'Coordinators Store', 'wmi' ),
    'state-reporting-form'     => __( 'State Reporting Form', 'wmi' ),
    'mpi-grants'               => __( 'MPI Grants', 'wmi' ),
    'coordinators-conference'  => __( 'Coordinators Conference', 'wmi' ),
    'order-guides'             => __( 'Order Guides', 'wmi' ),
  )
);
//make theme available for translation//
$lang_dir = ROOT . '/languages';
load_theme_textdomain( 'wmi', $lang_dir );


//js//
function load_js() {
  wp_deregister_script('jquery');

  wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js", false, null);
  wp_register_script( 'greensock', BOWER . '/greensock/src/minified/TweenMax.min.js');
  wp_register_script( 'placeholder', BOWER . '/jquery-placeholder/jquery.placeholder.min.js');
  wp_register_script( 'main', JS . '/main.min.js');


  wp_enqueue_script('jquery');
  // wp_enqueue_script('greensock');
  // wp_enqueue_script('placeholder');
  wp_enqueue_script('main');
}
add_action( 'wp_enqueue_scripts', 'load_js' );

/**
 * Load Enqueued Scripts in the Footer
 *
 * Automatically move JavaScript code to page footer, speeding up page loading time.
 */
function footer_enqueue_scripts() {
   remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);
    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5);
}
// add_action('after_setup_theme', 'footer_enqueue_scripts');

function my_bbp_forum_search_form(){
   ?>
   <div class="bbp-search-form">

       <?php bbp_get_template_part( 'form', 'search' ); ?>

   </div>
   <?php
}
add_action( 'bbp_template_before_single_forum', 'my_bbp_forum_search_form' );
//WIDGETS//
function create_widget($name, $id, $description) {
	register_sidebar(array(
		'name' => __( $name , 'uikit' ),	 
		'id' => $id, 
		'description' => __( $description ),
		'before_widget' => ' ',
		'after_widget' => ' ',
		'before_title' => '<h5>',
		'after_title' => '</h5>'
	));
}
create_widget('Twitter', 'twitter', 'This is the twitter widget area');
create_widget('Newsletter', 'newsletter', 'This is the newsletter widget area');

// pretty links
function add_query_vars($vars){
    $vars[] = "prop_type";
    return $vars;
}
add_filter('query_vars', 'add_query_vars');
function add_rewrite_rules($aRules){
    $aNewRules = array('properties/([^/]+)/?$' => 'index.php?page_id=10&prop_type=$matches[1]');
    $aRules = $aNewRules + $aRules;
    return $aRules;
}
add_filter('rewrite_rules_array', 'add_rewrite_rules');

//CUSTOM//
//custom featured image sizes
add_image_size('featuredImageNavList', 70, 70, true);

//excerpt limit//
function get_excerpt($count){
  $permalink = get_permalink($post->ID);
  $excerpt = get_the_content();
  $excerpt = strip_tags($excerpt);
  $excerpt = substr($excerpt, 0, $count);
  $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
  $excerpt = $excerpt.'... <a href="'.$permalink.'">read more</a>';
  return $excerpt;
}

// read more 
function custom_excerpt_more($post) {
  return '<a href="'.get_permalink($post->ID).'" class="read-more"></br>'.'Continue Reading'.'</a>';
}
add_filter('excerpt_more', 'custom_excerpt_more');


//breadcrumbs//
function the_breadcrumbs($seperator) {
    global $post;
    echo '<ul id="breadcrumbs">';
    
    echo '<li><a href="';
    echo get_option('home');
    echo '">';
    echo 'Home';
    echo "</a></li><li class='separator'> $seperator </li>";
    if (is_single() && has_category('blog', $post)) {
      echo '<li><a href="'.SITE.'/about/blog">Blog</a></li>';
      if (is_single()) {
          echo "<li class='separator'> $seperator </li><li><strong>";
          the_title();
          echo '</strong></li>';
      }
    }
    elseif ( is_singular('team_members') ) {
      echo '<li><a href="'.SITE.'/team">Team</a></li>';
      if (is_singular('team_members')) {
          echo "<li class='separator'> $seperator </li><li><strong>";
            the_title();
          echo '</strong></li>';
      }
    }
    elseif ( is_singular('locations') ) {
      echo '<li><a href="'.SITE.'/locations>Locations</a></li>';
      
        echo "<li class='separator'> $seperator </li><li><strong>";
          the_title();
        echo '</strong></li>';
      
    } 
    elseif ( is_attachment() ) {
      echo '<li><strong>Attachments</strong></li>';
      
        echo "<li class='separator'> $seperator </li><li><strong>";
          the_title();
        echo '</strong></li>';
      
    } 
    elseif ( is_singular('positions') ) {
      echo '<li><a href="'.SITE.'/available-positions">Available Positions</a></li>';
      
        echo "<li class='separator'> $seperator </li><li><strong>";
          the_title();
        echo '</strong></li>';
      
    }
    elseif(!is_category('blog') && is_single()) {
      echo '<li>';
      the_category(" </li><li class='separator'> $seperator </li><li> ");
      if (is_single()) {
          echo "<li class='separator'> $seperator </li><li><strong>";
          the_title();
          echo '</strong></li>';
      }
    }
     elseif (is_page()) {
        if($post->post_parent){
            $anc = get_post_ancestors( $post->ID );
            $title = get_the_title();
            foreach ( $anc as $ancestor ) {
                $output = '<li><a href="'.get_permalink($ancestor).'" title="'.get_the_title($ancestor).'">'.get_the_title($ancestor)."</a></li> <li class='separator'> $seperator </li>";
            }
            echo $output;
            echo '<strong title="'.$title.'"> '.$title.'</strong>';
        } else {
            echo '<li><strong> '.get_the_title().'</strong></li>';
        }
    } elseif(is_home()){
      echo '<li><strong title="blog"> Blog </strong></li>';
    }
    elseif (is_tag()) {single_tag_title();}
    elseif (is_day()) {echo"<li>Archive for "; the_time('F jS, Y'); echo'</li>';}
    elseif (is_month()) {echo"<li>Archive for "; the_time('F, Y'); echo'</li>';}
    elseif (is_year()) {echo"<li>Archive for "; the_time('Y'); echo'</li>';}
    elseif (is_author()) {echo"<li>Author Archive"; echo'</li>';}
    elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {echo "<li>Blog Archives"; echo'</li>';}
    elseif (is_search()) {echo"<li>Search Results"; echo'</li>';}
    echo '</ul>';
}


//get categories related to current post in the loop 
function the_category_unlinked($separator = ' ') {
    $categories = (array) get_the_category();
    
    $thelist = '';
    foreach($categories as $category) {    // concate
        $thelist .= $separator . $category->category_nicename;}
  
    echo $thelist;
}

/* pagination */
function wpbeginner_numeric_posts_nav() {
    if( is_singular() )
        return;
    global $wp_query;
    /** Stop execution if there's only 1 page */
    if( $wp_query->max_num_pages <= 1 )
        return;
    $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
    $max   = intval( $wp_query->max_num_pages );
    /** Add current page to the array */
    if ( $paged >= 1 )
        $links[] = $paged;
    /** Add the pages around the current page to the array */
    if ( $paged >= 3 ) {
        $links[] = $paged - 1;
        $links[] = $paged - 2;
    }
    if ( ( $paged + 2 ) <= $max ) {
        $links[] = $paged + 2;
        $links[] = $paged + 1;
    }
    echo '<div class="navigation"><ul>' . "\n";
    /** Previous Post Link */
    if ( get_previous_posts_link() )
        printf( '<li>%s</li>' . "\n", get_previous_posts_link() );
    /** Link to first page, plus ellipses if necessary */
    if ( ! in_array( 1, $links ) ) {
        $class = 1 == $paged ? ' class="active"' : '';

        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

        if ( ! in_array( 2, $links ) )
            echo '<li>…</li>';
    }
    /** Link to current page, plus 2 pages in either direction if necessary */
    sort( $links );
    foreach ( (array) $links as $link ) {
        $class = $paged == $link ? ' class="active"' : '';
        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
    }
    /** Link to last page, plus ellipses if necessary */
    if ( ! in_array( $max, $links ) ) {
        if ( ! in_array( $max - 1, $links ) )
            echo '<li>…</li>' . "\n";

        $class = $paged == $max ? ' class="active"' : '';
        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
    }
    /** Next Post Link */
    if ( get_next_posts_link() )
        printf( '<li>%s</li>' . "\n", get_next_posts_link() );

    echo '</ul></div>' . "\n";
}

//wrap string with tags
function ext_str_ireplace($findme, $replacewith, $text) { 
    // Replaces $findme in $subject with $replacewith 
    // Ignores the case and do keep the original capitalization by using $1 in $replacewith 
    // Required: PHP 5 

    $rest = $text; 

    $result = ''; 

    while (mb_stripos($rest, $findme) !== false) { 
      $pos = mb_stripos($rest, $findme); 

      // Remove the wanted string from $rest and append it to $result 
      $result .= mb_substr($rest, 0, $pos); 
      $rest = mb_substr($rest, $pos, mb_strlen($rest)-$pos); 

      // Remove the wanted string from $rest and place it correctly into $result 
      $result .= mb_ereg_replace('$1', mb_substr($rest, 0, mb_strlen($findme)), $replacewith); 
      $rest = mb_substr($rest, mb_strlen($findme), mb_strlen($rest)-mb_strlen($findme)); 
    } 

    // After the last match, append the rest 
    $result .= $rest; 

    return $result; 
} 

// detect if device is mobile
$mobile = array('iPhone', 'Android', 'webOS', 'BlackBerry', 'iPod'); //etc add more
function isMobile(){
  global $mobile;
  foreach($mobile as $agent){

     if ( strpos($_SERVER['HTTP_USER_AGENT'], $agent) ){
         //mobile detected
         //or return its name, do it the way you like
         return true;
     }
  }
}

//data uri 
function get_data_uri($file) {

    $contents = file_get_contents($file);
    $base64 = base64_encode($contents);
    $imagetype = exif_imagetype($file);
    $mime = image_type_to_mime_type($imagetype);
  
    return "data:$mime;base64,$base64";
}

function data_uri($file) {
    return get_data_uri($file);
}

//ADMIN VIEW//
// add a favicon for your admin
function custom_login_logo() {
  echo '<style type="text/css">
  body.login.wp-core-ui{
    background-image: url(/wp-content/themes/cc/img/all_the_trees.jpg); 
  }
  .login h1 {
      text-align: center;
      padding: 11px 50px 0;
      background: white;
  }
  .login h1 a { 
    background-image: url('.get_bloginfo('template_directory').'/img/logo@2x.png) !important; 
    -webkit-background-size: 100% !important;
    background-size: 100% !important;
    background-position: center top;
    background-repeat: no-repeat;
    color: #999;
    height: 155px;
    background-color: white;
    margin: 0 auto 0px;
    padding: 0;
    text-decoration: none;
    width: 100%;
    text-indent: -9999px;
    outline: 0;
    overflow: hidden;
    display: block;
  }
  .login form {
    margin-top: 0 !important;
  }
  .login #nav a,
  .login #backtoblog a{
    color: white;
    font-size: 15px;
    text-shadow: 1px 1px 3px black;
  }
  #login .button-primary {
    background: #00A5F4;
  }
  </style>';
}
add_action('login_head', 'custom_login_logo');

//show page ids
// Set columns to be used in the Pages section
function custom_set_pages_columns($columns) {
    return array(
        'cb'      => '<input type="checkbox" />',
        'title'   => __('Title'),
        'page_id' => __('ID'),
        'author'  => __('Author'),
        'date'    => __('Date')
    );
}
function create_staff_post_type() {
  register_post_type( 'staff_members',
    array(
      'labels' => array(
        'name' => __( 'Staff' ),
        'singular_name' => __( 'Staff Member' )
      ),
      'public' => true,
      'menu_position' => 6,
      'supports' => array('title','editor','thumbnail','excerpt','revisions','trackbacks','author','custom-fields', 'page-attributes'),
      'taxonomies' => array('category'),
      'has_archive' => false,
      'rewrite' => array(
        'slug' => 'staff', 
        'with_front' => false
      ) 
    )
  );
}
add_action( 'init', 'create_staff_post_type' );
// Add the ID to the page ID column
function custom_set_pages_columns_page_id($column, $post_id) {
    if ($column == 'page_id') {
        echo $post_id;
    }
}
// Add custom styles to the page ID column
function custom_admin_styling() {
    echo '<style type="text/css">',
         'th#page_id { width:60px; }',
         '</style>';
}
// Add filters and actions
add_filter('manage_edit-page_columns',   'custom_set_pages_columns');
add_action('manage_pages_custom_column', 'custom_set_pages_columns_page_id', 10, 2);
add_action('admin_head',                 'custom_admin_styling');

add_filter( 'manage_posts_columns', 'revealid_add_id_column', 5 );
add_action( 'manage_posts_custom_column', 'revealid_id_column_content', 5, 2 );
function revealid_add_id_column( $columns ) {
$columns['revealid_id'] = 'ID';
return $columns;
}
function revealid_id_column_content( $column, $id ) {
if( 'revealid_id' == $column ) {
echo $id;
}
}
function bbp_enable_visual_editor( $args = array() ) {
    $args['tinymce'] = true;
    return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );
?>