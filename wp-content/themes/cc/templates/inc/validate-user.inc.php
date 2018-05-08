<?php

/**
*
* Make sure that check-session.inc.php file is included just before.
*
*/

/**
* Get the type of authentication of active user.
*
* @param  void
* @return string $auth_mode
*/
function get_current_user_auth_type() {
    $auth_mode = "none";
    if(is_session_exists() && !is_user_logged_in())
        $auth_mode = "plt-auth";
    else if(is_user_logged_in() && !is_session_exists())
        $auth_mode = "wp-auth"; 
    else if(is_user_logged_in() && is_session_exists())
        $auth_mode = "dual-auth";
    
    return $auth_mode;
}

/**
* Get the role of active user.
*
* @param  void
* @return string $user_role
*/
function get_active_user_role($username) {
    $wp_user_id = get_wp_user_data($username)->ID;
    $user = new WP_User($wp_user_id);
    $user_role = $user->roles[0];
    
    if(empty($user_role)){ $user_role = NULL; }
    
    return $user_role;
}

/**
* Get active username.
*
* @param  string $auth_type (default empty)
* @return string $active_username
*/
function get_active_username($auth_type = ""){
    $auth_type = ($auth_type == "" ? get_current_user_auth_type() : $auth_type );
    $active_username = "";
    switch($auth_type){
        case "plt-auth":
            $active_username = $_SESSION['username'];
            break;
        case "wp-auth":
            $active_username = wp_get_current_user()->user_login;
            break;
        case "dual-auth":
            $active_username = $_SESSION['username'];
            break;
    }
    return $active_username;
}

/**
* Get user data.
*
* @param  string $username
* @return (anonymous) array
*/
function get_wp_user_data($username){
    return get_user_by('login', $username);
}
