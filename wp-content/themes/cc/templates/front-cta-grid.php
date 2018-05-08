<?php
require(dirname(__FILE__)."/inc/global-variables.inc.php");
?>
<div class="container no-p-m-auto cta-grid">
	<div class="row no-p-m">
		<div class="col-sm-6 col-md-6 no-p-m">
			<div class="row no-p-m max-height-540">
				<div class="col-sm-6 col-md-6 no-p-m">
					<div class="row no-p-m flex-container">
						<div class="col-md-12 no-p-m cta-dark cf flex-m-1">
							<h2>
								Order Guides
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
						<div class="col-md-12 no-p-m flex-m-0">
							<img src="<?php echo IMG; ?>/kids-bg@2x.png">
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 no-p-m cta-col-pl-5">
					<div class="row no-p-m">
						<div class="col-md-12 no-p-m cta-light cf">
							<h2>
								State Reporting Form
							</h2>
							<div class="link-container">
								<ul>
									<li>
										<a href="<?php echo $state_user_detail_url; ?>">
											<i class="fa fa-external-link" aria-hidden="true"></i>  Quarterly State Reporting Form
										</a>
									</li>
									<li>
										<a href="<?php echo SITE; ?>/program-operations/state-reporting/">
											Tutorials for Completing SRF
										</a>
									</li>
									<li>
										<a href="<?php echo $state_dashboard_data; ?>">
											<i class="fa fa-external-link" arai-hidden="true"></i> State Dashboard Data
										</a>
									</li>
								</ul>
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
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-6 no-p-m">
			<div class="row no-p-m">
				<div class="col-sm-6 col-md-6 no-p-m">
					<div class="col-sm-6 col-md-6 community-conversations">
						<a class="hover-community" href="http://coordinators.plt.org/forums/forum/coordinators-corner/"><img src="<?php echo IMG; ?>/icon-conversation@2x.png">
						<h2>
							Community Conversations
						</h2>
					</a>
					</div>
					<?php 
						$post_types = array('reply', );
						$meta_key = '_bbp_last_reply_id';
						$show = 6;
						$include_empty_topics = true;
						// allow for topics with no replies
						if ($include_empty_topics) {
						  $meta_key = '_bbp_last_active_id';
						  $post_types[] = 'topic';
						}
						
						// get the 5 topics with the most recent replie
						$args = array(
						  'posts_per_page' => $show,
						  'post_type' => array('topic'),
						  'post_status' => array('publish'),
						  'orderby' => 'meta_value_num',
						  'order' => 'DESC',
						  'meta_key' => $meta_key,
						);
						// allow for specific forum limit
						if ($forum){
						  $args['post_parent'] = $forum;
						}
						
						$query = new WP_Query($args);
						$reply_ids = array();  
						
						// get the reply post->IDs for these most-recently-replied-to-topics
						while($query->have_posts()){
						  $query->the_post();
						  if ($reply_post_id = get_post_meta(get_the_ID(), $meta_key, true)){
						    $reply_ids[] = $reply_post_id;
						  }
						}
						wp_reset_query();
						
						// get the actual replies themselves
						$args = array(
						  'posts_per_page' => $show,
						  'post_type' => $post_types,
						  'post__in' => $reply_ids,
						  'orderby' => 'date',
						  'order' => 'DESC'
						);
						
						$query = new WP_Query($args);

						?>
							<div class="testing" style="display:none">
								<?php 
									var_dump($args);
									var_dump($query->post_count);
								?>
							</div>
						<?php
						while($query->have_posts()){
							$query->the_post();
							$title = get_the_title();
							// var_dump(single_post_title());
						       
							$title = str_replace('Reply To: ', '', $title);

							$title = substr( $title, 0, 55);

							$title = wp_trim_words( $title, 5, '');

					?>

						<div class="col-md-12 conversation">
							<a href="<?php bbp_reply_url( get_the_ID() ); ?>">
								<?php echo $title; ?> >>
							</a>

							<div class="meta-info">
								<span class="date"><?php echo get_the_date('m/d/y'); ?></span> - <span class="author">By <?php echo the_author_nickname(); ?></span> | <a class="replies" href="<?php bbp_reply_url( get_the_ID() ); ?>"><?php echo bbp_topic_post_count(get_the_ID()); ?> Replies</a>
							</div>
						</div>
						
					<?php } wp_reset_query(); ?>
					
				</div>
				<div class="col-sm-6 col-md-6 no-p-m">
					<div class="row no-p-m">
						<div class="col-md-12 no-p-m">
							<img src="<?php echo IMG; ?>/kid-bg@2x.png" class="calendar-of-due-dates-img">
						</div>
						<div class="col-md-12 calendar-of-due-dates no-p-m">
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
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12 no-p-m cta-grid-bottom">
			<div class="row no-p-m">
				<div class="col-sm-6 col-md-6 no-p-m updated-content">
					<span class="updated-icon-container">
						<img src="<?php echo IMG; ?>/icon-updated@2x.png">
					</span>
					<div class="updated-content-container">
						<h2>Updated Content</h2>
						<ul>

							<?php
							
								$args = array(
									'post_type'  => array('post', 'tribe_events', 'attachment'),
									'post_status'  => 'any',
									'showposts'  => '5', 
								    'order'      => 'DESC',
								    'orderby'   => 'modified' //or 'meta_value_num'
								    
								);
												
								$query = new WP_Query($args); 
							?>
							
							<?php if ( $query->have_posts() ) : ?>
									<?php while ( $query->have_posts() ) : $query->the_post(); ?>
								
										
										<li>
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); ?></a>
											<div class="meta-info">
												Updated 
												<span class="date">
													<?php the_date(); ?>
												</span>
												-
												<span class="author">
													<?php echo get_author_name(); ?>
												</span>
											</div>
										</li>
										
									<?php endwhile; ?>
							<?php endif; ?>
							<?php wp_reset_postdata();?>
							
							
						</ul>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 no-p-m">
					<img src="<?php echo IMG; ?>/teens-bg@2x.png" alt="">
				</div>
				
			</div>
		</div>
	</div>
</div>




















