<?php 
/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @version 2.4
 * 
 * For more information on FeaturedArticles template functions, see: http://www.codeflavors.com/documentation/display-slider-file/
 */
?>
<div class="<?php the_slider_class( 'fa-slidenav' );?>" style="<?php the_slider_styles();?>" id="<?php the_slider_id();?>" <?php the_slider_data();?>>	
    <div class="slides">
    	<?php while( have_slides() ): ?>
        <div class="slide  <?php the_fa_class();?>">
        	<?php the_fa_image( '<div class="image_container">', '</div>', false, false );?>
        	<?php fa_content_wrapper('<div class="content">');?>
            	<div class="fa_content">
            		<?php the_fa_title('<h2>', '</h2>');?>
            		<?php the_fa_content('', '');?> 
            		<?php the_fa_read_more( 'read-more' );?>
            		<?php the_fa_play_video( 'play-video', 'modal' )?>
            	</div>
            <?php fa_content_wrapper('</div>');?>
        </div>
        <?php endwhile;?>
    </div>    
    
    <div class="navigation">
        <div class="navigation-inside">
            <ul class="navigation-items">
				<?php 
					$iii = 0;
					while( have_slides() ):
						$class = $iii % 2 != 0 ? ' class="even"' : ''; 
						$iii++; 
						$thumbnail = fa_get_custom_image( get_current_slide(), 'thumbnail');
				?>
                <li<?php echo $class;?>>
                	<?php if($thumbnail):?>
                        <div class="nav-thumb <?php the_slider_color();?>" style="display:none;"><img src="<?php echo $thumbnail;?>" alt="" /></div>
                    <?php endif;?>
                    <strong><?php the_fa_title('', '');?></strong>
                </li>
                <?php endwhile;?>
            </ul>
        </div>
        <a href="#" class="slide-back"></a>
        <a href="#" class="slide-forward"></a>
    </div>
</div>