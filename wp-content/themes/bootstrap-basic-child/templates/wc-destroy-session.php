<?php

/* Template Name: Destroy-Session */

session_start(); // Let this page to use session globals
session_destroy(); // Destroys all active session
// wp_logout(); // Logouts current logged in user of WP from same domain