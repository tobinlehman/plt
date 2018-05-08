<div id="fa-video-settings" class="fapro">
<?php 
	if( $options['video']['video_id'] && $options['video']['source'] ):
?>
	<input type="hidden" name="fa_slide[video][source]" value="<?php echo $options['video']['source'];?>" />
	<input type="hidden" name="fa_slide[video][video_id]" value="<?php echo $options['video']['video_id'];?>" />
	<input type="hidden" name="fa_slide[video][duration]" value="<?php echo $options['video']['duration'];?>" />
	<table class="form_table">
		<tbody>
			<!-- Video player options -->	
			<tr>
				<td colspan="2"><h4><?php _e('Video settings', 'fapro');?></h4></td>
			</tr>	
			<tr>
				<th><label for="fa-width"><?php _e('Player size', 'fapro');?>:</label></th>
				<td class="fa-player-aspect-options">
					<label for="fa-slide-video-aspect"><?php _e('Aspect ratio', 'fapro')?>:</label>
					<?php 
						fa_select_aspect_ratio(array(
							'name' => 'fa_slide[video][aspect]',
							'id' => 'fa-slide-video-aspect',
							'selected' => $options['video']['aspect']
						));
					?>
					
					<label for="fa-width"><?php _e('Width', 'fapro');?>:</label> <input class="fa_video_width" size="2" type="text" id="fa-width" name="fa_slide[video][width]" value="<?php echo $options['video']['width'];?>" /> px |
					<?php _e('Height', 'fapro');?>: <span class="fa_video_height"><?php echo fa_player_height( $options['video']['aspect'], $options['video']['width'] );?></span> px
					
				</td>			
			</tr>
			<tr>
				<th><label for="fa-volume"><?php _e( 'Volume', 'fapro' );?>:</label></th>
				<td>
					<input type="text" value="<?php echo $options['video']['volume'];?>" name="fa_slide[video][volume]" id="fa-volume" size="2" />
					<span class="description"><?php _e('playback volume (between 0 and 100)', 'fapro');?></span>
				</td>
			</tr>
			<tr>
				<th><label for="fa-play"><?php _e( 'Autoplay', 'fapro' );?>:</label></th>
				<td>
					<input type="checkbox" value="1" name="fa_slide[video][play]" id="fa-play"<?php fa_checked( (bool) $options['video']['play'] );?> />
					<span class="description"><?php _e('video will start playing when slide is active', 'fapro');?></span>
				</td>
			</tr>
			<tr>
				<th><label for="fa-fullscreen"><?php _e( 'Allow full screen', 'fapro' );?>:</label></th>
				<td>
					<input type="checkbox" value="1" name="fa_slide[video][fullscreen]" id="fa-fullscreen"<?php fa_checked( (bool) $options['video']['fullscreen'] );?> />
					<span class="description"><?php _e('allow the player to go fullscreen', 'fapro');?></span>
				</td>
			</tr>
			<tr>
				<th><label for="fa-loop"><?php _e( 'Loop', 'fapro' );?>:</label></th>
				<td>
					<input type="checkbox" value="1" name="fa_slide[video][loop]" id="fa-loop"<?php fa_checked( (bool) $options['video']['loop'] );?> />
					<span class="description"><?php _e('video will restart when reaching the end', 'fapro');?></span>
				</td>
			</tr>		
		</tbody>
	</table>
	
	<?php if( 'vimeo' == $options['video']['source'] ):?>
	<!-- Vimeo settings -->
	<div id="fa-vimeo">
		<h4><?php _e('Vimeo settings', 'fapro');?></h4>
		<p class="description">
			<?php _e('If the owner of a video is a Plus member, some of these settings may be overridden by their preferences.', 'fapro');?>
		</p>	
		<table class="form_table">
			<tbody>
				<tr>
					<th><label for="fa-title"><?php _e( 'Title', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][title]" id="fa-title"<?php fa_checked( (bool) $options['video']['title'] );?> />
						<span class="description"><?php _e('show video title in player', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-byline"><?php _e( 'Byline', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][byline]" id="fa-byline"<?php fa_checked( (bool) $options['video']['byline'] );?> />
						<span class="description"><?php _e('show video author name in player', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-portrait"><?php _e( 'Portrait', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][portrait]" id="fa-portrait"<?php fa_checked( (bool) $options['video']['portrait'] );?> />
						<span class="description"><?php _e('show author avatar in player', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-color"><?php _e( 'Color', 'fapro' );?>:</label></th>
					<td>
						<?php 
							fa_color_picker(array(
								'name' => 'fa_slide[video][color]',
								'id' => 'fa-color',
								'value' => $options['video']['color']
							));
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<p><a class="button" href="#" id="fapro-update-player"><?php _e('Preview Vimeo video changes', 'fapro')?></a></p>
	</div><!-- #fa-vimeo -->
	<?php endif; // end vimeo video check?>
	
	<!-- YouTube settings -->
	<?php if( 'youtube' == $options['video']['source'] ):?>
	<div id="fa-youtube">
		<h4><?php _e('YouTube settings', 'fapro');?></h4>
		<table class="form_table">
			<tbody>
				<tr>
					<th><label for="fa-controls"><?php _e( 'Controls', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][controls]" id="fa-controls"<?php fa_checked( (bool) $options['video']['controls'] );?> />
						<span class="description"><?php _e('show video controls in player', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-autohide"><?php _e( 'Auto hide controls', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][autohide]" id="fa-autohide"<?php fa_checked( (bool) $options['video']['autohide'] );?> />
						<span class="description"><?php _e('hide controls when video is playing', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-annotations"><?php _e( 'Hide annotations', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][iv_load_policy]" id="fa-annotations"<?php fa_checked( (bool) $options['video']['iv_load_policy'] );?> />
						<span class="description"><?php _e('hide annotations placed in videos', 'fapro');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="fa-modestbranding"><?php _e( 'Hide YouTube logo', 'fapro' );?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="fa_slide[video][modestbranding]" id="fa-modestbranding"<?php fa_checked( (bool) $options['video']['modestbranding'] );?> />
						<span class="description"><?php _e('when checked will show logo on video', 'fapro');?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<p><a class="button" href="#" id="fapro-update-player"><?php _e('Preview YouTube video changes', 'fapro')?></a></p>
	</div><!-- #fa-youtube -->
	<?php endif; // end youtube video check?>
	
	<!-- Live video embed -->
	<h4><?php _e('Video', 'fapro');?></h4>
	<div id="fa-video-output">
		<?php 
			$vid_opts = $options['video'];
			$vid_opts['width'] = 640;
			fa_video_output( $vid_opts, false, false, false );
		?>		
	</div>
<?php 
	endif;// end video checking
?>
</div><!-- #fa-video-settings -->