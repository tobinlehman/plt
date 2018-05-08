<?php

class GlossaryTooltipException extends Exception {

}

;

class CMTT_Pro {

	protected static $filePath		 = '';
	protected static $cssPath		 = '';
	protected static $jsPath		 = '';
	protected static $messages		 = '';
	public static $lastQueryDetails	 = array();
	public static $calledClassName;

	public static function init() {
		global $cmtt_isLicenseOk;

		self::setupConstants();

		self::includeFiles();

		self::initFiles();

		self::addOptions();

		if ( empty( self::$calledClassName ) ) {
			self::$calledClassName = __CLASS__;
		}

		$file	 = basename( __FILE__ );
		$folder	 = basename( dirname( __FILE__ ) );
		$hook	 = "in_plugin_update_message-{$folder}/{$file}";
		add_action( $hook, array( self::$calledClassName, 'cmtt_warn_on_upgrade' ) );

		self::$filePath	 = plugin_dir_url( __FILE__ );
		self::$cssPath	 = self::$filePath . 'assets/css/';
		self::$jsPath	 = self::$filePath . 'assets/js/';

		add_action( 'init', array( self::$calledClassName, 'cmtt_create_post_types' ) );

		add_action( 'admin_menu', array( self::$calledClassName, 'cmtt_admin_menu' ) );
		add_action( 'admin_init', array( self::$calledClassName, 'cmtt_glossary_handleexport' ) );
		add_action( 'admin_head', array( self::$calledClassName, 'addRicheditorButtons' ) );

		add_action( 'admin_enqueue_scripts', array( self::$calledClassName, 'cmtt_glossary_admin_settings_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( self::$calledClassName, 'cmtt_glossary_admin_edit_scripts' ) );

		add_action( 'restrict_manage_posts', array( self::$calledClassName, 'cmtt_restrict_manage_posts' ) );

		add_action( 'wp_print_styles', array( self::$calledClassName, 'cmtt_glossary_css' ) );
		add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_wp33' ) );
		add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_mbstring' ) );
		add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_client_pagination' ) );
		add_action( 'admin_print_footer_scripts', array( self::$calledClassName, 'cmtt_quicktags' ) );
		add_action( 'add_meta_boxes', array( self::$calledClassName, 'cmtt_RegisterBoxes' ) );
		add_action( 'save_post', array( self::$calledClassName, 'cmtt_save_postdata' ) );
		add_action( 'update_post', array( self::$calledClassName, 'cmtt_save_postdata' ) );

		add_action( 'wp_ajax_cmtt_get_glossary_backup', array( self::$calledClassName, 'cmtt_glossary_get_backup' ) );
		add_action( 'wp_ajax_nopriv_cmtt_get_glossary_backup', array( self::$calledClassName, 'cmtt_glossary_get_backup' ) );
		add_action( 'admin_init', array( self::$calledClassName, '__cmtt_rescheduleBackup' ) );
		add_action( 'cmtt_glossary_backup_event', array( self::$calledClassName, '__cmtt_doBackup' ) );

		add_filter( 'cmtt_settings_tooltip_tab_content_after', 'cminds_cmtt_settings_tooltip_tab_content_after' );
		add_filter( 'cmtt-custom-settings-tab-content-50', array( self::$calledClassName, 'outputLabelsSettings' ) );

		if ( $cmtt_isLicenseOk ) {
			/*
			 * FILTERS
			 */
			add_filter( 'get_the_excerpt', array( self::$calledClassName, 'cmtt_disable_parsing' ), 1 );
			add_filter( 'wpseo_opengraph_desc', array( self::$calledClassName, 'cmtt_reenable_parsing' ), 1 );
			/*
			 * Make sure parser runs before the post or page content is outputted
			 */
			add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
			add_filter( 'the_content', array( self::$calledClassName, 'removeGlossaryExclude' ), 25000 );

			add_filter( 'the_content', array( 'CMTT_Glossary_Index', 'lookForShortcode' ), 1 );
			add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );

			/*
			 * It's a custom filter which can be applied to create the tooltips
			 */
			add_filter( 'cm_tooltip_parse', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000, 2 );
			add_filter( 'the_title', array( self::$calledClassName, 'cmtt_glossary_addTitlePrefix' ), 22000, 2 );

			if ( get_option( 'cmtt_glossaryShowShareBoxTermPage' ) == 1 ) {
				add_filter( 'cmtt_glossary_term_after_content', array( self::$calledClassName, 'cmtt_glossaryAddShareBox' ) );
			}

			add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'addTitleToTooltip' ), 10, 2 );
			add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'addEditlinkToTooltip' ), 10, 2 );

			/*
			 * Filter for the BuddyPress record
			 */
			add_filter( 'bp_blogs_record_comment_post_types', array( self::$calledClassName, 'cmtt_bp_record_my_custom_post_type_comments' ) );

			add_filter( 'cmtt_is_tooltip_clickable', array( self::$calledClassName, 'isTooltipClickable' ) );

			/*
			 * Tooltip Content ADD
			 */
			add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'cmtt_glossary_parse_strip_shortcodes' ), 4, 2 );

			/*
			 * "Normal" Tooltip Content
			 */
			add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'getTheTooltipContentBase' ), 10, 2 );
			add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'cmtt_glossary_parse_strip_shortcodes' ), 20, 2 );
			add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'cmtt_glossary_filterTooltipContent' ), 30, 2 );

			add_filter( 'cmtt_parse_with_simple_function', array( self::$calledClassName, 'allowSimpleParsing' ) );

			// acf/load_value - filter for every value load
			add_filter( 'acf/load_value', array( self::$calledClassName, 'parseACFFields' ), 10, 3 );
			add_filter( 'bbp_get_reply_content', array( self::$calledClassName, 'parseBBPressFields' ) );

			add_filter( 'cmtt_tooltip_script_args', array( __CLASS__, 'addTooltipScriptArgs' ) );

			/*
			 * Tooltips in Woocommerce short description
			 */
			add_filter( 'woocommerce_short_description', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );

			/*
			 * SHORTCODES
			 */
			add_shortcode( 'cm_tooltip_parse', array( self::$calledClassName, 'cm_tooltip_parse' ) );
			/*
			 * Custom tooltip shortcode
			 */
			add_shortcode( 'glossary_tooltip', array( self::$calledClassName, 'cmtt_custom_tooltip_shortcode' ) );

			add_action( 'bp_before_create_group', array( self::$calledClassName, 'outputGlossaryExcludeStart' ) );
			add_action( 'bp_before_group_admin_content', array( self::$calledClassName, 'outputGlossaryExcludeStart' ), 50 );
			add_action( 'bp_after_create_group', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ) );
			add_action( 'bp_after_group_admin_content', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ), 50 );

			add_action( 'cmtt_save_options_before', array( self::$calledClassName, 'flushCaps' ), 10, 2 );

			/*
			 * Init the Glossary Index (adds hooks)
			 */
			CMTT_Glossary_Index::init();
		}
	}

	/**
	 * Add tooltip script args
	 * @param array $tooltipArgs
	 * @return type
	 */
	public static function addTooltipScriptArgs( $tooltipArgs ) {
		$tooltipArgs[ 'close_button' ] = (bool) get_option( 'cmtt_tooltipShowCloseIcon' );
		return $tooltipArgs;
	}

	/**
	 * Function adds the term highlighting to Advanced Custom Fields
	 * @param type $value
	 * @param type $post_id
	 * @param type $field
	 * @return type
	 */
	public static function parseACFFields( $value, $post_id, $field ) {

		if ( is_admin() ) {
			return $value;
		}

		if ( !is_string( $value ) ) {
			return $value;
		}

		$parseACFFields = get_option( 'cmtt_glossaryParseACFFields' );
		if ( $parseACFFields ) {

			/*
			 * Limit the scope
			 */
			if ( !in_array( $field[ 'type' ], array( 'text', 'wysiwyg' ) ) ) {
				return $value;
			}
			/*
			 * Creates problems in some cases
			 */
			remove_filter( 'acf_the_content', 'wptexturize' );
			$value = apply_filters( 'cm_tooltip_parse', $value, true );
		}
		return $value;
	}

	/**
	 * Function adds the term highlighting to bbPress fields
	 * @param type $value
	 * @param type $post_id
	 * @param type $field
	 * @return type
	 */
	public static function parseBBPressFields( $value ) {
		if ( !is_string( $value ) ) {
			return $value;
		}

		$parseBBPressFields = get_option( 'cmtt_glossaryParseBBPressFields' );
		if ( $parseBBPressFields ) {
			$value = apply_filters( 'cm_tooltip_parse', $value );
		}
		return $value;
	}

	/**
	 * Include the files
	 */
	public static function includeFiles() {
		do_action( 'cmtt_include_files_before' );

		include_once CMTT_PLUGIN_DIR . "glossaryIndex.php";
		include_once CMTT_PLUGIN_DIR . "synonyms.php";
		include_once CMTT_PLUGIN_DIR . "related.php";
		include_once CMTT_PLUGIN_DIR . "widgets.php";
		include_once CMTT_PLUGIN_DIR . "functions.php";
		include_once CMTT_PLUGIN_DIR . "cminds-pro.php";
		include_once CMTT_PLUGIN_DIR . "customTemplates.php";

		do_action( 'cmtt_include_files_after' );
	}

	/**
	 * Initialize the files
	 */
	public static function initFiles() {
		do_action( 'cmtt_init_files_before' );

		CMTT_RandomTerms_Widget::init();

		CMTT_Synonyms::init();
		CMTT_Related::init();
		CMTT_Custom_Templates::init();

		do_action( 'cmtt_init_files_after' );
	}

	/**
	 * Adds options
	 */
	public static function addOptions() {
		/*
		 * Options removed
		 */
		delete_option( 'cmtt_glossaryOnPages' ); //Show on Pages?
		delete_option( 'cmtt_glossaryOnPosts'); //Show on Posts?
		delete_option( 'cmtt_glossaryOnGlossary' ); //Show on Glossary Pages?
		/*
		 * General settings
		 */
		add_option( 'cmtt_glossaryOnMainQuery', 1 ); //Show on Main Query only
		add_option( 'cmtt_glossaryID', -1 ); //The ID of the main Glossary Page
		add_option( 'cmtt_glossaryPermalink', 'glossary' ); //Set permalink name
		add_option( 'cmtt_glossaryOnlySingle', 0 ); //Show on Home and Category Pages or just single post pages?
		add_option( 'cmtt_glossaryFirstOnly', 0 ); //Search for all occurances in a post or only one?
		add_option( 'cmtt_removeGlossaryCreateListFilter', 0 ); //Remove the Glossary Index List after first run
		add_option( 'cmtt_glossaryOnlySpaceSeparated', 1 ); //Search only for words separated by spaces
		add_option( 'cmtt_script_in_footer', 0 ); //Place the scripts in the footer not the header
		add_option( 'cmtt_glossaryOnPosttypes', array( 'post', 'page', 'glossary' ) ); //Default post types where the terms are highlighted

		add_option( 'cmtt_glossary_backup_pinprotect', '' ); //PIN Protect the backup

		add_option( 'cmtt_disable_metabox_all_post_types', 0 ); //show disable metabox for all post types
		/*
		 * Glossary page styling
		 */
		add_option( 'cmtt_glossaryDoubleclickEnabled', 0 );
		add_option( 'cmtt_glossaryDoubleclickService', 0 );
		/*
		 * Glossary page styling
		 */
		add_option( 'cmtt_glossaryShowShareBox', 0 ); //Show/hide the Share This box on top of the Glossary Index Page
		add_option( 'cmtt_glossaryShowShareBoxTermPage', 0 ); //Show/hide the Share This box on top of the Glossary Term Page
		add_option( 'cmtt_glossaryShowShareBoxLabel', 'Share This' ); //Label of the Sharing Box on the Glossary Index Page
		add_option( 'cmtt_glossaryTooltipDescLength', 300 ); //Limit the length of the definision shown on the Glossary Index Page
		add_option( 'cmtt_glossaryDiffLinkClass', 0 ); //Use different class to style glossary list
		add_option( 'cmtt_glossaryListTiles', 0 ); // Display glossary terms list as tiles
		add_option( 'cmtt_glossaryListTermLink', 0 ); //Remove links from glossary index to glossary page
		add_option( 'cmtt_index_letters', array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' ) );
		add_option( 'cmtt_glossaryTooltipDesc', 0 ); // Display description in glossary list
		add_option( 'cmtt_glossaryTooltipDescExcerpt', 0 ); // Display excerpt in glossary list
		add_option( 'cmtt_glossaryServerSidePagination', 0 ); //paginate server side or client side (with alphabetical index)
		add_option( 'cmtt_perPage', 0 ); //pagination on "glossary page" withing alphabetical navigation
		add_option( 'cmtt_glossaryRunApiCalls', 0 ); //exclude the API calls from the glossary main page
		add_option( 'cmtt_index_includeNum', 1 );
		add_option( 'cmtt_index_includeAll', 1 );
		add_option( 'cmtt_index_allLabel', 'ALL' );
		add_option( 'cmtt_glossary_addBackLink', 1 );
		add_option( 'cmtt_glossary_addBackLinkBottom', 1 );
		add_option( 'cmtt_glossary_backLinkText', '&laquo; Back to Glossary Index' );
		add_option( 'cmtt_glossary_backLinkBottomText', '&laquo; Back to Glossary Index' );
		/*
		 * Related articles
		 */
		add_option( 'cmtt_glossary_showRelatedArticles', 1 );
		add_option( 'cmtt_glossary_showRelatedArticlesCount', 5 );
		add_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount', 5 );
		add_option( 'cmtt_glossary_showRelatedArticlesTitle', 'Related Articles:' );
		add_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr', array( 'post', 'page', 'glossary' ) );
		add_option( 'cmtt_glossary_relatedArticlesPrefix', 'Glossary: ' );
		/*
		 * Synonyms
		 */
		add_option( 'cmtt_glossary_addSynonyms', 1 );
		add_option( 'cmtt_glossary_addSynonymsTitle', 'Synonyms: ' );
		add_option( 'cmtt_glossary_addSynonymsTooltip', 0 );
		/*
		 * Referral
		 */
		add_option( 'cmtt_glossaryReferral', false );
		add_option( 'cmtt_glossaryAffiliateCode', '' );
		/*
		 * Glossary term
		 */
		add_option( 'cmtt_glossaryBeforeTitle', '' ); //Text which shows up before the title on the term page
		/*
		 * Tooltip content
		 */
		add_option( 'cmtt_glossaryTooltip', 1 ); //Use tooltips on glossary items?
		add_option( 'cmtt_glossaryAddTermTitle', 1 ); //Add the term title to the glossary?
		add_option( 'cmtt_glossaryTooltipStripShortcode', 0 ); //Strip the shortcodes from glossary page before placing the tooltip?
		add_option( 'cmtt_glossaryFilterTooltip', 30 ); //Clean the tooltip text from uneeded chars?
		add_option( 'cmtt_glossaryFilterTooltipA', 0 ); //Clean the tooltip anchor tags
		add_option( 'cmtt_glossaryLimitTooltip', 0 ); // Limit the tooltip length  ?
		add_option( 'cmtt_glossaryTermDetailsLink', 'Term details' ); // Label of the link to term's details
		add_option( 'cmtt_glossaryExcerptHover', 0 ); //Search for all occurances in a post or only one?
		add_option( 'cmtt_glossaryProtectedTags', 1 ); //Aviod the use of Glossary in Protected tags?
		add_option( 'cmtt_glossaryCaseSensitive', 0 ); //Case sensitive?
		/*
		 * Glossary link
		 */
		add_option( 'cmtt_glossaryRemoveCommentsTermPage', 1 ); //Remove the comments from term page
		add_option( 'cmtt_glossaryInNewPage', 0 ); //In New Page?
		add_option( 'cmtt_glossaryTermLink', 0 ); //Remove links to glossary page
		add_option( 'cmtt_showTitleAttribute', 0 ); //show HTML title attribute
		/*
		 * Tooltip styling
		 */
		add_option( 'cmtt_tooltipIsClickable', 1 );
		add_option( 'cmtt_tooltipLinkUnderlineStyle', 'dotted' );
		add_option( 'cmtt_tooltipLinkUnderlineWidth', 1 );
		add_option( 'cmtt_tooltipLinkUnderlineColor', '#000000' );
		add_option( 'cmtt_tooltipLinkColor', '#000000' );
		add_option( 'cmtt_tooltipLinkHoverUnderlineStyle', 'solid' );
		add_option( 'cmtt_tooltipLinkHoverUnderlineWidth', '1' );
		add_option( 'cmtt_tooltipLinkHoverUnderlineColor', '#333333' );
		add_option( 'cmtt_tooltipLinkHoverColor', '#333333' );
		add_option( 'cmtt_tooltipBackground', '#666666' );
		add_option( 'cmtt_tooltipForeground', '#ffffff' );
		add_option( 'cmtt_tooltipOpacity', 95 );
		add_option( 'cmtt_tooltipBorderStyle', 'none' );
		add_option( 'cmtt_tooltipBorderWidth', 0 );
		add_option( 'cmtt_tooltipBorderColor', '#000000' );
		add_option( 'cmtt_tooltipPositionTop', 5 );
		add_option( 'cmtt_tooltipPositionLeft', 25 );
		add_option( 'cmtt_tooltipFontSize', 13 );
		add_option( 'cmtt_tooltipPadding', '2px 12px 3px 7px' );
		add_option( 'cmtt_tooltipBorderRadius', 6 );

		do_action( 'cmtt_add_options' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.1
	 * @return void
	 */
	public static function setupConstants() {
		/**
		 * Define Plugin Directory
		 *
		 * @since 1.0
		 */
		if ( !defined( 'CMTT_PLUGIN_DIR' ) ) {
			define( 'CMTT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Define Plugin URL
		 *
		 * @since 1.0
		 */
		if ( !defined( 'CMTT_PLUGIN_URL' ) ) {
			define( 'CMTT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define Plugin Slug name
		 *
		 * @since 1.0
		 */
		if ( !defined( 'CMTT_SLUG_NAME' ) ) {
			define( 'CMTT_SLUG_NAME', 'cm-tooltip-glossary' );
		}

		/**
		 * Define Plugin basename
		 *
		 * @since 1.0
		 */
		if ( !defined( 'CMTT_PLUGIN' ) ) {
			define( 'CMTT_PLUGIN', plugin_basename( __FILE__ ) );
		}

		if ( !defined( 'CMTT_MENU_OPTION' ) ) {
			define( 'CMTT_MENU_OPTION', 'cmtt_menu_options' );
		}

		define( 'CMTT_ABOUT_OPTION', 'cmtt_about' );
		define( 'CMTT_EXTENSIONS_OPTION', 'cmtt_extensions' );
		define( 'CMTT_SETTINGS_OPTION', 'cmtt_settings' );
		define( 'CMTT_IMPORTEXPORT_OPTION', 'cmtt_importexport' );
		define( 'CMTT_BACKUP_FILENAME', 'exportData.csv' );
		define( 'CMTT_TRANSIENT_ALL_ITEMS_KEY', 'cmtt_glossary_index_all_items' );

		do_action( 'cmtt_setup_constants_after' );
	}

	/**
	 * Create custom post type
	 */
	public static function cmtt_create_post_types() {
		$createGlossaryTermPages = (bool) get_option( 'cmtt_createGlossaryTermPages', TRUE );
		$glossaryPermalink		 = get_option( 'cmtt_glossaryPermalink', 'glossary' );
		$comments				 = get_option( 'cmtt_glossaryRemoveCommentsTermPage', 1 );
		/*
		 * Decide whether to add RSS feeds for custom post type or not (for fixing problems with missing links in Google Webdeveloper Tools)
		 */
		$addFeeds				 = get_option( 'cmtt_glossaryAddFeeds', true );

		$args = array(
			'label'					 => CMTT_Pro::__('Glossary'),
			'labels'				 => array(
				'add_new_item'	 => CMTT_Pro::__('Add New Glossary Item'),
				'add_new'		 => CMTT_Pro::__('Add Glossary Item'),
				'edit_item'		 => CMTT_Pro::__('Edit Glossary Item'),
				'view_item'		 => CMTT_Pro::__('View Glossary Item'),
				'singular_name'	 => CMTT_Pro::__('Glossary Item'),
				'name'			 => CMTT_NAME,
				'menu_name'		 => CMTT_Pro::__('Glossary')
			),
			'description'			 => '',
			'map_meta_cap'			 => true,
			'publicly_queryable'	 => $createGlossaryTermPages,
			'exclude_from_search'	 => false,
			'public'				 => $createGlossaryTermPages,
			'show_ui'				 => true,
			'show_in_admin_bar'		 => true,
			'show_in_menu'			 => CMTT_MENU_OPTION,
			'_builtin'				 => false,
			'capability_type'		 => 'post',
			'capabilities'			 => array(
				'edit_posts'	 => 'manage_glossary',
				'create_posts'	 => 'manage_glossary',
			),
			'hierarchical'			 => false,
			'has_archive'			 => false,
			'rewrite'				 => array( 'slug' => $glossaryPermalink, 'with_front' => false, 'feeds' => true, 'feed' => true ),
			'query_var'				 => true,
			'supports'				 => array( 'title', 'editor', 'author', 'excerpt', 'revisions',
				'custom-fields', 'page-attributes', 'post-thumbnails', 'thumbnail' ),
		);

		if ( !$comments ) {
			$args[ 'supports' ][] = 'comments';
		}

		register_post_type( 'glossary', apply_filters( 'cmtt_post_type_args', $args ) );

		if ( $addFeeds ) {
			global $wp_rewrite;
			$wp_rewrite->extra_permastructs[ 'glossary' ]	 = array();
			$args											 = (object) $args;

			$post_type		 = 'glossary';
			$archive_slug	 = $args->rewrite[ 'slug' ];
			if ( $args->rewrite[ 'with_front' ] ) {
				$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
			} else {
				$archive_slug = $wp_rewrite->root . $archive_slug;
			}
			if ( $args->rewrite[ 'feeds' ] && $wp_rewrite->feeds ) {
				$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
				add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
				add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
			}

			$permastruct_args			 = $args->rewrite;
			$permastruct_args[ 'feed' ]	 = $permastruct_args[ 'feeds' ];
			add_permastruct( $post_type, "{$args->rewrite[ 'slug' ]}/%$post_type%", $permastruct_args );
		}
	}

	public static function cmtt_admin_menu() {
		global $submenu;
		$current_user = wp_get_current_user();

		add_menu_page( 'Glossary', CMTT_NAME, 'manage_glossary', CMTT_MENU_OPTION, 'edit.php?post_type=glossary', CMTT_PLUGIN_URL . 'assets/css/images/cm-glossary-tooltip-icon.png' );

//        add_submenu_page(CMTT_MENU_OPTION, 'Trash', 'Trash', 'manage_glossary', 'edit.php?post_status=trash&post_type=glossary');
		add_submenu_page( CMTT_MENU_OPTION, 'Add New', 'Add New', 'manage_glossary', 'post-new.php?post_type=glossary' );
		do_action( 'cmtt_add_admin_menu_after_new' );
		add_submenu_page( CMTT_MENU_OPTION, 'TooltipGlossary Options', 'Settings', 'manage_options', CMTT_SETTINGS_OPTION, array( self::$calledClassName, 'outputOptions' ) );
		add_submenu_page( CMTT_MENU_OPTION, 'TooltipGlossary Import/Export', 'Import/Export', 'manage_options', CMTT_IMPORTEXPORT_OPTION, array( self::$calledClassName, 'cmtt_importExport' ) );

		$glossaryItemsPerPage = get_user_meta( get_current_user_id(), 'edit_glossary_per_page', true );
		if ( $glossaryItemsPerPage && intval( $glossaryItemsPerPage ) > 100 ) {
			update_user_meta( get_current_user_id(), 'edit_glossary_per_page', 100 );
		}

		add_filter( 'views_edit-glossary', array( self::$calledClassName, 'cmtt_filter_admin_nav' ), 10, 1 );
	}

	public static function cmtt_about() {
		ob_start();
		require 'views/backend/admin_about.php';
		$content = ob_get_contents();
		ob_end_clean();
		require 'views/backend/admin_template.php';
	}

	/**
	 * Shows extensions page
	 */
	public static function cmtt_extensions() {
		ob_start();
		include_once 'views/backend/admin_extensions.php';
		$content = ob_get_contents();
		ob_end_clean();
		require 'views/backend/admin_template.php';
	}

	public static function cmtt_importExport() {
		$showCredentialsForm	 = self::__cmtt_backupGlossary();
		$showBackupDownloadLink	 = self::__cmtt_getBackupGlossary( false );

		ob_start();
		include 'views/backend/admin_importexport.php';
		$content = ob_get_contents();
		ob_end_clean();
		include 'views/backend/admin_template.php';
	}

	public static function cmtt_glossary_handleexport() {
		if ( !empty( $_POST[ 'cmtt_doExport' ] ) ) {
			self::__cmtt_exportGlossary();
		} elseif ( !empty( $_POST[ 'cmtt_doImport' ] ) && !empty( $_FILES[ 'importCSV' ] ) && is_uploaded_file( $_FILES[ 'importCSV' ][ 'tmp_name' ] ) ) {
			self::__cmtt_importGlossary( $_FILES[ 'importCSV' ] );
		}
	}

	/**
	 * Function enqueues the scripts and styles for the admin Settings view
	 * @global type $parent_file
	 * @return type
	 */
	public static function cmtt_glossary_admin_settings_scripts() {
		global $parent_file;
		if ( CMTT_MENU_OPTION !== $parent_file ) {
			return;
		}

		wp_enqueue_style( 'jqueryUIStylesheet', self::$cssPath . 'jquery-ui-1.10.3.custom.css' );
		wp_enqueue_style( 'tooltip', self::$cssPath . 'tooltip.css' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script( 'tooltip-admin-js', self::$jsPath . 'cm-tooltip.js', array( 'jquery', 'wp-color-picker' ) );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		$tooltipData[ 'ajaxurl' ] = admin_url( 'admin-ajax.php' );
		wp_localize_script( 'tooltip-admin-js', 'cmtt_data', $tooltipData );
	}

	/**
	 * Function outputs the scripts and styles for the edit views
	 * @global type $typenow
	 * @return type
	 */
	public static function cmtt_glossary_admin_edit_scripts() {
		global $typenow;

		$defaultPostTypes			 = get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ? get_post_types() : array( 'post', 'page' );
		$allowedTermsBoxPostTypes	 = apply_filters( 'cmtt_allowed_terms_metabox_posttypes', $defaultPostTypes );

		if ( !in_array( $typenow, $allowedTermsBoxPostTypes ) ) {
			return;
		}

		wp_enqueue_style( 'tooltip', self::$cssPath . 'tooltip.css' );
		wp_enqueue_script( 'tooltip-admin-js', self::$jsPath . 'cm-tooltip.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tooltip' ) );
	}

	/**
	 * Filters admin navigation menus to show horizontal link bar
	 * @global string $submenu
	 * @global type $plugin_page
	 * @param type $views
	 * @return string
	 */
	public static function cmtt_filter_admin_nav( $views ) {
		global $submenu, $plugin_page;
		$scheme		 = is_ssl() ? 'https://' : 'http://';
		$adminUrl	 = str_replace( $scheme . $_SERVER[ 'HTTP_HOST' ], '', admin_url() );
		$currentUri	 = str_replace( $adminUrl, '', $_SERVER[ 'REQUEST_URI' ] );
		$submenus	 = array();
		if ( isset( $submenu[ CMTT_MENU_OPTION ] ) ) {
			$thisMenu = $submenu[ CMTT_MENU_OPTION ];

			$firstMenuItem = $thisMenu[ 0 ];
			unset( $thisMenu[ 0 ] );

			$secondMenuItem = array( 'Trash', 'manage_glossary', 'edit.php?post_status=trash&post_type=glossary', 'Trash' );

			array_unshift( $thisMenu, $firstMenuItem, $secondMenuItem );

			foreach ( $thisMenu as $item ) {
				$slug					 = $item[ 2 ];
				$isCurrent				 = ($slug == $plugin_page || strpos( $item[ 2 ], '.php' ) === strpos( $currentUri, '.php' ));
				$isExternalPage			 = strpos( $item[ 2 ], 'http' ) !== FALSE;
				$isNotSubPage			 = $isExternalPage || strpos( $item[ 2 ], '.php' ) !== FALSE;
				$url					 = $isNotSubPage ? $slug : get_admin_url( null, 'admin.php?page=' . $slug );
				$target					 = $isExternalPage ? '_blank' : '';
				$submenus[ $item[ 0 ] ]	 = '<a href="' . $url . '" target="' . $target . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[ 0 ] . '</a>';
			}
		}
		return $submenus;
	}

	public static function cmtt_restrict_manage_posts() {
		global $typenow, $wp_query;
		if ( $typenow == 'glossary' ) {
			$status	 = get_query_var( 'post_status' );
			$options = apply_filters( 'cmtt_glossary_restrict_manage_posts', array( 'published' => 'Published', 'trash' => 'Trash' ) );

			echo '<select name="post_status">';
			foreach ( $options as $key => $label ) {
				echo '<option value="' . $key . '" ' . selected( $key, $status ) . '>' . CMTT_Pro::__( $label ) . '</option>';
			}
			echo '</select>';

			/*
			 * create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
			 */
			$filters = get_object_taxonomies( 'glossary' );

			foreach ( $filters as $tax_slug ) {
				// retrieve the taxonomy object
				$tax_obj	 = get_taxonomy( $tax_slug );
				$tax_name	 = $tax_obj->labels->name;
				// retrieve array of term objects per taxonomy
				$terms		 = get_terms( $tax_slug );

				$currentValue = get_query_var( $tax_slug );

				// output html for taxonomy dropdown filter
				echo '<select name="' . $tax_slug . '" id="' . $tax_slug . '" class="postform">';
				echo '<option value="">Show All ' . $tax_name . '</option>';
				foreach ( $terms as $term ) {
					echo '<option value="' . $term->slug . '" ' . selected( $term->slug, $currentValue ) . '>' . $term->name . ' (' . $term->count . ')</option>';
				}
				echo '</select>';
			}
		}
	}

	/**
	 * Displays the horizontal navigation bar
	 * @global string $submenu
	 * @global type $plugin_page
	 */
	public static function cmtt_showNav() {
		global $submenu, $plugin_page;
		$submenus	 = array();
		$scheme		 = is_ssl() ? 'https://' : 'http://';
		$adminUrl	 = str_replace( $scheme . $_SERVER[ 'HTTP_HOST' ], '', admin_url() );
		$currentUri	 = str_replace( $adminUrl, '', $_SERVER[ 'REQUEST_URI' ] );

		if ( isset( $submenu[ CMTT_MENU_OPTION ] ) ) {
			$thisMenu = $submenu[ CMTT_MENU_OPTION ];
			foreach ( $thisMenu as $item ) {
				$slug			 = $item[ 2 ];
				$isCurrent		 = ($slug == $plugin_page || strpos( $item[ 2 ], '.php' ) === strpos( $currentUri, '.php' ));
				$isExternalPage	 = strpos( $item[ 2 ], 'http' ) !== FALSE;
				$isNotSubPage	 = $isExternalPage || strpos( $item[ 2 ], '.php' ) !== FALSE;
				$url			 = $isNotSubPage ? $slug : get_admin_url( null, 'admin.php?page=' . $slug );
				$submenus[]		 = array(
					'link'		 => $url,
					'title'		 => $item[ 0 ],
					'current'	 => $isCurrent,
					'target'	 => $isExternalPage ? '_blank' : ''
				);
			}
			require('views/backend/admin_nav.php');
		}
	}

	/**
	 * Returns TRUE if the tooltip should be clickable
	 */
	public static function isTooltipClickable( $isClickable ) {
		$isClickableArr[ 'is_clickable' ]	 = (bool) get_option( 'cmtt_tooltipIsClickable' );
		$isClickableArr[ 'edit_link' ]		 = (bool) get_option( 'cmtt_glossaryAddTermEditlink' ) && current_user_can( 'manage_glossary' );

		$isClickable = in_array( TRUE, $isClickableArr );
		return $isClickable;
	}

	/**
	 * Add the dynamic CSS to reflect the styles set by the options
	 * @return type
	 */
	public static function cmtt_glossary_dynamic_css() {
		ob_start();
		echo apply_filters( 'cmtt_dynamic_css_before', '' );
		?>
		span.glossaryLink, a.glossaryLink {
		border-bottom: <?php echo get_option( 'cmtt_tooltipLinkUnderlineStyle' ); ?> <?php echo get_option( 'cmtt_tooltipLinkUnderlineWidth' ); ?>px <?php echo get_option( 'cmtt_tooltipLinkUnderlineColor' ); ?> 
		color: <?php echo get_option( 'cmtt_tooltipLinkColor' ); ?> ;
		}
		a.glossaryLink:hover {
		border-bottom: <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ); ?> <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineWidth' ); ?>px <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineColor' ) ?> !important;
		color:<?php echo get_option( 'cmtt_tooltipLinkHoverColor' ); ?> 
		}
		<?php if ( get_option( 'cmtt_tooltipShadow', 1 ) ) : ?>
			#ttcont {
			box-shadow: #<?php echo str_replace( '#', '', get_option( 'cmtt_tooltipShadowColor', '666666' ) ); ?> 0px 0px 20px;
			}
			<?php
		endif;
		echo apply_filters( 'cmtt_dynamic_css_after', '' );
		$content = ob_get_clean();

		/*
		 * One can use this filter to change/remove the standard styling
		 */
		$dynamicCSScontent = apply_filters( 'cmtt_dynamic_css', $content );
		return trim( $dynamicCSScontent );
	}

	/**
	 * Outputs the frontend CSS
	 */
	public static function cmtt_glossary_css() {
		$fontName = get_option( 'cmtt_tooltipFontStyle', 'default' );

		wp_enqueue_style( 'tooltip', self::$cssPath . 'tooltip.css' );
		if ( is_string( $fontName ) && $fontName !== 'default' ) {
			wp_enqueue_style( 'tooltip-google-font', '//fonts.googleapis.com/css?family=' . $fontName );
		}

		/*
		 * It's WP 3.3+ function
		 */
		if ( function_exists( 'wp_add_inline_style' ) ) {
			wp_add_inline_style( 'tooltip', self::cmtt_glossary_dynamic_css() );
		}
	}

	/**
	 * Adds a notice about wp version lower than required 3.3
	 * @global type $wp_version
	 */
	public static function cmtt_glossary_admin_notice_wp33() {
		global $wp_version;

		if ( version_compare( $wp_version, '3.3', '<' ) ) {
			$message = sprintf( CMTT_Pro::__( '%s requires Wordpress version 3.3 or higher to work properly.' ), CMTT_NAME );
			cminds_show_message( $message, true );
		}
	}

	/**
	 * Adds a notice about mbstring not being installed
	 * @global type $wp_version
	 */
	public static function cmtt_glossary_admin_notice_mbstring() {
		$mb_support = function_exists( 'mb_strtolower' );

		if ( !$mb_support ) {
			$message = sprintf( CMTT_Pro::__( '%s since version 2.6.0 requires "mbstring" PHP extension to work! ' ), CMTT_NAME );
			$message .= '<a href="http://www.php.net/manual/en/mbstring.installation.php" target="_blank">(' . CMTT_Pro::__( 'Installation instructions.' ) . ')</a>';
			cminds_show_message( $message, true );
		}
	}

	/**
	 * Adds a notice about too many glossary items for client pagination
	 * @global type $wp_version
	 */
	public static function cmtt_glossary_admin_notice_client_pagination() {
		$serverSide			 = get_option( 'cmtt_glossaryServerSidePagination' );
		$glossaryItemsCount	 = wp_count_posts( 'glossary' );

		if ( !$serverSide && (int) $glossaryItemsCount->publish > 4000 ) {
			$message = sprintf( CMTT_Pro::__( '%s has detected that your glossary has more than 4000 terms and the "Client-side" pagination has been selected. <br/>'
			. 'Please switch to the "Server-side" pagination to avoid slowness and problems with the server memory on the Glossary Index Page.' ), CMTT_NAME );
			cminds_show_message( $message, true );
		}
	}

	/**
	 * Filters the tooltip content
	 * @param type $glossaryItemContent
	 * @param type $glossaryItemPermalink
	 * @return type
	 */
	public static function cmtt_glossary_filterTooltipContent( $glossaryItemContent, $glossaryItem ) {
		$glossaryItemPermalink	 = get_permalink( $glossaryItem->ID );
		$glossaryItemContent	 = str_replace( '[glossary_exclude]', '', $glossaryItemContent );
		$glossaryItemContent	 = str_replace( '[/glossary_exclude]', '', $glossaryItemContent );

		if ( get_option( 'cmtt_glossaryFilterTooltipImg' ) != 1 ) {
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<img>' );
		}

		if ( get_option( 'cmtt_glossaryFilterTooltipA' ) != 1 ) {
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<a>' );
		}

		if ( get_option( 'cmtt_glossaryFilterTooltip' ) == 1 ) {
			// remove paragraph, bad chars from tooltip text
			$glossaryItemContent = str_replace( array( chr( 10 ), chr( 13 ) ), array( '', '' ), $glossaryItemContent );
			$glossaryItemContent = str_replace( array( '</p>', '</ul>', '</li>' ), array( '<br/>', '<br/>', '<br/>' ), $glossaryItemContent );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<li>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<ul>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<p>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h1>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h2>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h3>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h4>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h5>' );
			$glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h6>' );
			$glossaryItemContent = htmlspecialchars( $glossaryItemContent );
			$glossaryItemContent = esc_attr( $glossaryItemContent );
			$glossaryItemContent = str_replace( "color:#000000", "color:#ffffff", $glossaryItemContent );
			$glossaryItemContent = str_replace( '\\[glossary_exclude\\]', '', $glossaryItemContent );
		} else {
			$glossaryItemContent = strtr( $glossaryItemContent, array( "\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />' ) );
		}


		/*
		 * 10.06.2015 added check for (get_option('cmtt_createGlossaryTermPages', TRUE)
		 */
		if ( (get_option( 'cmtt_createGlossaryTermPages', TRUE ) && get_option( 'cmtt_glossaryLimitTooltip' ) > 30) && (strlen( $glossaryItemContent ) > get_option( 'cmtt_glossaryLimitTooltip' )) ) {
			$text				 = CMTT_Pro::__( get_option( 'cmtt_glossaryTermDetailsLink' ) );
			$link				 = '<a class="glossaryTooltipMoreLink" href="' . $glossaryItemPermalink . '">' . $text . '</a>';
			$glossaryItemContent = cminds_truncate( html_entity_decode( $glossaryItemContent ), get_option( 'cmtt_glossaryLimitTooltip' ), '(...)' ) . ' <strong>' . $link . '</strong>';
		}

		return esc_attr( $glossaryItemContent );
	}

	/**
	 * Strips just one tag
	 * @param type $str
	 * @param type $tags
	 * @param type $stripContent
	 * @return type
	 */
	public static function cmtt_strip_only( $str, $tags, $stripContent = false ) {
		$content = '';
		if ( !is_array( $tags ) ) {
			$tags = (strpos( $str, '>' ) !== false ? explode( '>', str_replace( '<', '', $tags ) ) : array( $tags ));
			if ( end( $tags ) == '' ) {
				array_pop( $tags );
			}
		}
		foreach ( $tags as $tag ) {
			if ( $stripContent ) {
				$content = '(.+</' . $tag . '[^>]*>|)';
			}
			$str = preg_replace( '#</?' . $tag . '[^>]*>' . $content . '#is', '', $str );
		}
		return $str;
	}

	/**
	 * Disable the parsing for some reason
	 * @global type $wp_query
	 * @param type $smth
	 * @return type
	 */
	public static function cmtt_disable_parsing( $smth ) {
		global $wp_query;
		if ( $wp_query->is_main_query() && !$wp_query->is_singular ) {  // to prevent conflict with Yost SEO
			remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
			remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );
			do_action( 'cmtt_disable_parsing' );
		}
		return $smth;
	}

	/**
	 * Reenable the parsing for some reason
	 * @global type $wp_query
	 * @param type $smth
	 * @return type
	 */
	public static function cmtt_reenable_parsing( $smth ) {
		add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
		add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );
		do_action( 'cmtt_reenable_parsing' );
		return $smth;
	}

	/**
	 * Function strips the shortcodes if the option is set
	 * @param type $content
	 * @return type
	 */
	public static function cmtt_glossary_parse_strip_shortcodes( $content, $glossaryItem ) {
		if ( get_option( 'cmtt_glossaryTooltipStripShortcode' ) == 1 ) {
			$content = strip_shortcodes( $content );
		} else {
			$content = do_shortcode( $content );
		}

		return $content;
	}

	/**
	 * Function returns TRUE if the given post should be parsed
	 * @param type $post
	 * @param type $force
	 * @return boolean
	 */
	public static function cmtt_isParsingRequired( $post, $force = false, $from_cache = false ) {
		static $requiredAtLeastOnce = false;
		if ( $from_cache ) {
			/*
			 * Could be used to load JS/CSS in footer only when needed
			 */
			return $requiredAtLeastOnce;
		}

		if ( $force ) {
			return TRUE;
		}

		if ( !is_object( $post ) ) {
			return FALSE;
		}

		/*
		 *  Skip parsing for excluded pages and posts (except glossary pages?! - Marcin)
		 */
		$parsingDisabled = get_post_meta( $post->ID, '_glossary_disable_for_page', true ) == 1;
		if ( $parsingDisabled ) {
			return FALSE;
		}

		$currentPostType			 = get_post_type( $post );
		$showOnPostTypes			 = get_option( 'cmtt_glossaryOnPosttypes' );
		$showOnHomepageAuthorpageEtc = (!is_page( $post ) && !is_single( $post ) && get_option( 'cmtt_glossaryOnlySingle' ) == 0);
		$onMainQueryOnly			 = (get_option( 'cmtt_glossaryOnMainQuery' ) == 1 ) ? is_main_query() : TRUE;

		if ( !is_array( $showOnPostTypes ) ) {
			$showOnPostTypes = array();
		}
		$showOnSingleCustom = (is_singular( $post ) && in_array( $currentPostType, $showOnPostTypes ));

		$condition = ( $showOnHomepageAuthorpageEtc || $showOnSingleCustom );

		$result = $onMainQueryOnly && $condition;
		if ( $result ) {
			$requiredAtLeastOnce = TRUE;
		}
		$result = apply_filters( 'cmtt_isParsingRequiredResult', $result, $post, $force, $from_cache );
		return $result;
	}

	public static function cmtt_glossary_parse( $content, $force = false ) {
		global $post, $wp_query;
		static $initializeReplacedTerms = TRUE;

		/*
		 * Initialize $glossarySearchStringArr as empty array
		 */
		$glossarySearchStringArr = array();
		$onlySynonyms			 = array();

		if ( $post === NULL ) {
			return $content;
		}

		if ( !is_object( $post ) ) {
			$post = $wp_query->post;
		}

		$runParser = apply_filters( 'cmtt_runParser', self::cmtt_isParsingRequired( $post, $force ), $post, $content, $force );
		if ( !$runParser ) {
			return $content;
		}

		/*
		 * If there's more than one query and the "Only higlight once"
		 */
		if ( (get_option( 'cmtt_glossaryOnMainQuery' ) != 1 ) && (get_option( 'cmtt_glossaryFirstOnly' ) == 1) ) {
			$initializeReplacedTerms = true;
		}

		/*
		 * Run the glossary parser
		 */
		$contentHash = sha1( $content );
		if ( !$force ) {
			if ( !get_option( 'cmtt_glossaryEnableCaching', TRUE ) ) {
				wp_cache_delete( $contentHash );
			}
			$result = wp_cache_get( $contentHash, 'cachedParsedGlossaryPages' );
			if ( $result !== false ) {
				return $result;
			}
		}

		$args			 = apply_filters( 'cmtt_parser_query_args', array() );
		$glossary_index	 = CMTT_Pro::getGlossaryItemsSorted( $args );
//            if( !empty($glossary_index) )
//            {
//                // Sort by title length (function above)
//                uasort($glossary_index, array(self::$calledClassName, 'sortByWPQueryObjectTitleLength'));
//            }
		//the tag:[glossary_exclude]+[/glossary_exclude] can be used to mark text will not be taken into account by the glossary
		if ( $glossary_index ) {
			$excludeGlossary_regex = '/\\['   // Opening bracket
			. '(\\[?)'   // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "(glossary_exclude)"   // 2: Shortcode name
			. '\\b'   // Word boundary
			. '('  // 3: Unroll the loop: Inside the opening shortcode tag
			. '[^\\]\\/]*' // Not a closing bracket or forward slash
			. '(?:'
			. '\\/(?!\\])'   // A forward slash not followed by a closing bracket
			. '[^\\]\\/]*'   // Not a closing bracket or forward slash
			. ')*?'
			. ')'
			. '(?:'
			. '(\\/)'   // 4: Self closing tag ...
			. '\\]'  // ... and closing bracket
			. '|'
			. '\\]'  // Closing bracket
			. '(?:'
			. '('   // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			. '[^\\[]*+' // Not an opening bracket
			. '(?:'
			. '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			. '[^\\[]*+'   // Not an opening bracket
			. ')*+'
			. ')'
			. '\\[\\/\\2\\]' // Closing shortcode tag
			. ')?'
			. ')'
			. '(\\]?)/s';

			$excludeGlossaryStrs = array();

			/*
			 * Fix for the &amp; character and the AMP term
			 */
			$content = str_replace( '&#038;', '[glossary_exclude]&#038;[/glossary_exclude]', $content );

			/*
			 * Replace exclude tags and content between them in purpose to save the original text as is
			 * before glossary plug go over the content and add its code
			 * (later will be returned to the marked places in content)
			 */
			$excludeTagsCount	 = preg_match_all( $excludeGlossary_regex, $content, $excludeGlossaryStrs, PREG_PATTERN_ORDER );
			$i					 = 0;

			if ( $excludeTagsCount > 0 ) {
				foreach ( $excludeGlossaryStrs[ 0 ] as $excludeStr ) {
					$content = preg_replace( $excludeGlossary_regex, '#' . $i . 'excludeGlossary', $content, 1 );
					$i++;
				}
			}

			global $glossaryIndexArr, $onlySynonyms, $caseSensitive;

			$caseSensitive = get_option( 'cmtt_glossaryCaseSensitive', 0 );

			/*
			 * The loops prepares the search query for the replacement
			 */
			foreach ( $glossary_index as $glossary_item ) {
				if ( $post->post_type == 'glossary' && ($post->ID === $glossary_item->ID ) ) {
					continue;
				}
				$glossary_title = str_replace( '&#039;', '’', preg_quote( htmlspecialchars( trim( $glossary_item->post_title ), ENT_QUOTES, 'UTF-8' ), '/' ) );

				$addition							 = '';
				$synonymsArr						 = CMTT_Synonyms::getSynonymsArr( $glossary_item->ID, true );
				$onlySynonyms[ $glossary_item->ID ]	 = $synonymsArr;

				$variationsArr	 = CMTT_Synonyms::getSynonymsArr( $glossary_item->ID, false );
				$synonyms		 = array_merge( $synonymsArr, $variationsArr );
				$synonyms2		 = array();

				if ( !empty( $synonyms ) && count( $synonyms ) > 0 ) {
					foreach ( $synonyms as $val ) {
						$val = str_replace( '&#039;', '’', preg_quote( htmlspecialchars( trim( $val ), ENT_QUOTES, 'UTF-8' ), '/' ) );
						if ( !empty( $val ) ) {
							$synonyms2[] = $val;
						}
					}
					if ( !empty( $synonyms2 ) ) {
						$addition = '|' . implode( '|', $synonyms2 );
					}
				}
				$synonyms		 = null;
				$synonyms2		 = null;
				$synonymsArr	 = null;
				$variationsArr	 = null;

				$additionFiltered = apply_filters( 'cmtt_parse_addition_add', $addition, $glossary_item );

				$glossaryIndexArrKey = $glossary_title . $additionFiltered;
				if ( !$caseSensitive ) {
					$glossaryIndexArrKey = mb_strtolower( $glossaryIndexArrKey );
				}
				$glossarySearchStringArr[]					 = $glossary_title . $additionFiltered;
				$glossaryIndexArr[ $glossaryIndexArrKey ]	 = $glossary_item;
			}

			/*
			 * No replace required if there's no glossary items
			 */
			if ( !empty( $glossarySearchStringArr ) && is_array( $glossarySearchStringArr ) ) {
				$glossaryArrayChunk	 = apply_filters( 'cmtt_parse_array_chunk_size', 75 );
				$spaceSeparated		 = apply_filters( 'cmtt_parse_space_separated_only', 1 );

				global $replacedTerms;
				/*
				 * Initialize the array just once to make the "Highlight only the first occurance" work regardless of the filter parsing was attached to
				 */
				if ( $initializeReplacedTerms ) {
					$replacedTerms			 = array();
					$initializeReplacedTerms = FALSE;
				}

				if ( count( $glossarySearchStringArr ) > $glossaryArrayChunk ) {
					$chunkedGlossarySearchStringArr = array_chunk( $glossarySearchStringArr, $glossaryArrayChunk, TRUE );

					foreach ( $chunkedGlossarySearchStringArr as $glossarySearchStringArrChunk ) {
						$glossarySearchString	 = '/' . (($spaceSeparated) ? '(?<=\P{L}|^)(?<!(\p{N}))' : '') . '(?!(<|&lt;))(' . (!$caseSensitive ? '(?i)' : '') . implode( '|', $glossarySearchStringArrChunk ) . ')(?!(>|&gt;))' . (($spaceSeparated) ? '(?=\P{L}|$)(?!(\p{N}))' : '') . '/u';
						$content				 = self::cmtt_str_replace( $content, $glossarySearchString );
					}
				} else {
					$glossarySearchString	 = '/' . (($spaceSeparated) ? '(?<=\P{L}|^)(?<!(\p{N}))' : '') . '(?!(<|&lt;))(' . (!$caseSensitive ? '(?i)' : '') . implode( '|', $glossarySearchStringArr ) . ')(?!(>|&gt;))' . (($spaceSeparated) ? '(?=\P{L}|$)(?!(\p{N}))' : '') . '/u';
					$content				 = self::cmtt_str_replace( $content, $glossarySearchString );
				}
			}

			if ( $excludeTagsCount > 0 ) {
				$i = 0;
				foreach ( $excludeGlossaryStrs[ 0 ] as $excludeStr ) {
					$content = str_replace( '#' . $i . 'excludeGlossary', $excludeStr, $content );
					$i++;
				}
				//remove all the exclude signs
				$content = str_replace( array( '[glossary_exclude]', '[/glossary_exclude]' ), array( '', '' ), $content );
			}
		}
		if ( get_option( 'cmtt_glossaryEnableCaching', TRUE ) ) {
			$result = wp_cache_set( $contentHash, $content, 'cachedParsedGlossaryPages' );
		}

		return $content;
	}

	/**
	 * [cm_tooltip_parse]content[/cm_tooltip_parse]
	 * @param type $atts
	 * @param type $content
	 * @return type
	 */
	public static function cm_tooltip_parse( $atts, $content = '' ) {
		global $cmWrapItUp;
		$atts = $atts;

		$cmWrapItUp	 = true;
		$result		 = apply_filters( 'cm_tooltip_parse', $content, true );
		$cmWrapItUp	 = false;
		return $result;
	}

	/**
	 * Replaces the matches
	 * @param type $match
	 * @return type
	 */
	public static function cmtt_replace_matches( $match ) {
		if ( !empty( $match[ 0 ] ) ) {
			$replacementText = self::cmtt_prepareReplaceTemplate( htmlspecialchars_decode( $match[ 0 ], ENT_COMPAT ) );
			return $replacementText;
		}
	}

	/**
	 * Function which prepares the templates for the glossary words found in text
	 *
	 * @param string $title replacement text
	 * @return array|string
	 */
	public static function cmtt_prepareReplaceTemplate( $title ) {
		/*
		 * Placeholder for the title
		 */
		$titlePlaceholder = '##TITLE_GOES_HERE##';

		/*
		 * Array of glossary items, settings
		 */
		global $glossaryIndexArr, $caseSensitive, $templatesArr, $removeLinksToTerms, $replacedTerms, $post;

		/*
		 *  Checks whether to show tooltips on this page or not
		 */
		$tooltipsDisabled = get_post_meta( $post->ID, '_glossary_disable_tooltip_for_page', true ) == 1;

		/*
		 *  Checks whether to show links to glossary pages or not
		 */
		$linksDisabled = get_post_meta( $post->ID, '_glossary_disable_links_for_page', true ) == 1;

		/*
		 * If TRUE then the links to glossary pages are exchanged with spans
		 */
		$removeLinksToTerms = (get_option( 'cmtt_glossaryTermLink' ) == 1 || $linksDisabled || !get_option( 'cmtt_createGlossaryTermPages' ) );

		/*
		 * If "Highlight first occurance only" option is set
		 */
		$highlightFirstOccuranceOnly = (get_option( 'cmtt_glossaryFirstOnly' ) == 1);

		/*
		 * If it's case insensitive, then the term keys are stored as lowercased
		 */
		$normalizedTitle = str_replace( '&#039;', "’", preg_quote( htmlspecialchars( trim( $title ), ENT_QUOTES, 'UTF-8' ), '/' ) );
		$titleIndex		 = (!$caseSensitive) ? mb_strtolower( $normalizedTitle ) : $normalizedTitle;

		try {
			do_action( 'cmtt_replace_template_before_synonyms', $titleIndex, $title );
		} catch ( GlossaryTooltipException $ex ) {
			/*
			 * Trick to stop the execution
			 */
			$message = $ex->getMessage();
			return $message;
		}

		/*
		 * Upgrade to make it work with synonyms
		 */
		if ( $glossaryIndexArr ) {
			/*
			 * First - look for exact keys
			 */
			if ( array_key_exists( $titleIndex, $glossaryIndexArr ) ) {
				$glossary_item = $glossaryIndexArr[ $titleIndex ];
			} else {
				/*
				 * If not found - try the synonyms
				 */
				foreach ( $glossaryIndexArr as $key => $value ) {
					/*
					 * If we find the term we make sure it's a synonym and not a part of some other term
					 */
					if ( strstr( $key, '|' ) && strstr( $key, $titleIndex ) ) {
						$synonymsArray = explode( '|', $key );
						if ( in_array( $titleIndex, $synonymsArray ) ) {
							/*
							 * $replace = Glossary Post
							 */
							$glossary_item = $value;
							break;
						}
					}
				}
			}
		}

		try {
			do_action( 'cmtt_replace_template_after_synonyms', $glossary_item, $titleIndex, $title );
		} catch ( GlossaryTooltipException $ex ) {
			/*
			 * Trick to stop the execution
			 */
			$message = $ex->getMessage();
			return $message;
		}

		/*
		 * Error checking
		 */
		if ( !is_object( $glossary_item ) ) {
			return 'Error! Post not found for word:' . $titleIndex;
		}

		$id = $glossary_item->ID;

		/**
		 *  If "Highlight first occurance only" option is set, we check if the post has already been highlighted
		 */
		if ( $highlightFirstOccuranceOnly && is_array( $replacedTerms ) && !empty( $replacedTerms ) ) {
			foreach ( $replacedTerms as $replacedTerm ) {
				if ( $replacedTerm[ 'postID' ] == $id ) {
					/*
					 * If the post has already been highlighted
					 */
					return $title;
				}
			}
		}

		/*
		 * Save the post item to the global array so it can be used to generate "Related Terms" list
		 */
		$replacedTerms[ $title ][ 'post' ] = $glossary_item;

		/*
		 * Save the post item ID to the global array so it's easy to find out if it has been highlighted in text or not
		 */
		$replacedTerms[ $title ][ 'postID' ] = $id;

		/*
		 * Replacement is already cached - use it
		 */
		if ( !empty( $templatesArr[ $id ] ) ) {
			$templateReplaced = str_replace( $titlePlaceholder, $title, $templatesArr[ $id ] );
			return $templateReplaced;
		}

		$additionalClass = apply_filters( 'cmtt_term_tooltip_additional_class', '', $glossary_item );
		$excludeTT		 = get_post_meta( $id, '_cmtt_exclude_tooltip', true ) || $tooltipsDisabled;
		$permalink		 = apply_filters( 'cmtt_term_tooltip_permalink', get_permalink( $glossary_item->ID ), $glossary_item );

		/*
		 * Open in new window
		 */
		$windowTarget	 = (get_option( 'cmtt_glossaryInNewPage' ) == 1) ? ' target="_blank" ' : '';
		$titleAttr		 = (get_option( 'cmtt_showTitleAttribute' ) == 1) ? ' title="Glossary: ' . esc_attr( $glossary_item->post_title ) . '" ' : '';

		if ( get_option( 'cmtt_glossaryTooltip' ) == 1 && $excludeTT != 1 ) {
			$tooltipContent	 = apply_filters( 'cmtt_term_tooltip_content', '', $glossary_item );
			/*
			 * Apply filters for 3rd party widgets additions
			 */
			$tooltipContent	 = apply_filters( 'cmtt_3rdparty_tooltip_content', $tooltipContent, $glossary_item );
			/*
			 * Add filter to change the glossary item content on the glossary list
			 */
			$tooltipContent	 = apply_filters( 'cmtt_tooltip_content_add', $tooltipContent, $glossary_item );

			if ( $removeLinksToTerms ) {
				$link_replace = '<span ' . $titleAttr . ' data-tooltip="' . $tooltipContent . '" class="glossaryLink ' . $additionalClass . '">' . $titlePlaceholder . '</span>';
			} else {
				$link_replace = '<a href="' . $permalink . '"' . $titleAttr . ' data-tooltip="' . $tooltipContent . '"  class="glossaryLink ' . $additionalClass . '"' . $windowTarget . '>' . $titlePlaceholder . '</a>';
			}
		} else {
			if ( $removeLinksToTerms ) {
				$link_replace = '<span  ' . $titleAttr . ' class="glossaryLink">' . $titlePlaceholder . '</span>';
			} else {
				$link_replace = '<a href="' . $permalink . '"' . $titleAttr . ' class="glossaryLink"' . $windowTarget . '>' . $titlePlaceholder . '</a>';
			}
		}

		/*
		 * Save with $titlePlaceholder - for the synonyms
		 */
		$templatesArr[ $id ] = $link_replace;

		/*
		 * Replace it with title to show correctly for the first time
		 */
		$link_replace = str_replace( $titlePlaceholder, $title, $link_replace );
		return $link_replace;
	}

	/**
	 * Get the base of the Tooltip Content on Glossary Index Page
	 * @param type $content
	 * @param type $glossary_item
	 * @return type
	 */
	public static function getTheTooltipContentBase( $content, $glossary_item ) {
		$content = (get_option( 'cmtt_glossaryExcerptHover' ) && $glossary_item->post_excerpt) ? $glossary_item->post_excerpt : $glossary_item->post_content;
		return $content;
	}

	/**
	 * Function adds the title to the tooltip
	 * @global type $wpdb
	 * @param string $where
	 * @return string
	 */
	public static function addTitleToTooltip( $glossaryItemContent, $glossary_item ) {
		$showTitle = get_option( 'cmtt_glossaryAddTermTitle' );

		if ( $showTitle == 1 ) {
			$glossaryItemTitle	 = '<div class=glossaryItemTitle>' . get_the_title( $glossary_item->ID ) . '</div>';
			/*
			 * Add the title
			 */
			$glossaryItemContent = $glossaryItemTitle . $glossaryItemContent;
		}

		return $glossaryItemContent;
	}

	/**
	 * Function adds the editlink
	 * @return string
	 */
	public static function addEditlinkToTooltip( $glossaryItemContent, $glossary_item ) {
		$showTitle = get_option( 'cmtt_glossaryAddTermEditlink' );

		if ( $showTitle == 1 && current_user_can( 'manage_glossary' ) ) {
			$link					 = '<a href=&quot;' . get_edit_post_link( $glossary_item->ID ) . '&quot;>Edit term</a>';
			$glossaryItemEditlink	 = '<div class=glossaryItemEditlink>' . $link . '</div>';
			/*
			 * Add the editlink
			 */
			$glossaryItemContent	 = $glossaryItemEditlink . $glossaryItemContent;
		}

		return $glossaryItemContent;
	}

	/**
	 * Add the social share buttons
	 * @param string $content
	 * @return string
	 */
	public static function cmtt_glossaryAddShareBox( $content = '' ) {
		if ( !defined( 'DOING_AJAX' ) ) {
			ob_start();
			require CMTT_PLUGIN_DIR . 'views/frontend/social_share.phtml';
			$preContent = ob_get_clean();

			$content = $preContent . $content;
		}

		return $content;
	}

	/**
	 * Function responsible for saving the options
	 */
	public static function saveOptions() {
		$messages	 = '';
		$_POST		 = array_map( 'stripslashes_deep', $_POST );
		$post		 = $_POST;

		if ( isset( $post[ "cmtt_glossarySave" ] ) || isset( $post[ 'cmtt_glossaryRelatedRefresh' ] ) || isset( $post[ 'cmtt_glossaryRelatedRefreshContinue' ] ) ) {
			$test = check_admin_referer( 'update-options' );

			do_action( 'cmtt_save_options_before', $post, array( &$messages ) );
			$enqueeFlushRules = false;
			/*
			 * Update the page options
			 */
			update_option( 'cmtt_glossaryID', $post[ "cmtt_glossaryID" ] );
			CMTT_Glossary_Index::tryGenerateGlossaryIndexPage();
			if ( $post[ "cmtt_glossaryPermalink" ] !== get_option( 'cmtt_glossaryPermalink' ) ) {
				/*
				 * Update glossary post permalink
				 */
				$glossaryPost		 = array(
					'ID'		 => $post[ "cmtt_glossaryID" ],
					'post_name'	 => $post[ "cmtt_glossaryPermalink" ]
				);
				wp_update_post( $glossaryPost );
				$enqueeFlushRules	 = true;
			}

			update_option( 'cmtt_glossaryPermalink', $post[ "cmtt_glossaryPermalink" ] );

			if ( apply_filters( 'cmtt_enqueueFlushRules', $enqueeFlushRules, $post ) ) {
				self::_flush_rewrite_rules();
			}

			unset( $post[ 'cmtt_glossaryID' ], $post[ 'cmtt_glossaryPermalink' ], $post[ 'cmtt_glossarySave' ] );

			function cmtt_get_the_option_names( $k ) {
				return strpos( $k, 'cmtt_' ) === 0;
			}

			$options_names = apply_filters( 'cmtt_thirdparty_option_names', array_filter( array_keys( $post ), 'cmtt_get_the_option_names' ) );

			foreach ( $options_names as $option_name ) {
				if ( !isset( $post[ $option_name ] ) ) {
					update_option( $option_name, 0 );
				} else {
					if ( $option_name == 'cmtt_index_letters' ) {
						$optionValue = explode( ',', $post[ $option_name ] );
						$optionValue = array_map( 'mb_strtolower', $optionValue );
					} else {
						$optionValue = is_array( $post[ $option_name ] ) ? $post[ $option_name ] : trim( $post[ $option_name ] );
					}
					update_option( $option_name, self::sanitizeInput( $option_name, $optionValue ) );
				}
			}
			do_action( 'cmtt_save_options_after_on_save', $post, array( &$messages ) );
		}

		do_action( 'cmtt_save_options_after', $post, array( &$messages ) );

		if ( isset( $post[ 'cmtt_glossaryRelatedRefresh' ] ) ) {
			CMTT_Related::crawlArticles( TRUE );
			self::$messages = CMTT_Pro::__( 'Related Articles Index rebuild has been started.' );
		}

		if ( isset( $post[ 'cmtt_glossaryRelatedRefreshContinue' ] ) ) {
			CMTT_Related::crawlArticles();
			self::$messages = CMTT_Pro::__( 'Related Articles Index has been updated.' );
		}

		if ( isset( $post[ 'cmtt_removeAllOptions' ] ) ) {
			self::_cleanupOptions();
			self::$messages = 'CM Tooltip Glossary data options have been removed from the database.';
		}

		if ( isset( $post[ 'cmtt_removeAllItems' ] ) ) {
			self::_cleanupItems();
			self::$messages = 'CM Tooltip Glossary data terms have been removed from the database.';
		}

		return array( 'messages' => self::$messages );
	}

	/**
	 * Sanitizes the inputs
	 * @param type $input
	 */
	public static function sanitizeInput( $optionName, $optionValue ) {
		if ( in_array( $optionName, array( 'cmtt_glossaryPermalink' ) ) ) {
			$sanitizedValue = sanitize_title( $optionValue );
		} else {
			if ( !is_array( $optionValue ) ) {
				$sanitizedValue = esc_attr( $optionValue );
			} else {
				$sanitizedValue = $optionValue;
			}
		}

		return $sanitizedValue;
	}

	/**
	 * Displays the options screen
	 */
	public static function outputOptions() {
		$result		 = self::saveOptions();
		$messages	 = $result[ 'messages' ];

		ob_start();
		require('views/backend/admin_settings.php');
		$content = ob_get_contents();
		ob_end_clean();
		require('views/backend/admin_template.php');
	}

	public static function cmtt_quicktags() {
		global $post;
		?>
		<script type="text/javascript">
		    if ( typeof QTags !== "undefined" )
		    {
		        QTags.addButton( 'cmtt_parse', 'Glossary Parse', '[glossary_parse]', '[/glossary_parse]' );
		        QTags.addButton( 'cmtt_exclude', 'Glossary Exclude', '[glossary_exclude]', '[/glossary_exclude]' );
		        QTags.addButton( 'cmtt_translate', 'Glossary Translate', '[glossary_translate term=""]' );
		        QTags.addButton( 'cmtt_dictionary', 'Glossary Dictionary', '[glossary_dictionary term=""]' );
		        QTags.addButton( 'cmtt_thesaurus', 'Glossary Thesaurus', '[glossary_thesaurus term=""]' );
		    }
		</script>
		<?php
	}

	public static function __cmtt_prepareExportGlossary() {
		$args = array(
			'post_type'		 => 'glossary',
			'post_status'	 => 'publish',
			'nopaging'		 => true,
			'orderby'		 => 'ID',
			'order'			 => 'ASC'
		);

		$q							 = new WP_Query( $args );
		$exportData					 = array();
		$exportHeaderRow			 = array(
			'Id',
			'Title',
			'Excerpt',
			'Description',
			'Synonyms',
			'Variations',
		);
		$exportHeaderRowWithMeta	 = apply_filters( 'cmtt_export_header_row', $exportHeaderRow );
		$exportHeaderRowWithMeta[]	 = 'Meta';
		$exportData[]				 = $exportHeaderRowWithMeta;

		/*
		 * Get all the glossary items
		 */
		foreach ( $q->get_posts() as $term ) {
			/*
			 * All the meta information
			 */
			$meta = get_post_meta( $term->ID, '', true );
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = is_array( $value ) ? $value[ 0 ] : $value;
			}
			$jsonEncodedMeta = json_encode( $meta );

			$exportDataRow			 = array(
				$term->ID,
				$term->post_title,
				str_replace( array( "\r\n", "\n", "\r" ), array( "", "", "" ), nl2br( $term->post_excerpt ) ),
				str_replace( array( "\r\n", "\n", "\r" ), array( "", "", "" ), nl2br( $term->post_content ) ),
				CMTT_Synonyms::getSynonyms( $term->ID, true ),
				CMTT_Synonyms::getSynonyms( $term->ID, false ),
			);
			$exportDataRowWithMeta	 = apply_filters( 'cmtt_export_data_row', $exportDataRow, $term );
			$exportDataRowWithMeta[] = $jsonEncodedMeta;
			$exportData[]			 = $exportDataRowWithMeta;
		}

		return $exportData;
	}

	/**
	 * Outputs the backup file
	 */
	public static function cmtt_glossary_get_backup() {
		$pinOption = get_option( 'cmtt_glossary_backup_pinprotect', false );

		if ( !empty( $pinOption ) ) {
			$passedPin = filter_input( INPUT_GET, 'pin' );
			if ( $passedPin != $pinOption ) {
				echo 'Incorrect PIN!';
				die();
			}
		}

		$backupGlossary = self::__cmtt_getBackupGlossary( false );

		if ( $backupGlossary ) {
			$upload_dir	 = wp_upload_dir();
			$filepath	 = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/' . CMTT_BACKUP_FILENAME;

			$outstream = fopen( $filepath, 'r' );
			rewind( $outstream );

			header( 'Content-Encoding: UTF-8' );
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=glossary_backup_' . date( 'Ymd_His', current_time( 'timestamp' ) ) . '.csv' );
			/*
			 * Why including the BOM? - Marcin
			 */
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			while ( !feof( $outstream ) ) {
				echo fgets( $outstream );
			}
			fclose( $outstream );
		}
		die();
	}

	/**
	 * Outputs the backup glossary AJAX link
	 */
	public static function __cmtt_getBackupGlossary( $protect = true ) {
		$upload_dir	 = wp_upload_dir();
		$filepath	 = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/' . CMTT_BACKUP_FILENAME;

		if ( file_exists( $filepath ) ) {
			$url = admin_url( 'admin-ajax.php?action=cmtt_get_glossary_backup' );

			if ( !$protect ) {
				$pinOption = get_option( 'cmtt_glossary_backup_pinprotect' );
				$url .= $pinOption ? '&pin=' . $pinOption : '';
			}

			return $url;
		}

		return false;
	}

	/**
	 * Backups the glossary
	 */
	public static function __cmtt_backupGlossary() {
		if ( empty( $_POST ) ) {
			return false;
		}

		check_admin_referer( 'cmtt_do_backup' );

		if ( isset( $_POST[ 'cmtt_doBackup' ] ) ) {
			$url = wp_nonce_url( 'admin.php?page=cmtt_importexport' );
			self::__cmtt_doBackup( $url );
		}

		return false;
	}

	/**
	 * Reschedule the backup event
	 * @return type
	 */
	public static function __cmtt_rescheduleBackup() {
		$possibleIntervals = array_keys( wp_get_schedules() );

		$newScheduleHour	 = filter_input( INPUT_POST, 'cmtt_glossary_backupCronHour' );
		$newScheduleInterval = filter_input( INPUT_POST, 'cmtt_glossary_backupCronInterval' );

		if ( $newScheduleHour !== NULL && $newScheduleInterval !== NULL ) {
			wp_clear_scheduled_hook( 'cmtt_glossary_backup_event' );

			if ( $newScheduleInterval == 'none' ) {
				return;
			}

			if ( !in_array( $newScheduleInterval, $possibleIntervals ) ) {
				$newScheduleInterval = 'daily';
			}

			$time = strtotime( $newScheduleHour );
			if ( $time === FALSE ) {
				$time = current_time( 'timestamp' );
			}

			wp_schedule_event( $time, $newScheduleInterval, 'cmtt_glossary_backup_event' );
		}
	}

	public static function __cmtt_doBackup( $url = null ) {
		$form_fields = array( 'cmtt_doBackup' ); // this is a list of the form field contents I want passed along between page views
		$method		 = ''; // Normally you leave this an empty string and it figures it out by itself, but you can override the filesystem method here
		// check to see if we are trying to save a file

		$secureWrite = get_option( 'cmtt_glossary_backup_secure', true );

		if ( $secureWrite ) {
			if ( empty( $url ) ) {
				$url = wp_nonce_url( 'admin.php?page=cmtt_importexport' );
			}

			/** WordPress Administration File API */
			require_once(ABSPATH . 'wp-admin/includes/file.php');

			// okay, let's see about getting credentials
			if ( false === ($creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
				// if we get here, then we don't have credentials yet,
				// but have just produced a form for the user to fill in,
				// so stop processing for now
				return true; // stop the normal page form from displaying
			}

			// now we have some credentials, try to get the wp_filesystem running
			if ( !WP_Filesystem( $creds ) ) {
				// our credentials were no good, ask the user for them again
				request_filesystem_credentials( $url, $method, true, false, $form_fields );
				return true;
			}
		}

		// get the upload directory
		$upload_dir	 = wp_upload_dir();
		$filename	 = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/';

		if ( !file_exists( $filename ) ) {
			wp_mkdir_p( $filename );
		}

		chmod( $filename, 0775 );
		$filename .= CMTT_BACKUP_FILENAME;

		$string		 = '';
		$outstream	 = fopen( "php://temp", 'r+' );

		$exportData = self::__cmtt_prepareExportGlossary();
		foreach ( $exportData as $line ) {
			fputcsv( $outstream, $line, ',', '"' );
		}
		rewind( $outstream );
		while ( !feof( $outstream ) ) {
			$string .= fgets( $outstream );
		}
		fclose( $outstream );

		if ( $secureWrite ) {
			/*
			 * by this point, the $wp_filesystem global should be working, so let's use it to create a file
			 */
			global $wp_filesystem;
			if ( !$wp_filesystem->put_contents( $filename, $string, FS_CHMOD_FILE ) ) {
				echo "error saving file!";
			}
		} else {
			file_put_contents( $filename, $string, LOCK_EX );
			chmod( $filename, 0775 );
		}
	}

	/**
	 * Exports the glossary
	 */
	public static function __cmtt_exportGlossary() {
		$exportData = self::__cmtt_prepareExportGlossary();

		$outstream = fopen( "php://temp", 'r+' );

		foreach ( $exportData as $line ) {
			fputcsv( $outstream, $line, ',', '"' );
		}
		rewind( $outstream );

		header( 'Content-Encoding: UTF-8' );
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=glossary_export_' . date( 'Ymd_His', current_time( 'timestamp' ) ) . '.csv' );
		/*
		 * Why including the BOM? - Marcin
		 */
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		while ( !feof( $outstream ) ) {
			echo fgets( $outstream );
		}
		fclose( $outstream );
		exit;
	}

	/**
	 * Imports the single glossary item
	 * @global type $wpdb
	 * @param type $item
	 * @param type $override
	 * @return boolean
	 */
	public static function importGlossaryItem( $item, $override = TRUE ) {
		if ( !empty( $item ) && is_array( $item ) && count( $item ) >= 4 && !empty( $item[ 1 ] ) && !empty( $item[ 3 ] ) ) {
			global $wpdb;
			$data		 = array(
				'post_title'	 => $item[ 1 ],
				'post_type'		 => 'glossary',
				'post_excerpt'	 => $item[ 2 ],
				'post_content'	 => $item[ 3 ],
				'post_status'	 => 'publish'
			);
			$sql		 = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_title=%s AND post_status='publish'", 'glossary', $item[ 1 ] );
			$existingId	 = $wpdb->get_var( $sql );

			if ( !empty( $existingId ) ) {
				//update
				$data[ 'ID' ] = $existingId;

				if ( $override ) {
					$update = wp_update_post( $data );
				} else {
					$update = FALSE;
				}
			} else {
				//insert new
				$update = wp_insert_post( $data );
			}

			if ( $update > 0 ) {
				CMTT_Synonyms::setSynonyms( $update, $item[ 4 ], true );
				CMTT_Synonyms::setSynonyms( $update, $item[ 5 ], false );
			}

			do_action( 'cmtt_import_glossary_item', $item, $update );

			return $update;
		}

		return false;
	}

	public static function __cmtt_importGlossary( $file ) {
		$filesrc = $file[ 'tmp_name' ];
		$fp		 = fopen( $filesrc, 'r' );
		$tab	 = array();
		while ( !feof( $fp ) ) {
			$item	 = fgetcsv( $fp, 0, ',', '"' );
			$tab[]	 = $item;
		}
		$numberOfElements = 0;

		remove_action( 'save_post', array( 'CMTT_Related', 'triggerOnSave' ) );
		remove_action( 'save_post', array( 'CMTTW_Related', 'triggerOnSave' ) );

		for ( $i = 1; $i < count( $tab ); $i++ ) {
			$result = self::importGlossaryItem( $tab[ $i ] );
			if ( $result !== false ) {
				$numberOfElements++;
			}
		}
		wp_redirect( esc_url( add_query_arg( array( 'msg' => 'imported', 'itemsnumber' => $numberOfElements ), $_SERVER[ 'REQUEST_URI' ] ), 303 ) );
		exit;
	}

	/**
	 * Add the prefix before the title on the Glossary Term page
	 * @global type $wp_query
	 * @param string $title
	 * @param type $id
	 * @return string
	 */
	public static function cmtt_glossary_addTitlePrefix( $title = '', $id = null ) {
		global $wp_query;

		if ( $id ) {
			$glossaryItem = get_post( $id );
			if ( $glossaryItem && 'glossary' == $glossaryItem->post_type && $wp_query->is_single && isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == 'glossary' ) {
				$prefix = get_option( 'cmtt_glossaryBeforeTitle' );
				if ( !empty( $prefix ) ) {
					$title = '<span class="cmtt-glossary-item-title-prefix">' . $prefix . '</span>' . $title;
				}
			}
		}

		return $title;
	}

	/**
	 * Add the backlink on the Glossary Term page
	 * @global type $wp_query
	 * @param type $content
	 * @return type
	 */
	public static function cmtt_glossary_addBacklink( $content = '' ) {
		global $wp_query;

		if ( !isset( $wp_query->post ) ) {
			return $content;
		}
		$post	 = $wp_query->post;
		$id		 = $post->ID;

		$disableSynonymsForThisTerm			 = (bool) get_post_meta( $id, '_cmtt_disable_synonyms_for_term', true );
		$disableRelatedArticlesForThisTerm	 = (bool) get_post_meta( $id, '_cmtt_disable_related_articles_for_term', true );

		$onMainQueryOnly = (get_option( 'cmtt_glossaryOnMainQuery' ) == 1 ) ? is_main_query() : TRUE;

		if ( is_single() && get_query_var( 'post_type' ) == 'glossary' && $onMainQueryOnly && 'glossary' == get_post_type() ) {
			$mainPageId		 = CMTT_Glossary_Index::getGlossaryIndexPageId();
			$backlink		 = (get_option( 'cmtt_glossary_addBackLink' ) == 1 && $mainPageId > 0) ? '<a href="' . get_permalink( $mainPageId ) . '" class="cmtt-backlink cmtt-backlink-top">' . CMTT_Pro::__( get_option( 'cmtt_glossary_backLinkText' ) ) . '</a>' : '';
			$backlinkBottom	 = (get_option( 'cmtt_glossary_addBackLinkBottom' ) == 1 && $mainPageId > 0) ? '<a href="' . get_permalink( $mainPageId ) . '" class="cmtt-backlink cmtt-backlink-bottom">' . CMTT_Pro::__( get_option( 'cmtt_glossary_backLinkBottomText' ) ) . '</a>' : '';

			$synonymSnippet	 = (get_option( 'cmtt_glossary_addSynonyms' ) == 1 && !$disableSynonymsForThisTerm) ? CMTT_Synonyms::renderSynonyms( $post->ID ) : '';
			$relatedSnippet	 = (get_option( 'cmtt_glossary_showRelatedArticles' ) == 1 && !$disableRelatedArticlesForThisTerm) ? CMTT_Related::renderRelatedArticles( $post->ID, get_option( 'cmtt_glossary_showRelatedArticlesCount' ), get_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount' ) ) : '';

			$referralSnippet = (get_option( 'cmtt_glossaryReferral' ) == 1 && get_option( 'cmtt_glossaryAffiliateCode' )) ? self::cmtt_getReferralSnippet() : '';

			$contentWithoutBacklink = $content . $synonymSnippet . $relatedSnippet;

			$filteredContent = apply_filters( 'cmtt_add_backlink_content', $contentWithoutBacklink, $post );

			/*
			 * If the filteredContent is not empty - we add a second backlink
			 */
			if ( !empty( $filteredContent ) ) {
				$filteredContent = $filteredContent . $backlinkBottom;
			}

			/*
			 * In the end add the backlink at the beginning and the referral snippet at the end
			 */
			$contentWithBacklink = $backlink . $filteredContent . $referralSnippet;

			$contentWithBacklink = apply_filters( 'cmtt_glossary_term_after_content', $contentWithBacklink );

			return $contentWithBacklink;
		}

		return $content;
	}

	/**
	 * Outputs the Affiliate Referral Snippet
	 * @return type
	 */
	public static function cmtt_getReferralSnippet() {
		ob_start();
		?>
		<span class="glossary_referral_link">
			<a target="_blank" href="https://www.cminds.com/store/tooltipglossary/?af=<?php echo get_option( 'cmtt_glossaryAffiliateCode' ) ?>">
				<img src="https://www.cminds.com/wp-content/uploads/download_tooltip.png" width=122 height=22 alt="Download Tooltip Pro" title="Download Tooltip Pro" />
			</a>
		</span>
		<?php
		$referralSnippet = ob_get_clean();
		return $referralSnippet;
	}

	/**
	 * Attaches the hooks adding the custom buttons to TinyMCE and CKeditor
	 * @return type
	 */
	public static function addRicheditorButtons() {
		/*
		 *  check user permissions
		 */
		if ( !current_user_can( 'manage_glossary' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}

		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( self::$calledClassName, 'cmtt_mcePlugin' ) );
			add_filter( 'mce_buttons', array( self::$calledClassName, 'cmtt_mceButtons' ) );

			add_filter( 'ckeditor_external_plugins', array( self::$calledClassName, 'cmtt_ckeditorPlugin' ) );
			add_filter( 'ckeditor_buttons', array( self::$calledClassName, 'cmtt_ckeditorButtons' ) );
		}
	}

	public static function cmtt_mcePlugin( $plugins ) {
		$plugins					 = (array) $plugins;
		$plugins[ 'cmtt_glossary' ]	 = self::$jsPath . 'editor/glossary-mce.js';
		return $plugins;
	}

	public static function cmtt_mceButtons( $buttons ) {
		array_push( $buttons, '|', 'cmtt_exclude', 'cmtt_parse' );
		return $buttons;
	}

	public static function cmtt_ckeditorPlugin( $plugins ) {
		$plugins					 = (array) $plugins;
		$plugins[ 'cmtt_glossary' ]	 = self::$jsPath . '/editor/ckeditor/plugin.js';
		return $plugins;
	}

	public static function cmtt_ckeditorButtons( $buttons ) {
		array_push( $buttons, 'cmtt_exclude', 'cmtt_parse' );
		return $buttons;
	}

	public static function cmtt_warn_on_upgrade() {
		?>
		<div style="margin-top: 1em"><span style="color: red; font-size: larger">STOP!</span> Do <em>not</em> click &quot;update automatically&quot; as you will be <em>downgraded</em> to the free version of Tooltip Glossary. Instead, download the Pro update directly from <a href="http://www.cminds.com/downloads/cm-enhanced-tooltip-glossary-premium-version/">http://www.cminds.com/downloads/cm-enhanced-tooltip-glossary-premium-version/</a>.</div>
		<div style="font-size: smaller">Tooltip Glossary Pro does not use WordPress's standard update mechanism. We apologize for the inconvenience!</div>
		<?php
	}

	/**
	 * Registers the metaboxes
	 */
	public static function cmtt_RegisterBoxes() {
		add_meta_box( 'glossary-exclude-box', 'CM Tooltip - Term Properties', array( self::$calledClassName, 'cmtt_render_my_meta_box' ), 'glossary', 'side', 'high' );

		$defaultPostTypes	 = get_option( 'cmtt_disable_metabox_all_post_types' ) ? get_post_types() : array( 'glossary', 'post', 'page' );
		$disableBoxPostTypes = apply_filters( 'cmtt_disable_metabox_posttypes', $defaultPostTypes );
		foreach ( $disableBoxPostTypes as $postType ) {
			add_meta_box( 'glossary-disable-box', 'CM Tooltip - Disables', array( self::$calledClassName, 'cmtt_render_disable_for_page' ), $postType, 'side', 'high' );
		}

		do_action( 'cmtt_register_boxes' );
	}

	public static function cmtt_render_disable_for_page( $post ) {
		$dTTpage				 = get_post_meta( $post->ID, '_glossary_disable_tooltip_for_page', true );
		$disableTooltipForPage	 = (int) (!empty( $dTTpage ) && $dTTpage == 1 );

		$dLpage				 = get_post_meta( $post->ID, '_glossary_disable_links_for_page', true );
		$disableLinkForPage	 = (int) (!empty( $dLpage ) && $dLpage == 1 );

		$dpage					 = get_post_meta( $post->ID, '_glossary_disable_for_page', true );
		$disableParsingForPage	 = (int) (!empty( $dpage ) && $dpage == 1 );

		echo '<div class="cmtt_disable_tooltip_for_page_field">';
		echo '<label for="glossary_disable_tooltip_for_page" class="blocklabel">';
		echo '<input type="checkbox" name="glossary_disable_tooltip_for_page" id="glossary_disable_tooltip_for_page" value="1" ' . checked( 1, $disableTooltipForPage, false ) . '>';
		echo '&nbsp;&nbsp;&nbsp;Don\'t show the Tooltips on this post/page</label>';
		echo '</div>';

		echo '<div class="cmtt_disable_link_for_page_field">';
		echo '<label for="glossary_disable_link_for_page" class="blocklabel">';
		echo '<input type="checkbox" name="glossary_disable_link_for_page" id="glossary_disable_link_for_page" value="1" ' . checked( 1, $disableLinkForPage, false ) . '>';
		echo '&nbsp;&nbsp;&nbsp;Don\'t show links to glossary terms on this post/page</label>';
		echo '</div>';

		echo '<div class="cmtt_disable_for_page_field">';
		echo '<label for="glossary_disable_for_page" class="blocklabel">';
		echo '<input type="checkbox" name="glossary_disable_for_page" id="glossary_disable_for_page" value="1" ' . checked( 1, $disableParsingForPage, false ) . '>';
		echo '&nbsp;&nbsp;&nbsp;Don\'t search for glossary items on this post/page</label>';
		echo '</div>';

		do_action( 'cmtt_add_disables_metabox', $post );
	}

	public static function cmtt_glossary_meta_box_fields() {
		$metaBoxFields = apply_filters( 'cmtt_add_properties_metabox', array() );
		return $metaBoxFields;
	}

	public static function cmtt_render_my_meta_box( $post ) {
		$result = array();

		foreach ( self::cmtt_glossary_meta_box_fields() as $key => $value ) {
			$optionContent	 = '<div><label for="' . $key . '" class="blocklabel">';
			$fieldValue		 = get_post_meta( $post->ID, '_' . $key, true );

			if ( is_string( $value ) ) {
				$optionContent .= '<input type="checkbox" name="' . $key . '" id="' . $key . '" value="1" ' . ((bool) $fieldValue ? ' checked ' : '') . '>';
				$optionContent .= '&nbsp;&nbsp;&nbsp;' . $value . '</label></div>';
			} elseif ( is_array( $value ) ) {
				$label	 = isset( $value[ 'label' ] ) ? $value[ 'label' ] : CMTT_Pro::__( 'No label' );
				$options = isset( $value[ 'options' ] ) ? $value[ 'options' ] : array( '' => CMTT_Pro::__( '-no options-' ) );
				$optionContent .= '<select name="' . $key . '" id="' . $key . '">';
				foreach ( $options as $optionKey => $optionLabel ) {
					$optionContent .= '<option value="' . $optionKey . '" ' . selected( $optionKey, $fieldValue, false ) . '>' . $optionLabel . '</option>';
				}

				$optionContent .= '</select>';
				$optionContent .= '&nbsp;&nbsp;&nbsp;' . $label . '</label></div>';
			}

			$result[] = $optionContent;
		}

		$result = apply_filters( 'cmtt_edit_properties_metabox_array', $result );

		echo implode( '', $result );
	}

	public static function cmtt_save_postdata( $post_id ) {
		$post		 = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$postType	 = isset( $post[ 'post_type' ] ) ? $post[ 'post_type' ] : '';

		do_action( 'cmtt_on_glossary_item_save_before', $post_id, $post );

		$disableBoxPostTypes = apply_filters( 'cmtt_disable_metabox_posttypes', array( 'glossary', 'post', 'page' ) );
		if ( in_array( $postType, $disableBoxPostTypes ) ) {
			/*
			 * Disables the parsing of the given page
			 */
			$disableParsingForPage = 0;
			if ( isset( $post[ "glossary_disable_for_page" ] ) && $post[ "glossary_disable_for_page" ] == 1 ) {
				$disableParsingForPage = 1;
			}
			update_post_meta( $post_id, '_glossary_disable_for_page', $disableParsingForPage );

			/*
			 * Disables the showing of tooltip on given page
			 */
			$disableTooltipForPage = 0;
			if ( isset( $post[ "glossary_disable_tooltip_for_page" ] ) && $post[ "glossary_disable_tooltip_for_page" ] == 1 ) {
				$disableTooltipForPage = 1;
			}
			update_post_meta( $post_id, '_glossary_disable_tooltip_for_page', $disableTooltipForPage );

			/*
			 * Disables the showing of links to tooltip pages on given page
			 */
			$disableLinksForPage = 0;
			if ( isset( $post[ "glossary_disable_link_for_page" ] ) && $post[ "glossary_disable_link_for_page" ] == 1 ) {
				$disableLinksForPage = 1;
			}
			update_post_meta( $post_id, '_glossary_disable_links_for_page', $disableLinksForPage );
		}

		if ( 'glossary' != $postType ) {
			return;
		}

		do_action( 'cmtt_on_glossary_item_save', $post_id, $post );

		/*
		 * Invalidate the list of all glossary items stored in cache
		 */
		delete_transient( CMTT_TRANSIENT_ALL_ITEMS_KEY );

		/*
		 * Part for "glossary" items only starts here
		 */
		foreach ( array_keys( self::cmtt_glossary_meta_box_fields() ) as $value ) {
			$exclude_value = (isset( $post[ $value ] )) ? $post[ $value ] : 0;
			update_post_meta( $post_id, '_' . $value, $exclude_value );
		}
	}

	/**
	 * Allows to choose which parser should be used
	 * @param type $html
	 * @param type $glossarySearchString
	 * @return type
	 */
	public static function cmtt_str_replace( $html, $glossarySearchString ) {
		$filter							 = current_filter();
		$parseWithSimpleFunctionFilters	 = apply_filters( 'cmtt_parse_with_simple_function', array() );

		$runThroughSimpleFunction = in_array( $filter, $parseWithSimpleFunctionFilters );

		if ( $runThroughSimpleFunction ) {
			return self::cmtt_simple_str_replace( $html, $glossarySearchString );
		} else {
			return self::cmtt_dom_str_replace( $html, $glossarySearchString );
		}
	}

	/**
	 * Setups the filters which should use the simple parsing instead of DOM parser
	 * @param type $html
	 * @param type $glossarySearchString
	 * @return type
	 */
	public static function allowSimpleParsing( $simpleParsingList ) {
		if ( get_option( 'cmtt_disableDOMParserForACF', FALSE ) ) {
			$simpleParsingList[] = 'acf/load_value';
		}
		return $simpleParsingList;
	}

	public static function outputGlossaryExcludeStart() {
		echo '[glossary_exclude]';
	}

	public static function outputGlossaryExcludeEnd() {
		echo '[/glossary_exclude]';
	}

	public static function removeGlossaryExclude($content) {
		$content = str_replace( array( '[glossary_exclude]', '[/glossary_exclude]' ), array( '', '' ), $content );
		return $content;
	}

	/**
	 * New function to search the terms in the content
	 *
	 * @param strin $html
	 * @param string $glossarySearchString
	 * @since 2.3.1
	 * @return type
	 */
	public static function cmtt_dom_str_replace( $html, $glossarySearchString ) {
		global $cmWrapItUp;

		if ( !empty( $html ) && is_string( $html ) ) {
			if ( $cmWrapItUp ) {
				$html = '<span>' . $html . '</span>';
			}
			$dom = new DOMDocument();
			/*
			 * loadXml needs properly formatted documents, so it's better to use loadHtml, but it needs a hack to properly handle UTF-8 encoding
			 */
			libxml_use_internal_errors( true );
			if ( !$dom->loadHtml( mb_convert_encoding( $html, 'HTML-ENTITIES', "UTF-8" ) ) ) {
				libxml_clear_errors();
			}
			$xpath = new DOMXPath( $dom );

			/*
			 * Base query NEVER parse in scripts
			 */
			$query = '//text()[not(ancestor::script)][not(ancestor::style)]';
			if ( get_option( 'cmtt_glossaryProtectedTags' ) == 1 ) {
				$query .= '[not(ancestor::header)][not(ancestor::a)][not(ancestor::pre)][not(ancestor::object)][not(ancestor::h1)][not(ancestor::h2)][not(ancestor::h3)][not(ancestor::h4)][not(ancestor::h5)][not(ancestor::h6)][not(ancestor::textarea)]';
			}
			/*
			 * Parsing of the Glossary Index Page
			 */
			if ( get_option( 'cmtt_glossary_index_dont_parse', 1 ) == 1 ) {
				$query .= '[not(ancestor::div[@class=\'cm-tooltip\'])]';
			}
			/*
			 * Parsing of the already-parsed items
			 */
			$query .= '[not(ancestor::span[contains(concat(\' \', @class, \' \'), \' glossaryLink \')])]';

			/*
			 * Parsing of the already-parsed items
			 */
			$query .= '[not(ancestor::a[contains(concat(\' \', @class, \' \'), \' glossaryLink \')])]';

			/*
			 * Parsing of the wistia videos
			 */
			$query .= '[not(ancestor::div[contains(concat(\' \', @class, \' \'), \' avia_codeblock \')])]';

			foreach ( $xpath->query( apply_filters( 'cmtt_glossary_xpath_query', $query ) ) as $node ) {
				/* @var $node DOMText */
				$replaced = preg_replace_callback( $glossarySearchString, array( self::$calledClassName, 'cmtt_replace_matches' ), htmlspecialchars( $node->wholeText, ENT_COMPAT ) );
				if ( !empty( $replaced ) ) {
					$newNode			 = $dom->createDocumentFragment();
					$replacedShortcodes	 = strip_shortcodes( $replaced );
					$result				 = $newNode->appendXML( '<![CDATA[' . $replacedShortcodes . ']]>' );

					if ( $result !== false ) {
						$node->parentNode->replaceChild( $newNode, $node );
					}
				}
			}

			do_action( 'cmtt_xpath_main_query_after', $xpath, $glossarySearchString, $dom );

			/*
			 *  get only the body tag with its contents, then trim the body tag itself to get only the original content
			 */
			$bodyNode = $xpath->query( '//body' )->item( 0 );

			if ( $bodyNode !== NULL ) {
				$newDom = new DOMDocument();
				$newDom->appendChild( $newDom->importNode( $bodyNode, TRUE ) );

				$intermalHtml	 = $newDom->saveHTML();
				$html			 = mb_substr( trim( $intermalHtml ), 6, (mb_strlen( $intermalHtml ) - 14 ), "UTF-8" );
				/*
				 * Fixing the self-closing which is lost due to a bug in DOMDocument->saveHtml() (caused a conflict with NextGen)
				 */
				$html			 = preg_replace( '#(<img[^>]*[^/])>#Ui', '$1/>', $html );
			}
		}

		if ( $cmWrapItUp ) {
			$html = mb_substr( trim( $html ), 6, (mb_strlen( $html ) - 13 ), "UTF-8" );
		}

		return $html;
	}

	/**
	 * Simple function to search the terms in the content
	 *
	 * @param strin $html
	 * @param string $glossarySearchString
	 * @since 2.3.1
	 * @return type
	 */
	public static function cmtt_simple_str_replace( $html, $glossarySearchString ) {
		if ( !empty( $html ) && is_string( $html ) ) {
			$replaced	 = preg_replace_callback( $glossarySearchString, array( self::$calledClassName, 'cmtt_replace_matches' ), $html );
			$html		 = $replaced;
		}

		return $html;
	}

	/**
	 * BuddyPress record custom post type comments
	 * @param array $post_types
	 * @return string
	 */
	public static function cmtt_bp_record_my_custom_post_type_comments( $post_types ) {
		$post_types[] = 'glossary';
		return $post_types;
	}

	/**
	 * Adds the support for the custom tooltips
	 * [glossary_tooltip content="text"]term[/glossary_tooltip]
	 */
	public static function cmtt_custom_tooltip_shortcode( $atts, $text = '' ) {
		$content = CMTT_Pro::__( 'Use the &quot;content&quot; attribute on the shortcode to change this text' );
		extract( shortcode_atts( array( 'content' => $content ), $atts ) );

		$tooltip = '<span data-tooltip="' . $content . '" class="glossaryLink">' . $text . '</span>';
		return $tooltip;
	}

	public static function outputLabelsSettings() {
		$view	 = CMTT_PLUGIN_DIR . '/views/backend/settings_labels.phtml';
		ob_start();
		include $view;
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Function renders (default) or returns the setttings tabs
	 *
	 * @param type $return
	 * @return string
	 */
	public static function renderSettingsTabs( $return = false ) {
		$content				 = '';
		$settingsTabsArrayBase	 = array( '50' => 'Labels' );

		$settingsTabsArray = apply_filters( 'cmtt-settings-tabs-array', $settingsTabsArrayBase );

		if ( $settingsTabsArray ) {
			foreach ( $settingsTabsArray as $tabKey => $tabLabel ) {
				$filterName = 'cmtt-custom-settings-tab-content-' . $tabKey;

				$content .= '<div id="tabs-' . $tabKey . '">';
				$tabContent = apply_filters( $filterName, '' );
				$content .= $tabContent;
				$content .= '</div>';
			}
		}

		if ( $return ) {
			return $content;
		}
		echo $content;
	}

	/**
	 * Function renders (default) or returns the setttings tabs
	 *
	 * @param type $return
	 * @return string
	 */
	public static function renderSettingsTabsControls( $return = false ) {
		$content				 = '';
		$settingsTabsArrayBase	 = array(
			'1'	 => 'General Settings',
			'2'	 => 'Glossary Index Page',
			'3'	 => 'Glossary Term',
			'4'	 => 'Tooltip',
			'50' => 'Labels',
			'99' => 'Server Information',
		);

		$settingsTabsArray = apply_filters( 'cmtt-settings-tabs-array', $settingsTabsArrayBase );

		ksort( $settingsTabsArray );

		if ( $settingsTabsArray ) {
			$content .= '<ul>';
			foreach ( $settingsTabsArray as $tabKey => $tabLabel ) {
				$content .= '<li><a href="#tabs-' . $tabKey . '">' . $tabLabel . '</a></li>';
			}
			$content .= '</ul>';
		}

		if ( $return ) {
			return $content;
		}
		echo $content;
	}

	/**
	 * Returns the list of sorted glossary items
	 * @staticvar array $glossary_index_full_sorted
	 * @param type $args
	 * @return type
	 */
	public static function getGlossaryItemsSorted( $args = array() ) {
		static $glossary_index_full_sorted = array();

		if ( $glossary_index_full_sorted === array() ) {
			$glossary_index				 = self::getGlossaryItems( $args );
			$glossary_index_full_sorted	 = $glossary_index;
			uasort( $glossary_index_full_sorted, array( self::$calledClassName, '_sortByWPQueryObjectTitleLength' ) );
		}

		return $glossary_index_full_sorted;
	}

	/**
	 * Returns the cachable array of all Glossary Terms, either sorted by title, or by title length
	 *
	 * @staticvar array $glossary_index
	 * @staticvar array $glossary_index_sorted
	 * @param type $args
	 * @return type
	 */
	public static function getGlossaryItems( $args = array() ) {
		static $glossary_index_cache = array();

		$glossaryItems	 = array();
		$glossary_index	 = array();

		$encodedArgs = json_encode( $args );
		$argsKey	 = 'cmtt_' . md5( 'args' . $encodedArgs );

		if ( !isset( $glossary_index_cache[ $argsKey ] ) ) {
			if ( !get_option( 'cmtt_glossaryEnableCaching', TRUE ) ) {
				delete_transient( $argsKey );
			}
			if ( false === ($glossaryItems = get_transient( $argsKey ) ) ) {
				$defaultArgs = array(
					'post_type'				 => 'glossary',
					'post_status'			 => 'publish',
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'suppress_filters'		 => false,
				);

				$queryArgs = array_merge( $defaultArgs, $args );

				$nopaging_args					 = $queryArgs;
				$nopaging_args[ 'nopaging' ]	 = true;
				$nopaging_args[ 'numberposts' ]	 = -1;

				if ( $args === array() ) {
					$queryArgs = $nopaging_args;
				}

				$query			 = new WP_Query;
				$glossaryIndex	 = $query->query( $queryArgs );

				foreach ( $glossaryIndex as $post ) {
					$obj				 = new stdClass();
					$obj->ID			 = $post->ID;
					$obj->post_title	 = $post->post_title;
					$obj->post_content	 = $post->post_content;
					$obj->post_excerpt	 = $post->post_excerpt;
					$obj->post_date		 = $post->post_date;

					$newObj				 = apply_filters( 'cmtt_get_all_glossary_items_single', $obj, $post );
					$glossary_index[]	 = $newObj;
				}

				$glossaryItems[ 'index' ]			 = $glossary_index;
				$glossaryItems[ 'query' ]			 = $query;
				$glossaryItems[ 'args' ]			 = $queryArgs;
				$glossaryItems[ 'nopaging_args' ]	 = $nopaging_args;

				if ( get_option( 'cmtt_glossaryEnableCaching', TRUE ) ) {
					set_transient( $argsKey, $glossaryItems, 5 * MINUTE_IN_SECONDS );
				}
			}

			$glossary_index			 = $glossaryItems[ 'index' ];
			/*
			 * Save statically
			 */
			self::$lastQueryDetails	 = $glossaryItems;
		}

		return $glossary_index;
	}

	public static function outputCustomPostTypesList() {
		$content = '';
		$args	 = array(
			'public' => true,
//            '_builtin' => false
		);

		$output		 = 'objects'; // names or objects, note names is the default
		$operator	 = 'and'; // 'and' or 'or'

		$post_types			 = get_post_types( $args, $output, $operator );
		$selected_post_types = get_option( 'cmtt_glossaryOnPosttypes' );

		if ( !is_array( $selected_post_types ) ) {
			$selected_post_types = array();
		}

		foreach ( $post_types as $post_type ) {
			$label	 = $post_type->labels->singular_name . ' (' . $post_type->name . ')';
			$name	 = $post_type->name;

			$content .= '<div><label><input type="checkbox" name="cmtt_glossaryOnPosttypes[]" ' . checked( true, in_array( $name, $selected_post_types ), false ) . ' value="' . $name . '" />' . $label . '</label></div>';
		}
		return $content;
	}

	public static function outputRolesList() {
		$content = '';

		$roles			 = get_editable_roles();
		$selected_roles	 = get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );

		if ( !is_array( $selected_roles ) ) {
			$selected_roles = array();
		}

		foreach ( $roles as $role => $role_info ) {
			$label	 = $role . ' (' . $role_info[ 'name' ] . ')';
			$name	 = $role;

			$content .= '<div><label><input type="checkbox" name="cmtt_glossaryRoles[]" ' . checked( true, in_array( $name, $selected_roles ), false ) . ' value="' . $name . '" />' . $label . '</label></div>';
		}
		return $content;
	}

	public static function flushCaps( $post, $messages ) {
		$oldRoles	 = get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );
		$newRoles	 = $post[ 'cmtt_glossaryRoles' ];
		if ( $oldRoles != $newRoles ) {
			self::_add_caps( $newRoles );
			self::$messages = CMTT_Pro::__( 'New Role assignment has been saved!' );
		}
	}

	/*
	 *  Sort longer titles first, so if there is collision between terms
	 * (e.g., "essential fatty acid" and "fatty acid") the longer one gets created first.
	 */

	public static function _sortByWPQueryObjectTitleLength( $a, $b ) {
		$sortVal = 0;
		if ( property_exists( $a, 'post_title' ) && property_exists( $b, 'post_title' ) ) {
			$sortVal = strlen( $b->post_title ) - strlen( $a->post_title );
		}
		return $sortVal;
	}

	/**
	 * Function cleans up the plugin, removing the terms, resetting the options etc.
	 *
	 * @return string
	 */
	protected static function _cleanupOptions( $force = true ) {
		/*
		 * Remove the data from the other tables
		 */
		do_action( 'cmtt_do_cleanup' );

		$glossaryIndexPageId = CMTT_Glossary_Index::getGlossaryIndexPageId();
		if ( !empty( $glossaryIndexPageId ) ) {
			wp_delete_post( $glossaryIndexPageId );
		}

		/*
		 * Remove the options
		 */
		$optionNames = wp_load_alloptions();

		function cmtt_get_the_option_names( $k ) {
			return strpos( $k, 'cmtt_' ) === 0;
		}

		$options_names = array_filter( array_keys( $optionNames ), 'cmtt_get_the_option_names' );
		foreach ( $options_names as $optionName ) {
			delete_option( $optionName );
		}
	}

	/**
	 * Function cleans up the plugin, removing the terms, resetting the options etc.
	 *
	 * @return string
	 */
	protected static function _cleanupItems( $force = true ) {

		do_action( 'cmtt_do_cleanup_items_before' );

		$glossary_index = self::getGlossaryItems();

		/*
		 * Remove the glossary terms
		 */
		foreach ( $glossary_index as $post ) {
			wp_delete_post( $post->ID, $force );
		}

		/*
		 * Invalidate the list of all glossary items stored in cache
		 */
		delete_transient( CMTT_TRANSIENT_ALL_ITEMS_KEY );
		do_action( 'cmtt_do_cleanup_items_after' );
	}

	/**
	 * Plugin activation
	 */
	protected static function _activate() {
		CMTT_Glossary_Index::tryGenerateGlossaryIndexPage();
		do_action( 'cmtt_do_activate' );
	}

	/**
	 * Plugin installation
	 *
	 * @global type $wpdb
	 * @param type $networkwide
	 * @return type
	 */
	public static function _install( $networkwide ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ( $networkwide ) {
				$old_blog	 = $wpdb->blogid;
				// Get all blog ids
				$blogids	 = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs}" ) );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::_activate();
					self::_add_caps();
				}
				switch_to_blog( $old_blog );
				return;
			}
		}

		self::_activate();
		self::_add_caps();
	}

	/**
	 * Flushes the caps for the roles
	 *
	 * @global type $wp_rewrite
	 */
	public static function _add_caps( $roles = array() ) {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( !isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		/*
		 * First reset the caps
		 */
		$allRoles = get_editable_roles();
		foreach ( $allRoles as $role => $role_info ) {
			$wp_roles->remove_cap( $role, 'manage_glossary' );
		}

		$roles = !empty( $roles ) ? $roles : get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );

		if ( is_object( $wp_roles ) ) {

			foreach ( $roles as $role ) {
				$wp_roles->add_cap( $role, 'manage_glossary' );
			}
		}
	}

	/**
	 * Flushes the rewrite rules to reflect the permalink changes automatically (if any)
	 *
	 * @global type $wp_rewrite
	 */
	public static function _flush_rewrite_rules() {
		global $wp_rewrite;
		// First, we "add" the custom post type via the above written function.

		self::cmtt_create_post_types();

		do_action( 'cmtt_flush_rewrite_rules' );

		// Clear the permalinks
		flush_rewrite_rules();

		//Call flush_rules() as a method of the $wp_rewrite object
		$wp_rewrite->flush_rules();
	}

	/**
	 * Scoped i18n function
	 * @param type $message
	 * @return type
	 */
	public static function __( $message ) {
		return __( $message, CMTT_SLUG_NAME );
	}

	/**
	 * Scoped i18n function
	 * @param type $message
	 * @return type
	 */
	public static function _e( $message ) {
		return _e( $message, CMTT_SLUG_NAME );
	}

}
