<?php iframe_header();?>
<!-- Theme editor -->
<div class="wp-full-overlay expanded">
	<form id="customize-controls" class="wrap wp-full-overlay-sidebar" method="post" action="">
		<?php wp_nonce_field( 'fa-save-color-scheme', 'fa_nonce' );?>
		<input type="hidden" name="theme" value="<?php echo $theme['dir'];?>" />
		
		<div id="customize-header-actions" class="wp-full-overlay-header">
			<?php submit_button( __('Save', 'fapro'), 'primary save', 'save', false ); ?>
			<span class="spinner"></span>
			<a class="back button" href="<?php menu_page_url( 'fapro_themes', true );?>">
				<?php _e( 'Cancel', 'fapro' ); ?>
			</a>
		</div>		
		<div class="wp-full-overlay-sidebar-content accordion-container" tabindex="-1">
			<div id="customize-info" class="accordion-section cannot-expand">
				<div class="accordion-section-title" aria-label="<?php esc_attr_e( 'Theme Customizer Options' ); ?>" tabindex="0">
					<span class="preview-notice"><?php
						/* translators: %s is the theme name in the Customize/Live Preview pane */
						echo sprintf( __( 'You are configuring slider theme %s' ), '<strong class="theme-name">' . $theme['theme_config']['name'] . '</strong>' );
					?></span>
					<div class="color-details">
						<?php if( isset($theme_color) ):?>
						<div class="theme-description"><?php printf( __('Editing color scheme <strong>%s</strong>.', 'fapro'), $theme_color);?></div>
						<input type="hidden" name="color_name" value="<?php echo $theme_color;?>" />
						<?php else:?>
						<div class="theme-description"><?php _e('Create new color scheme.', 'fapro');?></div>
						<label>
							<span class="customize-control-title"><?php _e('Enter color scheme name', 'fapro');?></span>
							<div><input type="text" name="color_name" value="" /></div>
						</label>
						<?php endif;?>
						
						<?php 
						// display the layout variations for the theme
						$selector = substr( $style_rules['container']['css_selector'], 1 );						
						$args = array(
							'name' 		=> 'theme-views-' . $theme['dir'],
							'id' 		=> 'theme-views',
							'selected' 	=> false,
							'select_opt' => __('Default', 'fapro'),
							'class'		=> 'theme-views',
							'attrs'		=> 'data-apply_to="' . $selector . '"',
							'echo'		=> false
						);
						$select_box = fa_theme_layouts_dropdown( $theme['dir'], $args );
						if( $select_box ){							
						?>						
						<label for="theme-views">
							<?php _e('Select layout variation', 'fapro');?>
							<?php echo $select_box;?>
						</label>
						<?php
						}
						?>
					</div>					
				</div>				
			</div>
			<!-- Properties -->
			<div id="customize-theme-controls">
				<ul>
					<?php 
						foreach( $style_rules as $element => $details ):
							$el_properties = $details['properties'];
							if( !$el_properties ){
								continue;
							}					
					?>					
					<li class="control-section accordion-section">
						<h3 class="accordion-section-title"><?php echo $details['description'];?></h3>
						<ul class="accordion-section-content">
							<li class="fa-settings-container" data-selector="<?php echo $details['css_selector'];?>">								
							<?php 
								foreach( $el_properties as $property => $data ):
									$id = $element.'-'.$property;
									switch( $data['type'] ):
										case 'color':
										?>
										<label>
											<span class="customize-control-title"><?php  echo $data['text'];?></span>
											<?php 
												$args = array(
													'name' 	=> $element.'[' . $property . ']',
													'id' 	=> $id,
													'class' => 'fa-single-color-picker',
													'value' => $values[ $element ][ $property ],
													'attr'	=> 'data-property="' . $property . '"',
													'autoload' => false
												);											
												fa_color_picker( $args );
											?>	
										</label>
										<?php	
										break;// case:color	
										case 'size':
										?>										
										<span class="customize-control-title"><?php  echo $data['text'];?></span>
										<input type="text" name="<?php echo $element;?>[<?php echo $property;?>][value]" value="<?php echo $values[ $element ][ $property ]['value'];?>" id="<?php echo $id;?>" class="fa-size-input" data-property="<?php echo $property;?>" />
										<select name="<?php echo $element?>[<?php echo $property?>][unit]" class="customize-control-size-unit" data-for="<?php echo $id;?>">
											<option value="px">px</option>
											<option value="em"<?php if('em' == $values[ $element ][ $property ][ 'unit' ]):?> selected="selected"<?php endif;?>>em</option>
										</select>
										<?php	
										break;// case:size
										case 'size_px':
										?>
										<label>
											<span class="customize-control-title"><?php  echo $data['text'];?></span>
											<input type="text" name="<?php echo $element;?>[<?php echo $property;?>]" value="<?php echo $values[ $element ][ $property ];?>" id="<?php echo $id;?>" class="fa-size-input size-px" data-property="<?php echo $property;?>" /> px
										</label>	
										<?php
										break; // case:size_px
										case 'image':
										?>										
										<span class="customize-control-title"><?php  echo $data['text'];?></span>
										<div id="fa_elem_<?php echo $element;?>">
											<div class="fa_slide_image">
											<?php if( !empty( $values[ $element ][ $property ] ) && 'none' != $values[ $element ][ $property ] ):?>
												<img src="<?php echo $values[ $element ][ $property ];?>" alt="" />
												<p><a href="#" class="remove_image" data-url_field="<?php echo $id;?>" data-property="<?php echo $property;?>"><?php _e('Remove image', 'fapro');?></a></p>												
											<?php endif;?>
											</div>
										</div>
										<?php 
											$args = array(
												'page_title' 		=> __('Select image', 'fapro'),
												'button_text' 		=> __('Set image', 'fapro'),
												'select_multiple' 	=> false,
												'class'				=> 'button',
												'update_elem'		=> '#fa_elem_' . $element,
												'append_response'	=> false,
												'attributes'		=> 'data-property="' . $property . '" data-url_field="' . $id . '"'
											);
											fa_media_gallery( $args );
										?>
										<input type="hidden" name="<?php echo $element;?>[<?php echo $property?>]" id="<?php echo $id?>" value="<?php echo $values[ $element ][ $property ] ;?>" />
										<?php
										break; // case:image
										case 'options':
										?>										
										<span class="customize-control-title"><?php  echo $data['text'];?></span>
										<select name="<?php echo $element;?>[<?php echo $property?>]" id="<?php echo $id;?>" class="fa-option-prop" data-property="<?php echo $property;?>">
											<?php foreach($data['options'] as $option):?>
											<?php $selected = $values[$element][$property] == $option ? 'selected="selected"' : '';?>
											<option value="<?php echo $option;?>" <?php echo $selected;?>><?php echo $option;?></option>
											<?php endforeach;?>
										</select>											
										<?php
										break;// case: options
										case 'multi_value':
										?>										
										<span class="customize-control-title"><?php  echo $data['text'];?></span>
										<ul class="multi-value" data-property="<?php echo $property;?>">
										<?php 
										foreach($data['values'] as $s_property => $s_details):
											$id .= '-'.$s_property;
										?>							
											<li>
												<span class="customize-control-title"><?php echo $s_details['name'];?></span>													
												<?php if( 'size_px' == $s_details['type'] ):?>
													<input type="text" name="<?php echo $element;?>[<?php echo $property;?>][<?php echo $s_property;?>]" value="<?php echo $values[$element][$property][$s_property];?>" id="<?php echo $id;?>" class="fa-size-input" /> px
												<?php elseif ('color' == $s_details['type']):?>
													<?php 
														$args = array(
															'name' 	=> $element . '[' . $property . ']' . '[' . $s_property . ']' ,
															'id' 	=> $id,
															'value' => $values[ $element ][ $property ][ $s_property ],
															'class' => 'fa-multi-color-picker',
															'autoload' => false
														);
														fa_color_picker( $args );
													?>
												<?php endif; // end checking sub-property type?>													
											</li>																	
										<?php endforeach; // end multiple values properties?>
										</ul>
										<?php	
										break;// case: multi_value	
									endswitch;
								?>				
							<?php endforeach;?>				
							</li>
						</ul>
					</li>					
					<?php endforeach;?>								
				</ul>
			</div>
			<!-- /Properties -->
		</div>		
	</form>
	<div id="customize-preview" class="wp-full-overlay-main">
		<?php 
			$post_id = theme_editor_preview_post_id();			
			if( $post_id ):
				$color = isset( $_GET['color'] ) ? $_GET['color'] : '';
				$preview_args = array(
					'post_id' 	=> $post_id,
					'theme' 	=> $theme['dir'],
					'vars'		=> array(
						'color' 	=> $color,
						'action' 	=> 'theme_edit',
						'time'		=> time()
					)
				);
		?>
		<iframe id="fa-theme-color-preview" src="<?php fa_slider_preview_homepage( $preview_args );?>"></iframe>
		<?php else:?>
		<div class="error">
			<p><?php _e('In order to be able to edit themes, you need to have at least one slider created.', 'fapro');?></p>
			<p><?php _e("Plese note that the slider doesn't neccessarly has to be published. A draft will do.")?></p>
			<p><?php printf( __('You can create your first slider %shere%s.', 'fapro'), '<a href="post-new.php?post_type=' . fa_post_type_slider() . '">', '</a>' );?></p>
		</div>		
		<?php endif;// if( $post_id )?>		
	</div><!-- #customize-preview -->
</div><!-- .wp-full-overlay -->
<!-- /Theme editor -->
<?php iframe_footer();?>