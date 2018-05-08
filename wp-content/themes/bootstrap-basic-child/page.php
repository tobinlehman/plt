<?php
/**
 * Template for displaying pages
 * 
 * @package bootstrap-basic
 */

ob_start();
get_header();
require(get_template_directory()."/inc/global-variables.inc.php");
require_once(get_template_directory()."/inc/check-session.php");

$request_uri = $_SERVER['REQUEST_URI'];
$main_column_size = bootstrapBasicGetMainColumnSize();

if(is_session_exists() || is_user_logged_in()){
    if($request_uri == "/register/become-a-greenschool/"){
        if(is_user_green_school_member())
            if($_SESSION['green_school_member'] == "true")
                $full_url = $green_school_landing_page;
            else
                $full_url = $plt_login_url."/?target_page=green_school_register";
        else
            $full_url = $plt_login_url."/?target_page=green_school_register";
    }else if ($request_uri == "/register/access-plt-resources/"){
        $full_url = $plt_resource_landing_page;
    }else if ($request_uri == "/register/access-plt-resources/?target_page=grant_register"){
        $full_url = $plt_login_url."/?target_page=grant_register";
    }else if($request_uri == "/resources/greenworks-grants/apply-for-a-grant/"){
        $full_url = $plt_login_url."?target_page=apply_grant";
    }
}else{
    if($request_uri == "/register/access-plt-resources/")
        $full_url = $plt_login_url."/?target_page=plt_resource";
    else if($request_uri == "/register/access-plt-resources/?target_page=grant_register")
        $full_url = $plt_login_url."/?target_page=apply_grant";
    else if($request_uri == "/register/become-a-greenschool/")
        $full_url = $plt_login_url."/?target_page=green_school_register";
    else if($request_uri == "/welcome-plt-resources/")
        $full_url = $plt_login_url."/?target_page=plt_resource";
    else if($request_uri == "/resources/greenworks-grants/apply-for-a-grant/")
        $full_url = $plt_login_url;
}
?>

<? if (!(empty($full_url))): ?>
    <? header("Location: $full_url");exit(); ?>
<? else: ?>
<div class="col-md-<?php echo $main_column_size; ?> content-area" id="main-column">
    <main id="main" class="site-main" role="main">
        <?php 
        while (have_posts()) {
            the_post();
            get_template_part('content', 'page');

            echo "\n\n";

            // If comments are open or we have at least one comment, load up the comment template
            if (comments_open() || '0' != get_comments_number()) {
                comments_template();
            }

            echo "\n\n";

        } //endwhile;
        ?> 
    </main>
    </div>
<? endif ?>

<?php get_footer(); ?> 