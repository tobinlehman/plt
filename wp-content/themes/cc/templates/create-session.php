<?php

/* Template Name: Create-Session */

require(dirname(__FILE__).'/inc/global-variables.inc.php');
wp_logout();
session_start();

$request_page = $_REQUEST['request_page'];

if( isset($_REQUEST['username']) && !empty($_REQUEST['username']) ){
    $_SESSION['username'] = $_REQUEST['username'];
}

if ($request_page == "account_home" || $request_page == "account_cc"){
    echo "<a href='$cc_plt_base_url' id='load_at_parent' target='_parent'></a>";
}

?>
<script>
    function loadAtParent(){
        document.getElementById("load_at_parent").click();
    }
    loadAtParent();
</script>