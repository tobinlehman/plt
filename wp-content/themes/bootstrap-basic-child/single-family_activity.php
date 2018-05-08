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
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 
<?php get_sidebar('left'); ?> 
				<div class="col-md-<?php echo $main_column_size; ?> content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<?php 
						while (have_posts()) {
							the_post();

							get_template_part('content-singlestate', get_post_format());

							echo "\n\n";
							
							bootstrapBasicPagination();

							echo "\n\n";
							
							// If comments are open or we have at least one comment, load up the comment template
							if (comments_open() || '0' != get_comments_number()) {
								comments_template();
							}

							echo "\n\n";

						} //endwhile;
						?> 
					</main>

				</div>
				
				<div class="keylinks">
					<?php
							$orig_post = $post;
						
							global $post; ?>
							
							<?php
							
							$categories_family = get_the_category($post->ID);
						
							if ($categories_family) {
								$category_ids_family = array();
								
								foreach($categories_family as $individual_category_family) $category_ids_family[] = $individual_category_family->term_id;
									$args_family=array(
										'category__in' => $category_ids_family,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'family_activity',
										'orderby'=> 'rand'
									);
									
								$my_query_family = new wp_query( $args_family );
						
								if( $my_query_family->have_posts() ) {
									while( $my_query_family->have_posts() ) {
										
										$my_query_family->the_post(); ?>
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
								
								<?php
							
							
									$args_literature=array(
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'literature_connectio',
										'orderby'=> 'rand',
										'post_status' => 'publish'
									);
									
								$my_query_literature = new wp_query( $args_literature );
						
								if( $my_query_literature->have_posts() ) {
									while( $my_query_literature->have_posts() ) {
										
										$my_query_literature->the_post(); ?>
										<div class="widget">
										 <h2><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
										 <p><?php the_excerpt(); ?></p>
										 <p><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
										</div>
									<? 
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
								
								<?php
							
							
									$args=array(
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'story',
										'orderby'=> 'rand',
										'post_status' => 'publish'
									);
									
								$my_query = new wp_query( $args );
						
								if( $my_query->have_posts() ) {
									while( $my_query->have_posts() ) {
										
										$my_query->the_post(); ?>
										<div class="widget">
										 <h2><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
										 <p><?php the_excerpt(); ?></p>
										 <p><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
										</div>
									<? 
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
								
								<div class="widget">
									 <h2><a href="/outdoors/">Take Kids Outdoors</a></h2>
									 <p>Mother Nature is a great teacher and getting kids outside to learn and play is good for their brains and their bodies.</p>
									 <p><a href="/outdoors/"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
								 </div>

							
				</div><!-- /.keylinks -->
				
				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->

<?php get_footer(); ?> 
