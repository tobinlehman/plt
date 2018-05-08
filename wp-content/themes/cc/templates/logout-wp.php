<?php

/* Template Name: Logout WP */
wp_clear_auth_cookie();
wp_logout();
wp_redirect("http://cc.devsite.work/auto-login");