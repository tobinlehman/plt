<?php get_header(); ?>
		home
		<?php if( have_posts() ) : while( have_posts() ): ?>
			<?php the_post(); ?>
				
		<?php endwhile; ?>
	<?php endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
