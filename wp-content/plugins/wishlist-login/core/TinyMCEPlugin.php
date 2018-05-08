<?php

/**
 * WishList Member TinyMCE Plugin
 * @author Fel Jun Palawan <feljunpalawan@gmail.com>
 */
if ( ! class_exists( 'WLMTinyMCEPlugin' ) ) {

	class WLMTinyMCEPlugin {

		public $codes = array();
		public $use_legacy = false;
		public $wlmtinymceplugin4 = null;

		function __construct() {
			global $tinymce_version;
			$this->use_legacy = version_compare( $tinymce_version, '400', '<' );
			if ( $this->use_legacy ) {
				add_action('init', array(&$this, 'TMCE_InsertButton'));
			} else {
				$this->wlmtinymceplugin4 = new WLMTinyMCEPlugin_4;
				$this->wlmtinymceplugin4->codes = $this->codes;
				$this->wlmtinymceplugin4->legacy = $this;
			}
		}

		function TMCE_InsertButton() {
			//for users who can edit only
			if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
				return false;
			//for rich editing only
			if (get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', array(&$this, 'TNMCE_RegisterPlugin'));
				add_filter('tiny_mce_before_init', array(&$this, 'TNMCE_RegisterButton'));
			}
		}

		function TNMCE_RegisterButton($in) {
			//where would you like to put the new dropdown?
			$advance_button_place = 1; //1,2,3,4
			$key = 'theme_advanced_buttons' . $advance_button_place;
			$holder = explode(",", $in[$key]);
			$holder[] = 'wlm_shortcodes'; //add our plugin on the menu
			$in[$key] = implode(",", $holder);
			return $in;
		}

		function TNMCE_RegisterPlugin($plugin_array) {
			$pagenow = $GLOBALS['pagenow'];
			$page = isset($_GET['page']) ? $_GET['page'] : "";
			$p = $page != "" ? "?page={$page}&WLMTNMCEPlugin=1" : "?WLMTNMCEPlugin=1";
			$url = admin_url() . $pagenow . $p;
			$plugin_array['wlm_shortcodes'] = $url;
			return $plugin_array;
		}

		function TNMCE_GetSpecialCodeSub($specialcodes,$sclevel_count) {
			$code_js = "";
            foreach($specialcodes as $ind=>$scode){
                if(count($ind) == count($ind, COUNT_RECURSIVE)) {
                        $code_js .= "sub3 = sub2.addMenu({title : '{$ind}'})\n";
                    foreach($scode as $parent=>$code) {
                        $title = $code['title']; $value=$code['value'];
                        $code_js .= "sub3.add({title : '{$title}', onclick : function() {\n";
                        $code_js .= "  tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}');\n";
                        $code_js .= "}});\n";
                    }
                } else {
                    foreach($scode as $parent=>$code) {
                        $title = $code['title']; $value=$code['value'];
                        $code_js .= "sub3.add({title : '{$title}', onclick : function() {\n";
                        $code_js .= "  tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}');\n";
                        $code_js .= "}});\n";
                    }
                }
            }
            return $code_js;
		}

		function TNMCE_GenerateSpecialCode($specialcodes,$sclevel_count,$level=2) {
			$code_js = "";
			$sub = $level <= 2 ? "":$level-1;
	        foreach($specialcodes as $index=>$scodes){
	        	if(count($scodes) > 0){
	        		if(!is_integer($index)){
	        			if($sclevel_count != 1){
	        				$code_js .= "sub{$level} = sub{$sub}.addMenu({title : '{$index}'})\n";
	        				$level++;
	        			}
	        			$code_js .= $this->TNMCE_GenerateSpecialCode($scodes,$sclevel_count,$level);
	        		} else {
                        $title = $scodes['title']; $value=$scodes['value'];
                        $js_func= isset( $scodes['jsfunc'] ) ? $scodes['jsfunc'] : false ;
                        if ( $js_func ) {
	                        $code_js .= "sub{$sub}.add({title : '{$title}', onclick : {$js_func} });\n";
                        } else {
	                        $code_js .= "sub{$sub}.add({title : '{$title}', onclick : function() {\n";
	                        $code_js .= "  tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}');\n";
	                        $code_js .= "}});\n";
                        }
	        		}
	        	}
	        }
		    return $code_js;
		}

		function TNMCE_GeneratePlugin($ptitle, $icon_path, $plugin_name, $max_width) {
			$pagenow = $GLOBALS['pagenow'];
			header('Content-type: text/javascript');
			$shortcodes = "";
			foreach ($this->codes as $WLPShortcodes) {
				$code_js = "";
				$sclevel_count = 0;
				//if for post only, skip if not in post section
				if (isset($WLPShortcodes['wponly']) && $pagenow != "post.php" && $pagenow != "post-new.php")
					break;
				$sclevel_count += isset($WLPShortcodes['shortcode']) && count($WLPShortcodes['shortcode']) > 0 ? 1:0;
				$sclevel_count += isset($WLPShortcodes['mergecode']) && count($WLPShortcodes['mergecode']) > 0 ? 1:0;
				$sclevel_count += isset($WLPShortcodes['special']) && count($WLPShortcodes['special']) > 0 ? count($WLPShortcodes['special']):0;

				//for shortcodes
				if (isset($WLPShortcodes['shortcode']) && count($WLPShortcodes['shortcode']) > 0) {
                    $shortcode = $WLPShortcodes['shortcode'][0];
                    $stitle = isset($shortcode['replace_title']) && $shortcode['replace_title'] != '' ? $shortcode['replace_title'] : 'Shortcodes';
					if($sclevel_count != 1){
						$code_js .= "sub2 = sub.addMenu({title : '{$stitle}' })\n";
					}
					foreach ($WLPShortcodes['shortcode'] as $index => $scode) {
						$title = $scode['title'];
						$value = $scode['value'];
						if($sclevel_count != 1){
							$short_func = "sub2.add({title : '{$title}', onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}');
							}});";
						}else{
							$short_func = "sub.add({title : '{$title}', onclick : function() {
								tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}');
							}});";
						}
					$code_js .=$short_func;
					}
				}
				//for mergecodes
				if (isset($WLPShortcodes['mergecode']) && count($WLPShortcodes['mergecode']) > 0) {
					if($sclevel_count != 1){
						$code_js .= "sub2 = sub.addMenu({title : 'Mergecodes'})\n";
					}
					foreach ($WLPShortcodes['mergecode'] as $index => $scode) {
						$title = $scode['title'];
						$value = $scode['value'];
						$scode2 = substr_replace($value, '/', 1, 0);
						if($sclevel_count != 1){
							$merge_func = "sub2.add({title : '{$title}', onclick : function() {
								var t = tinyMCE.activeEditor.selection.getContent();
								tinyMCE.activeEditor.selection.setContent('{$value}' +t +'{$scode2}');
								if(t == ''){
									tinyMCE.activeEditor.focus();
								}
							}});";
						}else{
							$merge_func = "sub.add({title : '{$title}', onclick : function() {
								var t = tinyMCE.activeEditor.selection.getContent();
								tinyMCE.activeEditor.selection.setContent('{$value}' +t +'{$scode2}');
								if(t == ''){
									tinyMCE.activeEditor.focus();
								}
							}});";
						}
						$code_js .=$merge_func;
					}
				}

                //for special codes
                if(isset($WLPShortcodes['special']) && count($WLPShortcodes['special']) > 0){
                	$code_js .= $this->TNMCE_GenerateSpecialCode($WLPShortcodes['special'],$sclevel_count);
                }

                if ($WLPShortcodes['name']) {
                    if ($code_js != "") {
                        $shortcodes .= "sub = m.addMenu({title : '{$WLPShortcodes['name']}'})\n" . $code_js;
                    } else {
                        if ($WLPShortcodes['jsfunction']) {
                            $shortcodes .= "sub = m.add({title : '{$WLPShortcodes['name']}', onclick:{$WLPShortcodes['jsfunction']}})\n";
                        } else {
                            $shortcodes .= "sub = m.add({title : '{$WLPShortcodes['name']}'})\n";
                        }
                    }
                }
            }
			if ($shortcodes == "") return;
			echo "
				tinymce.create('tinymce.plugins.{$plugin_name}', {
				        createControl: function(n, cm) {
				                switch (n) {
				                        case '{$plugin_name}':
				                                var c = cm.createMenuButton('{$plugin_name}', {
				                                        title : '{$ptitle}',
				                                        image : '{$icon_path}',
				                                        icons : false
				                                });

				                                c.onRenderMenu.add(function(c, m) {
				                                        var sub;
				                                        m.settings['max_width'] = {$max_width};
				                                        //add our shortcodes
				                                        {$shortcodes}
				                                });

				                                // Return the new menu button instance
				                                return c;
				                }

				                return null;
				        }
				});
				// Register plugin with a short name
				tinymce.PluginManager.add('{$plugin_name}', tinymce.plugins.{$plugin_name});
			";
		}

	    function TNMCE_PluginJS() {
			if ( empty( $this->plugin ) ) {
				$icon_path =  plugins_url("/images/WishListIcon.png", dirname(__FILE__));
			} else {
				$icon_path = $this->plugin->get_lib_url() . 'images/WishListIcon.png';
			}
	        $title = "WLP Shortcodes";
	        $plugin_name = "wlm_shortcodes";
	        $max_width = 600;
			//generate WLM Tinymce Plugin
			if ( isset( $_GET['WLMTNMCEPlugin'] ) && $_GET["WLMTNMCEPlugin"] == "1" ) {
		        $this->TNMCE_GeneratePlugin( $title, $icon_path, $plugin_name, $max_width );
				exit(0);
			} else if ( isset( $_GET['WLMTNMCEPlugin4'] ) && $_GET["WLMTNMCEPlugin4"] == "1" ) {
				if ( ! $this->use_legacy && ! is_null( $this->wlmtinymceplugin4 ) ) {
					$this->wlmtinymceplugin4->TNMCE_GeneratePlugin( $title, $icon_path, $plugin_name, $max_width );
					exit(0);
				}
			} else if ( isset( $_GET['WLMTNMCEPlugin4css'] ) && $_GET["WLMTNMCEPlugin4css"] == "1" ) {
				if ( ! $this->use_legacy && ! is_null( $this->wlmtinymceplugin4 ) ) {
					$this->wlmtinymceplugin4->TNMCE_GeneratePluginCSS( $icon_path );
					exit(0);
				}
			}
	    }

	    /**
	    * Function to be called to register your shortcodes on your plugin
	    *
	    *@param string $name The name of your shortcode that will appear on the menu
	    *@param array $shortcodes (optional) Multi-dimensional array of shortcodes eg. array('title'=>'[wlm_fname]','value'=>'[wlm_fname]')
	    *@param array $mergecodes (optional) Multi-dimensional array of shortcodes eg. array('title'=>'[wlm_ismember]','value'=>'[wlm_ismember]')
	    *@param int $wponly (optional) Specify if your shortcode appear on post only
	    *@param string $jsfunc (optional) Applicable only when $shortcode and $mergecode were empty. The js function to be called when the $name is click
	    */
	    function RegisterShortcodes($name,$shortcodes=array(),$mergecodes=array(),$wponly=0,$jsfunc=null,$specialcodes=array()){
			$code = array();
			$code['name'] = $name;
			if($wponly == 1) $code['wponly'] = 1;
			if(!is_null($jsfunc)) $code['jsfunction'] = $jsfunc;
			if(count($shortcodes) > 0) $code['shortcode'] = $shortcodes;
			if(count($mergecodes) > 0) $code['mergecode'] = $mergecodes;
			if(count($specialcodes) > 0) $code['special'] = $specialcodes;
			$this->codes[] = $code;
			if ( ! $this->use_legacy ) {
				$this->wlmtinymceplugin4->codes[] = $code;
			}
	    }
	}
}

if ( ! class_exists( 'WLMTinyMCEPlugin_4' ) ) {

	class WLMTinyMCEPlugin_4 {

		public $codes = array();
		public $legacy = null;

		function __construct() {
			add_action('init', array(&$this, 'TMCE_InsertButton'));
			add_action('init', array(&$this, 'TMCE_InsertButton'));
		}

		function TMCE_InsertButton() {
			//for users who can edit only
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
				return false;
			//for rich editing only
			if ( get_user_option( 'rich_editing' ) == 'true' ) {
				add_filter( 'mce_external_plugins', array( &$this, 'TNMCE_RegisterPlugin' ) );
				add_filter( 'mce_buttons', array( &$this, 'TNMCE_RegisterButton' ) );

				$pagenow = $GLOBALS['pagenow'];
				$page = isset( $_GET['page'] ) ? $_GET['page'] : "";
				$p = $page != "" ? "?page={$page}&WLMTNMCEPlugin4css=1" : "?WLMTNMCEPlugin4css=1";
				$url = admin_url() . $pagenow . $p;
				wp_enqueue_style( 'wlmtinymceplugin_4_css', $url, array(), '1.0' );
			}
		}

		function TNMCE_RegisterPlugin( $plugin_array ) {
			$pagenow = $GLOBALS['pagenow'];
			$page = isset($_GET['page']) ? $_GET['page'] : "";
			$p = $page != "" ? "?page={$page}&WLMTNMCEPlugin4=1" : "?WLMTNMCEPlugin4=1";
			$url = admin_url() . $pagenow . $p;
			$plugin_array['wlm_shortcodes'] = $url;
			return $plugin_array;
		}

		function TNMCE_RegisterButton( $buttons ) {
		    array_push( $buttons, 'wlm_shortcodes' );
		    return $buttons;
		}

		function TNMCE_GeneratePluginCSS( $icon_path ) {
			$pagenow = $GLOBALS['pagenow'];
			header('Content-type: text/css');
			ob_start();
			echo "/*CSS FOR WLM TINYMCE PLUGIN MENU ICON*/
				i.mce-i-icon {
					font: 400 20px/1 dashicons;
					padding: 0;
					vertical-align: top;
					speak: none;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					margin-left: -2px;
					padding-right: 2px
				}

				i.wlpshortcode-own-icon {
					background-image: url('{$icon_path}');
				}
			";
			echo ob_get_clean();
		}

		function TNMCE_GenerateCode( $codes, $sclevel_count, $merge = false ) {
			$submenus = array();
	        foreach ( $codes as $index=>$scodes ) {
	        	$sub = "";
	        	$sub_sub = array();
	        	if ( count( $scodes ) > 0 ) {
	        		if ( ! is_integer( $index ) ) {
	        			if ( $sclevel_count != 1 ) {
	        				$sub = "text:'{$index}'";
	        			}
	        			$sub_sub = $this->TNMCE_GenerateCode( $scodes, $sclevel_count );
	        			if ( ! empty( $sub_sub ) ) {
		                	$sub_sub = "{" .implode("},\n{", $sub_sub ) ."}";
		                    $sub = "text:'{$index}',\nmenu:[{$sub_sub}]";
	        			}
	        		} else {
                        $title = $scodes['title']; $value=$scodes['value'];
                        $js_func= isset( $scodes['jsfunc'] ) ? $scodes['jsfunc'] : false ;
                        if ( $js_func ) {
                        	$sub = "text:'{$title}',onclick:{$js_func}";
                        } else {
                        	if ( $merge ){
                        		$scode2 = substr_replace($value, '/', 1, 0);
								$sub = "text:'{$title}',onclick:function() {
	                        		var t = tinyMCE.activeEditor.selection.getContent();
									tinyMCE.activeEditor.selection.setContent('{$value}' +t +'{$scode2}');
									if(t == ''){
										tinyMCE.activeEditor.focus();
									}
								}";
                        	} else {
                        		$sub = "text:'{$title}',onclick:function() { tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$value}'); }";
                        	}
                        }
	        		}
	        	}
	        	$submenus[] = $sub;
	        }
		    return $submenus;
		}

		function TNMCE_GeneratePlugin( $ptitle, $icon_path, $plugin_name, $max_width ) {
			// make sure $this->codes is latest
			$this->codes = $this->legacy->codes;
			$pagenow = $GLOBALS['pagenow'];
			$shortcodes = array();
			foreach ( $this->codes as $WLPShortcodes ) {
				if (isset($WLPShortcodes['wponly']) && $pagenow != "post.php" && $pagenow != "post-new.php") break;
				if ( ! isset( $WLPShortcodes['name'] ) || ! $WLPShortcodes['name'] ) break;
				$special_sub = array();
				$shortcode_sub = array();
				$mergecode_sub = array();
				$submenus = array();

				//check how many levels are there
				$sclevel_count += isset( $WLPShortcodes['shortcode'] ) && count( $WLPShortcodes['shortcode'] ) > 0 ? 1:0;
				$sclevel_count += isset( $WLPShortcodes['mergecode'] ) && count( $WLPShortcodes['mergecode'] ) > 0 ? 1:0;
				$sclevel_count += isset( $WLPShortcodes['special'] ) && count( $WLPShortcodes['special'] ) > 0 ? count( $WLPShortcodes['special'] ):0;
				//for shortcodes
				if ( isset( $WLPShortcodes['shortcode'] ) && count( $WLPShortcodes['shortcode'] ) > 0 ) {
					$shortcode = $WLPShortcodes['shortcode'][0];
                    $stitle = isset( $shortcode['replace_title'] ) && $shortcode['replace_title'] != '' ? $shortcode['replace_title'] : 'Shortcodes';
                    $code[ $stitle ] = $WLPShortcodes['shortcode'];
					$shortcode_sub = $this->TNMCE_GenerateCode( $code, $sclevel_count );
					unset( $code );
				}
				//for merge codes
                if( isset( $WLPShortcodes['mergecode'] ) && count( $WLPShortcodes['mergecode'] ) > 0 ) {
					$shortcode = $WLPShortcodes['mergecode'][0];
                    $stitle = isset( $shortcode['replace_title'] ) && $shortcode['replace_title'] != '' ? $shortcode['replace_title'] : 'Mergecodes';
                    $code[ $stitle ] = $WLPShortcodes['mergecode'];
					$mergecode_sub = $this->TNMCE_GenerateCode( $code, $sclevel_count );
					unset( $code );
                }
				//for special codes
                if( isset( $WLPShortcodes['special'] ) && count( $WLPShortcodes['special'] ) > 0 ) {
                	$special_sub = $this->TNMCE_GenerateCode( $WLPShortcodes['special'], $sclevel_count );
                }

                $submenus = array_merge( $shortcode_sub, $mergecode_sub, $special_sub );

                if ( ! empty( $submenus ) ) {
                	$submenus = "{" .implode("},\n{", $submenus ) ."}";
                    $shortcodes[] = "text:'{$WLPShortcodes['name']}',\nmenu:[\n{$submenus}\n]";
                } else {
                    if ($WLPShortcodes['jsfunction']) {
                        $shortcodes[] = "text:'{$WLPShortcodes['name']}',\nonclick:{$WLPShortcodes['jsfunction']}";
                    } else {
                        $shortcodes[] = "text:'{$WLPShortcodes['name']}'";
                    }
                }
            }
			if ( empty( $shortcodes ) ) return;
			$shortcodes = "{" .implode("},\n\n{", $shortcodes ) ."}";

			header('Content-type: text/javascript');
			ob_start();
		    echo "/*JS SCRIPT FOR WLM TINYMCE4 PLUGIN MENU*/
		    tinymce.PluginManager.add('{$plugin_name}', function( editor, url ) {
		        editor.addButton( '{$plugin_name}', {
		            title: '{$ptitle}',
		            type: 'menubutton',
		            icon: 'icon wlpshortcode-own-icon',
		            menu: \n[\n{$shortcodes}\n]
		        });
		    });";
			echo ob_get_clean();
		}
	}
}
?>
