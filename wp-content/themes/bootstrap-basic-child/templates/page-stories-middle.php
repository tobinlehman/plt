<?php
/**
 * Template Name: Page - Stories - Middle
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 
				<div class="col-md-<?php echo $main_column_size; ?> content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<?php 
						while (have_posts()) {
							the_post();

							get_template_part('content', 'page');

							echo "\n\n";

						} //endwhile;
						?> 
						
						<ul class="storiespage">
							<?php
							$orig_post = $post;
						
							global $post; ?>
							
							<?php
																
							$teacher_story_args = array(
								'post_type' => array('story','educator_tip'),
							 	'posts_per_page' => -1,
							 	'post_status' => 'publish',
							 	'category_name' => 'teacher_story'
							);
							
							$temp_query = new WP_Query( $teacher_story_args );
							
							$teacherstory_post_ids = array();
							
							foreach ( $temp_query->posts as $teacher_story_post ) {
									$teacherstory_post_ids[] = $teacher_story_post->ID;
								}
						
								$category_ids = array(287);
																
								$args=array(
										'cat' => $category_ids,
										'post__not_in' => array($post->ID),
										'post__in'=>$teacherstory_post_ids,
										'post_type'=>array('story','educator_tip'),
										'posts_per_page' => -1
									);
																									
								$my_query = new wp_query( $args );
						
								if( $my_query->have_posts() ) {
									while( $my_query->have_posts() ) {
										$my_query->the_post();
										echo '<li>';
										echo '<a href="';
										the_permalink();
										echo '" class="storiesimage">';
										the_post_thumbnail(array(400, 400));
										echo '</a>';
										echo '<h3><a href="';
										the_permalink();
										echo '">';
										the_title();
										echo '</a></h3>';
										echo '<div class="storiesexcerpt">';
										the_excerpt();
										echo '</div>';
										echo '</li>';					
									}
								}
							
																
								$post = $orig_post;
								
								wp_reset_query();
								?>
							
						</ul>

						
					</main>
				</div>
				
<?php get_footer(); ?> 