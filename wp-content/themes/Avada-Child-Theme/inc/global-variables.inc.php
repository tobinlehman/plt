<?php
/* Page commented for modification */
//$prefix = 'http://cc.plt.org/PLTWebsite';
$prefix = "http://192.168.1.50/pltdatatest/";
//$prefix = 'http://54.190.41.232:8080/PLTWebsite';

// Base urls of plt and cc.plt
//$plt_base_url = "http://devsite.work";
$plt_base_url = "http://plt.org";
$cc_plt_base_url = "http://coordinators.plt.org";
$plt_login_url = "http://plt.org/custom-login";

// Single signin app urls
$account_home_url = $prefix;
$single_signin_url = $prefix."/Account/Login";

// Greenschools url
$green_school_register_url = $prefix."/Home/GreenSchool";
$green_school_login_url = $prefix."/Account/GreenSchoollogin";
$green_school_landing_page = $plt_base_url."/green-schools/welcome-to-greenschools";

// Plt resource urls
$plt_resources_login_url = $prefix."/Account/Pltloginresource";
$plt_resource_landing_page = $plt_base_url."/welcome-plt-resources";

// Apply grant url
$apply_for_grant = $prefix."/GreenWorksGrant/CheckGreenworkgrant";

// Session out urls for all the web applications
$plt_session_out_url = $plt_base_url."/clear-session";
$cc_plt_session_out_url = $cc_plt_base_url."/clear-session";
$single_signin_app_url = $prefix."/Account/login";

// Signout url
$single_signout_url = $plt_base_url."/single-sign-out";
