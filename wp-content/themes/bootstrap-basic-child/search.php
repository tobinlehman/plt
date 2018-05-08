<?php
/**
 * The template for displaying search results.
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 

				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<?php if (have_posts()) { ?> 
						<header class="page-header">
							<h1 class="page-title"><?php printf(__('Search Results for: %s', 'bootstrap-basic'), '<span>' . get_search_query() . '</span>'); ?></h1>
						</header><!-- .page-header -->
						<?php 
						// start the loop
						while (have_posts()) {
							the_post();
							
							/* Include the Post-Format-specific template for the content.
							* If you want to override this in a child theme, then include a file
							* called content-___.php (where ___ is the Post Format name) and that will be used instead.
							*/
							get_template_part('content-search', 'search');
						}// end while
						
						bootstrapBasicPagination();
						?> 
						<?php } else { ?> 
						<?php get_template_part('no-results', 'search'); ?>
						<?php } // endif; ?> 
					</main>
				</div>

<?php get_footer(); ?> 