<?php
/**
* Template Name: Page - Custom Login Page
 * 
 * @package bootstrap-basic
 */

require(get_template_directory()."/inc/global-variables.inc.php");

$referer = $_SERVER['HTTP_REFERER'];
$uri = str_replace($plt_base_url, "", $referer);
$redirect_url = $plt_login_url;


if($uri == "/greenschools/student-investigations/")
    $redirect_url = $plt_login_url."/?target_page=green_school_login";
else if($uri == "/greenschools/early-childhood/")
    $redirect_url = $plt_login_url."/?target_page=green_school_login";
else if($referer == $plt_base_url)
    $redirect_url = $plt_login_url;
else if($uri == "/curriculum/environmental-education-for-early-childhood/")
    $redirect_url = $plt_login_url."/?target_page=plt_resource";
else
    $redirect_url = $plt_login_url;

header("Location: $redirect_url");