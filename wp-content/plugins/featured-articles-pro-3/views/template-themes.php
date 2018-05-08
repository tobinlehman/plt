<div class="wrap">
	<h2><?php _e('Themes editor', 'fapro');?></h2>
	<?php fa_display_admin_message();?>	
	
	<p class="description"><?php _e('Create different color variations for the existing slider themes.', 'fapro');?></p>	
	
	<div id="fa-tabs" class="fa-horizontal-tabs">
		<ul class="fa-tabs-nav">
			<li><a href="#fa-registered-themes"><?php _e('Themes', 'fapro');?></a></li>
			<li><a href="#fa-editor-preview-settings"><?php _e('Preview settings', 'fapro');?></a></li>
		</ul>
		
		<!-- Themes tab -->
		<div class="panel fa-themes" id="fa-registered-themes">	
			<?php foreach( $themes as $theme => $theme_details ):?>	
			<div class="fa-theme" id="fa-theme-<?php echo esc_attr( $theme );?>">
				<?php 
					$img = isset( $theme_details['preview'] ) && !empty( $theme_details['preview'] ) ? $theme_details['preview'] : false;
				?>
				<div class="fa-screenshot<?php if( !$img ):?> blank<?php endif;?>">
					<?php if( $img ):?><img src="<?php echo $theme_details['preview'];?>" /><?php endif;?>
				</div><!-- .fa-screenshot -->
				<h3 class="theme-name">
					<?php echo $theme_details['theme_config']['name'];?>
				</h3><!-- .theme-name -->
				<div class="theme-actions">
				<?php if( fa_theme_is_customizable( $theme ) ):?>
					<form method="get" action="<?php admin_url();?>" target="_self">
						<input type="hidden" name="page" value="fa-theme-customizer" />
						<input type="hidden" name="theme" value="<?php echo $theme_details['dir'];?>" />					
					<?php 
						$args = array(
							'name' 	=> 'color',
							'id'	=> 'color',
							'label' => __('Color', 'fapro'),
							'selected' => false,
							'multiple' => false,
							'select_opt' => __('Create new', 'fapro'),
							'select_opt_style' => 'font-style:italic; color:green;'
						);
						fa_theme_colors_dropdown( $theme_details, $args );
					?>							
					<?php submit_button( __('Apply', 'fapro'), 'primary', 'submit', false);?>
					</form>
				<?php else:?>
				<span style="color:red;"><?php _e('Not customizable.', 'fapro');?></span>
				<?php endif;?>	
				</div><!-- .theme-actions -->
			</div><!-- .fa-theme -->		
			<?php endforeach;// end themes loop?>	
			<br class="clear" />
		</div><!-- #fa-registered-themes -->	
		
		<!-- Preview settings -->
		<div class="panel preview-settings" id="fa-editor-preview-settings">
			<form method="post" action="">
				<?php wp_nonce_field('fa-theme-editor-preview-options-save', 'fa_nonce');?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="theme-editor-slider-id"><?php _e('Choose slider', 'fapro');?></label></th>
							<td>
								<?php fa_sliders_dropdown('slider_id', 'theme-editor-slider-id', $options['slider_id'], 'slider-id', 'any');?>
								<p class="description">
									<?php _e('The slider will be used as preview when you edit a slider theme using the built-in editor.', 'fapro');?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label for=""><?php _e('Dynamic area', 'fapro');?></label></th>
							<td>
								<?php 
									$args = array(
										'name' 	=> 'area_id',
										'id'	=> 'theme-editor-area-id',
										'selected' => $options['area_id']
									);
									fa_dynamic_areas_dropdown( $args );
								?>
								<p class="description">
									<?php _e('The dynamic area where the preview slider will be published when you edit a slider theme using the built-in editor.', 'fapro');?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __('Save options', 'fapro') );?>
			</form>			
		</div><!-- #fa-editor-preview-settings -->		
	</div><!-- #fa-tabs -->	
</div>