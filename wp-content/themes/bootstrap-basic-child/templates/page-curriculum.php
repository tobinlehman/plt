<?php
/**
 * Template Name: Page - Curriculum
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
				
				<div class="curriculum-description">
					<?php dynamic_sidebar('curriculum-description'); ?>
				</div><!-- /.curriculum-description -->

				<div class="curriculum-offerings">
					<?php 
						if(function_exists('fa_dynamic_area')){
							fa_dynamic_area('curriculum_offerings');
						}
					?>
				</div><!-- /.curriculum-offerings -->

				<div class="curriculum-cta">
					<?php dynamic_sidebar('curriculum-cta'); ?>
				</div><!-- /.curriculum-cta -->

				<div class="curriculum-testimonials">
					<?php dynamic_sidebar('curriculum-testimonials'); ?>
				</div><!-- /.curriculum-testimonials -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->

				<div class="curriculum-lesson-plans">
					<?php dynamic_sidebar('curriculum-lesson-plans'); ?>

					<?php echo do_shortcode("[metaslider id=447]"); ?>
				</div><!-- /.curriculum-lesson-plans -->
							
				
<?php get_footer(); ?> 