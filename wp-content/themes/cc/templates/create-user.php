<?php

require(dirname(__FILE__)."/inc/check-session.inc.php");
require(dirname(__FILE__)."/inc/validate-user.inc.php");

header("Content-Type: application/json;charset=utf-8");

$request_data = $_GET;

$parsed_data = parse_request_data($request_data);

if($parsed_data["operation"] == "create"){
    if( !username_exists($request_data["username"]) )
        create_wp_user($request_data["username"], $request_data["password"]);
    else if( username_exists($request_data["username"]) )
        toggle_user_status($parsed_data["username"], "active");
}else if($parsed_data["operation"] == "remove"){
    toggle_user_status($parsed_data["username"], "inactive");
}else
    echo send_error_message(array(400, "Requested operation can't be done"));

/**
* Create a new wordpress user, after an user is promoted as coordinator on plt site.
*
* @param  string $username, string $password
* @return void
*/
function create_wp_user($username, $password){
    $user_id = wp_create_user( $username , "", $username );
    $user = new WP_user($user_id);
    $user->add_role('subscriber');
    
    if(is_wp_error($user))
        echo send_error_message( array(400, "Can't create new user") );
    else
        echo send_success_message( array(200, "A new user is created"."--".$password) );
}

/**
* Changes the wordpress user status into inactive who is no longer a coordinator in the plt site.
*
* @param  string $username
* @return void
*/
function toggle_user_status($username, $toggle_status_to){
    
    if(username_exists($username)){
        global $wpdb;
        $user_id = get_user_by('login', $username)->ID;
        $update_status = -1;

        if($toggle_status_to != "none")
            $update_status = $wpdb->update( 
                'wp_users', 
                array('user_status' => ( $toggle_status_to == "active" ? 0:1 )), // Field to change
                array('ID' => $user_id), // Where to change
                array('%d'), 
                array('%d') 
            );

        if($update_status == -1)
            echo send_error_message( array(400, "Can't change user status") );
        else if($update_status == 1)
            echo send_success_message( array(200, "User status is changed" ));
        else if ($update_status == 0)
            echo send_error_message( array(409, "Duplicate request received" ));
    }else
        echo send_error_message( array(404, "No data found" ));
}

/**
* Returns json formatted array to the calling function
*
* @param  array string $arg
* @return (anonymous) json
*/
function send_error_message($arg){
    return json_encode(array("code"=>$arg[0], "message"=>$arg[1]));
}

/**
* Returns json formatted array to the calling function
*
* @param  array string $arg
* @return (anonymous) json
*/
function send_success_message($arg) {
    return json_encode(array("code"=>$arg[0], "message"=>$arg[1]));
}

/**
* Extracts request data and returns as string array to the calling function
*
* @param  array string $arg
* @return array string $parsed_data
*/
function parse_request_data($arg){
    $parsed_data = array();
    if( isset($arg["username"]) && !empty($arg["username"]) )
        $parsed_data["username"] = $arg["username"];
    if ( isset($arg["password"]) && !empty($arg["password"]) )
        $parsed_data["password"] = $arg["password"];
    if ( isset($arg['operation']) && !empty($arg['operation']) )
        $parsed_data["operation"] = $arg["operation"];
    if ( isset($arg['toggle_status_to']) && !empty($arg['toggle_status_to']) )
        $parsed_data["toggle_status_to"] = $arg["toggle_status_to"];
    
    return $parsed_data;
}
