<?php

/* Template Name: Check User Type */

	require(dirname(__FILE__)."/inc/check-session.inc.php");
	require(dirname(__FILE__)."/inc/validate-user.inc.php");

echo "current user type".get_current_user_auth_type();