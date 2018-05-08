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
		
<!-- Go to www.addthis.com/dashboard to customize your tools -->
<!-- <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-577fedaf02872df0"></script> -->

<!--wordpress footer-->

<!-- 		<script async defer src="//assets.pinterest.com/js/pinit.js"></script> -->

    <script
      src="https://code.jquery.com/jquery-3.2.1.min.js"
      integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
      crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <? if(is_session_exists()): ?>
        <script>
            $(document).ready(function(){
              $('#account_home').css('display', 'inline');  
            })
        </script>
    <? endif; ?>
	</body>
</html>