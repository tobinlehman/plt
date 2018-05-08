<?php get_header(); ?>

    <div class="container search-results">
        <div class="row">
            <div class="col-md-12 no-p-m">
                <?php the_breadcrumbs(">"); ?>
            </div>
            <div class="col-md-9">
                <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'shape' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
                <ol>
                    <?php if( have_posts() ) : while( have_posts() ): ?>
                        <?php the_post(); ?>
                            <?php get_template_part( 'templates/content', 'search' ); ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </ol>
            </div>
            <div class="col-md-3 no-p-m">
                <?php get_template_part('templates/sidebar'); ?>
            </div>
                
        </div>
    </div>
        
<?php get_footer(); ?>
