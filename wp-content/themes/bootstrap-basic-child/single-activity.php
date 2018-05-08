<?php
/**
 * Template for displaying single post (read full post page).
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
?> 
				<div class="col-md-9 content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<h1 class="entry-title">Resources for <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
							</header><!-- .entry-header --> 
							
							<div class="entry-content">
								<?php the_content(); ?> 
							</div><!-- .entry-content -->
							
						</article>
						
							<div class="studentpages">
								<?php if( have_rows ('student_pages') ): ?>
								<h2>STUDENT PAGES <img src="/wp-content/uploads/2016/07/student_icon_green.png" class="activityicon activityiconright"  /></h2>
								<p>Download the copyright free student pages that are included with this activity:</p>
								
									<?php if( have_rows ('student_pages') ): ?>
								
									<?php while( have_rows('student_pages') ): the_row();
										
										$pagecontent = get_sub_field('student_page_attachment');
										?>
										
										<p><a href="<?php echo $pagecontent['url']; ?>" title="<?php echo $pagecontent['title']; ?>">
										<span><?php echo $pagecontent['title']; ?></span>
										</a> (PDF)</p>
									
									<?php endwhile; ?>
 
									<?php endif; ?>
								<?php endif; ?>
								
								<?php if( get_field('spanish_student_page') ): ?>
								
									<p>Spanish Student Page:</p>
									
									<?php $file2 = get_field('spanish_student_page'); ?>
	
									<?php if( $file2 ): ?>
									
										<?php
											// vars
											$url2 = $file2['url'];
											$title2 = $file2['title'];
										?>
		
										<a href="<?php echo $url2; ?>" title="<?php echo $title2; ?>">
											<span><?php echo $title2; ?></span>
										</a> (PDF)
								
									<?php endif; ?>
								
								<?php endif; ?>
								
								<?php if( have_rows ('spanish_student_pages') ): ?>
								<p>&nbsp;</p>
								
								<p>Spanish Student Page(s):</p>
																
									<?php while( have_rows('spanish_student_pages') ): the_row();
										
										$pagecontent2 = get_sub_field('spanish_student_page_attachment');
										?>
										
										<p><a href="<?php echo $pagecontent2['url']; ?>" title="<?php echo $pagecontent2['title']; ?>">
										<span><?php echo $pagecontent2['title']; ?></span>
										</a> (PDF)</p>
									
									<?php endwhile; ?>
 
								<?php endif; ?>
								
							</div>
							
							<?php
							$orig_post = $post;
						
							global $post; ?>
							
							<?php
							
							$categories_stem = get_the_category($post->ID);
						
							if ($categories_stem) {
								$category_ids_stem = array();
								
								foreach($categories_stem as $individual_category_stem) $category_ids_stem[] = $individual_category_stem->term_id;
									$args_stem=array(
										'category__in' => $category_ids_stem,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> -1, // Number of related posts that will be displayed.
										'post_type'=>'stem_connection',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query_stem = new wp_query( $args_stem );
						
								if( $my_query_stem->have_posts() ) {
									echo '<div class="activitystemconnections">
											<h2>STEM STRATEGIES <img src="/wp-content/uploads/2016/07/gears_icon_green.png" class="activityicon activityiconleft" /></h2>
											<p>Engage students in real-world applications of STEM (science, technology, engineering, math) education.</p>
				
											<p>Try these STEM Connections for this PLT activity:</p><ul>';
									while( $my_query_stem->have_posts() ) {
										$my_query_stem->the_post(); ?>
										<li>
										 <a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
										</li>
									<? }
									echo '</ul></div>';
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								
								 ?>
								 <div class="clearfix"></div>
							
							<?php
						
							$categories = get_the_category($post->ID);
						
							if ($categories) {
								$category_ids = array();
								
								foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
									$args=array(
										'category__in' => $category_ids,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> -1, // Number of related posts that will be displayed.
										'post_type'=>'literature_connectio',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query = new wp_query( $args );
						
								if( $my_query->have_posts() ) {
									echo '<div class="activityliteratureconnections">
											<h2>RECOMMENDED READING <img src="/wp-content/uploads/2016/07/books_icon_green.png" class="activityicon activityiconright"  /></h2>
											<p>Expand your students’ learning and imaginations. Help students meet their reading goals while building upon concepts learned in this activity with the following children’s book recommendations:</p>';
									echo '<div id="recommendedreading" class="clear"><ul>';
									while( $my_query->have_posts() ) {
										$my_query->the_post(); ?>
										<li>
										 <a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
										</li>
									<? }
									echo '</ul></div></div>';
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								
								 ?>
								  <div class="clearfix"></div>
						
 
								<?php
							$categoriesfamily = get_the_category($post->ID);
						
							if ($categoriesfamily) {
								$category_ids_family = array();
								
								foreach($categoriesfamily as $individual_category_family) $category_ids_family[] = $individual_category_family->term_id;
									$args_family=array(
										'category__in' => $category_ids_family,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 1, // Number of related posts that will be displayed.
										'post_type'=>'family_activity',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query_family = new wp_query( $args_family );
						
								if( $my_query_family->have_posts() ) {
									echo '<div class="activityfamilyactivities">
							<h2>FAMILY ACTIVITY <img src="/wp-content/uploads/2016/07/leaf_icon_green.png" class="activityicon activityiconleft"  /></h2>
							<p>Try a simple variation of this activity to engage children in the outdoors at home. Download this fun and easy-to-do';
									while( $my_query_family->have_posts() ) {
										$my_query_family->the_post(); ?>
										 <a href="<?php the_permalink(); ?>">family activity</a>.</p></div>
									<? }
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
							
							<div class="clearfix"></div>
							
							<?php
							$categorieseeresources = get_the_category($post->ID);
						
							if ($categorieseeresources) {
								$category_ids_eeresources = array();
								
								foreach($categorieseeresources as $individual_category_eeresources) $category_ids_eeresources[] = $individual_category_eeresources->term_id;
									$args_eeresources=array(
										'category__in' => $category_ids_eeresources,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> -1, // Number of related posts that will be displayed.
										'post_type'=>'ee_resource',
										'orderby'=>'date',
										'order'=>DESC
									);
									
								$my_query_eeresources = new wp_query( $args_eeresources );
						
								if( $my_query_eeresources->have_posts() ) {
									echo '<div class="activityeeresources">
							<h2>ADDITIONAL RESOURCES <img src="/wp-content/uploads/2016/07/magnifying_icon_green.png" class="activityicon activityiconright"  /></h2>
							<p>Every month we carefully select new tools and resources that enhance PLT’s lessons. These include educational apps, videos, posters, interactive websites, careers information, and teacher-generated materials. Browse a chronological listing below:</p>';
									echo '<div id="eeresources" class="clear"><ul>';
									while( $my_query_eeresources->have_posts() ) {
										$my_query_eeresources->the_post(); ?>
										<li>
										<span class="eeresourcetitle"><?php the_title(); ?></span> <?php the_content(); ?>
										</li>
									<? }
									echo '</ul></div></div>';
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
						
					</main>

				</div>
				
				<div class="keylinks">
					<?php
							
							$categories_tips = get_the_category($post->ID);
						
							if ($categories_tips) {
								$category_ids_tips = array();
								
								foreach($categories_tips as $individual_category_tips) $category_ids_tips[] = $individual_category_tips->term_id;
									$args_tips=array(
										'category__in' => $category_ids_tips,
										'post__not_in' => array($post->ID),
										'posts_per_page'=> 4, // Number of related posts that will be displayed.
										'post_type'=>'educator_tip',
										'orderby'=> 'rand'
									);
									
								$my_query_tips = new wp_query( $args_tips );
						
								if( $my_query_tips->have_posts() ) {
									while( $my_query_tips->have_posts() ) {
										
										$my_query_tips->the_post(); ?>
										<div class="widget">
										 <h2><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
										 <p><?php the_excerpt(); ?></p>
										 <p><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><img src="/wp-content/themes/bootstrap-basic-child/img/white_arrow.png" alt="Icon: white arrow" /></a></p>
										</div>
									<? }
								} }
								
								$post = $orig_post;
								
								wp_reset_query();
								?>
					
					
				</div><!-- /.keylinks -->
				
				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->

<?php get_footer(); ?> 
