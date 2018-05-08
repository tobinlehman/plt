<?php
    require(dirname(__FILE__).'/templates/inc/global-variables.inc.php');   
?>

<footer class="footer container-full no-p-m">
	<div class="container no-p-m-auto">
		<div class="row no-p-m">
		<div class="col-sm-3 col-md-3 footer-links">
			<div class="link-group">
				<?php wp_nav_menu( array('theme_location'  => 'footer-column-1')); ?>
			</div>
		</div>
		<div class="col-sm-3 col-md-3 footer-links">
			<div class="link-group">
				<?php wp_nav_menu( array('theme_location'  => 'footer-column-2')); ?>
			</div>
		</div>
		<div class="col-sm-3 col-md-3 footer-links-bg">
			
		</div>
		<div class="col-sm-3 col-md-3 quick-links cf pull-left">
			<div class="link-group">
				<h2>
					WHO TO CONTACT
				</h2>

					<?php
					
						$args = array(
							'post_type'  => 'staff_members', 
						    'order'      => 'ASC',
						    'orderby'    => 'menu_order'
						    
						);
										
						$query = new WP_Query($args); 
					?>
					
					<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						
								<?php the_field('snippet'); ?>
								<ul class="footer-contact">
									<li class="name">
										<?php the_title(); ?>
									</li>
									<li class="email">
										<a href="mailto:<?php the_field('email'); ?>"><?php the_field('email'); ?></a>
									</li>
									<li class="title">
										<?php the_field('title'); ?>
									</li>
								</ul>
								
							<?php endwhile; ?>
					<?php endif; ?>
					<?php wp_reset_postdata();?>
					
					
					<!-- <ul class="footer-contact">
						<li class="name">
							Rachel Bayer
						</li>
						<li class="email">
							<a href="mailto:rbayer@plt.org">rbayer@plt.org</a>
						</li>
						<li class="title">
							strategic planning, network relations, MPI, Education Operating Committee
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Vanessa Bullwinkle
						</li>
						<li class="email">
							<a href="mailto:vbullwinkle@plt.org">vbullwinkle@plt.org</a>
						</li>
						<li class="title">
							marketing & communications, website
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Haley Herbst
						</li>
						<li class="email">
							<a href="mailto:hherbst@plt.org">hherbst@plt.org</a>
						</li>
						<li class="title">
							network relations, GreenWorks! grants, Coordinators’ Conference, state report form
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Kaylin Lee
						</li>
						<li class="email">
							<a href="mailto:klee@plt.org">klee@plt.org</a>
						</li>
						<li class="title">
							marketing & communications, curriculum 
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							James McGirt
						</li>
						<li class="email">
							<a href="mailto:jmcgirt@plt.org">jmcgirt@plt.org</a>
						</li>
						<li class="title">
							service-learning programs
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Kathy McGlauflin
						</li>
						<li class="email">
							<a href="mailto:kmcglauflin@plt.org">kmcglauflin@plt.org</a>
						</li>
						<li class="title">
							organizational management, strategic planning, development, governance
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Jennifer Pic 
						</li>
						<li class="email">
							<a href="mailto:jpic@plt.org">jpic@plt.org</a>
						</li>
						<li class="title">
							online PD, PD event support, Coordinators’ Conference, outstanding educators
						</li>
					</ul>
					<ul class="footer-contact">
						<li class="name">
							Jaclyn Stallard
						</li>
						<li class="email">
							<a href="mailto:jstallard@plt.org">jstallard@plt.org</a>
						</li>
						<li class="title">
							curriculum, education conferences 
						</li>
					</ul> -->
				</div>
			</div>
		</div>
	</div>
	
	<div class="row member-nav-footer">
		<div class="container">
			<ul class="copyright">
				<li>&#169; 2017, American Forest Foundation</li>
				<li><a target="_blank" href="https://www.plt.org/about-us/privacy-policy/">Privacy Policy</a> PLT is a program of the American Forest Foundation</li>
				
			</ul>
			<ul class="social">
				<li><a target="_blank" href="http://facebook.com/projectlearningtree"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/img/icon-fb@2x.png" alt=""></a></li>
				<li><a target="_blank" href="http://pinterest.com/nationalplt"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/img/icon-pinterest@2x.png" alt=""></a></li>
				<li><a target="_blank" href="http://twitter.com/plt"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/img/icon-twitter@2x.png" alt=""></a></li>
				<li><a target="_blank" href="http://youtube.com/user/ProjectLearningTree"><img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/img/icon-youtube@2x.png" alt=""></a></li>
			</ul>
			<ul class="support">
				<li><a href="<?php echo site_url(); ?>/support">Support</a></li>
				<li><a href="<?php echo $plt_login_url.'/?target_page=account_home'; ?>">My Account</a></li>
			</ul>
		</div>
	</div>
</footer>

<!-- End Document -->

<?php wp_footer(); ?>

	
</body>
</html>
