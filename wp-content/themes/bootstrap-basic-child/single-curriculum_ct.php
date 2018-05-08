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

							get_template_part('content', get_post_format());

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
					<div class="widget">
									 <h2><a href="/environmental-education-resources/">Environmental Education Resources</a></h2>
									 <p>Every month we carefully select new educational apps, videos, interactive websites, books, careers information, and teacher-generated materials that support PLT lessons.</p>
									 <p><a href="/environmental-education-resources/"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
								 </div>
								 
					<div class="widget">
									 <h2><a href="/greenschools/">GreenSchools</a></h2>
									 <p>PLT’s GreenSchools program inspires students to apply their STEM and investigative skills to create greener and healthier schools – and save schools money.</p>
									 <p><a href="/greenschools/"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
								 </div>
					
					<?php
							$orig_post = $post;
						
							global $post; ?>
							
							<?php
							
							$categories = get_the_category($post->ID);
						
							if ($categories) {
								$category_ids = array();
								
								foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
									$args=array(
										'category__in' => $category_ids,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'educator_tip',
										'orderby'=> 'rand'
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
									<? }
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
								
								 <div class="widget">
									 <h2><a href="/apply-for-greenworks-environmental-education-grant/">Apply for a Grant</a></h2>
									 <p>Do you have an idea for a service-learning project to improve the environment at your school or in your community? Apply for a PLT GreenWorks! grant by Sept. 30.</p>
									 <p><a href="/apply-for-greenworks-environmental-education-grant/"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
								 </div>
							
					
					
				</div><!-- /.keylinks -->
				
				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->

<?php get_footer(); ?> 
