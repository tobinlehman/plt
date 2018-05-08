<?php

/* Template Name: Auto login */
//wp_logout();
require(dirname(__FILE__)."/inc/check-session.inc.php");
require(dirname(__FILE__)."/inc/validate-user.inc.php");
require(dirname(__FILE__)."/inc/global-variables.inc.php");


if(get_current_user_auth_type() == "plt-auth") {
    auto_login();
}else {
    auto_login();
}

function auto_login(){
    global $cc_forum_url;
    $username = $_REQUEST['username'];
    if(username_exists($username) != "" && get_wp_user_data($username)->user_status == 0) {
        $user = get_user_by('login', $username);
        if(!is_wp_error($user)){
            wp_clear_auth_cookie();
            wp_set_current_user ( $user->ID );
            wp_set_auth_cookie  ( $user->ID );
            wp_safe_redirect($cc_forum_url);
        }
    }else{
        wp_safe_redirect($cc_forum_url);
    }
}