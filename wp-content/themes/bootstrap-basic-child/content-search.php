<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
	</header><!-- .entry-header -->

	
	<?php if (is_search()) { // Only display Excerpts for Search ?> 
	<div class="entry-summary">
		<?php the_excerpt(); ?> 
		<div class="clearfix"></div>
	</div><!-- .entry-summary -->
	<?php } else { ?> 
	<div class="entry-content">
		<?php the_content(bootstrapBasicMoreLinkText()); ?> 
		<div class="clearfix"></div>
		<?php 
		/**
		 * This wp_link_pages option adapt to use bootstrap pagination style.
		 * The other part of this pager is in inc/template-tags.php function name bootstrapBasicLinkPagesLink() which is called by wp_link_pages_link filter.
		 */
		wp_link_pages(array(
			'before' => '<div class="page-links">' . __('Pages:', 'bootstrap-basic') . ' <ul class="pagination">',
			'after'  => '</ul></div>',
			'separator' => ''
		));
		?> 
	</div><!-- .entry-content -->
	<?php } //endif; ?> 

	</article><!-- #post-## -->