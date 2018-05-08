<div class="wrap">
	<h2><?php _e('Plugin settings', 'fapro');?></h2>
	<?php fa_display_admin_message();?>
	<?php $this->show_errors();?>	
	<form method="post" action="">
		<?php wp_nonce_field('fapro_save_settings', 'fa_nonce');?>
		<div id="fa_tabs" class="fa_tabs">
			<ul class="fa-tab-labels">
				<li><a href="#fa-plugin-settings"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Settings', 'fapro')?></a></li>
				<li><a href="#fa-plugin-access"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Permissions', 'fapro')?></a></li>
				<li><a href="#fa-extra-themes"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Themes', 'fapro')?></a></li>
				<li><a href="#fa-plugin-apis"><i class="dashicons dashicons-arrow-right"></i> <?php _e('YouTube API', 'fapro')?></a></li>
				<li><a href="#fa-plugin-license"><i class="dashicons dashicons-arrow-right"></i> <?php _e('License', 'fapro')?></a></li>				
			</ul>
			
			<!-- Plugin settings -->
			<div id="fa-plugin-settings">
				<h4><i class="dashicons dashicons-admin-tools"></i> <?php _e('Plugin settings', 'fapro');?></h4>
				<table class="form-table">
					<tbody>
						<?php 
							$post_checkboxes = fa_all_post_types_checkboxes(array(
								'name' => 'custom_posts',
								'echo' => false,
								'selected' => $settings['custom_posts']	
							));
							if( $post_checkboxes ):
						?>						
						<tr valign="top">
							<th scope="row">
								<label for=""><?php _e('Custom posts', 'fapro');?>:</label>
							</th>
							<td>
								<?php echo $post_checkboxes;?>
								<p class="description">
									<?php _e('By default, only regular post type is allowed in slideshows.', 'fapro');?><br />
									<?php _e('By checking any of the above post types you will be able to incorporate them into slideshows.', 'fapro');?>
								</p>
							</td>
						</tr>
						<?php endif; // post types select checkboxes?>
						
						<tr valign="top">
							<th scope="row">
								<label for="post_slide_edit"><?php _e('Allow slide editing on post edit', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="post_slide_edit" id="post_slide_edit" value="1"<?php fa_checked( (bool) $settings['post_slide_edit'] );?> />
								<span class="description"><?php _e('If checked will display slide edit settings on allowed posts post edit page.', 'fapro');?></span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="complete_unistall"><?php _e('Full uninstall', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="complete_uninstall" id="complete_unistall" value="1"<?php fa_checked( (bool) $settings['complete_uninstall'] );?> />
								<span class="description"><?php _e('If checked, when the plugin is uninstalled from plugins page, all data (sliders, slides, options and meta fields) will also be removed from database.', 'fapro');?></span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="cache"><?php _e('Cache sliders', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="cache" id="cache" value="1"<?php fa_checked( (bool) $settings['cache'] );?> />
								<span class="description">
									<?php _e('If checked, sliders will be cached.', 'fapro');?><br />
									<?php _e('Sliders made from posts will be cached for 5 minutes while mixed and image sliders will be cached for 12 hours.', 'fapro');?><br />
									<?php _e('Slider cache is flushed when a slider is saved or the slide contents are edited.', 'fapro');?>
								</span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="preload_sliders"><?php _e('Preload sliders', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="preload_sliders" id="preload_sliders" value="1"<?php fa_checked( (bool) $settings['preload_sliders'] );?> />
								<span class="description">
									<?php _e('If checked, sliders will be preloaded.', 'fapro');?><br />
									<?php _e('This means that on every page of your blog a small CSS rule will be injected into the page head section.', 'fapro');?>									
								</span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="edit_links"><?php _e('Show edit links', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="edit_links" id="edit_links" value="1"<?php fa_checked( (bool) $settings['edit_links'] );?> />
								<span class="description">
									<?php _e('If checked, under each slider in front-end an edit slider link will be displayed.', 'fapro');?><br />
									<?php _e('Links will be displayed only for logged in users that can edit sliders.', 'fapro');?>
								</span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="load_font_awesome"><?php _e('Allow Font Awesome', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="load_font_awesome" id="load_font_awesome" value="1"<?php fa_checked( (bool) $settings['load_font_awesome'] );?> />
								<span class="description">
									<?php printf( __('Some slider themes will require %s to be loaded when displaying sliders.', 'fapro'), '<a href="http://fortawesome.github.io/Font-Awesome/" target="_blank">Font Awesome</a>' ) ;?><br />
									<?php _e('If your theme already uses Font Awesome, just uncheck this option to avoid loading it twice on pages displaying sliders.', 'fapro');?>
								</span>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label for="allow_image_autodetect"><?php _e('Allow slides image autodetect', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="allow_image_autodetect" id="allow_image_autodetect" value="1"<?php fa_checked( (bool) $settings['allow_image_autodetect'] );?> />
								<span class="description">
									<?php _e('When checked will try to autodetect image in slide content when displaying sliders if no slide or featured image is found.', 'fapro');?>
								</span>
							</td>
						</tr>
						
						<?php if( fa_is_wptouch_installed() ):?>
						<tr valign="top">
							<th scope="row">
								<label for="load_in_wptouch"><?php _e('Load in WPtouch', 'fapro');?>:</label>
							</th>
							<td>
								<input type="checkbox" name="load_in_wptouch" id="load_in_wptouch" value="1"<?php fa_checked( $settings['load_in_wptouch'] );?> />
								<span class="description">
									<?php _e('By enabling this option you will allow slideshows to be displayed and run into your WPtouch mobile version website.', 'fapro');?><br />
									<?php if( !fa_is_wptouch_exclusive() ): ?>
										<?php _e('Please note that if you have enabled WPtouch <strong>Restricted Mode</strong> setting, no slideshows will be displayed into your website mobile version.', 'fapro');?>
									<?php else:?>
										<strong style="color:red;"><?php _e('WPtouch Restrictive Mode is ON.', 'fapro');?></strong>
										<?php _e('Even if you enable this option, none of the slideshows published into your pages will be displayed into your website mobile version.', 'fapro');?>
									<?php endif; // end WPtouch is exclusive?>
								</span>
							</td>
						</tr>						
						<?php endif; // wpTouch verification?>	
					</tbody>
				</table>	
				<?php submit_button(__('Save settings', 'fapro'));?>
			</div>
			
			<!-- Plugin access -->
			<div id="fa-plugin-access" class="hide-if-js">
				<h4><i class="dashicons dashicons-admin-users"></i> <?php _e('User permissions', 'fapro');?></h4>
				<p class="description">
					<?php _e('This section allows you to give access to plugin pages to different user roles.', 'fapro');?><br />
					<?php _e('By default, administrators have full access and subscribers have no access.', 'fapro');?>
				</p>				
				<table class="form-table">
					<tbody>
						<tr>
							<?php foreach( $roles as $role => $name ):
								$r = get_role( $role );?>
							<th scope="row"><?php printf(_x('%s is allowed to', 'Administrator is allowed to', 'fapro'), $name);?></th>	
							<?php endforeach;?>	
						</tr>
						<?php foreach( parent::get_caps( true ) as $cap ):?>
						<tr>
							<?php foreach( $roles as $role => $name ):
								$r = get_role( $role );
								$checked = $r->has_cap($cap['cap']) ? ' checked="checked"' : '';
								$class = $r->has_cap($cap['cap']) ? ' fa-cap-allowed' : ' fa-cap-denied';?>
							<td>
								<input type="checkbox" name="caps[<?php echo $role?>][<?php echo $cap['cap'];?>]" id="field-<?php echo $role?>-<?php echo $cap['cap'];?>" value="1"<?php echo $checked;?>  />
								<label class="small<?php echo $class;?>" for="field-<?php echo $role?>-<?php echo $cap['cap'];?>"><?php echo $cap['label'];?></label>
							</td>	
							<?php endforeach;//roles loop?>	
						</tr>
						<?php endforeach;// caps loop?>
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'fapro'));?>
			</div>
			
			<!-- Extra themes path -->
			<div id="fa-extra-themes" class="hide-if-js">
				<h4><i class="dashicons dashicons-slides"></i> <?php _e('Extra slideshow themes path', 'fapro');?></h4>
				<p class="description">
					<?php _e('Default plugin slideshow themes can be found in plugin folder themes.', 'fapro');?><br />
					<?php _e('Besides the default themes, you can also create your own slideshow theme to better suit your website needs.', 'fapro');?><br />
				</p>
				<p class="description">	
					<?php _e('While you can put the themes you create into the same themes folder as the default ones are, when updating the plugin you might lose your custom themes.', 'fapro');?><br />
					<?php _e('To avoid this, simply instruct the plugin to look for your themes into a different folder inside wp-content folder. Just select one below and put all themes you create into it.', 'fapro')?><br />
					<?php printf( __('For more detailed instructions see the %stutorial on how to move slider themes outside plugin folder%s.', 'fapro'),'<a href="http://www.codeflavors.com/documentation/intermediate-tutorials/moving-slider-themes-folder/" target="_blank">' ,'</a>' );?>
				</p>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="themes_path"><?php _e('Custom themes folder', 'fapro');?>:</label>
							</th>
							<td>
								<?php echo WP_CONTENT_DIR;?>/
						    	<?php 
						    		fa_select_extra_dir(array(
						    			'name' 		=> 'themes_dir',
						    			'id' 		=> 'themes_dir',
						    			'selected' 	=> $settings['themes_dir'],
						    			
						    		));
						    	?>
							</td>
						</tr>	
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'fapro'));?>
			</div>
			
			<!-- YouTube API Key -->
			<div id="fa-plugin-apis" class="hide-if-js">
				<h4><i class="dashicons dashicons-admin-network"></i> <?php _e('YouTube API server key', 'fapro');?></h4>
				<p class="description">
					<?php _e( 'In order to be able to create slides using YouTube videos, you will have to enter your YouTube API server key.' , 'fapro' );?><br />
					<?php printf( __( 'Google API Projects can be created here: %s', 'fapro' ), '<a href="https://code.google.com/apis/console/" target="_blank">https://code.google.com/apis/console/</a>' );?><br />
					<?php printf( __( 'For a tutorial that will walk you through the process of creating YouTube API credentials, please see this Docs page: %s', 'fapro' ), '<a href="http://www.codeflavors.com/documentation/basic-tutorials/how-to-set-youtube-api-access/" target="_blank">' . __( 'How to set YouTube API access', 'fapro' ) . '</a>');?>
				</p>
				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="youtube_key"><?php _e('YouTube API key', 'fapro');?>:</label>
							</th>
							<td>
								<input type="text" name="youtube_key" id="youtube_key" value="<?php echo $api_keys['youtube_key'];?>" size="40" />
							</td>
						</tr>	
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'fapro'));?>				
			</div>
			
			<!-- Plugin license -->
			<div id="fa-plugin-license" class="hide-if-js">
				<h4><i class="dashicons dashicons-admin-network"></i> <?php _e('Plugin license key', 'fapro');?></h4>
				<p class="description">
					<?php _e('The license key grants you access to plugin updates.', 'fapro');?><br />
					<?php _e('To transfer your license key from one domain to another you must first detach the license from the current domain from your CodeFlavors account.', 'fapro');?>
				</p>
				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="license_key"><?php _e('CodeFlavors License key', 'fapro');?>:</label>
							</th>
							<td>
								<input type="text" name="license_key" id="license_key" value="<?php echo $license['license_key'];?>" size="40" />
							</td>
						</tr>	
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'fapro'));?>
			</div>
		</div>
	</form>
</div>