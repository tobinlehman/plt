<?php
require(dirname(__FILE__)."/inc/global-variables.inc.php");
?>

<div class="sidebar">
	<div class="col-md-12 calendar-of-due-dates no-p-m cf">
		<h2>
			Calendar of Due Dates
		</h2>
		<ul>
			<?php
			
				$args = array(
					'post_type'  => array('tribe_events'),
					'showposts'  => '4', 
				    'order'      => 'ASC'
				    
				);
								
				$query = new WP_Query($args); 
			?>
			
			<?php 

				if ( $query->have_posts() ) : ?>
					<?php while ( $query->have_posts() ) : $query->the_post(); 

					?>
						
						<li>
							<a href="<?php the_permalink(); ?>">
								<span class="date">
									<?php echo tribe_get_start_date(null, true, 'm/d/y'); ?>
								</span>
								<span class="title">
									<?php echo substr(get_the_title(), 0, 19); ?>
								</span>
							</a>
						</li>
						
						
						
					<?php endwhile; ?>
			<?php endif; ?>
			<?php wp_reset_postdata();?>
		</ul>
	</div>
	<div class="col-md-12 no-p-m cta-dark cf">

		<h2>
			Coordinators' Store
		</h2>
		<div class="link-container">
			
			<ul>
				<li>
					<a target="_blank" href="<?php echo $shop_index_url; ?>">
						<i class="fa fa-external-link" aria-hidden="true"></i> Order Guides, E-Units, Promo Items
					</a>
				</li>
				<li>
					<a target="_blank" href="http://shop.plt.org/~userInfo">
						<i class="fa fa-external-link" aria-hidden="true"></i> Order History
					</a>
				</li>
				<li>
					<a target="_blank" href="http://shop.plt.org/cart">
						<i class="fa fa-external-link" aria-hidden="true"></i> Manage Online Courses
					</a>
				</li>
			</ul>
			<span class="icon">
				<img src="<?php echo IMG; ?>/icon-cart@2x.png">
			</span>
		</div>
		
	</div>
	<div class="col-md-12 no-p-m cta-light cf">
		<h2>
		
			State Reporting Form
		</h2>
		<div class="link-container">
			<?php wp_nav_menu( array('theme_location'  => 'state-reporting-form')); ?>
			<span class="icon">
				<img src="<?php echo IMG; ?>/icon-documents@2x.png">
			</span>
		</div>
	</div>
	<div class="col-md-12 no-p-m cta-dark cf">
		<h2>
			MPI Grants
		</h2>
		<div class="link-container">
			<?php wp_nav_menu( array('theme_location'  => 'mpi-grants')); ?>
			<span class="icon">
				<img src="<?php echo IMG; ?>/icon-grants-dark@2x.png">
			</span>
		</div>
	</div>
	<div class="col-md-12 no-p-m cta-light cf">
		<h2>
			Coordinators' Conference
		</h2>
		<div class="link-container">
			<?php wp_nav_menu( array('theme_location'  => 'coordinators-conference')); ?>
			<span class="icon">
				<img src="<?php echo IMG; ?>/icon-people-light@2x.png">
			</span>
		</div>
	</div>
</div>
