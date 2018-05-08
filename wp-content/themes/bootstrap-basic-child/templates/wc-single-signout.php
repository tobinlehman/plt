<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css">
<?php
/* Template Name: Single-Signout */
require(get_template_directory()."/inc/global-variables.inc.php");

$redirect_url = $plt_base_url;
if(isset($_REQUEST['http_referer']) && !empty($_REQUEST['http_referer'])){
    $http_referer = $_REQUEST['http_referer'];
    if($http_referer == "cc-plt")
        $redirect_url = $cc_plt_base_url;
}
?>

<img src="<?php echo $cc_plt_session_out_url; ?>" style="display:none">
<img src="<?php echo $single_signin_url; ?>" style="display:none">
<img src="<?php echo $plt_session_out_url; ?>" style="display:none">

<div class="container">
    <div class="col-xs-12">
        <h3>Please wait. You'll be redirected in couple of seconds...</h3>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
$(window).on('load', function(){
    window.location.href = "<?php echo $redirect_url; ?>";
})
</script>