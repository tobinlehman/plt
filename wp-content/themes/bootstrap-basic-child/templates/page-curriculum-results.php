<?php
/**
 * Template Name: Page - Curriculum Results
 * 
 * @package bootstrap-basic
 */

// retrieve our search query if applicable
$query = isset( $_REQUEST['swpquery'] ) ? sanitize_text_field( $_REQUEST['swpquery'] ) : '';
// retrieve our pagination if applicable
$swppg = isset( $_REQUEST['swppg'] ) ? absint( $_REQUEST['swppg'] ) : 1;
if ( class_exists( 'SWP_Query' ) ) {
	$engine = 'curriculum_offerings'; // taken from the SearchWP settings screen
	$swp_query = new SWP_Query(
		// see all args at https://searchwp.com/docs/swp_query/
		array(
			's'      => $query,
			'engine' => $engine,
			'page'   => $swppg,
		)
	);
	// set up pagination
	$pagination = paginate_links( array(
		'format'  => '?swppg=%#%',
		'current' => $swppg,
		'total'   => $swp_query->max_num_pages,
	) );
}


get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 
				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						
						<header class="page-header">
						<h1 class="page-title">
							<?php if ( ! empty( $query ) ) : ?>
								<?php printf( __( 'Curriculum Search Results for: <em>%s</em>', 'bootstrap-basic' ), $query ); ?>
							<?php else : ?>
								Search our curriculum
							<?php endif; ?>
						</h1>
		
					</header><!-- .page-header -->
		
					<?php if ( ! empty( $query ) && isset( $swp_query ) && ! empty( $swp_query->posts ) ) {
						foreach ( $swp_query->posts as $post ) {
							setup_postdata( $post );
		
							// output the result
							get_template_part( 'content', 'search' );
						}
						
						wp_reset_postdata();
		
						// pagination
						if ( $swp_query->max_num_pages > 1 ) { ?>
							<div class="navigation pagination" role="navigation">
								<h2 class="screen-reader-text">Posts navigation</h2>
								<div class="nav-links">
									<?php echo wp_kses_post( $pagination ); ?>
								</div>
							</div>
						<?php }
					} else {
						get_template_part( 'content', 'none' );
					} ?>
						
						<h2>Search again</h2>
						
						<div class="search-box">
							<form role="search" method="get" class="search-form" action="<?php echo get_permalink( 443 ); ?>">
								<label>
								<span class="screen-reader-text">Search for:</span>
								</label>
								<input type="search" class="search-field" placeholder="Search our curriculum" value="" name="swpquery" title="Search for:">
								<input type="submit" class="search-submit" value="Search">
							</form>
						</div><!-- /.search-box -->
						
						<p>&nbsp;</p>
						
					</main>
				</div>


				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->


<?php get_footer(); ?> 