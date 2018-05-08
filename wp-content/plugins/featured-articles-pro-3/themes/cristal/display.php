<?php 
/**
 * Theme Name: Cristal
 * Author: CodeFlavors
 * Compatibility: Featured Articles PRO 3.0+
 * Uses: Font Awesome
 */
?>
<?php 
	// get the options implemented by the slider theme
	$options = get_slider_theme_options();
?>
<div class="<?php the_slider_class( 'fa_slider_cristal' );?>" style="<?php the_slider_styles();?>" id="<?php the_slider_id();?>" <?php the_slider_data();?>>
	<div class="fa_slides">
	<?php while( have_slides() ): ?>
		<div class="fa_slide <?php the_fa_class();?>" style="<?php the_slide_styles();?>">
			<?php the_fa_image( '<div class="image_container">', '</div>', false, false );?>
			<?php 
				$ignore = array( 'image', 'video', 'author' );
				fa_content_wrapper( '<div class="fa_slide_content">', $ignore );
			?>
				<?php 
					// slide title
					the_fa_title('<h2>', '</h2>');
				?>
				<?php 
					the_fa_date('<span class="slide-date">', '</span>');
				?>
				<?php 
					// slide content
					the_fa_content('<div class="description">', '</div>');
				?>
				<?php 
					// slide read more link
					the_fa_read_more('fa_read_more');
				?>
				<?php 
					// slide play video link
					the_fa_play_video('', 'modal');
				?>
			<?php fa_content_wrapper( '</div>', $ignore );?>	
		</div>
	<?php endwhile;?>
	
	<?php if( has_sideways_nav() ):?>
		<div class="go-forward"></div>
		<div class="go-back"></div>
	<?php endif;?>
	
	<?php if( has_bottom_nav() ):?>		
		<?php 
			// display carousel navigation if set
			if( 'carousel' == $options['navigation'] ):
		?>	
		<div class="fa-nav-container-bottom">        
	        <div class="fa_carousel">
				<div class="fa_carousel_inside">
					<ul class="fa_carousel_items FA_navigation">
						<?php while( have_slides() ):?>
							<?php $thumbnail = fa_get_custom_image( get_current_slide(), array( 'width' => 120, 'height' => 70 ) );?>
						<li class="item"><a href="#" class="fa-nav" title="<?php echo esc_attr( the_fa_title('', '', false, true) );?>"><?php if($thumbnail):?> <img src="<?php echo $thumbnail;?>" alt="" /> <?php endif;?></a></li>	
						<?php endwhile;?>
					</ul>
				</div>
				<div class="fa-vertical-center">
					<a href="#" class="nav-back sidenavs"><i class="icon-chevron-left"></i></a>
					<a href="#" class="nav-fwd sidenavs"><i class="icon-chevron-right"></i></a>
				</div>
			</div>		
		</div>
		<?php 
			// display regular dots navigation
			else:
		?>
		<div class="main-nav">
			<?php while( have_slides() ): ?>
			<a href="#" title="" class="fa-nav"></a>
			<?php endwhile;?>		
		</div>
		<?php endif;?>
	<?php endif;?>
		<div class="timer"><!-- slideshow timer --></div>
	</div>	
</div>