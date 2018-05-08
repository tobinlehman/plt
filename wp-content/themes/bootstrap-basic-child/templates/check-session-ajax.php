<?php

/* Template Name: Check-Session-Ajax */

session_start();
if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id']))
    echo "true";
else
    echo "false";