<?php 
/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @version 2.4
 * 
 * For more information on FeaturedArticles template functions, see: http://www.codeflavors.com/documentation/display-slider-file/
 */
?>
<div class="<?php the_slider_class( 'fa-accordion' );?>" style="<?php the_slider_styles();?>" id="<?php the_slider_id();?>"  <?php the_slider_data();?>>
    <div class="fa-accordion-inside">
    	<?php 
    		// loop slides
    		while( have_slides() ): 
    	?>
        <div class="slide  <?php the_fa_class();?>" style="<?php the_slide_styles(); ?>">
        	<div class="slide-content">
        		<?php 
        			// the slide image
        			the_fa_image( '<div class="image_container">', '</div>', false, false );
        		?> 
        		<?php 
        			// play video link
        			the_fa_play_video( 'play-video-overlay', 'modal', false );
        		?>       	
	            <div class="info">
	            	<?php 
	            		// slide title
	            		the_fa_title('<h2 class="title idle"><span>', '</span></h2>');
	            	?>
	            	<?php 
	            		// content wrap open
	            		fa_content_wrapper('<div class="hide idle">');
	            	?>
	            		<?php 
	            			// slide date
	            			the_fa_date('<span class="fa-date">On ', '</span> ');
	            		?>
	            		<?php 
	            			// slide author
	            			the_fa_author('<span class="fa-author">by ', '</span><br />');
	            		?>	            	
	            		<?php 
	            			// slide content
	            			the_fa_content('', '');
	            		?>
	            		<?php 
	            			// slide read more link
	            			the_fa_read_more('fa-read-more');
	            		?>
	            		<?php 
	            			// play video below content
	            			the_fa_play_video( 'play-video-text', 'modal' );
	            		?>
	            	<?php 
	            		// content wrap close
	            		fa_content_wrapper('</div>');
	            	?>	            	
	            </div>
        	</div>
        </div>
        <?php 
        	// end slides loop
        	endwhile;
        ?>
   	</div>    	
</div>