<?php
/*
 * MailChimp Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.php 2813 2015-07-29 14:30:25Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for MailChimp Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.mailchimp.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the MailChimp Integration settings for each membership level.
 */

$__index__ = 'mailchimp';
$__ar_options__[$__index__] = 'MailChimp';
$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		
		if (class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT')) {
			$api_key = $data[$__index__]['mcapi'];
			if ($api_key != "") {
				$WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
				$lists = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mcCallServer("lists", array("limit"=>100), $api_key);
				$start = floor ( $lists["total"] / 100); //100 is the maximum number of lists to return with each call
				$offset = 1;
				while ($offset <= $start){
					$lists2 = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mcCallServer("lists", array("start"=>$offset, "limit"=>100), $api_key);
					$lists = array_merge_recursive($lists, $lists2);
					$offset += 1;
				}
				if (!isset($lists['error']) && $lists['total'] > 0) {
					$lists = $lists['data'];
				} else {
					$lists = array();
				}
			}	
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.wlmmcAction').change(function(){
					var selected = jQuery(this).val();
					if(selected == "unsub" || selected == ""){
						jQuery(this).parent().find("input").val("");
						jQuery(this).parent().find("input").prop("disabled",true);
						jQuery(this).parent().find("input").addClass("disabled");
					}else{
						jQuery(this).parent().find("input").removeClass("disabled");
						jQuery(this).parent().find("input").prop("disabled",false);
					}
				});
			});
		</script>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<h2 class="wlm-integration-steps">Step 1. Configure the MailChimp API Settings:</h2>
			<p><?php _e('API Credentials can be found within the MailChimp account by using the following link:', 'wishlist-member'); ?><br><a href="http://admin.mailchimp.com/account/api/" target="_blank">http://admin.mailchimp.com/account/api/</a></p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('MailChimp API Key', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[mcapi]" value="<?php echo $data[$__index__]['mcapi']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-API-Key"); ?>
						<br />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Double Opt-in:', 'wishlist-member'); ?></th>
					<td colspan="2">
						<p>
							<?php $optin = ($data[$__index__]['optin'] == 1 ? true : false); ?>
							<input type="checkbox" name="ar[optin]" value="1" <?php echo $optin ? "checked='checked'" : ""; ?> /> Disable Double Opt-in <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-optin"); ?>
						</p>
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding MailChimp Lists:</h2>
			<p>Membership Levels can be assigned to Email Lists by selecting a List ID from the corresponding column below.</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" style="width:38%;"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" style="width:30%;"><?php _e('List Id', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-Lists-Unique-Id"); ?>
						</th>
						<th class="col" style="width:2%;">&nbsp;</th>
						<th class="col" style="width:30%;"><?php _e('If Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody valign="top">
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td>
								<select class='wlmmcAction' name="ar[mcID][<?php echo $levelid; ?>]" style="width:100%;" onchange="jQuery(this).next('div.group-info').css('display',this.selectedIndex > 0 ? 'block' : 'none')" >
									<option value='' >- Select a List -</option>
									<?php
									foreach ((array)$lists as $list) {
										$selected = $data[$__index__]['mcID'][$levelid] == $list['id'] ? "selected='selected'" : "";
										echo "<option value='{$list['id']}' {$selected}>{$list['name']}</option>";
									}
									?>
								</select>
								<?php $isDisabled = ( $data[$__index__]['mcID'][$levelid] == "" ) ? true : false; ?>
								<div class="group-info" <?php echo $isDisabled ? 'style="display:none"' : ''; ?> >
									<blockquote>
										<div>
											Group Title <em>(optional)</em>: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br>
											<input type="text" name="ar[mcGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGp'][$levelid]; ?>" style="width:100%" />
										</div>
										<div>
											Group Names <em>(optional)</em>:	<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?><br>
											<input type="text" name="ar[mcGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGping'][$levelid]; ?>" style="width:100%" />
										</div>
									</blockquote>
								</div>
							</td>
							<td>&nbsp;</td>
							<?php $mcOnRemCan = isset($data[$__index__]['mcOnRemCan'][$levelid]) ? $data[$__index__]['mcOnRemCan'][$levelid] : ""; ?>
							<td >
								<select class='wlmmcAction' name="ar[mcOnRemCan][<?php echo $levelid; ?>]" style="width:100%;" onchange="jQuery(this).next('div.group-info').css('display',this.selectedIndex > 1 ? 'block' : 'none')">
									<option value='' <?php echo $mcOnRemCan == "" ? "selected='selected'" : ""; ?> >- Select a Action -</option>
									<option value='unsub' <?php echo $mcOnRemCan == "unsub" ? "selected='selected'" : ""; ?> >Unsubscribe from List</option>
									<option value='move' <?php echo $mcOnRemCan == "move" ? "selected='selected'" : ""; ?> >Move to Group</option>
									<option value='add' <?php echo $mcOnRemCan == "add" ? "selected='selected'" : ""; ?> >Add to Group</option>
								</select>
								<?php $isDisabled = ($mcOnRemCan == "" || $mcOnRemCan == "unsub") ? true : false; ?>
								<div class="group-info" <?php echo $isDisabled ? 'style="display:none"' : ''; ?> >
									<blockquote>
										<div>
											Group Title: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br>
											<input type="text" name="ar[mcRCGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGp'][$levelid]; ?>" style="width:100%" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> />
										</div>
										<div>
											Group Names: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?><br>
											<input type="text" name="ar[mcRCGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGping'][$levelid]; ?>" style="width:100%" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> />
										</div>
									</blockquote>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.mailchimp.tooltips.php');
	endif;
endif;
