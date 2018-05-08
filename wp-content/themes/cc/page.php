<?php get_header(); ?>

	<div class="container">
		<div class="row">
			<div class="col-md-12 no-p-m breadcrumb-container">
				<?php the_breadcrumbs(">"); ?>
			</div>
			<div class="col-md-9 no-p-m content">
				<?php if( have_posts() ) : while( have_posts() ): ?>
					<?php the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>
				<?php endif; ?>
			</div>
			<div class="col-md-3 no-p-m">
				<?php get_template_part('templates/sidebar'); ?>
			</div>
				
		</div>
	</div>
		
<?php get_footer(); ?>
