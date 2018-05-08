<?php get_header(); ?> 

				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<section class="error-404 not-found">
							<header class="page-header">
								<h1 class="page-title"><?php _e('Nope. Not here.', 'bootstrap-basic'); ?></h1>
							</header><!-- .page-header -->

							<div class="page-content">
								<p style="text-align: center;"><img src="/wp-content/uploads/2016/06/searching.jpg" alt="Girl with binoculars" /></p>
								<p><?php _e('We recently redesigned our website. To find content on our new site, browse through our menu or use the search tool below.', 'bootstrap-basic'); ?></p>

								<!--search form-->
								<form class="form-horizontal" method="get" action="<?php echo esc_url(home_url('/')); ?>" role="form">
									<div class="form-group">
										<div class="col-xs-10">
											<input type="text" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder', 'bootstrap-basic'); ?>" title="<?php echo esc_attr_x('Search &hellip;', 'label', 'bootstrap-basic'); ?>" class="form-control" />
										</div>
										<div class="col-xs-2">
											<button type="submit" class="btn btn-default"><?php _e('Search', 'bootstrap-basic'); ?></button>
										</div>
									</div>
								</form>


							</div><!-- .page-content -->
						</section><!-- .error-404 -->
					</main>
				</div>

<?php get_footer(); ?> 