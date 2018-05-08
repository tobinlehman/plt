<div id="fa-video-query-container">	
	<div id="fa-video-query-messages"></div>
	<table class="form_table" width="100%">
		<tbody>
			<?php 
				if( !$options['video']['video_id'] || !$options['video']['source'] ):
			?>
			<tr valign="top">
				<th scope="row"><label for="fa_video_source_yt"><?php _e('Video source', 'fapro');?>:</label></th>
				<td>
					<?php 
						fa_video_sources_checkboxes(array(
							'name' 		=> 'fa_video_source',
							'id' 		=> 'fa_video_source',
							'selected' 	=> false 
						));					
					?>				
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="fa_video_id"><?php _e('Video ID', 'fapro');?>:</label></th>
				<td>
					<input type="text" name="fa_video_id" id="fa_video_id" size="15" />
				</td>
			</tr>
			<?php 
				else:
			?>
			<tr>
				<td colspan="2">
					<p>
						<strong><?php _e('Video source', 'fapro');?>:</strong> <?php echo ucfirst( $options['video']['source'] );?><br />
						<strong><?php _e('Video ID', 'fapro')?>:</strong> <?php echo $options['video']['video_id'];?><br />
						<strong><?php _e('Duration', 'fapro')?>:</strong> <?php echo fa_video_duration( $options['video']['duration'], __('unknown', 'fapro') );?>
					</p>
					<input type="hidden" name="fa_video_source" id="fa_video_source" value="<?php echo $options['video']['source'];?>" />
					<input type="hidden" name="fa_video_id" id="fa_video_id" value="<?php echo $options['video']['video_id'];?>" />
					<a class="button" id="fa-remove-video"><?php _e('Remove attached video', 'fapro');?></a>
				</td>
			</tr>				
			<?php 
				endif; // end checking if video already attached
			?>
			
			<tr valign="top">
				<td colspan="2">
					<hr />
					<input type="checkbox" name="fa_set_title" id="fa_set_title" value="1" autocomplete="off" />
					<label for="fa_set_title"><?php _e('Set video title as title', 'fapro');?></label>
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<input type="checkbox" name="fa_set_content" id="fa_set_content" value="1" autocomplete="off" />
					<label for="fa_set_content"><?php _e('Set video content as content', 'fapro');?></label>
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<input type="checkbox" name="fa_set_image" id="fa_set_image" value="1" autocomplete="off" />
					<label for="fa_set_image"><?php _e('Set image as featured image', 'fapro');?></label>
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<input type="checkbox" name="fa_set_slide_image" id="fa_set_slide_image" value="1" autocomplete="off" />
					<label for="fa_set_slide_image"><?php _e('Set image as slide image', 'fapro');?></label>
				</td>
			</tr>
			<?php 
				if( !$options['video']['video_id'] || !$options['video']['source'] ):
			?>
			<tr valign="top">
				<td colspan="2">
					<input type="checkbox" name="fa_set_video" id="fa_set_video" value="1" autocomplete="off" />
					<label for="fa_set_video"><?php _e('Attach video to slide', 'fapro');?></label>
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<td colspan="2">				
					<p>
						<input type="button" class="button secondary" name="video-query" value="<?php esc_attr_e( 'Query video', 'fapro' );?>" id="fa-video-query-btn" />
					</p>
				</td>
			</tr>		
		</tbody>
	</table>	
</div>	