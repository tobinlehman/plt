<?php

/* Template Name: check-session */

session_start(); // Let this page to access session globals

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id']))
    echo $_SESSION['user_id'];
else 
    echo "Session is not alive";