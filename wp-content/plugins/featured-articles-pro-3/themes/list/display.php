<?php 
/**
 * Theme Name: List
 * Author: CodeFlavors
 * Compatibility: Featured Articles PRO 3.0+
 * Uses: Font Awesome
 */
?>
<div class="<?php the_slider_class( 'fa_slider_list' );?>" style="<?php the_slider_styles();?>" id="<?php the_slider_id();?>" <?php the_slider_data();?>>
	<div class="slides">
		<?php 
			// start slides loop
			while( have_slides() ):
		?>
		<div class="slide <?php the_fa_class();?>" style="<?php the_slide_styles();?>">
			<?php 
				// wrap contents
				fa_content_wrapper( '<div class="slide-content">' );
			?>
				<?php 
					// the slide image
					the_fa_image( '<div class="image_container">', '</div>', false, false );
				?>
				<?php 
					// slide title
					the_fa_title( '<h2 class="title"><span>', '</span></h2>' );
				?>
				
				<?php 
					the_fa_date('<span class="slide-date">On ', '</span>');
				?>
				
				<?php 
					the_fa_author('<span class="slide-author">by ', '</span>');
				?>
								
				<?php 
					// output slide content
					the_fa_content( '<div class="text">', '</div>' );
				?>
				<?php 
					// output read more link
					the_fa_read_more( 'read-more' );
				?>		
				<?php 
					// output video link
					the_fa_play_video( 'play-video', 'modal' );
				?>		
			<?php 
				// close content wrap
				fa_content_wrapper('</div>');
			?>			
		</div>
		<?php 
			// slides loop
			endwhile;
		?>
	</div>
	<div class="navigation" data-item="nav">
		
			<?php while( have_slides() ):?>
			<?php 
				$thumbnail = fa_get_custom_image( get_current_slide(), array( 'width' => 70, 'height' => 50 ) );
				$img = $thumbnail ? sprintf( '<img src="%s" alt="" />', $thumbnail ) : '';
			?>
			<div class="nav">
				<a href="#">
					<?php echo $img;?>
					<span class="item_title"><?php the_fa_title('', '', true, true);?></span>
				</a>	
			</div>
			<?php endwhile;?>
		
	</div>
</div>