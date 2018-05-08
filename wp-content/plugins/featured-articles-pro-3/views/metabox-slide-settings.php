<?php wp_nonce_field('fa-slide-options-save', 'fa-slide-settings-nonce');?>
<table class="form-table">
	<tbody>
		<?php 
			// on posts other than plugin custom post type, add custom title and custom text fields
			if( parent::get_type_slide() != $post->post_type && !defined('FAPRO_IFRAME') ):
		?>
		<tr>
			<th><label for="fa-custom-title"><?php _e('Slide title', 'fapro');?>:</label></th>
			<td>
				<input type="text" name="fa_slide[title]" id="fa-custom-title" value="<?php echo $options['title'];?>" style="width:80%;" />
				<p class="description"><?php _e('custom title for slide made from this post', 'fapro');?></p>
			</td>
		</tr>
		<?php endif;?>
		
		<?php 
		     /**
		      * Action after slide title field
		      */
		     do_action( 'fapro_after_slide_title_field', $options, $post );
		?>
		
		<?php 
			// on posts other than plugin custom post type, add custom title and custom text fields
			if( parent::get_type_slide() != $post->post_type && !defined('FAPRO_IFRAME') ):
		?>
		<tr>
			<th><label for="fa-custom-content"><?php _e('Slide content', 'fapro');?>:</label></th>
			<td>
				<?php 
					wp_editor( $options['content'] , 'fa-custom-content-post', array(
						'teeny' => true,
						'media_buttons' => false,
						'textarea_name' => 'fa_slide[content]',
						'textarea_rows' => 10
					));
				?>
			</td>
		</tr>
		<?php endif;?>		
		
		<?php 
		     /**
		      * Action after slide title/content fields
		      */
		     do_action( 'fapro_slide_options_fields', $options, $post );
		?>
		
		<tr>
			<th><label for="fa-link_text"><?php _e('Read', 'fapro');?>:</label></th>
			<td>
				<input type="text" name="fa_slide[link_text]" id="fa-link_text" value="<?php echo $options['link_text'];?>" />
				<span class="description"><?php _e('read more text displayed on slide', 'fapro');?></span>
			</td>
		</tr>
		<tr>
			<th><label for="fa-class"><?php _e('Class', 'fapro');?>:</label></th>
			<td>
				<input type="text" name="fa_slide[class]" id="fa-class" value="<?php echo $options['class'];?>" />
				<span class="description"><?php _e('extra CSS class used to style the slide', 'fapro');?></span>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="fa-url"><?php _e('URL', 'fapro');?>:</label></th>
			<td>
				<?php 
					$hidden = (bool)$options['link_to_post'];
					if( fa_post_type_slide() == $post->post_type ){
						$hidden = false;
					}
				?>
				<div id="fa-custom-link-option" <?php fa_hide( $hidden );?>>				
					<input type="text" name="fa_slide[url]" id="fa-url" value="<?php echo $options['url'];?>" />
					<span class="description"><?php _e('URL to point to on the read more link', 'fapro');?></span><br />
				</div>	
				<?php 
					// custom slides aren't public so no need to display link to post
					if( fa_post_type_slide() != $post->post_type ):
				?>
				<input autocomplete="off" type="checkbox" name="fa_slide[link_to_post]" value="1" id="fa-link-post"<?php fa_checked( (bool) $options['link_to_post'] );?> />
				<label for="fa-link-post"><?php _e( 'Link slide to post', 'fapro' );?></label>
				<?php 
					endif;
				?>
			</td>
		</tr>		
		<tr>
			<th><label for="title_color"><?php _e('Title color', 'fapro');?>:</label></th>
			<td>
				<?php 
					fa_color_picker(array(
						'name' 	=> 'fa_slide[title_color]',
						'id'	=> 'title_color',
						'value' => $options['title_color']
					));
				?>
			</td>
		</tr>
		<tr>
			<th><label for="content_color"><?php _e('Content color', 'fapro');?>:</label></th>
			<td>
				<?php 
					fa_color_picker(array(
						'name' 	=> 'fa_slide[content_color]',
						'id'	=> 'content_color',
						'value' => $options['content_color']
					));
				?>
			</td>
		</tr>
		<tr>
			<th><label for="bg_color"><?php _e('Background color', 'fapro');?>:</label></th>
			<td>
				<?php 
					fa_color_picker(array(
						'name' 	=> 'fa_slide[background]',
						'id'	=> 'fa-background',
						'value' => $options['background']
					));
				?>
			</td>
		</tr>
		<?php if( 'attachment' != $post->post_type ):?>
		<tr>
			<th valign="top"><label for="slide_image"><?php _e('Slide image', 'fapro');?>:</label></th>
			<td>
				<?php the_fa_slide_image( $post->ID );?>			
			</td>
		</tr>
		<?php endif;?>				
	</tbody>
</table>
<?php include fa_metabox_path( 'slide-video-settings' );?>
