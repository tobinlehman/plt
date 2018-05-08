<?php
/**
 * Template Name: Page - Attend a Training
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
				
				<div class="clearfix"></div>
				
				<div class="attendatraining-testimonials">
					<?php
						if( function_exists('fa_dynamic_area') ){
    							fa_dynamic_area( 'attend_a_training_testimonial' );
								}
					?>
					<?php dynamic_sidebar('attendatraining-testimonials'); ?>
				</div><!-- /.attendatraining-testimonials -->
				
				<div class="clearfix"></div>
				
				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->			
				
<?php get_footer(); ?> 