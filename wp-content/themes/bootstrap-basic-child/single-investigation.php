<?php
/**
 * Template for displaying single post (read full post page).
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
?> 
				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<h1 class="entry-title">Resources for <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
							</header><!-- .entry-header --> 
							
							<div class="entry-content">
								<?php the_content(); ?> 
							</div><!-- .entry-content -->
							
						</article>
						
														
							<?php
							$orig_post = $post;
						
							global $post; ?>
							
							<?php
							
							$categories_stem = get_the_category($post->ID);
						
							if ($categories_stem) {
								$category_ids_stem = array();
								
								foreach($categories_stem as $individual_category_stem) $category_ids_stem[] = $individual_category_stem->term_id;
									$args_stem=array(
										'category__in' => $category_ids_stem,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'family_activity',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query_stem = new wp_query( $args_stem );
						
								if( $my_query_stem->have_posts() ) {
									echo '<div class="activitystemconnections">
											<h2>FAMILY ACTIVITY <img src="/wp-content/uploads/2016/07/leaf_icon_green.png" class="activityicon activityiconright" /></h2>';
									while( $my_query_stem->have_posts() ) {
										$my_query_stem->the_post();
										echo '<p>Discover ways to green your home - and save money - with these questions and tips. This <a href="';
										the_permalink();
										echo '">Green Your Home checklist</a> will help children and their families decide together what they might do to improve their environment at home.</p>';
									}
									echo '</div>';
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								
								 ?>
														
							<div class="clearfix"></div>
							
							<?php
							$categorieseeresources = get_the_category($post->ID);
						
							if ($categorieseeresources) {
								$category_ids_eeresources = array();
								
								foreach($categorieseeresources as $individual_category_eeresources) $category_ids_eeresources[] = $individual_category_eeresources->term_id;
									$args_eeresources=array(
										'category__in' => $category_ids_eeresources,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> -1, // Number of related posts that will be displayed.
										'post_type'=>'ee_resource',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query_eeresources = new wp_query( $args_eeresources );
						
								if( $my_query_eeresources->have_posts() ) {
									echo '<div class="activityeeresources">
							<h2>ADDITIONAL RESOURCES <img src="/wp-content/uploads/2016/07/magnifying_icon_green.png" class="activityicon activityiconleft" /></h2>
							<p>Every month we carefully select new tools and resources that enhance PLT’s lessons. These include educational apps, videos, posters, interactive websites, careers information, and teacher-generated materials. Browse a chronological listing below:<br /></p>';
									echo '<div id="eeresources" class="clear"><ul>';
									while( $my_query_eeresources->have_posts() ) {
										$my_query_eeresources->the_post(); ?>
										<li>
										<span class="eeresourcetitle"><?php the_title(); ?></span> <?php the_content(); ?>
										</li>
									<? }
									echo '</ul></div></div>';
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
						
					</main>

				</div>
				
				<div class="keylinks">
					<?php
							
							$categories_tips = get_the_category($post->ID);
						
							if ($categories_tips) {
								$category_ids_tips = array();
								
								foreach($categories_tips as $individual_category_tips) $category_ids_tips[] = $individual_category_tips->term_id;
									$args_tips=array(
										'category__in' => $category_ids_tips,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 4, // Number of related posts that will be displayed.
										'post_type'=>'educator_tip',
										'orderby'=> 'rand'
									);
									
								$my_query_tips = new wp_query( $args_tips );
						
								if( $my_query_tips->have_posts() ) {
									while( $my_query_tips->have_posts() ) {
										
										$my_query_tips->the_post(); ?>
										<div class="widget">
										 <h2><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
										 <p><?php the_excerpt(); ?></p>
										 <p><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
										</div>
									<? }
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
					
					
				</div><!-- /.keylinks -->
				
				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->

<?php get_footer(); ?> 
