<?php
/**
 * Template Name: Page - Trainings
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
$url = '/foo';
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
				
				<div class="trainings-description">
					<?php dynamic_sidebar('trainings-description'); ?>
				</div><!-- /.trainings-description -->

				<div class="trainings-cta">
					<?php dynamic_sidebar('trainings-cta'); ?>
				</div><!-- /.trainings-cta -->

				<div class="trainings-testimonials">
					<?php
						if( function_exists('fa_dynamic_area') ){
    							fa_dynamic_area( 'training_testimonials' );
								}
					?>
					<?php dynamic_sidebar('trainings-testimonials'); ?>
				</div><!-- /.trainings-testimonials -->
				
				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->
				
				<div class="trainings-faq">
					<?php dynamic_sidebar('trainings-faq'); ?>
				</div><!-- /.trainings-faq -->				
				
<?php get_footer(); ?> 