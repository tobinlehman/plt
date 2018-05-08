<?php

class CMTT_Glossary_Index {

	protected static $filePath	 = '';
	protected static $cssPath	 = '';
	protected static $jsPath	 = '';

	/**
	 * Adds the hooks
	 */
	public static function init() {
		self::$filePath	 = plugin_dir_url( __FILE__ );
		self::$cssPath	 = self::$filePath . 'assets/css/';
		self::$jsPath	 = self::$filePath . 'assets/js/';

		/*
		 * ACTIONS
		 */
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'addScripts' ) );

		/*
		 * FILTERS
		 */

		/*
		 * Glossary Index Tooltip Content
		 */
		add_filter( 'cmtt_glossary_index_tooltip_content', array( __CLASS__, 'getTheTooltipContentBase' ), 10, 2 );
		add_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_parse_strip_shortcodes' ), 20, 2 );
		add_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_filterTooltipContent' ), 30, 2 );

		add_filter( 'cmtt_glossary_index_remove_links_to_terms', array( __CLASS__, 'removeLinksToTerms' ), 10, 2 );
		add_filter( 'cmtt_glossary_index_disable_tooltips', array( __CLASS__, 'disableTooltips' ), 10, 2 );

		add_filter( 'cmtt_glossary_index_pagination', array( __CLASS__, 'outputPagination' ), 10, 3 );

		add_filter( 'cmtt_glossary_index_listnav_content', array( __CLASS__, 'removeListnav' ) );

		add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'wrapInMainContainer' ), 1, 2 );
		if ( get_option( 'cmtt_glossaryShowShareBox' ) == 1 ) {
			add_filter( 'cmtt_glossary_index_after_content', array( 'CMTT_Pro', 'cmtt_glossaryAddShareBox' ), 5, 2 );
		}
		add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'wrapInStyleContainer' ), 10, 2 );
		add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'addReferalSnippet' ), 50 );

		add_filter( 'cmtt_glossary_index_shortcode_default_atts', array( __CLASS__, 'setupDefaultGlossaryIndexAtts' ), 5 );

		add_filter( 'cmtt_tooltip_script_data', array( __CLASS__, 'tooltipsDisabledForPage' ), 50000 );

		/*
		 * SHORTCODES
		 */
		add_shortcode( 'glossary', array( __CLASS__, 'glossaryShortcode' ) );
		add_shortcode( 'glossary_search', array( __CLASS__, 'glossarySearchShortcode' ) );
	}

	/**
	 * Returns true if the server-side pagination is enabled
	 * @return type
	 */
	public static function setupDefaultGlossaryIndexAtts( $baseAtts ) {
		$defaultAtts[ 'pagination_position' ]	 = get_option( 'cmtt_glossaryPaginationPosition', 'bottom' );
		$atts									 = array_merge( $baseAtts, $defaultAtts );
		return $atts;
	}

	/**
	 * Returns true if the server-side pagination is enabled
	 * @return type
	 */
	public static function isServerSide() {
		return (bool) apply_filters( 'cmtt_is_serverside_pagination', get_option( 'cmtt_glossaryServerSidePagination' ) == 1 );
	}

	/**
	 * Function serves the shortcode: [glossary]
	 */
	public static function glossaryShortcode( $atts = array() ) {
		global $post;

		if ( !is_array( $atts ) ) {
			$atts = array();
		}

		if ( $post !== null ) {
			$glossaryPageLink = get_page_link( $post );
		} elseif ( !empty( $atts[ 'post_id' ] ) ) {
			$glossaryPageLink = get_permalink( $atts[ 'post_id' ] );
		} else {
			$glossaryPageLink = get_permalink( self::getGlossaryIndexPageId() );
		}

		$default_atts	 = apply_filters( 'cmtt_glossary_index_shortcode_default_atts', array(
			'glossary_page_link' => $glossaryPageLink,
			'itemspage'			 => filter_input( INPUT_GET, 'itemspage' )
		)
		);
		$shortcode_atts	 = apply_filters( 'cmtt_glossary_index_atts', array_merge( $default_atts, $atts ) );

		do_action( 'cmtt_glossary_shortcode_before', $shortcode_atts );

		$output = self::outputGlossaryIndexPage( $shortcode_atts );

		do_action( 'cmtt_glossary_shortcode_after', $atts );

		return $output;
	}

	/**
	 * Function serves the shortcode: [glossary_search]
	 */
	public static function glossarySearchShortcode( $atts = array() ) {
		global $post;

		if ( !is_array( $atts ) ) {
			$atts = array();
		}

		$default_atts = apply_filters( 'cmtt_glossary_search_shortcode_default_atts', array(
			'glossary_page_link' => get_permalink( self::getGlossaryIndexPageId() ),
		)
		);

		$shortcode_atts	 = apply_filters( 'cmtt_glossary_search_atts', array_merge( $default_atts, $atts ) );
		do_action( 'cmtt_glossary_search_shortcode_before', $shortcode_atts );
		$output			 = self::outputSearch( $shortcode_atts );
		do_action( 'cmtt_glossary_search_shortcode_after', $atts );

		return $output;
	}

	/**
	 * Displays the main glossary index
	 *
	 * @param type $shortcodeAtts
	 * @return string $content
	 */
	public static function outputSearch( $shortcodeAtts ) {
		global $post;

		$content = '';

		if ( $post === NULL && $shortcodeAtts[ 'post_id' ] ) {
			$post = get_post( $shortcodeAtts[ 'post_id' ] );
		}

		$content .= apply_filters( 'cmtt_glossary_search_before_content', '', $shortcodeAtts );
		$content .= '<form method="post" action="' . esc_attr( $shortcodeAtts[ 'glossary_page_link' ] ) . '">';

		$additionalClass = (!empty( $shortcodeAtts[ 'search_term' ] )) ? 'search' : '';

		$searchLabel		 = CMTT_Pro::__( get_option( 'cmtt_glossary_SearchLabel', 'Search:' ) );
		$searchButtonLabel	 = CMTT_Pro::__( get_option( 'cmtt_glossary_SearchButtonLabel', 'Search' ) );
		$searchTerm			 = isset( $shortcodeAtts[ 'search_term' ] ) ? $shortcodeAtts[ 'search_term' ] : '';
		$searchHelp			 = CMTT_Pro::__( get_option( 'cmtt_glossarySearchHelp', 'The search returns the partial search for the given query from both the term title and description. So it will return the results even if the given query is part of the word in the description.' ) );
		ob_start();
		?>
		<div class="cmtt_help" data-tooltip="<?php echo $searchHelp ?>"></div><?php echo $searchLabel ?>
		<input type="search" value="<?php echo $searchTerm ?>" class="glossary-search-term <?php echo $additionalClass ?>" name="search_term" id="glossary-search-term" />
		<input type="submit" value="<?php echo $searchButtonLabel ?>" id="glossary-search" class="glossary-search button" />
		<?php
		$content .= ob_get_clean();
		$content .= '</form>';
		$content			 = apply_filters( 'cmtt_glossary_search_after_content', $content, $shortcodeAtts );

		do_action( 'cmtt_after_glossary_search' );

		return $content;
	}

	/**
	 * Function should return the ID of the Glossary Index Page
	 * @since 2.7.4
	 * @return type
	 */
	public static function getGlossaryIndexPageId() {
		$glossaryPageID = apply_filters( 'cmtt_get_glossary_index_page_id', get_option( 'cmtt_glossaryID' ) );
		/*
		 * WPML integration
		 */
		if ( function_exists( 'icl_object_id' ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
			$glossaryPageID = icl_object_id( $glossaryPageID, 'page', ICL_LANGUAGE_CODE );
		}
		return $glossaryPageID;
	}

	/**
	 * Create the actual glossary
	 * @param type $content
	 * @return string
	 */
	public static function lookForShortcode( $content ) {
		$currentPost	 = get_post();
		$glossaryPageID	 = self::getGlossaryIndexPageId();

		$seo = doing_action( 'wpseo_opengraph' );
		if ( $seo ) {
			return $content;
		}

		if ( is_numeric( $glossaryPageID ) && is_page( $glossaryPageID ) && $glossaryPageID > 0 && $currentPost && $currentPost->ID == $glossaryPageID ) {
			if ( !has_shortcode( $currentPost->post_content, 'glossary' ) ) {
				$content = $currentPost->post_content . '[glossary]';
				wp_update_post( array( 'ID' => $glossaryPageID, 'post_content' => $content ) );
			}
		}
		return $content;
	}

	/**
	 * Function tries to generate the new Glossary Index Page
	 */
	public static function tryGenerateGlossaryIndexPage() {
		$glossaryIndexId = self::getGlossaryIndexPageId();
		if ( $glossaryIndexId == -1 && get_post( $glossaryIndexId ) === null ) {
			$id = wp_insert_post( array(
				'post_author'	 => get_current_user_id(),
				'post_status'	 => 'publish',
				'post_title'	 => 'Glossary',
				'post_type'		 => 'page',
				'post_content'	 => '[glossary]'
			) );

			if ( is_numeric( $id ) ) {
				update_option( 'cmtt_glossaryID', $id );
			}
		}
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
	 * Check whether to remove links to term pages from Glossary Index or not
	 * @param type $disable
	 * @param type $post
	 * @return type
	 */
	public static function removeLinksToTerms( $disable, $post ) {
		$linksDisabled		 = get_post_meta( $post->ID, '_glossary_disable_links_for_page', true ) == 1;
		$removeLinksToTerms	 = get_option( 'cmtt_glossaryListTermLink' ) == 1;

		$disable = $linksDisabled || $removeLinksToTerms;

		return $disable;
	}

	/**
	 * Check whether to disable the tooltips on Glossary Index page
	 * @param type $disable
	 * @param type $post
	 * @return type
	 */
	public static function disableTooltips( $disable, $post ) {
		$tooltipsDisabledGlobal	 = get_option( 'cmtt_glossaryTooltip' ) != 1;
		$tooltipsDisabled		 = get_post_meta( $post->ID, '_glossary_disable_tooltip_for_page', true ) == 1;

		$result = $tooltipsDisabled || $tooltipsDisabledGlobal;
		return $result;
	}

	/**
	 * Wrap Glossary Index in styling container
	 * @param type $content
	 * @param type $glossaryIndexStyle
	 * @return type
	 */
	public static function wrapInStyleContainer( $content, $glossaryIndexStyle ) {
		if ( !defined( 'DOING_AJAX' ) ) {
			if ( $glossaryIndexStyle != 'classic' ) {
				$styles = apply_filters( 'cmtt_glossary_index_style_classes', array(
					'small-tiles' => 'tiles'
				) );
				if ( isset( $styles[ $glossaryIndexStyle ] ) ) {
					$class	 = $styles[ $glossaryIndexStyle ];
					$content = '<div class="cm-glossary ' . $class . '">' . $content . '<p class="clear clearfix"></p></div>';
				}
			}
		}
		return $content;
	}

	/**
	 * Wrap Glossary Index in main container
	 * @param type $content
	 * @param type $glossaryIndexStyle
	 * @return type
	 */
	public static function wrapInMainContainer( $content, $glossaryIndexStyle ) {
		if ( !defined( 'DOING_AJAX' ) ) {
			$content = '<div class="glossary-container">' . $content . '</div>';
		}
		return $content;
	}

	/**
	 * Check whether to disable the tooltips on Glossary Index page
	 * @param type $disable
	 * @param type $post
	 * @return type
	 */
	public static function addReferalSnippet( $content ) {
		if ( get_option( 'cmtt_glossaryReferral' ) == 1 && get_option( 'cmtt_glossaryAffiliateCode' ) ) {
			$content .= CMTT_Pro::cmtt_getReferralSnippet();
		}
		return $content;
	}

	/**
	 * Detects the new letter in Glossary Index Page
	 * @staticvar boolean $lastIndexLetter
	 * @param type $glossaryItem
	 * @param type $title
	 * @return boolean
	 */
	public static function detectStartNewIndexLetter( $glossaryItem = null, $title = null ) {
		static $lastIndexLetter = false;

		if ( ($glossaryItem && is_object( $glossaryItem ) && isset( $glossaryItem->post_title )) || ($title && is_string( $title )) ) {
			/*
			 * In case the former parameter only is sent
			 */
			if ( empty( $title ) && !empty( $glossaryItem ) ) {
				$title = $glossaryItem->post_title;
			}

			$newIndexLetter = mb_substr( $title, 0, 1 );

			if ( !(bool) get_option( 'cmtt_index_nonLatinLetters' ) ) {
				$newIndexLetter = remove_accents( $newIndexLetter );
			}

			if ( mb_strtolower( $newIndexLetter ) !== $lastIndexLetter ) {
				$lastIndexLetter = mb_strtolower( $newIndexLetter );
				return $lastIndexLetter;
			}
		}

		return false;
	}

	/**
	 * Removes the ListNav when there's server side pagination
	 * @param type $content
	 * @return string
	 */
	public static function removeListnav( $content ) {
		if ( self::isServerSide() ) {
			$content = '';
		}
		return $content;
	}

	/**
	 * Displays the main glossary index
	 *
	 * @param type $shortcodeAtts
	 * @return string $content
	 */
	public static function outputGlossaryIndexPage( $shortcodeAtts ) {
		global $post;

		$content = '';

		$glossaryIndexContentArr = array();

		if ( $post === NULL && $shortcodeAtts[ 'post_id' ] ) {
			$post = get_post( $shortcodeAtts[ 'post_id' ] );
		}

		/*
		 *  Checks whether to show tooltips on main glossary page or not
		 */
		$tooltipsDisabled = apply_filters( 'cmtt_glossary_index_disable_tooltips', FALSE, $post );

		/*
		 *  Checks whether to show links to glossary pages or not
		 */
		$removeLinksToTerms = apply_filters( 'cmtt_glossary_index_remove_links_to_terms', FALSE, $post );

		/*
		 * Whether the terms should be hidden
		 */
		$hideTerms = !empty( $shortcodeAtts[ 'hide_terms' ] );

		/*
		 * Set the display style of Glossary Index Page
		 */
		$glossaryIndexStyle = apply_filters( 'cmtt_glossary_index_style', get_option( 'cmtt_glossaryListTiles' ) == '1' ? 'small-tiles' : 'classic'  );

		/*
		 * Get the pagination position
		 */
		$paginationPosition = $shortcodeAtts[ 'pagination_position' ];

		$args = array(
			'post_type'				 => 'glossary',
			'post_status'			 => 'publish',
			'orderby'				 => 'title',
			'order'					 => 'ASC',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'		 => false
		);

		if ( self::isServerSide() ) {
			$args[ 'posts_per_page' ] = get_option( 'cmtt_perPage' );

			/*
			 * Turn off the pagination if terms are hidden, so we can fill the list with synonyms and abbreviations
			 */
			if ( $args[ 'posts_per_page' ] != 0 && !$hideTerms ) {
				$currentPage = isset( $shortcodeAtts[ 'itemspage' ] ) ? $shortcodeAtts[ 'itemspage' ] : 1;
				if ( $currentPage < 1 ) {
					$currentPage = 1;
				}
				$args[ 'paged' ] = $currentPage;
			} else {
				$args[ 'nopaging' ] = true;
			}
		} else {
			$args[ 'nopaging' ] = true;
		}

		$args = apply_filters( 'cmtt_glossary_index_query_args', $args, $shortcodeAtts );
		do_action( 'cmtt_glossary_index_query_before', $args, $shortcodeAtts );

		$glossary_index	 = CMTT_Pro::getGlossaryItems( $args );
		$glossary_query	 = CMTT_Pro::$lastQueryDetails[ 'query' ];

		do_action( 'cmtt_glossary_index_query_after', $glossary_query, $args );

		/*
		 * Size of the Glossary Index Letters (defaults to 'small')
		 */
		$letterSize			 = get_option( 'cmtt_indexLettersSize' );
		$glossary_list_id	 = apply_filters( 'cmtt_glossary_index_list_id', 'glossaryList' );
		/*
		 * Style links based on option
		 */
		$glossary_list_class = apply_filters( 'cmtt_glossary_index_list_class', (get_option( 'cmtt_glossaryDiffLinkClass' ) == 1) ? 'glossaryLinkMain' : 'glossaryLink'  );

		$content .= apply_filters( 'cmtt_glossary_index_before_listnav_content', '', $shortcodeAtts, $glossary_query );

		$listnavContent = '<div id="' . $glossary_list_id . '-nav" class="listNav ' . $letterSize . '">';
		$listnavContent .= apply_filters( 'cmtt_glossary_index_listnav_content_inside', '', $shortcodeAtts, $glossary_query );
		$listnavContent .= '</div>';

		$content .= apply_filters( 'cmtt_glossary_index_listnav_content', $listnavContent );

		if ( self::isServerSide() && !isset( $args[ 'nopaging' ] ) && in_array( $paginationPosition, array( 'top', 'both' ) ) ) {
			$content .= apply_filters( 'cmtt_glossary_index_pagination', '', $glossary_query, $shortcodeAtts );
		}

		if ( $glossary_index ) {
			foreach ( $glossary_index as $glossaryItem ) {
				/*
				 *  Check if need to add description/excerpt on tooltip index
				 */
				$glossaryItemDesc	 = (get_option( 'cmtt_glossaryTooltipDesc' ) == 1) ? '<div class="glossary_itemdesc">' . strip_tags( $glossaryItem->post_content ) . '</div>' : '';
				$glossaryItemDesc	 = apply_filters( 'cmtt_glossary_index_item_desc', $glossaryItemDesc, $glossaryItem, $glossaryIndexStyle, $shortcodeAtts );

				$permalink = apply_filters( 'cmtt_term_tooltip_permalink', get_permalink( $glossaryItem->ID ), $glossaryItem );

				if ( $removeLinksToTerms ) {
					$href	 = '';
					$tag	 = 'span';
				} else {
					$tag	 = 'a';
					$href	 = 'href="' . $permalink . '"';
				}

				$letterSeparatorContent	 = '';
				$preItemTitleContent	 = '';
				$postItemTitleContent	 = '';

				$liAdditionalClass	 = '';
				$thumbnail			 = '';

				if ( get_option( 'cmtt_showFeaturedImageThumbnail', FALSE ) && in_array( $glossaryIndexStyle, array( 'classic-excerpt', 'classic-description' ) ) ) {
					$thumbnail = get_the_post_thumbnail( $glossaryItem->ID, array( 50, 50 ), array( 'style' => 'margin:1px 5px' ) );
					if ( !empty( $thumbnail ) ) {
						$liAdditionalClass = 'cmtt-has-thumbnail';
					}
				}

				$preItemTitleContent .= '<li class="' . $liAdditionalClass . '">';
				$preItemTitleContent .= $thumbnail;

				/*
				 * Start the internal tag: span or a
				 */
				$additionalClass = apply_filters( 'cmtt_term_tooltip_additional_class', '', $glossaryItem );
				$excludeTT		 = get_post_meta( $glossaryItem->ID, '_cmtt_exclude_tooltip', true );
				$preItemTitleContent .= '<' . $tag . ' class="' . $glossary_list_class . ' ' . $additionalClass . '" ' . $href . ' ';

				/*
				 * Add tooltip if needed (general setting enabled and page not excluded from plugin)
				 */
				if ( !$tooltipsDisabled && !$excludeTT ) {
					$tooltipContent	 = apply_filters( 'cmtt_glossary_index_tooltip_content', '', $glossaryItem );
					$tooltipContent	 = apply_filters( 'cmtt_3rdparty_tooltip_content', $tooltipContent, $glossaryItem );
					$tooltipContent	 = apply_filters( 'cmtt_tooltip_content_add', $tooltipContent, $glossaryItem );
					$preItemTitleContent .= 'data-tooltip="' . $tooltipContent . '"';
				}
				$preItemTitleContent .= '>';

				/*
				 * Add filter to change the content of what's before the glossary item title on the list
				 */
				$preItemTitleContent = apply_filters( 'cmtt_glossaryPreItemTitleContent_add', $preItemTitleContent, $glossaryItem );

				/*
				 * Insert post title here later on
				 */
				$postItemTitleContent .= '</' . $tag . '>';
				/*
				 * Add description if needed
				 */
				$postItemTitleContent .= $glossaryItemDesc;
				$postItemTitleContent .= '</li>';

				if ( !$hideTerms ) {
					$glossaryIndexContentArr[ mb_strtolower( $glossaryItem->post_title ) ] = $letterSeparatorContent . $preItemTitleContent . $glossaryItem->post_title . $postItemTitleContent;
				}

				$glossaryIndexContentArr = apply_filters( 'cmtt_glossary_index_content_arr', $glossaryIndexContentArr, $glossaryItem, $preItemTitleContent, $postItemTitleContent, $shortcodeAtts );
			}

			/*
			 * Don't need this later
			 */
			$glossary_index = NULL;

			$content .= '<ul class="glossaryList" id="' . $glossary_list_id . '">';

			if ( extension_loaded( 'intl' ) === true ) {
				$customLocale	 = get_option( 'cmtt_index_locale', '' );
				$locale			 = !empty( $customLocale ) ? $customLocale : get_locale();

				if ( is_object( $collator = collator_create( $locale ) ) === true ) {
					/*
					 * Add support for natural sorting order
					 */
					$collator->setAttribute( Collator::NUMERIC_COLLATION, Collator::ON );
					$glossariIndexContentArrFliped	 = array_flip( $glossaryIndexContentArr );
					$glossaryIndexContentArr		 = null;
					collator_asort( $collator, $glossariIndexContentArrFliped );
					$glossariIndexContentArrUnFliped = array_flip( $glossariIndexContentArrFliped );
				}
			} else {
				$glossariIndexContentArrUnFliped = $glossaryIndexContentArr;
				uksort( $glossariIndexContentArrUnFliped, array( __CLASS__, 'mb_string_compare' ) );
			}

			$isFirstIndexLetter = true;

			foreach ( $glossariIndexContentArrUnFliped as $key => $value ) {
				if ( in_array( $glossaryIndexStyle, array( 'classic-table', 'modern-table' ) ) ) {
					$newIndexLetter = self::detectStartNewIndexLetter( null, $key );

					if ( $newIndexLetter !== false ) {
						if ( !$isFirstIndexLetter ) {
							$content .= '<li class="the-letter-separator"></li>';
						}

						$content .= '<li class="the-index-letter"><h2>' . $newIndexLetter . '</h2></li>';
						$isFirstIndexLetter = FALSE;
					}
				}

				$content .= $value;
			}
			$content .= '</ul>';

			if ( self::isServerSide() && !isset( $args[ 'nopaging' ] ) && in_array( $paginationPosition, array( 'bottom', 'both' ) ) ) {
				$content .= apply_filters( 'cmtt_glossary_index_pagination', '', $glossary_query, $shortcodeAtts );
			}
		} else {
			$noResultsText = CMTT_Pro::__( get_option( 'cmtt_glossary_NoResultsLabel', 'Nothing found. Please change the filters.' ) );
			$content.= '<span class="error">' . $noResultsText . '</span>';
		}

		$content = apply_filters( 'cmtt_glossary_index_after_content', $content, $glossaryIndexStyle );

		do_action( 'cmtt_after_glossary_index' );

		return $content;
	}

	/**
	 * Outputs the pagination
	 * @param type $content
	 * @param type $glossary_query
	 * @param type $currentPage
	 * @return type
	 */
	public static function outputPagination( $content, $glossary_query, $shortcodeAtts ) {
		$currentPage		 = $shortcodeAtts[ 'itemspage' ];
		$glossaryPageLink	 = $shortcodeAtts[ 'glossary_page_link' ];

		$showPages	 = 11;
		$lastPage	 = $glossary_query->max_num_pages;

		$prevPage	 = ($currentPage - 1 < 1) ? 1 : $currentPage - 1;
		$nextPage	 = ($currentPage + 1 > $lastPage) ? $lastPage : $currentPage + 1;

		$prevHalf	 = ($currentPage - ceil( $showPages / 2 )) <= 0 ? 0 : ($currentPage - ceil( $showPages / 2 ));
		$prevDiff	 = (ceil( $showPages / 2 ) - $currentPage >= 0) ? ceil( $showPages / 2 ) - $currentPage : 0;
		$nextHalf	 = ($currentPage + ceil( $showPages / 2 )) > $lastPage ? $lastPage : ($currentPage + ceil( $showPages / 2 ));

		$prevSectionPage = ($currentPage - ceil( $showPages / 2 )) < 1 ? 1 : $currentPage - ceil( $showPages / 2 );
		$nextSectionPage = ($currentPage + ceil( $showPages / 2 )) > $lastPage ? $lastPage : $currentPage + ceil( $showPages / 2 );

		$pagesStart	 = ($prevHalf > 0) ? $prevHalf : 1;
		$pagesEnd	 = min( $nextHalf + $prevDiff, $nextSectionPage );

		$showFirst	 = $prevHalf > 1;
		$showLast	 = $nextHalf < $lastPage;

		ob_start();
		?>
		<ul class="pageNumbers">

		<?php
		if ( 1 != $currentPage ) :
			?>
				<li data-page-number="<?php echo $prevPage ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $prevPage ), $glossaryPageLink ) ); ?>">&lt;&lt;</a>
				</li>
		<?php endif; ?>

			<?php
			$pageSelected = (1 == $currentPage) ? 'class="selected"' : '';
			if ( $showFirst ) :
				?>
				<li <?php echo $pageSelected ?> data-page-number="1">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => 1 ), $glossaryPageLink ) ); ?>">1</a>
				</li>
		<?php endif; ?>

			<?php
			if ( $prevSectionPage > 1 ) :
				?>
				<li data-page-number="<?php echo $prevSectionPage ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $prevSectionPage ), $glossaryPageLink ) ); ?>">(...)</a>
				</li>
		<?php endif; ?>

			<?php for ( $i = $pagesStart; $i <= $pagesEnd; $i++ ): ?>
				<?php $pageSelected = ($i == $currentPage) ? 'class="selected"' : '' ?>
				<li <?php echo $pageSelected ?> data-page-number="<?php echo $i ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $i ), $glossaryPageLink ) ); ?>"><?php echo $i; ?></a>
				</li>
		<?php endfor; ?>

			<?php
			if ( $nextHalf !== $lastPage ) :
				?>
				<li data-page-number="<?php echo $nextSectionPage ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $nextSectionPage ), $glossaryPageLink ) ); ?>">(...)</a>
				</li>
		<?php endif; ?>

			<?php
			$pageSelected = ($lastPage == $currentPage) ? 'class="selected"' : '';
			if ( $showLast ) :
				?>
				<li <?php echo $pageSelected ?> data-page-number="<?php echo $lastPage ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $lastPage ), $glossaryPageLink ) ); ?>"><?php echo $lastPage ?></a>
				</li>
		<?php endif; ?>

			<?php
			if ( $lastPage != $currentPage ) :
				?>
				<li data-page-number="<?php echo $nextPage ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => ($nextPage) ), $glossaryPageLink ) ); ?>">&gt;&gt;</a>
				</li>
		<?php endif; ?>

		</ul>
		<?php
		$content.=ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Check if tooltips are disabled for given page
	 * @global type $post
	 * @param type $tooltipData
	 * @return type
	 */
	public static function tooltipsDisabledForPage( $tooltipData ) {
		global $post;
		$postId = empty( $post->ID ) ? '' : $post->ID;

		if ( !empty( $postId ) ) {
			/*
			 *  Checks whether to show tooltips on this page or not
			 */
			$tooltipsDisabled = get_post_meta( $postId, '_glossary_disable_tooltip_for_page', true ) == 1;
			if ( $tooltipsDisabled ) {
				unset( $tooltipData[ 'tooltip' ] );
			}
		}
		return $tooltipData;
	}

	/**
	 * Adds the scripts which has to be included on the main glossary index page only
	 */
	public static function addScripts() {
		static $runOnce;
		if ( $runOnce === TRUE ) {
			return;
		}

		global $post;
		$postId = empty( $post->ID ) ? '' : $post->ID;

		$inFooter = get_option( 'cmtt_script_in_footer', false );

		wp_enqueue_script( 'cm-modernizr-js', self::$jsPath . 'modernizr.min.js', array(), false, $inFooter );
		wp_enqueue_script( 'tooltip-frontend-js', self::$jsPath . 'tooltip.js', array( 'jquery', 'cm-modernizr-js', 'mediaelement' ), false, $inFooter );

		$tooltipData = array();

		$tooltipArgs								 = array(
			'clickable'		 => (bool) apply_filters( 'cmtt_is_tooltip_clickable', FALSE ),
			'delay'			 => (int) get_option( 'cmtt_tooltipDisplayDelay', 0 ),
			'timer'			 => (int) get_option( 'cmtt_tooltipHideDelay', 0 ),
			'minw'			 => (int) get_option( 'cmtt_tooltipWidthMin', 200 ),
			'maxw'			 => (int) get_option( 'cmtt_tooltipWidthMax', 400 ),
			'top'			 => (int) get_option( 'cmtt_tooltipPositionTop' ),
			'left'			 => (int) get_option( 'cmtt_tooltipPositionLeft' ),
			'endalpha'		 => (int) get_option( 'cmtt_tooltipOpacity' ),
			'borderStyle'	 => get_option( 'cmtt_tooltipBorderStyle' ),
			'borderWidth'	 => get_option( 'cmtt_tooltipBorderWidth' ) . 'px',
			'borderColor'	 => get_option( 'cmtt_tooltipBorderColor' ),
			'background'	 => get_option( 'cmtt_tooltipBackground' ),
			'foreground'	 => get_option( 'cmtt_tooltipForeground' ),
			'fontSize'		 => get_option( 'cmtt_tooltipFontSize' ) . 'px',
			'padding'		 => get_option( 'cmtt_tooltipPadding' ),
			'borderRadius'	 => get_option( 'cmtt_tooltipBorderRadius' ) . 'px'
		);
		$tooltipData[ 'tooltip' ]					 = apply_filters( 'cmtt_tooltip_script_args', $tooltipArgs );
		$tooltipData[ 'ajaxurl' ]					 = admin_url( 'admin-ajax.php' );
		$tooltipData[ 'post_id' ]					 = $postId;
		$tooltipData[ 'mobile_disable_tooltips' ]	 = get_option( 'cmtt_glossaryMobileDisableTooltips' );

		wp_localize_script( 'tooltip-frontend-js', 'cmtt_data', apply_filters( 'cmtt_tooltip_script_data', $tooltipData ) );

		/*
		 * Search for the Glossary shortcode and display the CSS/JS if found
		 */
		$hasGlossaryShortcode = FALSE;
		if ( have_posts() ) {
			while ( have_posts() ): the_post();
				$the_content = get_the_content();
				if ( has_shortcode( $the_content, 'glossary' ) ) {
					$hasGlossaryShortcode = TRUE;
				}
			endwhile;
		}

		if ( $hasGlossaryShortcode ) {
			wp_enqueue_style( 'jquery-listnav-style', self::$cssPath . 'jquery.listnav.css' );
			wp_enqueue_script( 'tooltip-listnav-js', self::$jsPath . 'cm-glossary-listnav.js', array( 'jquery' ), false, $inFooter );

			if ( !self::isServerSide() ) {
				$listnavArgs				 = array(
					'perPage'		 => (int) get_option( 'cmtt_perPage', 0 ),
					'letters'		 => (array) get_option( 'cmtt_index_letters' ),
					'includeNums'	 => (bool) get_option( 'cmtt_index_includeNum' ),
					'includeAll'	 => (bool) get_option( 'cmtt_index_includeAll' ),
					'initLetter'	 => get_option( 'cmtt_index_initLetter', '' ),
					'allLabel'		 => CMTT_Pro::__( get_option( 'cmtt_index_allLabel', 'ALL' ) ),
					'noResultsLabel' => CMTT_Pro::__( get_option( 'cmtt_glossary_NoResultsLabel', 'Nothing found. Please change the filters.' ) )
				);
				$tooltipData[ 'listnav' ]	 = apply_filters( 'cmtt_listnav_js_args', $listnavArgs );
				$tooltipData[ 'list_id' ]	 = apply_filters( 'cmtt_glossary_index_list_id', 'glossaryList' );
			}

			$tooltipData[ 'ajaxurl' ] = admin_url( 'admin-ajax.php' );
			wp_localize_script( 'tooltip-listnav-js', 'cmtt_listnav_data', $tooltipData );
		}

		/*
		 * Run this only once
		 */
		$runOnce = TRUE;
	}

	/**
	 * Sort array with specialchars alphabetically and maintain index
	 * association.
	 *
	 * Example:
	 *
	 * $array = array('Barcelona', 'Madrid', 'Albacete', 'Álava', 'Bilbao');
	 *
	 * asort($array);
	 * var_dump($array);
	 *     => array('Albacete', 'Barcelona', 'Bilbao', 'Madrid', 'Álava')
	 *
	 * $array = util::array_mb_sort($array);
	 * var_dump($array);
	 *     => array('Álava', 'Albacete', 'Barcelona', 'Bilbao', 'Madrid')
	 *
	 * @param   array  $array   Array of elements to sort.
	 *
	 * @return  array           Sorted array
	 *
	 * @access  public
	 *
	 * @static
	 */
	public static function array_mb_sort_alphabetically( array $array, $reverse = FALSE ) {
		if ( $reverse ) {
			usort( $array, array( __CLASS__, 'mb_string_compare' ) );
		} else {
			uasort( $array, array( __CLASS__, 'mb_string_compare' ) );
		}

		return $array;
	}

	/**
	 * Comparaison de chaines unicode. This method can come in handy when we
	 * want to use as a callback function on uasort & usort PHP functions to
	 * sort arrays when you have special characters for example accents.
	 *
	 * @param   string  $s1  First string to compare with
	 *
	 * @param   string  $s2  Second string to compare with
	 *
	 * @return  boolean
	 *
	 * @access  public
	 * @since   1.0.000
	 * @static
	 */
	public static function mb_string_compare( $s1, $s2 ) {
		return strcmp(
		iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT', self::decode_characters( $s1 ) ), iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT', self::decode_characters( $s2 ) ) );
	}

	/**
	 * Decode a string
	 *
	 * @param   string  $string   Encoded string
	 *
	 * @return  string
	 *
	 * @access  public
	 *
	 * @static
	 */
	public static function decode_characters( $string ) {
		$string	 = mb_convert_encoding( $string, "HTML-ENTITIES", "UTF-8" );
		$string	 = preg_replace( '~^(&([a-zA-Z0-9]);)~', htmlentities( '${1}' ), $string );
		return($string);
	}

}
