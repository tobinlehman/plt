<?php
/**
 * Template Name: Page - Homepage
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
							
							// If comments are open or we have at least one comment, load up the comment template
							if (comments_open() || '0' != get_comments_number()) {
								comments_template();
							}

							echo "\n\n";

						} //endwhile;
						?> 
					</main>
				</div>
				
				<div class="homepage-description">
					<?php dynamic_sidebar('homepage-description'); ?>
				</div><!-- /.homepage-description -->
				
				<div class="homepage-email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.homepage-email-signup -->

				<div class="homepage-resources">
					<?php dynamic_sidebar('homepage-resources'); ?>
				</div><!-- /.homepage-resources -->
				
				<div class="homepage-news">
					<div class="container">
						<?php dynamic_sidebar('homepage-news'); ?>
						
						<div id="latest-news">
						<ul>
							<?php
	
							wp_reset_postdata();
							
							$temp = $wp_query;
							$wp_query = NULL;
							$args = array();							
							
							$args['post_type'] = array ('post');		
							$args['orderby'] = 'date';
							$args['order'] = 'DESC';
							$args['posts_per_page'] = 2;
							$args['post_status'] = 'publish';
							
							$wp_query = new WP_Query($args);
							
							if ( $wp_query->have_posts() ) {
								while ( $wp_query->have_posts() ) {
									$wp_query->the_post();
									echo '<li>';
									echo '<a href="';
									the_permalink();
									echo '" class="newsimage">';
									the_post_thumbnail('medium');
									echo '</a>';
									echo '<div class="homepagenewsmeta"><strong>';
									the_date();
									echo '</strong> | ';
									the_category( ' ' );
									echo '</div>';
									echo '<h3><a href="';
									the_permalink();
									echo '">';
									the_title();
									echo '</a></h3>';
									echo '<div class="newsexcerpt">';
									the_excerpt();
									echo ' <a href="';
									echo the_permalink();
									echo '">Read More &raquo;</a>';	
									echo '</div>';
									echo '<hr />';
									echo '</li>';					
								}
							}
					
							?>
						</ul>
						</div><!-- /#latest-news -->
				
					<?php dynamic_sidebar('homepage-news-footer'); ?>
					</div><!-- /.contaier -->
					
				</div><!-- /.homepage-news -->
				
				
<?php get_footer(); ?> 