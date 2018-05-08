<?php
/**
 * Template Name: Page - The Branch
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 
				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<?php 
						while (have_posts()) {
							the_post();

							get_template_part('content', 'page');

							echo "\n\n";

						} //endwhile;
						?> 
						
						<?php
						$orig_post = $post;
						
						global $post;
						
						$args_featured_article=array(
										'category__in' => 318,
										'posts_per_page'=> -1, // Number of related posts that will be displayed.
										'orderby'=>'date',
										'post_status' => 'publish',
										'order'=>DESC
									);
							
						$my_query_featured_article = new wp_query( $args_featured_article );
						
						if( $my_query_featured_article->have_posts() ) {
									echo '<div class="branchpageblock">';
									echo '<h2>Featured Article</h2>';
									echo '<hr class="purplebar" />';
									echo '<ul>';
									while( $my_query_featured_article->have_posts() ) {
										$my_query_featured_article->the_post(); ?>
										<li>
											<a href="<?php the_permalink(); ?>" class="branchcontentimage"><?php the_post_thumbnail(array(400, 400)) ?></a>
											<div class="branchexcerpt">
												<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												<?php the_excerpt(); ?>
											</div>
										</li>
									<? }
									echo '</ul></div>';
									}
								
						$post = $orig_post;
								
						wp_reset_query();
	
							
						?>
						
						<?php
						
						$args_news=array(
										'category__in' => 317,
										'posts_per_page' => -1, // Number of related posts that will be displayed.
										'post_status' => 'publish',
										'order' => ASC,
										'post_type' => array('post'),
										'meta_key' => 'the_branch_page_sort_order',
										'orderby' => 'meta_value'
									);
							
						$my_query_news = new wp_query( $args_news );
						
						if( $my_query_news->have_posts() ) {
									echo '<div class="branchpageblock">';
									echo '<h2>News & Updates</h2>';
									echo '<hr class="tealbar" />';
									echo '<ul>';
									while( $my_query_news->have_posts() ) {
										$my_query_news->the_post(); ?>
										<li>
											<a href="<?php the_permalink(); ?>" class="branchcontentimage"><?php the_post_thumbnail(array(400, 400)) ?></a>
											<div class="branchexcerpt">
												<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												<?php the_excerpt(); ?>
											</div>
										</li>
									<? }
									echo '</ul></div>';
									}
								
						$post = $orig_post;
								
						wp_reset_query();
	
							
						?>
						
						<?php
						
						$args_stories=array(
										'category__in' => 317,
										'posts_per_page' => -1, // Number of related posts that will be displayed.
										'post_status' => 'publish',
										'order' => ASC,
										'post_type' => array('story'),
										'meta_key' => 'the_branch_page_sort_order',
										'orderby' => 'meta_value'
									);
							
						$my_query_stories = new wp_query( $args_stories );
						
						if( $my_query_stories->have_posts() ) {
									echo '<div class="branchpageblock">';
									echo '<h2>Stories</h2>';
									echo '<hr class="tealbar" />';
									echo '<ul>';
									while( $my_query_stories->have_posts() ) {
										$my_query_stories->the_post(); ?>
										<li>
											<a href="<?php the_permalink(); ?>" class="branchcontentimage"><?php the_post_thumbnail(array(400, 400)) ?></a>
											<div class="branchexcerpt">
												<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												<?php the_excerpt(); ?>
											</div>
										</li>
									<? }
									echo '</ul></div>';
									}
								
						$post = $orig_post;
								
						wp_reset_query();
	
							
						?>
						
						<?php
						
						$args_educator=array(
										'category__in' => 317,
										'posts_per_page' => -1, // Number of related posts that will be displayed.
										'post_status' => 'publish',
										'order' => ASC,
										'post_type' => array('educator_tip'),
										'meta_key' => 'the_branch_page_sort_order',
										'orderby' => 'meta_value'
									);
							
						$my_query_educator = new wp_query( $args_educator );
						
						if( $my_query_educator->have_posts() ) {
									echo '<div class="branchpageblock">';
									echo '<h2>Educator Tips</h2>';
									echo '<hr class="tealbar" />';
									echo '<ul>';
									while( $my_query_educator->have_posts() ) {
										$my_query_educator->the_post(); ?>
										<li>
											<a href="<?php the_permalink(); ?>" class="branchcontentimage"><?php the_post_thumbnail(array(400, 400)) ?></a>
											<div class="branchexcerpt">
												<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												<?php the_excerpt(); ?>
											</div>
										</li>
									<? }
									echo '</ul></div>';
									}
								
						$post = $orig_post;
								
						wp_reset_query();
	
							
						?>	
						
						<div class="branchpageblock">
						<h2>Resources</h2>
						
						<?php dynamic_sidebar('branch-evergreen'); ?>
						
						<?php
						
						$args_resource=array(
										'category__in' => 317,
										'posts_per_page' => -1, // Number of related posts that will be displayed.
										'post_status' => 'publish',
										'order' => ASC,
										'post_type' => array('stem_connection', 'literature_connectio'),
										'meta_key' => 'the_branch_page_sort_order',
										'orderby' => 'meta_value'
									);
							
						$my_query_resource = new wp_query( $args_resource );
						
						if( $my_query_resource->have_posts() ) {
									echo '<ul>';
									while( $my_query_resource->have_posts() ) {
										$my_query_resource->the_post(); ?>
										<li>
											<a href="<?php the_permalink(); ?>" class="branchcontentimage"><?php the_post_thumbnail(array(400, 400)) ?></a>
											<div class="branchexcerpt">
												<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												<?php the_excerpt(); ?>
											</div>
										</li>
									<? }
									echo '</ul>';
									}
								
						$post = $orig_post;
								
						wp_reset_query();
	
							
						?>	
						</div>
						
						<hr class="tealbar" />

						
					</main>
				</div>
				
<?php get_footer(); ?> 