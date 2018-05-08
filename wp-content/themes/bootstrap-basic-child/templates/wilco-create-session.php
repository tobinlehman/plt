<?php
/* Template Name: Wilco - Create - Session */


require_once(get_template_directory()."/inc/global-variables.inc.php");
session_start();

if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])){
    if(!isset($_SESSION['user_id']) && empty($_SESSION['user_id'])){
        $_SESSION['user_id'] = $_REQUEST['user_id'];
    }
}
 
if(isset($_REQUEST['green_school_member']) && !empty($_REQUEST['green_school_member'])){
    if(!isset($_SESSION['green_school_member']) && empty($_SESSION['green_school_member'])){
        $_SESSION['green_school_member'] = $_REQUEST['green_school_member'];
    }
}

if(isset($_REQUEST['request_page'])){
    if($_REQUEST['request_page'] == "green_school"){
        header("Location: $green_school_landing_page");
    }
    else if ($_REQUEST['request_page'] == "plt_resources") {
        header("Location: $plt_resource_landing_page");
    }
}