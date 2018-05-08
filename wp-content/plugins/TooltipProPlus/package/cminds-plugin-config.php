<?php

$cminds_plugin_config = array(
	'plugin-is-pro'				 => TRUE,
	'plugin-has-addons'			 => TRUE,
	'plugin-addons'				 => array(
		array( 'title' => 'Tooltip Glossary Search Widget', 'description' => 'Make your glossary more accessible by adding a search widget on the bottom of your website.', 'link' => 'https://www.cminds.com/store/tooltip-glossary-search-console-widget-add-on-for-wordpress-by-creativeminds/#', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=105680&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Custom Taxonomies', 'description' => 'Add support for multiple taxonomies and filtering for the Glossary terms.', 'link' => 'https://www.cminds.com/store/tooltip-glossary-custom-taxonomies-add-on-for-wordpress-by-creativeminds/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=113609&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Skins', 'description' => 'Lets you change the tooltip shape, color, opacity and much more. It offers various improved shapes and themes for the tooltip and improves the overall user experience. It is mobile responsive.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-skins-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=9644&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Log & Statistics', 'description' => 'Tracks and reports tooltip usage statistics such as number of tooltip hovers, term link clicks, inside tooltip clicks, term overall impressions, and server loads. Apply this data to your site to improve your glossary performance.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-log-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=10130&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Community Terms', 'description' => 'Let users suggest new terms for your Glossary. Works for both anonymous and registered users and allows you to control which users can add new terms directly and which needs moderation', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-community-terms-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=11837&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Remote Import', 'description' => 'Provides an easy way to import, replicate and create an up-to-date copy of your CM Glossary across several WordPress sites or domains.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-remote-import-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=12111&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Widgets', 'description' => 'Lets you add six new widgets to your glossary, which enhance the user experience and glossary engagement by exposing its content to users and visitors. Create visually appealing widgets to improve glossary content and user interaction.', 'link' => 'https://www.cminds.com/store/purchase-cm-tooltip-glossary-widgets-add-wordpress/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=30457&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'All Glossary AddOns Bundle', 'description' => 'Includes All CM Tooltip Glossary 5 AddOns.', 'link' => '', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=107574&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
	),
	'plugin-show-shortcodes'	 => TRUE,
	'plugin-shortcodes'			 => '<ul style="list-style-type:disc;margin-left:20px;">
        <li><strong>Custom glossary tooltip</strong> - [glossary_tooltip content="text"] term [/glossary_tooltip]</li>
        <li><strong>Exclude from parsing</strong> - [glossary_exclude] text [/glossary_exclude]</li>
        <li><strong>Apply tooltip</strong> - [cm_tooltip_parse] text [/cm_tooltip_parse] <sup>1</sup></li>
        <li><strong>Show Glossary Index</strong> - [glossary cat="cat1,cat2" gtags="tag1,tag2" search_term="term" <sup>2</sup>itemspage="1" <sup>2</sup>letter="all" related="0" no_desc="0" hide_terms="0" hide_abbrevs="0" hide_synonyms="0" glossary_index_style="tiles"<sup>3</sup> ]</li>
        <li><strong>Show Glossary Search Form</strong> - [glossary_search]</li>
		<li><strong>Show Merriam-Webster Dictionary</strong> - [glossary_dictionary term="term name"]</li>
        <li><strong>Show Merriam-Webster Thesaurus</strong> - [glossary_thesaurus term="term name"]</li>
        <li><strong>Translate</strong> - [glossary_translate term="text-to-translate" source="english" target="spanish"]</li>
        <li><strong>Toggle Tooltips</strong> - [glossary-toogle-tooltips session="0"]</li>
        <li><strong>Toggle Theme</strong> - [glossary-toggle-theme label="Test theme" class="test"]</li>
        <li><del><strong>Wikipedia</strong> - [glossary_wikipedia term="term name"]</del> - Only in <a href="https://tooltip.cminds.com"  target="_blank">Ecommerce</a></li>
        <li>
            <sup>1</sup> The shortcode internally calls custom filter called \'cm_tooltip_parse\' which can be used if you want the tooltip funtionality outside of \'the_content\':
            <code>$text_with_tooltip = apply_filters(\'cm_tooltip_parse\', $text);</code>
        </li>
        <li>
            <sup>2</sup> This attribute is for Server-side pagination only
        </li>
        <li>
            <sup>3</sup> Possible values are (use the value in quotes): Classic "classic", Classic + definition "classic-definition", Classic + excerpt "classic-excerpt", Small Tiles "small-tiles", Big Tiles "big-tiles", Classic table "classic-table", Modern table "modern-table", Sidebar + term page "sidebar-termpage", Expand style "expand-style", Grid "grid", Cube "cube"
        </li>
    </ul>',
	'plugin-shortcodes-action'	 => 'cmtt_glossary_supported_shortcodes',
	'plugin-version'			 => '3.4.1',
	'plugin-abbrev'				 => 'cmtt',
	'plugin-short-slug'			 => 'cmtooltip',
	'plugin-parent-short-slug'	 => '',
	'plugin-settings-url'		 => admin_url( 'admin.php?page=cmtt_settings' ),
	'plugin-show-guide'			 => FALSE,
	'plugin-guide-text'			 => '<p>
										The description of the plugin goes here
									</p>',
	'plugin-guide-video-height'	 => 180,
	'plugin-guide-videos'		 => array(
		array( 'title' => 'Free Version Installation Tutorial', 'video_id' => '157868636' ),
	),
	'plugin-file'				 => CMTT_PLUGIN_FILE,
	'plugin-dir-path'			 => plugin_dir_path( CMTT_PLUGIN_FILE ),
	'plugin-dir-url'			 => plugin_dir_url( CMTT_PLUGIN_FILE ),
	'plugin-basename'			 => plugin_basename( CMTT_PLUGIN_FILE ),
	'plugin-icon'				 => '',
	'plugin-name'				 => CMTT_NAME,
	'plugin-license-name'		 => CMTT_CANONICAL_NAME,
	'plugin-slug'				 => '',
	'plugin-menu-item'			 => CMTT_MENU_OPTION,
	'plugin-textdomain'			 => CMTT_SLUG_NAME,
	'plugin-userguide-key'		 => '6-cm-tooltip',
	'plugin-store-url'			 => 'https://www.cminds.com/store/tooltipglossary/',
	'plugin-review-url'			 => 'https://wordpress.org/support/view/plugin-reviews/enhanced-tooltipglossary',
	'plugin-changelog-url'		 => CMTT_RELEASE_NOTES,
	'plugin-licensing-aliases'	 => array( CMTT_LICENSE_NAME ),
);
