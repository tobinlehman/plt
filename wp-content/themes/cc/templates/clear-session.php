<?php

/* Template Name: Clear-Session */

session_start();

// Destroys all the sessions created on this domain.
session_destroy();

//Logs out wordpress users
wp_logout();

wp_set_current_user(0);