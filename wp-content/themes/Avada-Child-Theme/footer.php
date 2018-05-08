
		</div>
	</div>
	<?php global $smof_data, $social_icons; ?>
	<?php
	$object_id = get_queried_object_id();
	if((get_option('show_on_front') && get_option('page_for_posts') && is_home()) ||
	    (get_option('page_for_posts') && is_archive() && !is_post_type_archive()) && !(is_tax('product_cat') || is_tax('product_tag')) || (get_option('page_for_posts') && is_search())) {
		$c_pageID = get_option('page_for_posts');
	} else {
		if(isset($object_id)) {
			$c_pageID = $object_id;
		}

		if(class_exists('Woocommerce')) {
			if(is_shop() || is_tax('product_cat') || is_tax('product_tag')) {
				$c_pageID = get_option('woocommerce_shop_page_id');
			}
		}
	}
	?>
	<?php if(!is_page_template('blank.php')): ?>
	<?php if( ($smof_data['footer_widgets'] && get_post_meta($c_pageID, 'pyre_display_footer', true) != 'no') ||
			  ( ! $smof_data['footer_widgets'] && get_post_meta($c_pageID, 'pyre_display_footer', true) == 'yes') ): ?>
	
	<?php endif; ?>
	<?php if( ($smof_data['footer_copyright'] && get_post_meta($c_pageID, 'pyre_display_copyright', true) != 'no') ||
			  ( ! $smof_data['footer_copyright'] && get_post_meta($c_pageID, 'pyre_display_copyright', true) == 'yes') ): ?>
	
	<?php endif; ?>
	<?php endif; ?>
	<!-- <footer class="footer">
		<div class="member-nav-footer">
			<div class="wrap">
				<ul class="copyright">
					<li>&#169; <?php echo date('Y'); ?>, American Forest Foundation</li>
					<li><a target="_blank" href="https://www.plt.org/about-us/privacy-policy/">Privacy Policy</a> PLT is a program of the American Forest Foundation</li>
					
				</ul>
				<ul class="social">
					<li><a target="_blank" href="http://facebook.com/projectlearningtree"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/facebook.png" alt=""></a></li>
					<li><a target="_blank" href="http://pinterest.com/nationalplt"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/pinterest.png" alt=""></a></li>
					<li><a target="_blank" href="http://twitter.com/plt"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/twitter.png" alt=""></a></li>
					<li><a target="_blank" href="http://youtube.com/user/ProjectLearningTree"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/youtube.png" alt=""></a></li>
				</ul>
				<ul class="support">
					<li><a href="<?php echo site_url(); ?>/support">Support</a></li>
					<li><a href="http://shop.plt.org/~userInfo">My Account</a></li>
				</ul>
			</div>
		</div>
	</footer> -->
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
			<div id="custom_post_widget-7" class="widget widget_custom_post_widget"><p><a href="mailto:information@plt.org">CONTACT US</a> | <a href="https://www.plt.org/about-us/privacy-policy/">PRIVACY POLICY</a> | <a href="http://www.sfiprogram.org/">PLT IS A PROGRAM OF THE SUSTAINABLE FORESTRY INITIATIVE INC</a>.<br> © <?php echo date('Y'); ?>, SUSTAINABLE FORESTRY INITIATIVE®</p>
</div>
	        </div>
	    </div>
	    <div class="row footer-funding">
	    	<p>The Pine Integrated Network: Education, Mitigation, and Adaptation project (PINEMAP) is a Coordinated Agricultural Project funded by the USDA National Institute of Food and Agriculture, Award #2011-68002-30185.</p>
	    </div>
	</footer>
	</div><!-- wrapper -->
	<?php //include_once('style_selector.php'); ?>
	
	<!-- W3TC-include-js-head -->

	
	<?php wp_footer(); ?>

	<?php echo $smof_data['space_body']; ?>

	<!--[if lte IE 8]>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/respond.js"></script>
	<![endif]-->
	</div>

	<script type="text/javascript">
		jQuery('.download-link').attr('target', '_blank');
		jQuery('a[href^="http://"]').not('a[href*="sfcc.plt.org"]').attr('target','_blank');
		jQuery("a[href$='pdf']").attr('target','_blank');
		jQuery('.fusion-megamenu-menu a, .fusion-dropdown-menu a').on('click', function(){
		
			// jQuery(this).next('.sub-menu, .fusion-megamenu-wrapper').addClass('show-dropdown');
			if(jQuery(this).next('.sub-menu, .fusion-megamenu-wrapper').hasClass('show-dropdown')){
				jQuery(this).next('.sub-menu, .fusion-megamenu-wrapper').removeClass('show-dropdown');
				return;
			}
			if(!jQuery(this).next('.sub-menu, .fusion-megamenu-wrapper').hasClass('show-dropdown')){
				jQuery(this).next('.sub-menu, .fusion-megamenu-wrapper').addClass('show-dropdown');
				jquery(this).find('.fusion-megamenu-widgets-container').fadeIn();
				return;
			}
			return;
		});

		jQuery(document).on("click", function(e) {
			if (jQuery(e.target).is(".fusion-megamenu-menu a, .fusion-dropdown-menu a") === false) {
				jQuery('.sub-menu, .fusion-megamenu-wrapper').removeClass('show-dropdown');
			}
		});
	</script>

		<script type='text/javascript'>
(function (d, t) {
  var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
  bh.type = 'text/javascript';
  bh.src = 'https://www.bugherd.com/sidebarv2.js?apikey=xcupht1y1hfqg1iixu6cgq';
  s.parentNode.insertBefore(bh, s);
  })(document, 'script');
</script>	


	</body>
</html>