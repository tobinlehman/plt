<?php
/**
 * Template Name: Page - GreenSchools
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
				
				<div class="greenschools-description">
					<?php dynamic_sidebar('greenschools-description'); ?>
				</div><!-- /.greenschools-description -->

				<div class="greenschools-benefits">
					<?php 
						if(function_exists('fa_dynamic_area')){
							fa_dynamic_area('green_schools_benefits');
						}
					?>
				</div><!-- /.greenschools-benefits -->

				<div class="greenschools-investigations">
					<?php dynamic_sidebar('greenschools-investigations'); ?>
				</div><!-- /.greenschools-investigations -->

				<div class="greenschools-grants">
					<?php dynamic_sidebar('greenschools-grants'); ?>
				</div><!-- /.greenschools-grants -->
				
				<div class="clearfix"></div>

				<div class="greenschools-cta">
					<?php dynamic_sidebar('greenschools-cta'); ?>
				</div><!-- /.greenschools-cta -->

				<div class="greenschools-testimonials">
					<?php dynamic_sidebar('greenschools-testimonials'); ?>
				</div><!-- /.greenschools-testimonials -->
				
				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->
							
				
<?php get_footer(); ?> 