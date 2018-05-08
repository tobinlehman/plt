<?php
/**
 * Template Name: Page - Curriculum Offerings
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
				
				<div class="curriculum-offerings-education">
					<?php dynamic_sidebar('curriculum-offerings-education'); ?>
				</div><!-- /.curriculum-offerings-education -->
				
				<div class="curriculum-offerings-unit">
					<?php dynamic_sidebar('curriculum-offerings-unit'); ?>
				</div><!-- /.curriculum-offerings-unit -->
				
				<div class="curriculum-offerings-title">
					<?php dynamic_sidebar('curriculum-offerings-title'); ?>
					<ul class="curriculumpage">
						<?php
	
							wp_reset_postdata();
							
							$temp = $wp_query;
							$wp_query = NULL;
							$args = array();							
							
							$args['post_type'] = array ('curriculum_ct');		
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
									echo '" class="curriculumimage">';
									the_post_thumbnail(array(210,260));
									echo '</a>';
									echo '<h2><a href="';
									the_permalink();
									echo '">';
									the_title();
									echo '</a></h2>';
									echo '<div class="curriculumexcerpt">';
									the_excerpt();	
									echo '</div>';
									echo '</li>';					
								}
							}
					
							?>
						</ul>
						<p style="text-align: right;"><a href="/curriculum-offerings/browse-all/">BROWSE ALL &raquo;</a></p>
				</div><!-- /.curriculum-offerings-title -->
				
				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->			
				
<?php get_footer(); ?> 