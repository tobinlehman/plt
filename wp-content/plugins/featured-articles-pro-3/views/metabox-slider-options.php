<input type="hidden" name="wp-preview" id="wp-preview" value="" />
<input type="hidden" name="content" id="wp-content" value="" />
<table class="form_table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="slider_cache"><?php _e('Cache slider', 'fapro');?>:</label></th>
			<td>
				<?php if( $settings['cache'] ):?>
				<input type="checkbox" name="slider[cached]" value="1"<?php fa_checked( (bool) $options['slider']['cached'] );?> /><br />
				<?php endif;?>
				<span class="description"><?php echo $cache_time;?></span>			
			</td>
		</tr>
	</tbody>
</table>