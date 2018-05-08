<?php

session_start();

function is_session_exists(){
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_user_green_school_member() {
    return isset($_SESSION['green_school_member']) && !empty($_SESSION['green_school_member']);
}