<?php
/**
 * Template Name: Page - News & Stories
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
						
						<ul class="newspage">
						<?php
	
							wp_reset_postdata();
							
							$temp = $wp_query;
							$wp_query = NULL;
							$args = array();							
							
							$args['post_type'] = array ('post', 'story');		
							$args['orderby'] = 'date';
							$args['order'] = 'DESC';
							$args['posts_per_page'] = 3;
							$args['post_status'] = 'publish';
							
							$wp_query = new WP_Query($args);
							
							if ( $wp_query->have_posts() ) {
								while ( $wp_query->have_posts() ) {
									$wp_query->the_post();
									echo '<li>';
									echo '<a href="';
									the_permalink();
									echo '" class="newspageimage">';
									the_post_thumbnail('medium');
									echo '</a>';
									echo '<div class="newspagemeta"><strong>';
									the_date();
									echo '</strong> | ';
									the_category( ' ' );
									echo '</div>';
									echo '<h3><a href="';
									the_permalink();
									echo '">';
									the_title();
									echo '</a></h3>';
									echo '<div class="newspageexcerpt">';
									the_excerpt();
									echo ' <a href="';
									echo the_permalink();
									echo '">Read More &raquo;</a>';	
									echo '</div>';
									echo '</li>';					
								}
							}
					
							?>
						</ul>

						
					</main>
				</div>
				
				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->
							
				<div class="newstories-bottomfeed">
											<ul class="newspage">
						<?php
	
							wp_reset_postdata();
							
							$temp = $wp_query;
							$wp_query = NULL;
							$args = array();							
							
							$args['post_type'] = array ('post', 'story');		
							$args['orderby'] = 'date';
							$args['order'] = 'DESC';
							$args['posts_per_page'] = 4;
							$args['offset']= 3;
							$args['post_status'] = 'publish';
							
							$wp_query = new WP_Query($args);
							
							if ( $wp_query->have_posts() ) {
								while ( $wp_query->have_posts() ) {
									$wp_query->the_post();
									echo '<li>';
									echo '<a href="';
									the_permalink();
									echo '" class="newspageimage">';
									the_post_thumbnail('medium');
									echo '</a>';
									echo '<div class="newspagemeta"><strong>';
									the_date();
									echo '</strong> | ';
									the_category( ' ' );
									echo '</div>';
									echo '<h3><a href="';
									the_permalink();
									echo '">';
									the_title();
									echo '</a></h3>';
									echo '<div class="newspageexcerpt">';
									the_excerpt();
									echo ' <a href="';
									echo the_permalink();
									echo '">Read More &raquo;</a>';	
									echo '</div>';
									echo '</li>';					
								}
							}
					
							?>
						</ul>
					
					<?php dynamic_sidebar('newstories-bottomfeed'); ?>
				</div><!-- /.newstories-bottomfeed -->
<?php get_footer(); ?> 