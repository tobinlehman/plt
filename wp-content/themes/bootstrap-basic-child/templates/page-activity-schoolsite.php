<?php
/**
 * Template Name: Page - Activities - School Site Investigation
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
						
						<ul class="activitylistpage">
						<?php
	
							wp_reset_postdata();
							
							$temp = $wp_query;
							$wp_query = NULL;
							$args = array();							
							
							$args['post_type'] = array ('activity');
							$args['category_name'] = 'greenschools_school';		
							$args['orderby'] = 'date';
							$args['order'] = 'DESC';
							$args['posts_per_page'] = -1;
							$args['post_status'] = 'publish';
							
							$wp_query = new WP_Query($args);
							
							if ( $wp_query->have_posts() ) {
								while ( $wp_query->have_posts() ) {
									$wp_query->the_post();
									echo '<li>';
									echo '<a href="';
									the_permalink();
									echo '" class="activitylistimage">';
									the_post_thumbnail(array(400, 400));
									echo '</a>';
									echo '<h3><a href="';
									the_permalink();
									echo '">';
									the_title();
									echo '</a></h3>';
									echo '<div class="activitylistexcerpt">';
									the_excerpt();
									echo ' <a href="';
									echo the_permalink();
									echo '">VIEW ACTIVITY</a>';	
									echo '</div>';
									echo '</li>';					
								}
							}
					
							?>
						</ul>

						
					</main>
				</div>
				
<?php get_footer(); ?> 