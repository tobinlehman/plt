<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="categoryimage"><?php the_post_thumbnail(array(400,400)); ?> </div>
		<h2><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	</header><!-- .entry-header -->

	
	<div class="entry-summary">
		<?php the_excerpt(); ?> 
		<div class="clearfix"></div>
	</div><!-- .entry-summary -->

	
	</article><!-- #post-## -->