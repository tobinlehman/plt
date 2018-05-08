<div class="wrap">
	<h2><?php _e('Dynamic areas', 'fapro');?></h2>
	<div class="widget-liquid-left">
		<div id="widgets-left">
			<div id="available-widgets" class="widgets-holder-wrap">
				<div class="sidebar-name">
					<h3><?php _e('Available sliders', 'fapro');?></h3>
				</div><!-- .sidebar-name -->
				<div class="widget-holder">
					<div class="sidebar-description">
						<p class="description">
							<?php _e('To assign a slider drag it to an area. To remove a slider from an area, drag it back.', 'fapro');?>
						</p>
					</div>
					<div id="widget-list">
						<?php 
							$sliders = fa_get_sliders();
							foreach( $sliders as $slider ):
						?>
						<!-- single item -->
						<?php fa_slider_area_output( $slider );?>
						<!-- /single item -->						
						<?php endforeach;?>						
					</div><!-- .widget-list -->
					<br class="clear">
				</div><!-- .widget-holder -->
				<br class="clear">
			</div><!-- #available-widgets -->
		</div><!-- #widgets-left -->
	</div><!-- .widget-liquid-left -->
	
	<div class="widget-liquid-right single-sidebar">
		<div id="widgets-right">
			<div class="sidebars-column-1">
			<?php foreach( $areas as $area_id => $data ):?>
			<?php
				// leave widgets out 
				if( 'widget' == $area_id ){
					continue;
				}
			?>
				<!-- single slider area -->
				<div class="widgets-holder-wrap">
					<?php if('loop_start' != $area_id):?>
					<div class="fa-area-actions">
						<?php 
							$url = add_query_arg(array(
								'action' 	=> 'delete',
								'area'		=> $area_id
							), html_entity_decode( menu_page_url('fapro_hooks', false) ) );
						?>
						<a href="<?php echo wp_nonce_url( $url, 'fa_remove_dynamic_area', 'fa_nonce' );?>" class="fa-area-action action-delete" title="<?php esc_attr_e('Delete area', 'fapro');?>"></a>
						<?php 
							$url = add_query_arg(array(
								'edit'		=> $area_id
							), html_entity_decode( menu_page_url('fapro_hooks', false) ) );
						?>
						<a href="<?php echo $url;?>" class="fa-area-action action-edit" id="action-edit" title="<?php esc_attr_e('Edit area', 'fapro');?>"></a>
					</div>
					<?php endif;?>
					<div id="<?php echo $area_id;?>" class="widgets-sortables">
						<div class="sidebar-name">
							<div class="sidebar-name-arrow"><br></div>
							<h3><?php echo $data['name'];?><span class="spinner"></span></h3>
						</div>
						<div class="sidebar-description">
							<p class="description"><?php echo $data['description'];?></p>
							<?php if( 'loop_start' != $area_id ):?>
							<div class="description">
								<span class="fa-area-code-label"><?php _e('Area PHP code', 'fapro');?></span>
								<div class="fa-area-desc">	
									<div class="fa-area-code">
										&lt;?php<br /> 
										if( function_exists( 'fa_dynamic_area' ) ){<br />
										&nbsp;&nbsp;&nbsp;&nbsp;fa_dynamic_area( '<?php echo $area_id;?>' );<br />
										}<br />									
										?&gt;
									</div>
									<p class="info">
										* <?php _e('Place the code above in your WP theme files where you want to display sliders.', 'fapro');?>
									</p>
								</div>	
							</div>
							<?php endif;?>
						</div>
						<?php 
							$sliders = (array) $data['sliders']; 
							foreach( $sliders as $slider_id ){
								fa_slider_area_output( $slider_id );
							}
						?>																							
					</div><!-- .widgets-sortables -->												
				</div><!-- .widgets-holder-wrap -->	
				<!-- /single slider area -->
			<?php endforeach;?>	
			</div><!-- .sidebars-column-1 -->
			
			<div class="sidebars-column-2">
				<p class="description">
					<?php _e('Create a new automatic placement area.', 'fapro');?>
					<?php _e('Automatic placement areas can be placed into WordPress theme files to allow you to quickly publish sliders into any page.', 'fapro');?>
				</p>
				<form method="post" action="">
					<?php wp_nonce_field( 'fa_create_dynamic_area', 'fa_area_nonce' );?>
					<input type="hidden" name="action" value="<?php echo ( isset( $edit_area ) ? 'edit' : 'create' );?>" />
					<?php if( isset( $area_edited ) ):?>
					<input type="hidden" name="area" value="<?php echo $area_edited;?>" />
					<?php endif;?>
					<table class="form-table">
						<tbody>
							<tr>
								<th><label for="hook_name"><?php _e('Name', 'fapro');?>:</label></th>
								<td><input type="text" name="hook_name" value="<?php echo $name;?>" /></td>
							</tr>
							<tr>
								<th><label for="hook_description"><?php _e('Description', 'fapro');?>:</label></th>
								<td><textarea name="hook_description" style="width:100%; height:150px;"><?php echo $description;?></textarea></td>
							</tr>
						</tbody>
					</table>
					<?php submit_button( ( isset( $edit_area ) ? __('Edit area', 'fapro')  : __('Register area', 'fapro' ) ) );?>
				</form>	
			</div>
		</div><!-- .widgets-right -->
	</div><!-- .widget-liquid-right -->
</div>