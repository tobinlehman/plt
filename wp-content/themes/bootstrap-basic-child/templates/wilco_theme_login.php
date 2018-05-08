<?php

/* Template Name: Wilco Custom Login */

require_once(get_template_directory()."/inc/global-variables.inc.php");
ob_start();
?>

<?php get_header(); ?>

<?php

$iframe_url =  is_session_exists() ? $account_home_url : $single_signin_app_url; // Initiate default iframe url

/**
* $_REQUEST['target_page] reveals 
*
*/
if(isset($_REQUEST['target_page']) && !empty($_REQUEST['target_page'])){
    $request_uri = $_REQUEST['target_page'];
    switch($request_uri){
        case "true":
            $iframe_url = $green_school_landing_url;
            break;
        case "green_school_login":
            $iframe_url = $green_school_login_url;
            break;
        case "plt_resource":
            $iframe_url = $plt_resources_login_url;
            break;
        case "account_home":
            $iframe_url = $account_home_url;
            break;
        case "apply_grant":
            $iframe_url = $apply_for_grant;
            break;
        case "green_school_register":
            $iframe_url = is_session_exists() ? $green_school_register_url : $green_school_login_url;
            break;
        case "grant_register":
            $iframe_url = $apply_for_grant;
            break;
    }
}

echo "<iframe id='iframe' src='$iframe_url' style='height:750px !important;width:100%;border:none;'></iframe>";

?>
</div>
<?php
/**
 * The theme footer
 * 
 * @package bootstrap-basic
 */
wp_footer();

?>
    <footer id="site-footer" role="contentinfo">
        <div class="row site-footer">
            <div class="col-md-10">
                <?php dynamic_sidebar('footer-menu'); ?>
            </div>
        </div><!-- /.site-footer -->

        <div class="row site-footer footer-social-icons">
            <div class="col-md-10">
                <?php dynamic_sidebar('footer-social-icons'); ?>
            </div>
        </div><!-- /.site-footer -->

        <div id="footer-row" class="row site-footer">
            <div class="col-md-4 col-md-offset-1 footer-left">
                <?php dynamic_sidebar('footer-left'); ?>  
            </div>
            <div class="col-md-6 footer-right">
                <?php dynamic_sidebar('footer-right'); ?> 
            </div>
        </div>
    </footer>
<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        var globalFlag = false;
        // Create IE + others compatible event handler
        var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
        var eventer = window[eventMethod];
        var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
        
        /**
        * Changes login link text, scrolls postion to top and expands and shrinks the iframe size
        *
        * @param  {event} e
        * @return void
        */
        eventer(messageEvent,function(e) {
            console.log(e.data);
            var receivedMessage = String(e.data); // Extracts the data that comes from iframe domain to parent window domain

            if(receivedMessage.substr(0,1) == "s"){
                animateIFrame(receivedMessage.substr(1, receivedMessage.length-1));
            }else if(e.data == "clicked"){ // Identifies click event occured on iframe web page
                $('li.menu-item').removeClass('open');
            }else if(e.data == "update"){ // Identifies update button is pressed on iframe web page
                globalFlag = true;
            }else if(e.data == "sessionout"){ 
                $('#link').text('login'); // Changes logout link text
                $('#link').attr('href', '<?php echo $base_login_url; ?>'); // Changes logout link url
                $('#account_home').css('display','none'); // Disables 'account home' link
            }
            else{
                if(!globalFlag){ // Identifies all accordions are open : False
                    var scroolHeight = receivedMessage.substr(1, receivedMessage.length-1);
                    animateIFrame(e.data + 75);
                    animateScrollToTop(0);
                }else{ // All accordions are open : True
                    globalFlag = false;
                    animateIFrame(6000);
                    animateScrollToTop(600);
                }
            }
            
        },false);
    })
    
    /**
    * Sets iFrame height
    *
    * @param  {number} heightValue
    * @return void
    */
    function animateIFrame(heightValue){
        $('#iframe').animate({
            height: heightValue
        }, 50)
    }
    
    /**
    * Scroll current position to top
    *
    * @param  {number} topValue
    * @return void
    */
    function animateScrollToTop(topValue){
        $('body, html').animate({
            scrollTop: topValue
        },10)
    }
</script>
<? if(!is_session_exists()): ?> <!-- Checks whether user_id session is exists or not -->
<!-- is_session_exists() return true -->
<script>
$(document).ready(function() {
    var interval = setInterval(doStuff, 2000); // Calls doStuff() function to check user_id session at 2 seconds of time interval
    function doStuff(){
        $.ajax({
           url: 'https://www.plt.org/check-session-ajax/', // Checks whether user_id session exists or not
            type: 'GET',
            success: function(receivedData){
                if(receivedData == "true"){
                    $('#link').text('Logout'); // Changes the login button text if user_id session exist
                    $('#link').attr('href', '<?php echo $single_signout_url; ?>'); // Changes logout url if user_id session exist
                    $('#account_home').css('display','inline'); // Makes 'account home' link visible
                    clearInterval(interval); // Clears the interval time if all the above code get executed successfully
                }
            }
        });
    }
})
</script>
<? else: ?>
<!-- is_session_exists() retuns false -->
<script>
    $(document).ready(function(){
      $('#account_home').css('display', 'inline'); // Makes 'account home' like visible
    })
</script>
<? endif; ?>

	</body>
</html>