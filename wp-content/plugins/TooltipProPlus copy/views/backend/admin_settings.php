<?php if ( !empty( $messages ) ): ?>
	<div class="updated" style="clear:both"><p><?php echo $messages; ?></p></div>
<?php endif; ?>

<br/>

<br/>

<?php echo do_shortcode( '[cminds_free_ads]' ); ?>

<div class="cminds_settings_description">
    <p>
        <strong>Supported Shortcodes:</strong> <a href="javascript:void(0)" onclick="jQuery( this ).parent().next().slideToggle()">Show/Hide</a>
    </p>

    <ul style="display:none;list-style-type:disc;margin-left:20px;">
        <li><strong>Custom glossary tooltip</strong> - [glossary_tooltip content="text"] term [/glossary_tooltip]</li>
        <li><strong>Exclude from parsing</strong> - [glossary_exclude] text [/glossary_exclude]</li>
        <li><strong>Apply tooltip</strong> - [cm_tooltip_parse] text [/cm_tooltip_parse] <sup>1</sup></li>
        <li><strong>Show Glossary Index</strong> - [glossary cat="cat1,cat2" gtags="tag1,tag2" search_term="term" <sup>2</sup>itemspage="1" <sup>2</sup>letter="all" related="0" no_desc="0" hide_terms="0" hide_abbrevs="0" hide_synonyms="0" ]</li>
        <li><strong>Show Glossary Search Form</strong> - [glossary_search]</li>
		<li><strong>Show Merriam-Webster Dictionary</strong> - [glossary_dictionary term="term name"]</li>
        <li><strong>Show Merriam-Webster Thesaurus</strong> - [glossary_thesaurus term="term name"]</li>
        <li><strong>Translate</strong> - [glossary_translate term="text-to-translate" source="english" target="spanish"]</li>
        <li><strong>Toggle Tooltips</strong> - [glossary-toogle-tooltips session="0"]</li>
        <li><strong>Toggle Theme</strong> - [glossary-toggle-theme label="Test theme" class="test"]</li>
        <li><del><strong>Wikipedia</strong> - [glossary_wikipedia term="term name"]</del> - Only in <a href="https://tooltip.cminds.com"  target="_blank">Ecommerce</a></li>
        <li>
            <sup>1</sup> The shortcode internally calls custom filter called 'cm_tooltip_parse' which can be used if you want the tooltip funtionality outside of 'the_content':
            <code>$text_with_tooltip = apply_filter('cm_tooltip_parse', $text);</code>
        </li>
        <li>
            <sup>2</sup> This attribute is for Server-side pagination only
        </li>
    </ul>
    <p>
		<?php
		$glossaryId = CMTT_Glossary_Index::getGlossaryIndexPageId();
		if ( $glossaryId > 0 ) :

			$glossaryIndexPageEditLink	 = admin_url( 'post.php?post=' . $glossaryId . '&action=edit' );
			$glossaryIndexPageLink		 = get_page_link( $glossaryId );
			?>
			<strong>Link to the Glossary Index Page:</strong> <a href="<?php echo $glossaryIndexPageLink; ?>" target="_blank"><?php echo $glossaryIndexPageLink; ?></a> (<a title="Edit the Glossary Index Page" href="<?php echo $glossaryIndexPageEditLink; ?>">edit</a>)
			<?php
		endif;
		?>
    </p>
    <p>
        <strong>Example of Glossary Term link:</strong> <?php echo trailingslashit( home_url( get_option( 'cmtt_glossaryPermalink' ) ) ) . 'sample-term' ?>
    </p>
    <form method="post">
        <div>
            <div class="cmtt_field_help_container">Warning! This option will completely erase all of the data stored by the CM Tooltip Glossary in the database: terms, options, synonyms etc. <br/> It will also remove the Glossary Index Page. <br/> It cannot be reverted.</div>
            <input onclick="return confirm( 'All options of CM Tooltip Glossary will be erased. This cannot be reverted.' )" type="submit" name="cmtt_removeAllOptions" value="Remove all options" class="button cmtt-cleanup-button"/>
            <input onclick="return confirm( 'All terms of CM Tooltip Glossary will be erased. This cannot be reverted.' )" type="submit" name="cmtt_removeAllItems" value="Remove all items" class="button cmtt-cleanup-button"/>
            <span style="display: inline-block;position: relative;"></span>
        </div>
    </form>

	<?php
// check permalink settings
	if ( get_option( 'permalink_structure' ) == '' ) {
		echo '<span style="color:red">Your WordPress Permalinks needs to be set to allow plugin to work correctly. Please Go to <a href="' . admin_url() . 'options-permalink.php" target="new">Settings->Permalinks</a> to set Permalinks to Post Name.</span><br><br>';
	}
	?>

</div>

<?php
include plugin_dir_path( __FILE__ ) . '/call_to_action.phtml';
?>

<br/>
<div class="clear"></div>

<form method="post">
	<?php wp_nonce_field( 'update-options' ); ?>
    <input type="hidden" name="action" value="update" />


    <div id="cmtt_tabs" class="glossarySettingsTabs">
        <div class="glossary_loading"></div>

		<?php
		CMTT_Pro::renderSettingsTabsControls();

		CMTT_Pro::renderSettingsTabs();
		?>

        <div id="tabs-1">
            <div class="block">
                <h3>General Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top" class="whole-line">
                        <th scope="row">Glossary Index Page ID</th>
                        <td>
							<?php wp_dropdown_pages( array( 'name' => 'cmtt_glossaryID', 'selected' => (int) get_option( 'cmtt_glossaryID', -1 ), 'show_option_none' => '-None-', 'option_none_value' => '0' ) ) ?>
                            <br/><input type="checkbox" name="cmtt_glossaryID" value="-1" /> Generate page for Glossary Index
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the page ID of the page you would like to use as the Glossary Index Page. If you select "-None-" terms will still be highlighted in relevant posts/pages but there won't be a central list of terms (Glossary Index Page). If you check the checkbox a new page would be generated automatically. WARNING! You have to manually remove old pages!</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Roles allowed to add/edit terms:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRoles" value="0" />
							<?php
							echo CMTT_Pro::outputRolesList();
							?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the custom post types where you'd like the Glossary Terms to be highlighted.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Create Glossary Term Pages:</th>
                        <td>
                            <input type="hidden" name="cmtt_createGlossaryTermPages" value="0" />
                            <input type="checkbox" name="cmtt_createGlossaryTermPages" <?php checked( true, get_option( 'cmtt_createGlossaryTermPages', TRUE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Uncheck this if you don't want the Glossary Term pages to be created. <strong>After disabling this all of the links to the Glossary Term pages will be removed.</strong></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Terms Permalink</th>
                        <td><input type="text" name="cmtt_glossaryPermalink" value="<?php echo get_option( 'cmtt_glossaryPermalink' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink to the Glossary Terms.
                            By default this is "glossary", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary", otherwise just leave glossary or the name you have chosen.
                            <br/><br/>
                            The permalink of the Glossary Index Page will change automatically, but you can change it manually (if you like) using the "edit" link near the "Link to the Glossary Index Page" above.
                            <br/><br/>WARNING! If you already use this permalink the plugin's behavior may be unpredictable.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Categories Permalink</th>
                        <td><input type="text" name="cmtt_glossaryCategoriesPermalink" value="<?php echo get_option( 'cmtt_glossaryCategoriesPermalink', 'glossary-categories' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink for the Glossary Categories.
                            By default this is "glossary-categories", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary-categories", otherwise just leave glossary-categories or the name you have chosen.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Tags Permalink</th>
                        <td><input type="text" name="cmtt_glossaryTagsPermalink" value="<?php echo get_option( 'cmtt_glossaryTagsPermalink', 'glossary-tags' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink to the Glossary Tags.
                            By default this is "glossary-tags", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary-tags", otherwise just leave glossary-tags or the name you have chosen.
                        </td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Term higlighting</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Highlight terms on given post types:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnPosttypes" value="0" />
							<?php
							echo CMTT_Pro::outputCustomPostTypesList();
							?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the custom post types where you'd like the Glossary Terms to be highlighted.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Only show terms on single posts/pages (not Homepage, authors etc.)?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnlySingle" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnlySingle" <?php checked( true, get_option( 'cmtt_glossaryOnlySingle' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to only highlight glossary terms when viewing a single page/post.
                            This can be used so terms aren't highlighted on your homepage, or author pages and other taxonomy related pages.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight terms in ACF fields?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseACFFields" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseACFFields" <?php checked( true, get_option( 'cmtt_glossaryParseACFFields' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"> Select this option if you wish to highlight Glossary Terms in ALL of the "Advanced Custom Fields" fields.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight terms in bbPress replies?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseBBPressFields" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseBBPressFields" <?php checked( true, get_option( 'cmtt_glossaryParseBBPressFields' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"> Select this option if you wish to highlight Glossary Terms in ALL of the "bbPress" replies.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight first term occurance only?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFirstOnly" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFirstOnly" <?php checked( true, get_option( 'cmtt_glossaryFirstOnly' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to only highlight the first occurance of each term on a page/post.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight only space separated terms?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnlySpaceSeparated" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnlySpaceSeparated" <?php checked( true, get_option( 'cmtt_glossaryOnlySpaceSeparated' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to only search for the terms separated from other words (usually by space).</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight the terms in comments</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermsInComments" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermsInComments" <?php checked( true, get_option( 'cmtt_glossaryTermsInComments' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to highlight the glossary terms in the comments.</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Performance &amp; Debug</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Add RSS feeds?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddFeeds" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddFeeds" <?php checked( true, get_option( 'cmtt_glossaryAddFeeds', true ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you want to have the RSS feeds for your glossary terms.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Load the scripts in footer?</th>
                        <td>
                            <input type="hidden" name="cmtt_script_in_footer" value="0" />
                            <input type="checkbox" name="cmtt_script_in_footer" <?php checked( true, get_option( 'cmtt_script_in_footer' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you want to load the plugin's js files in the footer.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Only highlight on "main" WP query?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnMainQuery" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnMainQuery" <?php checked( 1, get_option( 'cmtt_glossaryOnMainQuery' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you wish to only highlight glossary terms on main glossary query.
                            Unchecking this box may fix problems with highlighting terms on some themes which manipulate the WP_Query.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Run the function outputting the Glossary Index Page only once</th>
                        <td>
                            <input type="hidden" name="cmtt_removeGlossaryCreateListFilter" value="0" />
                            <input type="checkbox" name="cmtt_removeGlossaryCreateListFilter" <?php checked( 1, get_option( 'cmtt_removeGlossaryCreateListFilter' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you wish to remove the filter responsible for outputting the Glossary Index. <br/>
                            When this option is selected the function responsible for rendering the Glossary Index page (hooked to "the_content" filter) <br/>
                            will run only once and then it will be removed. It's known that this conflicts with some translation plugins (e.g. qTranslate, Jetpack, PageBuilder).
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable the caching mechanisms</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryEnableCaching" value="0" />
                            <input type="checkbox" name="cmtt_glossaryEnableCaching" <?php checked( true, get_option( 'cmtt_glossaryEnableCaching', TRUE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use the internal caching mechanisms.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable the "Hide term from Glossary Index" functionality</th>
                        <td>
                            <input type="hidden" name="cmtt_enableHidingFromIndex" value="0" />
                            <input type="checkbox" name="cmtt_enableHidingFromIndex" <?php checked( true, get_option( 'cmtt_enableHidingFromIndex', FALSE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to disable the functionality. Doing this solves the performance problems with long query on some hostings.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Don't use the DOM parser for ACF fields?</th>
                        <td>
                            <input type="hidden" name="cmtt_disableDOMParserForACF" value="0" />
                            <input type="checkbox" name="cmtt_disableDOMParserForACF" <?php checked( true, get_option( 'cmtt_disableDOMParserForACF', FALSE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to parse the ACF fields using the simple parser (preg_replace) instead of DOM parser. Warning! May break content.</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Backup</h3>
                <p>Easily backup your glossary to the file. You can create/download a backup on the <a href="<?php echo admin_url( 'admin.php?page=cmtt_importexport' ); ?>">Import/Export</a> page.</p>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >PIN Protect</th>
                        <td>
                            <input type="text" name="cmtt_glossary_backup_pinprotect" value="<?php echo get_option( 'cmtt_glossary_backup_pinprotect' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Fill this field with a PIN code which will be required to get the backup. Leave empty to disable PIN Protection.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >Secure Backup</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_backup_secure" value="0" />
                            <input type="checkbox" name="cmtt_glossary_backup_secure" <?php checked( true, get_option( 'cmtt_glossary_backup_secure', true ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this field if you want to use the secure WP Filesystem API for the file saves. Note: This may require the FTP/SSH credentials.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Backup rebuild interval:</th>
                        <td>
                            <select name="cmtt_glossary_backupCronInterval" >
								<?php
								$types				 = wp_get_schedules();
								$selectedInterval	 = get_option( 'cmtt_glossary_backupCronInterval', 'none' );
								?>
                                <option value="none" <?php selected( 'none', $selectedInterval ) ?>><?php CMTT_Pro::_e( 'Never' ) ?></option>
								<?php foreach ( $types as $typeName => $type ): ?>
									<option value="<?php echo $typeName; ?>" <?php selected( $typeName, $selectedInterval ) ?>><?php echo $type[ 'display' ]; ?></option>
								<?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose how often the backup of the glossary is saved. Choose 'none' to disable automatic saves.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Backup rebuild hour:</th>
                        <td><input type="time" placeholder="00:00" size="5" name="cmtt_glossary_backupCronHour" value="<?php echo get_option( 'cmtt_glossary_backupCronHour' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the hour when the Glossary Index Backup save should take place. The hour should be properly formatted string eg. 23:00 or 1 AM</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Metaboxes</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >&quot;CM Tooltip - Disables&quot; metabox</th>
                        <td>
                            <input type="hidden" name="cmtt_disable_metabox_all_post_types" value="0" />
                            <input type="checkbox" name="cmtt_disable_metabox_all_post_types" <?php checked( true, get_option( 'cmtt_disable_metabox_all_post_types' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the metabox allowing to disable tooltips on all post types.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >&quot;CM Tooltip - Allowed Terms&quot; metabox</th>
                        <td>
                            <input type="hidden" name="cmtt_allowed_terms_metabox_all_post_types" value="0" />
                            <input type="checkbox" name="cmtt_allowed_terms_metabox_all_post_types" <?php checked( true, get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the metabox allowing to set allowed terms list on all post types.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" ><?php CMTT_Pro::_e( 'Synonym Suggestions API' ); ?>:</th>
                        <td>
                            <input type="text" name="cmtt_glossarySynonymSuggestionsAPI" value="<?php echo get_option( 'cmtt_glossarySynonymSuggestionsAPI' ); ?>" placeholder="<?php CMTT_Pro::_e( 'Affiliate Code' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">To get the API Key please go to <a href="https://words.bighugelabs.com/getkey.php" target="_blank">Big Huge Thesaurus</a></td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Referrals</h3>
                <p>Refer new users to any of the CM Plugins and you'll receive a minimum of <strong>15%</strong> of their purchase! For more information please visit CM Plugins <a href="http://www.cminds.com/referral-program/" target="new">Affiliate page</a></p>
                <table>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >Enable referrals:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryReferral" value="0" />
                            <input type="checkbox" name="cmtt_glossaryReferral" <?php checked( 1, get_option( 'cmtt_glossaryReferral' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Enable referrals link at the bottom of the question and the answer page<br><br></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" ><?php CMTT_Pro::_e( 'Affiliate Code' ); ?>:</th>
                        <td>
                            <input type="text" name="cmtt_glossaryAffiliateCode" value="<?php echo get_option( 'cmtt_glossaryAffiliateCode' ); ?>" placeholder="<?php CMTT_Pro::_e( 'Affiliate Code' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"><?php CMTT_Pro::_e( 'Please add your affiliate code in here.' ); ?></td>
                    </tr>
                </table>
            </div>
		</div>
        <div id="tabs-2">
            <div class="block">
                <h3>Glossary Index Page Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Display style:</th>
                        <td><select name="cmtt_glossaryDisplayStyle">
                                <option value="classic" <?php selected( 'classic', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic</option>
                                <option value="classic-definition" <?php selected( 'classic-definition', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic + definition</option>
                                <option value="classic-excerpt" <?php selected( 'classic-excerpt', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic + excerpt</option>
                                <option value="small-tiles" <?php selected( 'small-tiles', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Small Tiles</option>
                                <option value="big-tiles" <?php selected( 'big-tiles', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Big Tiles</option>
                                <option value="classic-table" <?php selected( 'classic-table', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic table</option>
                                <option value="modern-table" <?php selected( 'modern-table', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Modern table</option>
                            </select><br />
                        <td colspan="2" class="cmtt_field_help_container">Set display style of the Glossary Index page. By default the "Classic" style is selected.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show featured image thumbnail?</th>
                        <td>
                            <input type="hidden" name="cmtt_showFeaturedImageThumbnail" value="0" />
                            <input type="checkbox" name="cmtt_showFeaturedImageThumbnail" <?php checked( true, get_option( 'cmtt_showFeaturedImageThumbnail' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to display the thumbnails of the featured image on the Glossary Index (when available).
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Limit the definition length</th>
                        <td><input type="text" name="cmtt_glossaryTooltipDescLength" value="<?php echo get_option( 'cmtt_glossaryTooltipDescLength', 300 ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to show only a limited number of chars of the decinition and add "(...)" at the end. Minimum is 30 chars.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Strip the shortcodes from definition?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryIndexDescStripShortcode" value="0" />
                            <input type="checkbox" name="cmtt_glossaryIndexDescStripShortcode" <?php checked( true, get_option( 'cmtt_glossaryIndexDescStripShortcode' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to strip the shortcodes from the definition displayed on the Glossary Index page.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Run the API calls on the Glossary Index page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRunApiCalls" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRunApiCalls" <?php checked( true, get_option( 'cmtt_glossaryRunApiCalls' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to call the APIs on the Glossary Index page. <br/>
                            <strong>Warning!</strong> Enabling this option can slow the loading time of the Glossary Index page drastically. </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove the tooltips on the Glossary Index Page?</th>
                        <td>&nbsp;</td>
						<?php
						$link				 = admin_url( 'post.php?post=' . get_option( 'cmtt_glossaryID' ) . '&action=edit' );
						?>
                        <td colspan="2" class="cmtt_field_help_container">If you want to remove the tooltip from the Glossary Index page, you should edit the page using Wordpress's Pages menu (or clicking <a href="<?php echo $link; ?>" target="_blank">this link</a>)<br/>
                            And in the <strong>"Tooltip Plugin"</strong> tab select the option <strong>"Exclude this page from Tooltip plugin"</strong></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Mark terms not older than X days as "New"</th>
                        <td><input type="text" name="cmtt_glossaryNewItemMaxDays" value="<?php echo get_option( 'cmtt_glossaryNewItemMaxDays', '0' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            If this setting contains a positive number then Glossary Terms not older than this number will be marked as "New". 0 turns off the feature.
                        </td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Links</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Remove the link from Glossary Index to the Glossary Term pages?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryListTermLink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryListTermLink" <?php checked( true, get_option( 'cmtt_glossaryListTermLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you do not want to show links to the glossary term pages on the Glossary Index page. Keep in mind that the plugin use a <strong>&lt;span&gt;</strong> tag instead of a link tag and if you are using a custom CSS you should take this into account</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Style links differently?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryDiffLinkClass" value="0" />
                            <input type="checkbox" name="cmtt_glossaryDiffLinkClass" <?php checked( true, get_option( 'cmtt_glossaryDiffLinkClass' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish for the links in the Glossary Index page to be styled differently than the regular way glossary terms links are styled.  By selecting this option you will be able to use the class 'glossaryLinkMain' to style only the links on the Glossary Index page otherwise they will retain the class 'glossaryLink' and will be identical to the linked terms on all other pages.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Sharing box</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show the sharing box on the Glossary Index Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryShowShareBox" value="0" />
                            <input type="checkbox" name="cmtt_glossaryShowShareBox" <?php checked( true, get_option( 'cmtt_glossaryShowShareBox' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show the "Share This" box on the Glossary Index Page with links to Facebook, Twitter, Google+ and LinkedIn.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Search, Categories &amp; Tags</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show the search on the Glossary Index page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showSearch" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showSearch" <?php checked( true, get_option( 'cmtt_glossary_showSearch' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you like the "search" functionality to appear on the Glossary Index page.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Category selection method:</th>
                        <td><select name="cmtt_glossaryCategoriesDisplayType">
                                <option value="0" <?php echo selected( '0', get_option( 'cmtt_glossaryCategoriesDisplayType' ) ); ?>>Dropdown</option>
                                <option value="1" <?php echo selected( '1', get_option( 'cmtt_glossaryCategoriesDisplayType' ) ); ?>>Links</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Select the way how categories are displayed on the Glossary Index Page </td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Pagination</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Paginate Glossary Index page (items per page)</th>
                        <td><input type="text" name="cmtt_perPage" value="<?php echo get_option( 'cmtt_perPage' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many elements per page should be displayed (0 to disable pagination)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination type</th>
                        <td><select name="cmtt_glossaryServerSidePagination">
                                <option value="0" <?php echo selected( 0, get_option( 'cmtt_glossaryServerSidePagination' ) ); ?>>Client-side</option>
                                <option value="1" <?php echo selected( 1, get_option( 'cmtt_glossaryServerSidePagination' ) ); ?>>Server-side</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Client-side: longer loading, fast page switch (with additional alphabetical index)<br />
                            Server-side: faster loading, slower page switch </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination position (Server-side only)</th>
                        <td><select name="cmtt_glossaryPaginationPosition">
                                <option value="bottom" <?php echo selected( 'bottom', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Bottom</option>
                                <option value="top" <?php echo selected( 'top', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Top</option>
                                <option value="both" <?php echo selected( 'both', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Both</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose where you would like the pagination to appear on the Index Page (only for the Server-side pagination). For the client side the pagination is always on the bottom. </td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Alphabetic index</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Letters in alphabetic index</th>
                        <td><input type="text" name="cmtt_index_letters" value="<?php echo esc_attr( implode( ',', get_option( 'cmtt_index_letters', array() ) ) ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Which letters should be shown in alphabetic index (separate by commas)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Size of the letters in alphabetic index</th>
                        <td>
                            <select name="cmtt_indexLettersSize">
                                <option value="small" <?php selected( 'small', get_option( 'cmtt_indexLettersSize' ) ); ?>>Small</option>
                                <option value="medium" <?php selected( 'medium', get_option( 'cmtt_indexLettersSize' ) ); ?>>Medium</option>
                                <option value="large" <?php selected( 'large', get_option( 'cmtt_indexLettersSize' ) ); ?>>Large</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the size of the letters in the alphabetic index: small(7pt), medium(10pt), large(14pt)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show numeric [0-9] in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_includeNum" value="0" />
                            <input type="checkbox" name="cmtt_index_includeNum" <?php checked( true, get_option( 'cmtt_index_includeNum' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show [0-9] option in alphabetical index.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show all [ALL] in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_includeAll" value="0" />
                            <input type="checkbox" name="cmtt_index_includeAll" <?php checked( true, get_option( 'cmtt_index_includeAll' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show [All] option in alphabetical index.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">What letter should be preselected in alphabetic index?</th>
                        <td><input type="text" size="1" name="cmtt_index_initLetter" value="<?php echo get_option( 'cmtt_index_initLetter', '' ) ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">You can choose which letter should be preselected. e.g. &quot;b&quot;(without quotes) would mean "B" will be preselected each time user visits Glossary Index page. If you leave this field empty the leftmost item on the alphabetic index is selected.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Consider non-latin letters separate from their latin base?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_nonLatinLetters" value="0" />
                            <input type="checkbox" name="cmtt_index_nonLatinLetters" <?php checked( true, get_option( 'cmtt_index_nonLatinLetters', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">With this setting you can control how the non-latin letters used in many national character sets should be displayed on the Glossary Index alphabetical list. When this setting is unchecked the terms starting with: "A" and "Á" will be displayed for "A".</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">What locale should be used for sorting?</th>
                        <td><input type="text" size="4" name="cmtt_index_locale" value="<?php echo get_option( 'cmtt_index_locale', get_locale() ) ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container"> You can specify the locale which should be used for sorting the items on Glossary Index eg. 'de_DE', 'it_IT'. If left empty the locale of the Wordpress installation will be used.
                            <br/><i>Works only if the "intl" library is installed (see "Server Information" tab).</i></td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-3">
            <div class="block">
                <h3>Glossary Term - Display</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Use custom template for terms?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryUseTemplate" value="0" />
                            <input type="checkbox" name="cmtt_glossaryUseTemplate" <?php checked( true, get_option( 'cmtt_glossaryUseTemplate' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If you select this option then the plugin will search for the custom template for the glossary term page. <br/>
                            If you want to customize it, you can copy the file from: <br/>
                            <strong><?php echo CMTT_PLUGIN_DIR; ?>theme/Tooltip/single-glossary.php</strong> to <br/>
                            <strong><?php echo get_stylesheet_directory() ?>/Tooltip/single-glossary.php</strong> <br/>
                            (If the plugin doesn't find the template in your theme's folder it will use the default one)
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Choose the template for glossary term?</th>
                        <td>
                            <select name="cmtt_glossaryPageTermTemplate">
								<?php
								$selectedTemplate	 = get_option( 'cmtt_glossaryPageTermTemplate', 0 );
								$templates			 = CMTT_Custom_Templates::getPageTemplatesOptions();
								?>
								<?php foreach ( $templates as $templateKey => $template ): ?>
									<option value="<?php echo $templateKey; ?>" <?php selected( $templateKey, $selectedTemplate ) ?>><?php echo $template; ?></option>
								<?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the page template of the current theme or set default.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show the sharing box on the Glossary Term Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryShowShareBoxTermPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryShowShareBoxTermPage" <?php checked( true, get_option( 'cmtt_glossaryShowShareBoxTermPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show the "Share This" box on the Glossary Index Page with links to Facebook, Twitter, Google+ and LinkedIn.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show back link on the top</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addBackLink" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addBackLink" <?php checked( true, get_option( 'cmtt_glossary_addBackLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show link back to Glossary Index from glossary term page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show back link on the bottom</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addBackLinkBottom" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addBackLinkBottom" <?php checked( true, get_option( 'cmtt_glossary_addBackLinkBottom' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show link back to Glossary Index from glossary term page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove comments from term page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRemoveCommentsTermPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRemoveCommentsTermPage" <?php checked( true, get_option( 'cmtt_glossaryRemoveCommentsTermPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to remove the comments support form the term pages.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Display alphabetical list on top of Term Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermShowListnav" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermShowListnav" <?php checked( true, get_option( 'cmtt_glossaryTermShowListnav' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the alphabetical list on top of Glossary Term Page.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Links</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Remove link to the glossary term page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermLink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermLink" <?php checked( true, get_option( 'cmtt_glossaryTermLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you do not want to show links from posts or pages to the glossary term pages. This will only apply to Post / Pages and not to the Glossary Index page, for Glossary Index page please visit index page tab in settings. Keep in mind that the plugin use a <strong>&lt;span&gt;</strong> tag instead of a link tag and if you are using a custom CSS you should take this into account</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Open glossary term page in a new windows/tab?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryInNewPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryInNewPage" <?php checked( true, get_option( 'cmtt_glossaryInNewPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want glossary term page to open in a new window/tab.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show HTML "title" attribute for glossary links</th>
                        <td>
                            <input type="hidden" name="cmtt_showTitleAttribute" value="0" />
                            <input type="checkbox" name="cmtt_showTitleAttribute" <?php checked( true, get_option( 'cmtt_showTitleAttribute' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use glossary name as HTML "title" for link</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link underline</th>
                        <td>Style: <select name="cmtt_tooltipLinkUnderlineStyle">
                                <option value="none" <?php selected( 'none', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>None</option>
                                <option value="solid" <?php selected( 'solid', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Solid</option>
                                <option value="dotted" <?php selected( 'dotted', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Dotted</option>
                                <option value="dashed" <?php selected( 'dashed', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Dashed</option>
                            </select><br />
                            Width: <input type="number" name="cmtt_tooltipLinkUnderlineWidth" value="<?php echo get_option( 'cmtt_tooltipLinkUnderlineWidth' ); ?>" step="1" min="0" max="10"/>px<br />
                            Color: <input type="text" class="colorpicker" name="cmtt_tooltipLinkUnderlineColor" value="<?php echo get_option( 'cmtt_tooltipLinkUnderlineColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set style of glossary link underline</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link underline (hover)</th>
                        <td>Style: <select name="cmtt_tooltipLinkHoverUnderlineStyle">
                                <option value="none" <?php selected( 'none', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>None</option>
                                <option value="solid" <?php selected( 'solid', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Solid</option>
                                <option value="dotted" <?php selected( 'dotted', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Dotted</option>
                                <option value="dashed" <?php selected( 'dashed', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Dashed</option>
                            </select><br />
                            Width: <input type="number" name="cmtt_tooltipLinkHoverUnderlineWidth" value="<?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineWidth' ); ?>" step="1" min="0" max="10"/>px<br />
                            Color: <input type="text" class="colorpicker" name="cmtt_tooltipLinkHoverUnderlineColor" value="<?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set style of glossary link underline on mouse hover</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link text color</th>
                        <td><input type="text" class="colorpicker" name="cmtt_tooltipLinkColor" value="<?php echo get_option( 'cmtt_tooltipLinkColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set color of glossary link text color</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link text color (hover)</th>
                        <td><input type="text" class="colorpicker" name="cmtt_tooltipLinkHoverColor" value="<?php echo get_option( 'cmtt_tooltipLinkHoverColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set color of glossary link text color on mouse hover</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Related Articles &amp; Terms</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Index rebuild interval:</th>
                        <td>
                            <select name="cmtt_glossary_relatedCronInterval" >
								<?php
								$types				 = wp_get_schedules();
								$selectedInterval	 = get_option( 'cmtt_glossary_relatedCronInterval', 'daily' );
								?>
                                <option value="none" <?php selected( 'none', $selectedInterval ) ?>><?php CMTT_Pro::_e( 'Never' ) ?></option>
								<?php foreach ( $types as $typeName => $type ): ?>
									<option value="<?php echo $typeName; ?>" <?php selected( $typeName, $selectedInterval ) ?>><?php echo $type[ 'display' ]; ?></option>
								<?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose how often the related articles index is being rebuilt.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Index rebuild hour:</th>
                        <td><input type="time" placeholder="00:00" size="5" name="cmtt_glossary_relatedCronHour" value="<?php echo get_option( 'cmtt_glossary_relatedCronHour' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the hour when the Related Articles Rebuild should take place. The hour should be properly formatted string eg. 23:00 or 1 AM</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show related articles</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showRelatedArticles" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showRelatedArticles" <?php checked( true, get_option( 'cmtt_glossary_showRelatedArticles' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of related articles (posts, pages) on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Order of the related articles by:</th>
                        <td>
                            <select name="cmtt_glossary_relatedArticlesOrder">
                                <option value="menu_order" <?php selected( 'menu_order', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Menu Order</option>
                                <option value="post_title" <?php selected( 'post_title', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Post Title</option>
                                <option value="post_date DESC" <?php selected( 'post_date DESC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Publising Date DESC</option>
                                <option value="post_date ASC" <?php selected( 'post_date ASC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Publising Date ASC</option>
                                <option value="post_modified DESC" <?php selected( 'post_modified DESC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Last Modified DESC</option>
                                <option value="post_modified ASC" <?php selected( 'post_modified ASC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Last Modified ASC</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">How the related articles should be ordered?</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable related terms on glossary term pages:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryDisableRelatedTermsForTerms" value="0" />
                            <input type="checkbox" name="cmtt_glossaryDisableRelatedTermsForTerms" <?php checked( true, get_option( 'cmtt_glossaryDisableRelatedTermsForTerms' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you don't want to show list of related terms on glossary term pages</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show related glossary terms in a separate list</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showRelatedArticlesMerged" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showRelatedArticlesMerged" <?php checked( true, get_option( 'cmtt_glossary_showRelatedArticlesMerged' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of related glossary terms in the separate list.
							If this option is not checked, the list of related articles and glossary terms will be the same list.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Number of related articles:</th>
                        <td><input type="number" name="cmtt_glossary_showRelatedArticlesCount" value="<?php echo get_option( 'cmtt_glossary_showRelatedArticlesCount' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many related articles should be shown?</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Number of related glossary terms:</th>
                        <td><input type="number" name="cmtt_glossary_showRelatedArticlesGlossaryCount" value="<?php echo get_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many related glossary terms should be shown? Works only if "Show related articles and glossary terms together" is enabled</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Post types to index:</th>
                        <td><select multiple name="cmtt_glossary_showRelatedArticlesPostTypesArr[]" >
								<?php
								$types = get_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr' );
								foreach ( get_post_types() as $type ):
									?>
									<option value="<?php echo $type; ?>" <?php if ( is_array( $types ) && in_array( $type, $types ) ) echo 'selected'; ?>><?php echo $type; ?></option>
								<?php endforeach; ?>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Which post types should be indexed? (select more by holding down ctrl key)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Related articles index rebuild chunk size:</th>
                        <td>
                            <input type="text" name="cmtt_glossary_relatedArticlesCrawlChunkSize" value="<?php echo esc_attr( get_option( 'cmtt_glossary_relatedArticlesCrawlChunkSize', 500 ) ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Since rebuilding the Glossary Index requires a lot of resources, both memory and time.
                            It has to be done in chunks. The optimal size of the chunk depends on your server.
                            If after clicking the button page goes blank, try to make this value much smaller and try to rebuild it again.
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Refresh related articles index:</th>
                        <td>
                            <input type="submit" name="cmtt_glossaryRelatedRefresh" value="Rebuild Index!" class="button"/>
                            <br/>
							<?php if ( CMTT_Related::showContinueButton() ) : ?>
								<input type="submit" name="cmtt_glossaryRelatedRefreshContinue" value="Continue indexing" class="button"/>
								<br/>
							<?php endif; ?>
                            <span><?php echo CMTT_Related::getRemainingArticlesCount(); ?></span>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">The index for relations between articles (posts, pages) and glossary terms is being rebuilt on daily basis. Click this button if you want to do it manually (it may take a while)</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Show linked glossary terms list under post/page?</th>
                        <td>
                            <input type="hidden" name="cmtt_showRelatedTermsList" value="0" />
                            <input type="checkbox" name="cmtt_showRelatedTermsList" <?php checked( true, get_option( 'cmtt_showRelatedTermsList' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show the widget containing a list of all glossary items found in the post/page</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Synonyms</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show synonyms list</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addSynonyms" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addSynonyms" <?php checked( true, get_option( 'cmtt_glossary_addSynonyms' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of synonyms of the term on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show synonyms list in tooltip</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addSynonymsTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addSynonymsTooltip" <?php checked( true, get_option( 'cmtt_glossary_addSynonymsTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show the list of synonyms of the term tooltip</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show synonyms in Glossary Index Page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossarySynonymsInIndex" value="0" />
                            <input type="checkbox" name="cmtt_glossarySynonymsInIndex" <?php checked( true, get_option( 'cmtt_glossarySynonymsInIndex' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show synonyms as terms in Glossary Index Page</td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-4">
            <div class="block">
                <h3>Tooltip - Content</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show tooltip when the user hovers over the term?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTooltip" <?php checked( true, get_option( 'cmtt_glossaryTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish for the definition to show in a tooltip when the user hovers over the term.  The tooltip can be styled differently using the tooltip.css and tooltip.js files in the plugin folder.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add term title to the tooltip content?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddTermTitle" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddTermTitle" <?php checked( true, get_option( 'cmtt_glossaryAddTermTitle' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term title to appear in the tooltip content.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add term editlink to the tooltip content?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddTermEditlink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddTermEditlink" <?php checked( true, get_option( 'cmtt_glossaryAddTermEditlink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term editlink to appear in the tooltip content (only for logged in users with "edit_posts" capability).</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Strip the shortcodes?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTooltipStripShortcode" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTooltipStripShortcode" <?php checked( true, get_option( 'cmtt_glossaryTooltipStripShortcode' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to strip the shortcodes from the glossary page description/excerpt before showing the tooltip.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Limit tooltip length?</th>
                        <td><input type="text" name="cmtt_glossaryLimitTooltip" value="<?php echo get_option( 'cmtt_glossaryLimitTooltip' ); ?>"  /></td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show only a limited number of chars and add "<?php echo get_option( 'cmtt_glossaryTermDetailsLink' ); ?>" at the end of the tooltip text. Minimum is 30 chars.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Clean tooltip text?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltip" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to remove extra spaces and special characters from tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Leave the &lt;a&gt; tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltipA" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltipA" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltipA' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to preserve the html anchor tags in tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Leave the &lt;img&gt; tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltipImg" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltipImg" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltipImg' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to preserve the images in tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Use term excerpt for hover?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryExcerptHover" value="0" />
                            <input type="checkbox" name="cmtt_glossaryExcerptHover" <?php checked( true, get_option( 'cmtt_glossaryExcerptHover' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use the term excerpt (if it exists) as hover text.
                            <br/>NOTE: You have to manually create the excerpts for term pages using the "Excerpt" field.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Avoid parsing protected tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryProtectedTags" value="0" />
                            <input type="checkbox" name="cmtt_glossaryProtectedTags" <?php checked( true, get_option( 'cmtt_glossaryProtectedTags' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to avoid using the glossary for the following tags: Script, A, H1, H2, H3, PRE, Object.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Terms case-sensitive?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryCaseSensitive" value="0" />
                            <input type="checkbox" name="cmtt_glossaryCaseSensitive" <?php checked( '1', get_option( 'cmtt_glossaryCaseSensitive' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want glossary terms to be case-sensitive.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Tooltip - Mobile Support</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Enable the mobile support?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryMobileSupport" value="0" />
                            <input type="checkbox" name="cmtt_glossaryMobileSupport" <?php checked( true, get_option( 'cmtt_glossaryMobileSupport' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is enabled then on the mobile devices a link to the term page will appear on the bottom of the tooltip.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable tooltips on mobile devices?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryMobileDisableTooltips" value="0" />
                            <input type="checkbox" name="cmtt_glossaryMobileDisableTooltips" <?php checked( true, get_option( 'cmtt_glossaryMobileDisableTooltips' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is enabled then on the mobile devices the tooltips will not appear.</td>
                    </tr>
                </table>
            </div>
			<?php
			$additionalTooltipTabContent = apply_filters( 'cmtt_settings_tooltip_tab_content_after', '' );
			echo $additionalTooltipTabContent;
			?>
        </div>
        <!-- Start Server information Module -->
        <div id="tabs-99">
            <div class='block'>
                <h3>Server Information</h3>
				<?php
				$safe_mode					 = ini_get( 'safe_mode' ) ? ini_get( 'safe_mode' ) : 'Off';
				$upload_max					 = ini_get( 'upload_max_filesize' ) ? ini_get( 'upload_max_filesize' ) : 'N/A';
				$post_max					 = ini_get( 'post_max_size' ) ? ini_get( 'post_max_size' ) : 'N/A';
				$memory_limit				 = ini_get( 'memory_limit' ) ? ini_get( 'memory_limit' ) : 'N/A';
				$allow_url_fopen			 = ini_get( 'allow_url_fopen' ) ? ini_get( 'allow_url_fopen' ) : 'N/A';
				$max_execution_time			 = ini_get( 'max_execution_time' ) !== FALSE ? ini_get( 'max_execution_time' ) : 'N/A';
				$cURL						 = function_exists( 'curl_version' ) ? 'On' : 'Off';
				$mb_support					 = function_exists( 'mb_strtolower' ) ? 'On' : 'Off';
				$intl_support				 = extension_loaded( 'intl' ) ? 'On' : 'Off';

				$php_info = cminds_parse_php_info();
				?>
                <span class="description" style="">
                    Cm Tooltip is a mix of  JavaScript application and a parsing engine.
                    This information is useful to check if CM Tooltip might have some incompabilities with you server
                </span>
                <table class="form-table server-info-table">
                    <tr>
                        <td>PHP Version</td>
                        <td><?php echo phpversion(); ?></td>
                        <td><?php if ( version_compare( phpversion(), '5.3.0', '<' ) ): ?><strong>Recommended 5.3 or higher</strong><?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>mbstring support</td>
                        <td><?php echo $mb_support; ?></td>
                        <td><?php if ( $mb_support == 'Off' ): ?>
								<strong>"mbstring" library is required for plugin to work.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>intl support</td>
                        <td><?php echo $intl_support; ?></td>
                        <td><?php if ( $intl_support == 'Off' ): ?>
								<strong>"intl" library is required for proper sorting of accented characters on Glossary Index page.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP Memory Limit</td>
                        <td><?php echo $memory_limit; ?></td>
                        <td><?php if ( cminds_units2bytes( $memory_limit ) < 1024 * 1024 * 128 ): ?>
								<strong>This value can be too low for a site with big glossary.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP Max Upload Size (Pro, Pro+, Ecommerce)</td>
                        <td><?php echo $upload_max; ?></td>
                        <td><?php if ( cminds_units2bytes( $upload_max ) < 1024 * 1024 * 5 ): ?>
								<strong>This value can be too low to import large files.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP Max Post Size (Pro, Pro+, Ecommerce)</td>
                        <td><?php echo $post_max; ?></td>
                        <td><?php if ( cminds_units2bytes( $post_max ) < 1024 * 1024 * 5 ): ?>
								<strong>This value can be too low to import large files.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP Max Execution Time </td>
                        <td><?php echo $max_execution_time; ?></td>
                        <td><?php if ( $max_execution_time != 0 && $max_execution_time < 300 ): ?>
								<strong>This value can be too low for lengthy operations. We strongly suggest setting this value to at least 300 or 0 which is no limit.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP cURL (Pro+, Ecommerce)</td>
                        <td><?php echo $cURL; ?></td>
                        <td><?php if ( $cURL == 'Off' ): ?>
								<strong>cURL library is required to check if remote audio file exists.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>
                    <tr>
                        <td>PHP allow_url_fopen (Pro+, Ecommerce)</td>
                        <td><?php echo $allow_url_fopen; ?></td>
                        <td><?php if ( $allow_url_fopen == '0' ): ?>
								<strong>allow_url_fopen is required to connect to the Merriam-Webster and Wikipedia API.</strong>
							<?php else: ?><span>OK</span><?php endif; ?></td>
                    </tr>

					<?php
					if ( isset( $php_info[ 'gd' ] ) && is_array( $php_info[ 'gd' ] ) ) {
						foreach ( $php_info[ 'gd' ] as $key => $val ) {
							if ( !preg_match( '/(WBMP|XBM|Freetype|T1Lib)/i', $key ) && $key != 'Directive' && $key != 'gd.jpeg_ignore_warning' ) {
								echo '<tr>';
								echo '<td>' . $key . '</td>';
								if ( stripos( $key, 'support' ) === false ) {
									echo '<td>' . $val . '</td>';
								} else {
									echo '<td>enabled</td>';
								}
								echo '</tr>';
							}
						}
					}
					?>
                </table>
            </div>
        </div>
    </div>
    <p class="submit" style="clear:left">
        <input type="submit" class="button-primary" value="<?php CMTT_Pro::_e( 'Save Changes' ) ?>" name="cmtt_glossarySave" />
    </p>
</form>