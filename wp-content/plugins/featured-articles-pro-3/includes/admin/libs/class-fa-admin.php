<?php
if( !class_exists( 'FA_Ajax_Actions' ) ){
	require_once fa_get_path( 'includes/admin/libs/class-fa-ajax-actions.php' );	
}

/**
 * Implements the administration functionality
 *
 */
class FA_Admin extends FA_Custom_Post_Type{
	/**
	 * Stores WP errors when processing data
	 * @var WP_Error
	 */
	private $errors;
	
	/**
	 * Prefix on meta boxes ids added by the plugin; used
	 * to identify them.
	 * @var string
	 */
	private $meta_box_prefix = 'fapro';
	
	/**
	 * Reference for WP Ajax actions class
	 * @var object
	 */
	private $wp_ajax;
	
	/**
	 * Slider transient prefix. Must have the same name as
	 * variable $transient_prefix from class FA_Slider
	 */
	private $slider_transient_prefix = 'fa_slider_';
	
	/**
	 * Constructor
	 */
	public function __construct(){
		
		parent::__construct();
		
		// start ajax actions
		$this->wp_ajax = new FA_Ajax_Actions();
		// check for iframe plugin variables and set the according variables needed by WordPress
		add_action( 'init', array( $this, 'is_iframe' ), -9999);
		// check for previews
		add_action( 'init', array( $this, 'is_preview' ), -9999);
		
		// admin menu
		add_action( 'admin_menu', array($this, 'admin_menu'), 1);
		// add scripts
		add_action( 'load-post.php', array( $this, 'post_edit_assets' ));
		add_action( 'load-post-new.php', array( $this, 'post_edit_assets' ));
		
		// remove autosave script from slider editing screen
		add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_slider_autosave' ) );
		
		// save slide data
		add_action( 'save_post', array( $this, 'save_slide' ), 1, 3);
		add_action( 'edit_attachment', array( $this, 'edit_attachment' ), 1, 1);
		add_filter( 'wp_save_image_editor_file', array( $this, 'save_image' ), 1, 5);
		
		// save slider data
		add_action( 'save_post_' . parent::get_type_slider(), array( $this, 'save_slider' ), 10, 3);	
		// save slider revisions (for preview purposes)
		add_action( 'save_post_revision', array( $this, 'save_slider_revisions' ), 10, 3);
		
		// detect images in post contents when saving post
		add_action( 'save_post', array( $this, 'detect_image' ), 10, 3);
		
		// add extra columns on sliders table
		add_filter( 'manage_' . parent::get_type_slider() . '_posts_columns', array( $this, 'extra_slider_columns' ), 9999 );
		add_action( 'manage_'. parent::get_type_slider() .'_posts_custom_column', array($this, 'output_extra_slider_columns'), 10, 2 );	
		
		// add extra columns on slides table
		add_filter( 'manage_' . parent::get_type_slide() . '_posts_columns', array( $this, 'extra_slide_columns' ), 9999 );
		add_action( 'manage_'. parent::get_type_slide() .'_posts_custom_column', array($this, 'output_extra_slide_columns'), 10, 2 );	
		
		// register slide meta boxes on allowed post types
		add_action( 'add_meta_boxes', array( $this, 'register_posts_meta_boxes' ), 10, 2 );
		
		// add meta boxes for slider post type
		add_action( 'fa_meta_box_cb_' . parent::get_type_slider(), array( $this, 'register_slider_meta_boxes' ) );
		// the slide taxonomy meta box
		add_action( 'fa_tax_meta_box_cb_' . parent::get_slide_tax() , array( $this, 'slide_tax_metabox' ) );
		
		// remove all metaboxes except the ones implemented by the plugin and the default allowed ones
		add_action( 'screen_options_show_screen', array( $this, 'remove_meta_boxes' ));
		
		// tinymce
		add_action( 'admin_head', array( $this, 'tinymce' ) );
		add_filter( 'mce_external_languages', array( $this, 'tinymce_languages' ) );
		
		// for WP version prior to WP 4, use alternative for slider shortcode tinyMCE button
		if( version_compare( get_bloginfo( 'version' ), '4', '<' ) ){
			// register shortcode meta box if version smaller than 4
			add_action( 'add_meta_boxes', array( $this, 'register_slider_shortcode_meta_box' ), 10, 2 );
		}
		
		add_filter( 'enter_title_here', array( $this, 'post_title_label' ), 999, 2);		
		add_filter( 'preview_post_link', array( $this, 'slider_preview_link' ), 999, 1 );
		
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'fa_admin_notices', array( $this, 'fa_admin_notices' ) );
		
		// add a filter to detect if FA PRO is installed and remove activation link and add a message
		add_filter('plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2);
		add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);
	}
	
	/**
	 * Modify post preview link with our own implementation.
	 * When user hits preview theme from slider settings, this is the URL he will be taken to.
	 * 
	 * @param string $url
	 */
	public function slider_preview_link( $url ){
		global $post;
		if( !$post || parent::get_type_slider() != $post->post_type ){
			return $url;
		}
		
		$preview_args = array(
			'post_id' 	=> $post->ID,
			'theme' 	=> ( isset( $_POST['theme']['active'] ) ? $_POST['theme']['active'] : '' ),
			'vars'		=> array(
				'color' => ( isset( $_POST['theme']['color'] ) ? $_POST['theme']['color'] : '' )
			),
			'echo'		=> false
		);
		$url = fa_slider_preview_homepage( $preview_args );
		return htmlspecialchars_decode( $url );
	}
	
	/**
	 * Check if iframe request was issued
	 */
	public function is_iframe(){
		if( isset($_GET['fapro_inline']) ){
			$_GET['noheader'] = true;
			if( !defined( 'IFRAME_REQUEST' ) ){
				define( 'IFRAME_REQUEST', true );
			}
			if( !defined( 'FAPRO_IFRAME' ) ){
				define( 'FAPRO_IFRAME', true );
			}
			
			fa_load_admin_style('iframe');	
		}		
	}
	
	/**
	 * Init callback that checks if a preview should be displayed.
	 * Verifies only front-end pages.
	 * Adds filter loop_start to display the slider preview.
	 */
	public function is_preview(){
		
		// previews not available on admin pages
		if( is_admin() ){
			return;
		}
		// check for preview variable
		if( !fa_is_preview() ){
			// user must be capabile of editing fa items
			if( !current_user_can('edit_fa_items') ){
				wp_die( __('Not allowed.', 'fapro') );
			}			
			return;
		}
		
		check_admin_referer( 'fa-slider-theme-preview', 'fa-preview-nonce' );
		
		if( fa_is_theme_edit_preview() ){
			// add filter to prevent dynamic areas display on theme color scheme edit
			add_filter('fa_display_dynamic_areas', array( $this, 'prevent_dynamic_areas' ), 999, 2);
			// prevent all other sliders from being displayed except the one set on $_GET
			add_filter( 'fa_display_slider' , array( $this, 'prevent_sliders' ), 999, 3 );	
			// hide the edit links displayed below sliders
			add_filter( 'fa_show_slider_edit_link', array( $this, 'hide_edit_links' ), 999, 2 );	
			// if a color scheme is set, output the styling in page
			if( !isset( $_GET['color'] ) || empty( $_GET['color'] ) ){
				// add the styling for creating new color scheme
				add_filter('wp_print_styles', array( $this, 'color_scheme_styles' ));
			}	
			show_admin_bar( false );
		}		
	}
	
	/**
	 * Prevent dynamic areas to be displayed on theme color scheme editor
	 */
	public function prevent_dynamic_areas( $show, $area ){
		$area_set = theme_editor_preview_area_id();				
		// If area is options area, show the slider from $_GET
		if( $area_set == $area ){
			if( isset( $_GET['slider_id'] ) ){
				$slider_id = absint( $_GET['slider_id'] );
				// set as display area an area that is custom. 
				// The filter to display the sider will check this and will display only for it.
				fa_display_slider( $slider_id, 'fa_slider_preview_area' );	
			}			
		}
				
		/**
		 * On theme edit previews, prevent other area from being displayed except the 
		 * loop_start that is used to display the slider for the theme editor
		 */
		return false;
	}
	
	/**
	 * Prevent all sliders from being displayed on theme edit preview except the one set on 
	 * $_GET
	 */
	public function prevent_sliders( $show, $slider_id, $dynamic_area ){
		// the area fa_slider_preview_loop_start is a temporary area passed from function $this->prevent_dynamic_areas
		// only the preview slider will have this area so all other sliders won't be displayed into the preview
		if( 'fa_slider_preview_area' != $dynamic_area ){
			return false;			
		}		
		return true;
	}
	
	/**
	 * On theme color scheme edit preview, hide the edit links below sliders.
	 */
	public function hide_edit_links( $show, $slider_id ){
		return false;
	}
	
	/**
	 * Outputs the styling on the preview when creatign a new color scheme
	 */
	public function color_scheme_styles(){
		if( !fa_is_preview() || !fa_is_theme_edit_preview() ){
			return;
		}
		
		$theme_name = $_GET['theme'];
		require_once fa_get_path('includes/admin/libs/class-fa-theme-editor.php');
		$editor = new FA_Theme_Editor( $theme_name );
		$styles = $editor->get_theme_styles( $theme_name );
		if( is_wp_error( $styles ) ){
			return;
		}
		$theme = $editor->get_the_theme( $theme_name );		
?>
<!-- FA default color scheme -->
<style type="text/css">
<?php foreach ( $styles as $style ):?>
<?php echo $style['css_selector']?>{
	<?php foreach( $style['properties'] as $property => $data ):?>
	<?php echo $property;?>: <?php 
		if( 'image' == $data['type'] && ( !empty( $data['default'] ) || 'none' != trim( $data['default'] )  ) ){
			echo 'url(' . $theme['url'] . '/' . $data['default'] . ')';
		}else{
			echo $data['default'];
		}	
	?>; /* <?php echo $data['text'];?> */
	<?php endforeach;?>
}
<?php endforeach;?>
</style>
<!-- /End FA default color scheme -->
<?php	
	}
	
	/**
	 * Used when the slide taxonomy metabox is displayed.
	 * It removes the category parent drop down.
	 */
	public function slide_tax_metabox(){
		add_filter('wp_dropdown_cats', array( $this, 'remove_tax_parent' ), 10, 2);
	}
	
	/**
	 * Removes the parent category dropdown when creating a category on slide 
	 * edit page.
	 * @param string $output
	 * @param array $r
	 */
	public function remove_tax_parent( $output, $r ){
		remove_filter( 'wp_dropdown_cats', array( $this, 'remove_tax_parent' ) );
		
		// on tags editing display a text
		$screen = get_current_screen();
		if( 'edit-tags' == $screen->base && parent::get_slide_tax() == $screen->taxonomy && parent::get_type_slide() == $screen->post_type ){
			return '<span style="font-style:italic; color: #999;">' . __('not available', 'fapro') . '</span>';			
		}
		
		return '';
	}
	
	/**
	 * Remove all other metaboxes from slider and slide edit screen except the ones
	 * added by the plugin and the default WP meta boxes
	 * @param object $post
	 */
	public function remove_meta_boxes( $show_screen, $check_screen = true ){		
		$screen = get_current_screen();
		$page 	= $screen->id;
		
		if( $check_screen ){
			// apply this only for sliders and slides
			if( parent::get_type_slide() != $page && parent::get_type_slider() != $page ){
				return $show_screen;			
			}
		}
		
		global $wp_meta_boxes;
		$default_meta_boxes = array( 
			'submitdiv', 
			'postimagediv', 
			'authordiv', 
			parent::get_type_slide() . '_categoriesdiv' 
		);
		
		// loop all contexts
		foreach( array('side', 'normal', 'advanced') as $context ){
			// if context is missing, skip it
			if( !isset( $wp_meta_boxes[ $page ][ $context ] ) ){
				continue;
			}
			// loop priorities
			foreach ( array('high', 'core', 'default', 'low') as $priority ){
				// if priority is missing, skip it
				if( !isset( $wp_meta_boxes[ $page ][ $context ][ $priority ] ) ){
					continue;
				}
				// loop registered meta boxes
				foreach( $wp_meta_boxes[ $page ][ $context ][ $priority ] as $id => $meta_box ){
					// if plugin meta box, keep it
					if( $this->meta_box_prefix === substr( $id, 0, strlen( $this->meta_box_prefix ) ) ){
						continue;						
					}
					// if default wp meta box, keep it
					if( in_array( $id, $default_meta_boxes) ){
						continue;
					}	
					// remove all other meta boxes		
					$wp_meta_boxes[ $page ][ $context ][ $priority ][ $id ] = false;	
				}				
			}					
		}
		return $show_screen;		
	}
	
	/**
	 * Admin menu setup
	 */
	public function admin_menu(){
		// get slide object post type to retrieve labels
		$slide_object 	= get_post_type_object( parent::get_type_slide() );
		$parent_slug 	= 'edit.php?post_type=' . parent::get_type_slider();
		$slide_tax_object = get_taxonomy( parent::get_slide_tax() );
		
		// slides list menu page
		$slides = add_submenu_page(
			$parent_slug,
			$slide_object->labels->name,  
			$slide_object->labels->name, 
			'edit_fa_items', 
			'edit.php?post_type=' . parent::get_type_slide()
		);
		
		// new slide menu page
		$new_slide = add_submenu_page(
			$parent_slug, 
			$slide_object->labels->new_item, 
			$slide_object->labels->new_item, 
			'edit_fa_items', 
			'post-new.php?post_type=' . parent::get_type_slide()
		);
		
		// slides taxonomy
		$slide_groups = add_submenu_page(
			$parent_slug, 
			$slide_tax_object->labels->name, 
			$slide_tax_object->labels->name, 
			'manage_fa_terms', 
			'edit-tags.php?taxonomy=' . parent::get_slide_tax() . '&post_type=' . parent::get_type_slide()
		);
		// load actions on plugin terms page
		add_action( 'load-edit-tags.php', array( $this, 'on_slide_terms_load' ) );
		
		$dynamic_areas = add_submenu_page(
			$parent_slug, 
			__('Dynamic areas', 'fapro'), 
			__('Dynamic areas', 'fapro'), 
			'manage_options', 
			'fapro_hooks',
			array( $this, 'dynamic_slider_areas' )
		);
		add_action('load-' . $dynamic_areas, array( $this, 'on_dynamic_slider_areas_load' ) );
		
		$themes_edit = add_submenu_page(
			$parent_slug, 
			__('Slideshow Themes'), 
			__('Themes'), 
			'manage_options', 
			'fapro_themes',
			array($this, 'themes_manager')
		);	
		add_action( 'load-' . $themes_edit, array( $this, 'on_themes_edit_load' ) );
		
		// theme color customizer
		$theme_customizer = add_submenu_page(
			null,
			'',
			'',
			'edit_fa_items',
			'fa-theme-customizer',
			array( $this, 'theme_customizer' )
		);
		add_action( 'load-' . $theme_customizer, array( $this, 'on_theme_customizer_load' ) );
		
		// Plugin settings menu page
		$settings = add_submenu_page(
			$parent_slug, 
			__('Settings', 'fapro'), 
			__('Settings', 'fapro'), 
			'manage_options', 
			'fapro_settings',
			array($this, 'page_settings')
		);
		// load action for plugin settings page
		add_action( 'load-' . $settings, array( $this, 'on_page_settings_load' ) );
		
		$tax_modal = add_submenu_page(
			null, 
			'', 
			'', 
			'edit_fa_items', 
			'fa-tax-modal',
			array( $this, 'modal_taxonomy' ));
		
		$mixed_modal = add_submenu_page(
			null, 
			'', 
			'', 
			'edit_fa_items', 
			'fa-mixed-content-modal',
			array( $this, 'modal_mixed_content' ));	
			
		$mixed_slide_edit = add_submenu_page(
			null,
			'',
			'',
			'edit_fa_items',
			'fa-post-slide-edit',
			array( $this, 'modal_mixed_slide_edit' )
		);	
		add_action('load-' . $mixed_slide_edit, array( $this, 'on_slide_modal_load' ) );
	}
	
	/**
	 * Load callback on terms page load
	 */
	public function on_slide_terms_load(){
		$screen = get_current_screen();
		if( !$screen ){
			return;
		}
		
		if( parent::get_type_slide() == $screen->post_type && parent::get_slide_tax() == $screen->taxonomy ){
			add_filter('wp_dropdown_cats', array( $this, 'remove_tax_parent' ), 10, 2);	
		}		
	}
	
	/**
	 * Modal taxonomy iframe
	 */
	public function modal_taxonomy(){
		if( defined('FAPRO_IFRAME') ){
			iframe_header();
		}		
		
		require_once fa_get_path('includes/admin/libs/class-fa-taxonomies-list-table.php');
		$tbl = new FA_Taxonomies_List_Table();
		$tbl->prepare_items();
		?>
		<?php $tbl->views();?>
		<form method="get" action="">
			<input type="hidden" name="page" value="fa-tax-modal" />
			<input type="hidden" name="fapro_inline" value="true" />
			<input type="hidden" name="pt" value="<?php echo $tbl->get_post_type();?>" />
			<input type="hidden" name="tax" value="<?php echo $tbl->get_taxonomy();?>" />		
			<?php $tbl->search_box( __('search', 'fapro'), 'id');?>
		</form>
		<?php $tbl->display();?>
		<?php
		if( defined('FAPRO_IFRAME') ){
			iframe_footer();
			exit();
		}
	}
	
	/**
	 * Mixed slider content selection modal iframe
	 */
	public function modal_mixed_content(){
		if( defined('FAPRO_IFRAME') ){
			iframe_header();
		}
		
		require_once fa_get_path('includes/admin/libs/class-fa-posts-list-table.php');
		$tbl = new FA_Posts_List_Table();
		$tbl->prepare_items();
		?>
		<form method="get" action="">
			<input type="hidden" name="page" value="fa-mixed-content-modal" />
			<input type="hidden" name="fapro_inline" value="true" />
			<input type="hidden" name="post_type" value="<?php echo $tbl->get_post_type();?>" />			
			<?php $tbl->views();?>
			<?php $tbl->search_box(__('search', 'fapro'), 'id');?>
			<?php $tbl->display();?>
		</form>
		<?php 
		if( defined('FAPRO_IFRAME') ){
			iframe_footer();
			exit();
		}		
	}
	
	/**
	 * Slide modal edit load page callback
	 */
	public function on_slide_modal_load(){
		
		if( !isset( $_GET['post_id'] ) ){
			wp_die(-1);
		}
		
		// trying to create a new custom slide?
		if( 'new-custom-slide' == $_GET['post_id'] ){
			$post = get_default_post_to_edit( parent::get_type_slide(), true );
			$_GET['post_id'] = $post->ID;
		}		
		
		$post_id = absint( $_GET['post_id'] );		
		if( !current_user_can('edit_fa_items', $post_id) ){
			wp_die(-1);
		}
		
		$screen = get_current_screen();
		global $post;
		$post = get_post( $post_id );
		require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
		
		// Slide video attachment meta box
		add_meta_box(
			$this->meta_box_prefix . '-slide-settings', 
			__('Featured Articles PRO - slide settings', 'fapro'), 
			array( $this, 'meta_box_slide_settings' ),
			$screen->id,
			'advanced',
			'default' );
		
		if( 'attachment' == $post->post_type ){
			// submitdiv	
			add_meta_box( 
				'submitdiv', 
				__( 'Publish' ), 
				'attachment_submit_meta_box', 
				$screen->id, 
				'side', 
				'core'
			);			
		}else{
			// submitdiv	
			add_meta_box( 
				'submitdiv', 
				__( 'Publish' ), 
				'post_submit_meta_box', 
				$screen->id, 
				'side', 
				'core'
			);		
			// Slide video attachment meta box
			add_meta_box(
				$this->meta_box_prefix . '-slide-video-query', 
				__('Featured Articles PRO - video', 'fapro'), 
				array( $this, 'meta_box_slide_video' ),
				$screen->id,
				'side',
				'default' );	
			
			add_meta_box(
				'postimagediv', 
				__('Featured Image'), 
				array( $this, 'post_thumbnail_meta_box' ), 
				$screen->id, 
				'side', 
				'low'
			);			
		}	
			
		$this->load_slide_assets();	
		wp_enqueue_script( 'post' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'jquery-cookie' );
		
		// Save slide settings
		// check for nonce presence, if set, save options
		if( !isset( $_POST['fa-slide-modal-settings-nonce'] ) ){
			return;
		}
		
		$post = get_post( $post_id );
		check_admin_referer( 'fa-slide-modal-options-save', 'fa-slide-modal-settings-nonce' );
		
		// if is cutom post type, update the post title and post content
		if( parent::get_type_slide() == $post->post_type ){
			$update_args = array();
			if( isset( $_POST['fa_slide']['title'] ) ){
				$update_args['post_title'] = $_POST['fa_slide']['title'];
				unset( $_POST['fa_slide']['title'] );
			}
			if( isset( $_POST['fa_slide']['content'] ) ){
				$update_args['post_content'] = $_POST['fa_slide']['content'];
				unset( $_POST['fa_slide']['content'] );
			}
			if( $update_args ){
				$update_args['ID'] = $post_id;
				wp_update_post( $update_args );
			}
		}
		
		// update the parent slider transient to reflect changes
		$slider_id = absint( $_POST['slider_ID'] );
		delete_transient( $this->slider_transient_prefix . $slider_id );
		
		// process attachments separately
		if( 'attachment' == $post->post_type ){
			fa_update_slide_options( $post_id, $_POST['fa_slide'] );			
		}else{
			// remove the action set on save post to detect images sicne it's not needed
			remove_action('save_post', array( $this, 'detect_image' ), 10, 3);
			// update options not needed. Update performed on save_post hook in edit_post() above
			edit_post();
		}
		/**
		 * Action on slide save
		 * @param int $post_id - ID of post being saved
		 * @param object $post - object of post being saved
		 * @param bool $update - is update or new post
		 */
		do_action('fa-save-slide', $post_id, $post, true);
		
		$redirect = fa_iframe_admin_page_url( 
			'fa-post-slide-edit', 
			array(
				'post_id' => $post_id, 
				'slider_id' => $slider_id 
			), 
			false 
		);
		wp_redirect( $redirect );
		die();
	}
	
	/**
	 * Display featured image meta box on slide edit modal
	 */
	public function post_thumbnail_meta_box( $post ){
		remove_all_filters('admin_post_thumbnail_html');
		$thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
		echo _wp_post_thumbnail_html( $thumbnail_id, $post->ID );
	}
	
	/**
	 * Mixed content slide edit details modal
	 */
	public function modal_mixed_slide_edit(){
		if( defined('FAPRO_IFRAME') ){
			iframe_header();
		}
		
		$post_id = absint( $_GET['post_id'] );
		$post = get_post( $post_id );
		if( !$post ){
			wp_die( __('Post not found.', 'fapro') );			
		}
		
		$slider_id = absint( $_GET['slider_id'] );
		$slider = get_post( $slider_id );
		if( !$slider ){
			wp_die( __('Slider not found.', 'fapro') );
		}
		
		$options = fa_get_slide_options( $post_id );
		
		if( 'auto-draft' == $post->post_status ){
			$post->post_title = '';
			$options['title'] = '';
		}
		
		$screen = get_current_screen();
		$screen_id = $screen->id;
		
		$this->remove_meta_boxes( null, false );
		
		$modal = fa_modal_path('slide-settings');
		include_once $modal;
		 
		if( defined('FAPRO_IFRAME') ){
			iframe_footer();
			exit();
		}
	}
	
	/**
	 * Loads assets on slide/slider post type edit
	 */
	public function post_edit_assets(){
		$screen = get_current_screen();
		$page = $screen->id;
		
		// load different things for sliders
		if( parent::get_type_slider() == $page ){
			/**
			 * Action on slider edit page load
			 */
			do_action( 'load_fa_slider_edit' );
			
			$this->load_slider_assets();
			return;			
		}
		
		// allow slide edit scripts on all allowed post types
		if( in_array( $page, fa_allowed_post_types() ) ){
			// if not allowed to edit slides and not type slide, return
			if( !fa_allowed_slide_edit() && parent::get_type_slide() != $page ){
				return;
			}			
			$this->load_slide_assets();
			return;
		}		
	}
	
	/**
	 * Dequeue autosave.js from slider edit screen
	 */
	public function dequeue_slider_autosave(){
		if( parent::get_type_slider() == get_post_type() ){
			wp_dequeue_script('autosave');
		}
	}
	
	/**
	 * Load JavaScript needed on slider editing screen.
	 * Used by function $this->post_edit_assets.
	 */
	private function load_slider_assets(){
		fa_load_admin_style('slider-edit');
		wp_enqueue_style('media-views');
		$modal = fa_load_admin_script('modal');		
		$handle = fa_load_admin_script('slider-edit', array( $modal, 'jquery-ui-tabs' ,'jquery-ui-sortable', 'jquery' ));
		
		wp_localize_script( $handle, 'faEditSlider', array(
			'assign_slide_wp_nonce' 	=> $this->wp_ajax->get_nonce('assign_slide'),
			'assign_slide_ajax_action' 	=> $this->wp_ajax->get_action('assign_slide'),
			
			'assign_image_nonce' 		=> $this->wp_ajax->get_nonce('assign_images'),	
			'assign_image_ajax_action' 	=> $this->wp_ajax->get_action('assign_images'),	
			
			'rem_slider_default_img' => array(
				'nonce' => $this->wp_ajax->get_nonce('rem_default_slides_image'),
				'action' => $this->wp_ajax->get_action('rem_default_slides_image')
			),
		
			'messages' => array(
				'close_modal' 		=> __('Close', 'fapro'),
				'title_slides' 		=> __('Choose slides', 'fapro'),
				'title_edit_post' 	=> __('Edit slide options', 'fapro'),
				'title_categories' 	=> __('Choose categories', 'fapro')
			)
		));
		
		// Add the action to the footer to output the modal window.
        add_action( 'admin_footer', array( $this, 'tax_selection_modal' ) );
	}
	
	public function tax_selection_modal(){
?>
<div class="fapro-default-ui-wrapper" id="fapro-modal" style="display: none;">
	<div class="fapro-default-ui">
		<div class="media-modal wp-core-ui">
			<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>
			<div class="media-modal-content">
				<div class="media-frame wp-core-ui hide-menu hide-router fapro-meta-wrap">
					<div class="media-frame-title">
						<h1 data-title="<?php echo esc_attr( __('Choose', 'fapro') );?>"><?php _e( 'Choose', 'fapro' ); ?></h1>
					</div>
					<div class="media-frame-content">
		            	<!-- Injected by functions -->   
					</div><!-- .media-frame-content -->
					<div class="media-frame-toolbar">
						<div class="media-toolbar">
							<div class="media-toolbar-secondary">
								<a href="#" class="fapro-cancel-action button media-button button-large button-secondary media-button-insert" title="<?php esc_attr_e( 'Cancel', 'fapro' ); ?>"><?php _e( 'Cancel', 'fapro' ); ?></a>
							</div>
							<div class="media-toolbar-primary">
								<a href="#" class="fapro-make-action button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save', 'fapro' ); ?>"><?php _e( 'Save', 'fapro' ); ?></a>
							</div><!-- .media-toolbar-primary -->
						</div><!-- .media-toolbar -->
					</div><!-- .media-frame-toolbar -->					
				</div><!-- .media-frame -->		     	                                           
			</div><!-- .media-modal-content -->
		</div><!-- .media-modal -->
		<div class="media-modal-backdrop"></div>
	</div><!-- #fapro-default-ui -->
</div><!-- #fapro-default-ui-wrapper -->
<?php 				
	}
	
	/**
	 * Meta box output for slider post type.
	 * Prefix all plugin meta boxes with $this->meta_box_prefix-NAME to avoid having them removed by
	 * function $this->remove_meta_boxes
	 */
	public function register_slider_meta_boxes( $post ){
		// Slide video attachment meta box
		add_meta_box(
			$this->meta_box_prefix . '-slider-settings', 
			__('Slider content', 'fapro'), 
			array( $this, 'meta_box_slider_content' ),
			null,
			'normal'
		);
		// slide theme meta box
		add_meta_box(
			$this->meta_box_prefix . '-slider-theme', 
			__('Slider output', 'fapro'), 
			array( $this, 'meta_box_slider_theme' ),
			null,
			'normal'
		);
		// slide details meta box
		add_meta_box(
			$this->meta_box_prefix . '-slider-options', 
			__('Slider options', 'fapro'), 
			array( $this, 'meta_box_slider_options' ),
			null,
			'side'
		);
		
		// slider PHP code
		add_meta_box(
			$this->meta_box_prefix . '-slider-code', 
			__('Slider PHP code', 'fapro'), 
			array( $this, 'meta_box_slider_code' ),
			null,
			'side'
		);		
		
		// help links meta box
		add_meta_box(
			$this->meta_box_prefix . '-codeflavors-docs',
			__('Docs & Help', 'fapro'  ),
			array( $this, 'meta_box_docs' ),
			null,
			'side',
			'low'
		);
		
		// add the expiration date and other to post submitbox for slider
		add_action('post_submitbox_misc_actions', array( $this, 'slider_submitbox' ));		
	}
	
	public function slider_submitbox(){
		global $post;
		if( !$post || parent::get_type_slider() != $post->post_type ){
			return;
		}

		$options = fa_get_slider_options( $post->ID );
		if( '0000-00-00 00:00:00' != $options['slider']['expires'] ){
			$datef = __( 'M j, Y @ G:i' );
			$date = date_i18n( $datef, strtotime( $options['slider']['expires'] ) );
		}else{
			$date = __('unavailable', 'fapro');
		}
		
		if( parent::get_status_expired() == $post->post_status ){
			$stamp = sprintf( __('Expired on: <b>%s</b>', 'fapro'), $date );
		}else if( '0000-00-00 00:00:00' != $options['slider']['expires'] ){
			$stamp = sprintf( __('Expires on: <b>%s</b>', 'fapro'), $date );
		}else{
			$stamp = __('No expiration date.', 'fapro');			
		}		
?>
<div class="misc-pub-section curtime misc-pub-exptime">
	<span id="exp_timestamp">
	<?php echo $stamp; ?></span>
	<a href="#timestamp_exp_div" class="edit-exp_timestamp hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit slider expiration date', 'fapro' ); ?></span></a>
	<div id="timestamp_exp_div" class="hide-if-js"><?php fa_touch_time();?></div>
</div>
<?php 
	}
	
	/**
	 * Slider content meta box callback function.
	 * @param object $post - current slider post being edited
	 */
	public function meta_box_slider_content( $post ){
		// get the themes
		$themes = fa_get_themes();
		// get the options
		$options = fa_get_slider_options( $post->ID );
		// metabox template
		$template = fa_metabox_path('slider-content');
		include_once $template;
	}
	
	/**
	 * Slider theme meta box callback function
	 * @param object $post - current slider post being edited
	 */
	public function meta_box_slider_theme( $post ){		
		// get the options		
		$options 	= fa_get_slider_options( $post->ID );		
		// get the themes
		$themes 	= fa_get_themes();
		// get the display areas
		$areas = fa_get_options( 'hooks' );
		// load the template
		$template 	= fa_metabox_path('slider-theme');
		include_once $template;
	}
	
	/**
	 * Slider options meta box callback function
	 * @param object $post - current slider post being edited
	 */
	public function meta_box_slider_options( $post ){
		$transient = get_transient( $this->slider_transient_prefix . $post->ID );
		$settings = fa_get_options( 'settings' );
		$options = fa_get_slider_options( $post->ID );
		
		if( $settings['cache'] && $options['slider']['cached'] ){
			$cache_time = __('not cached', 'fapro');
			if( $transient && isset( $transient['time'] ) ){
				$cache_time = __('cache age: ', 'fapro') . human_time_diff( strtotime( $transient['time'] ) ) . ' ';
			}
		}else{
			$cache_time = __('not enabled', 'fapro');			
		}
		if( !$settings['cache'] ){
			$cache_time = __('disabled for all sliders', 'fapro');
		}
		
		// load the template
		$template 	= fa_metabox_path('slider-options');
		include_once $template;
	}
	
	/**
	 * Slider code metabox callback
	 * @param object $post - current slider post being edited
	 */
	public function meta_box_slider_code( $post ){
		$template = fa_metabox_path('slider-code');
		include_once $template;
	}
	
	/**
	 * Display a list of useful links
	 */
	public function meta_box_docs( $post ){
		$links = array(
			array(
				'text' => __( 'How to create sliders', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documentation/featured-articles-pro-3/creating-sliders/'
			),
			array(
				'text' => __( 'Publish sliders above the page loop', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documentation/basic-tutorials/publish-sliders-page-loop/'
			),
			array(
				'text' => __( 'Use the slider shortcode', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documentation/basic-tutorials/how-to-use-the-slider-shortcode/'
			),
			array(
				'text' => __( 'How to use the slider widget', 'fapro' ),
				'url' => 'http://www.codeflavors.com/featured-articles-for-wp/how-to-create-a-wordpress-slider-widget-with-featured-articles/',
			),
			array(
				'text' => __( 'Allow custom post types in sliders', 'fapro' ),
				'url' => 'http://www.codeflavors.com/featured-articles-for-wp/create-wordpress-slider-from-custom-post-type/'
			),
			array(
				'text' => __( 'Allow plugin access based on user roles', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documentation/basic-tutorials/allow-plugin-access-based-user-roles/'
			),
			array(
				'text' => __( 'Store slider themes outside plugin folder', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documentation/intermediate-tutorials/moving-slider-themes-folder/'
			)			
		);
		
		$extra = array(
			array(
				'text' => __( 'Plugin download & license codes', 'fapro' ),
				'url' => 'http://www.codeflavors.com/welcome-to-your-account/downloads-and-licenses/'
			),
			array(
				'text' => __( 'Docs page', 'fapro' ),
				'url' => 'http://www.codeflavors.com/documents/featured-articles-pro-3/'
			),
			array(
				'text' => __( 'Private priority support', 'fapro' ),
				'url' => 'http://www.codeflavors.com/tickets/'
			),
			array(
				'text' => __( 'Plugin Forum', 'fapro' ),
				'url' => 'http://www.codeflavors.com/codeflavors-forums/forum/featured-articles-3-0/'
			)
		);
		
		$campaign = array(
			'utm_source' => 'plugin',
			'utm_medium' => 'doc_link',
			'utm_campaign' => 'fa_pro'
		);
		$q = http_build_query( $campaign );
?>
<?php _e( 'Below is a list of useful tutorials to help you get started.', 'fapro' );?>

<ul>
<?php foreach( $links as $link ):?>
	<li><a href="<?php  echo $link['url'] . '?' . $q;?>" target="_blank" title="<?php esc_attr( $link['text'] );?>"><?php echo $link['text'];?></a></li>
<?php endforeach;?>
</ul>

<?php _e('Other useful links', 'fapro');?>

<ul>
<?php foreach( $extra as $link ):?>
	<li><a href="<?php  echo $link['url'] . '?' . $q;?>" target="_blank" title="<?php esc_attr( $link['text'] );?>"><?php echo $link['text'];?></a></li>
<?php endforeach;?>
</ul>
<?php	
	}
	
	/**
	 * Save slider details. Callback function for action save_post_{post_type}
	 * @param int $post_id
	 * @param object $post
	 * @param bool $update
	 */
	public function save_slider( $post_id, $post, $update ){
		if( !current_user_can('edit_fa_items', $post_id) ){
			wp_die(-1);
		}
		
		// remove the slider transient
		delete_transient( $this->slider_transient_prefix . $post->ID );
		
		// check for the nonce presence
		if( !isset( $_POST['fa-slider-settings-nonce'] ) ){
			return;
		}
		
		check_admin_referer('fa-slider-options-save', 'fa-slider-settings-nonce');
				
		// process expiration date
		$process_date = false;
		foreach ( array('exp_mm', 'exp_dd', 'exp_yy', 'exp_hh', 'exp_ii') as $timeunit ){
			if( $_POST[ $timeunit ] != $_POST[ 'curr_' . $timeunit ] ){
				$process_date = true;
				break;
			}
		}
		// if date should be processed		
		if( $process_date ){
			$date = $_POST['exp_yy'] . '-' . $_POST['exp_mm'] . '-' . $_POST['exp_dd'];
			if( wp_checkdate( $_POST['exp_mm'], $_POST['exp_dd'], $_POST['exp_yy'], $date) ){
				$expiration_date = $date . ' ' . $_POST['exp_hh'] . ':' . $_POST['exp_ii'] . ':' . $_POST['exp_ss'];
				$_POST['slider']['expires'] = $expiration_date;
				// check if currently set up date is less than post date
				if( strtotime( $expiration_date ) < time() && parent::get_status_expired() != $post->post_status ){
					$args = array(
						'ID' => $post_id,
						'post_status' => parent::get_status_expired()
					);
					// remove the action to avoid a loop
					remove_action( 'save_post_' . parent::get_type_slider(), array( $this, 'save_slider' ) );
					wp_update_post( $args );
				}
			}	
		}
		// remove the expiration date if set
		if( isset( $_POST['exp_ignore'] ) ){
			$_POST['slider']['expires'] = '0000-00-00 00:00:00';
		}

		// do not allow no post type specified for posts
		if( !isset( $_POST['slides']['post_type'] ) ){
			$_POST['slides']['post_type'][] = 'post';
		}

		// allow no categories specified (allow all categories if none specified)
		if( !isset( $_POST['slides']['tags'] ) ){
			$_POST['slides']['tags'] = array();
		}
		
		// allow empty content on mixed posts
		if( !isset( $_POST['slides']['posts'] ) ){
			$_POST['slides']['posts'] = array();
		}
		// allow empty content on images
		if( !isset( $_POST['slides']['images'] ) ){
			$_POST['slides']['images'] = array();
		}
		// set the slider color
		if( isset( $_POST['theme']['active'] ) ){
			$theme = $_POST['theme']['active'];
			
			// process the layout variation if available
			if( isset( $_POST['layout']['class'][ $theme ] ) ){
				$_POST['layout']['class'] = $_POST['layout']['class'][ $theme ];
			}else{
				$_POST['layout']['class'] = '';
			}			
			// set the color
			if( isset( $_POST['theme_color'][ $theme ] ) ){
				$_POST['theme']['color'] = $_POST['theme_color'][ $theme ];
			}else{
				$_POST['theme']['color'] = '';
			}
		}
		// allow empty on display categories
		if( !isset( $_POST['display']['tax'] ) ){
			$_POST['display']['tax'] = array();
		}
		// allow empty on display posts
		if( !isset( $_POST['display']['posts'] ) ){
			$_POST['display']['posts'] = array();
		}
		
		// process the publish areas
		$areas = fa_get_options('hooks');
		$set = isset( $_POST['slider_area'] ) ? $_POST['slider_area'] : array();
		foreach( $areas as $area_id => $area ){
			if( in_array( $area_id, $set ) ){
				if( !in_array( $post_id, $area['sliders'] ) ){
					$areas[ $area_id ]['sliders'][] = $post_id;
				}				
			}else{
				if( in_array( $post_id , $area['sliders']) ){
					$key = array_search( $post_id , $area['sliders'] );
					if( false !== $key ){
						unset( $areas[ $area_id ]['sliders'][ $key ] );
					}	
				}
			}			
		}
		fa_update_options( 'hooks' , $areas );
		
		// update the slider options
		fa_update_slider_options( $post_id, $_POST );
		
		/**
		 * Action on slider save
		 * @param int $post_id - ID of post being saved
		 * @param object $post - object of post being saved
		 * @param bool $update - is update or new post
		 * @param array - values send from form
		 */
		do_action('fa-save-slider', $post_id, $post, $update, $_POST);
	}
	
	/**
	 * Store the options on slider revision. The revisions are created whenever a
	 * preview is triggered by user on published sliders.
	 * 
	 * @param int $post_id
	 * @param object $post
	 * @param bool $update
	 */
	public function save_slider_revisions( $revision_id, $revision, $update ){
		global $post;
		if( !$post || parent::get_type_slider() != $post->post_type ){
			return;
		}
		// confirm that revision belongs to current post
		if( $revision->post_parent != $post->ID ){
			return;			
		}
		
		// check for the nonce presence
		if( !isset( $_POST['fa-slider-settings-nonce'] ) ){
			return;
		}
		check_admin_referer('fa-slider-options-save', 'fa-slider-settings-nonce');
				
		// do not allow no post type specified for posts
		if( !isset( $_POST['slides']['post_type'] ) ){
			$_POST['slides']['post_type'][] = 'post';
		}

		// allow no categories specified (allow all categories if none specified)
		if( !isset( $_POST['slides']['tags'] ) ){
			$_POST['slides']['tags'] = array();
		}
		
		// allow empty content on mixed posts
		if( !isset( $_POST['slides']['posts'] ) ){
			$_POST['slides']['posts'] = array();
		}
		// allow empty content on images
		if( !isset( $_POST['slides']['images'] ) ){
			$_POST['slides']['images'] = array();
		}
		// set the slider color
		if( isset( $_POST['theme']['active'] ) ){
			$theme = $_POST['theme']['active'];
			
			// process the layout variation if available
			if( isset( $_POST['layout']['class'][ $theme ] ) ){
				$_POST['layout']['class'] = $_POST['layout']['class'][ $theme ];
			}else{
				$_POST['layout']['class'] = '';
			}			
			// set the color
			if( isset( $_POST['theme_color'][ $theme ] ) ){
				$_POST['theme']['color'] = $_POST['theme_color'][ $theme ];
			}else{
				$_POST['theme']['color'] = '';
			}
		}
		// allow empty on display categories
		if( !isset( $_POST['display']['tax'] ) ){
			$_POST['display']['tax'] = array();
		}
		// allow empty on display posts
		if( !isset( $_POST['display']['posts'] ) ){
			$_POST['display']['posts'] = array();
		}
		
		fa_update_slider_options( $revision_id , $_POST );		
	}
	
	/**
	 * Callback on hook save_post. Detects images in post content
	 * to be used as slide image. Applies only for post types allowed from plugin settings.
	 */
	public function detect_image( $post_id, $post, $update ){
		// no autodectected images for custom slides
		if( parent::get_type_slide() == $post->post_type ){
			return;
		}
		
		// no autodetect for post types not allowed from plugin settings
		$allowed = fa_allowed_post_types();
		if( !in_array( $post->post_type , $allowed) || 'trash' == $post->post_status ){
			return;
		}
		
		// scan content for image
		$image = $this->find_image_in_post_content( $post->post_content );
		if( $image['id'] || $image['img'] ){
			$options = fa_get_slide_options( $post_id );
			$options['temp_image_id'] = $image['id'];
			$options['temp_image_url'] = $image['url'];
			fa_update_slide_options( $post_id , $options );
		}		
	}
	
	/**
	 * Scans a given post content for images.
	 * 
	 * @param string $content - the post content to be scanned
	 * @return array
	 */
	private function find_image_in_post_content( $content ){
		return fa_detect_image( $content );
	}
	
	/**
	 * Create extra columns on slider display table for administrators.
	 * 
	 * @param array $columns
	 */
	public function extra_slider_columns( $columns ){
		$columns = array(
			'cb' 		=> $columns['cb'], 
			'title' 	=> $columns['title'], 
			'content'	=> __('Content Type', 'fapro'),
			'theme'		=> __('Slider Theme', 'fapro'),
			'auto_display' => __('Display on', 'fapro'),
			'author' 	=> __('Author', 'fapro'),
			'date' 		=> $columns['date']
		);
		
		fa_load_admin_style( 'list-tables' );
		
		return $columns;
	}
	
	/**
	 * Output the extra columns data
	 * 
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function output_extra_slider_columns(  $column_name, $post_id  ){
		switch( $column_name ){
			// output slider content type (latest posts, mixed content, images)
			case 'content':
				$options = fa_get_slider_options( $post_id, 'slides' );
				switch( $options['type'] ){
					case 'post':
						$order = 'latest';
						if( 'comments' == $options['orderby'] ){
							$order = __('most commented', 'fapro');
						}else if( 'random' == $options['orderby'] ){
							$order = __('random', 'fapro');
						}
						// 5 (recent|most commented|random) posts from 
						printf( __('%d %s posts', 'fapro'), $options['limit'], $order );
					break;
					case 'mixed':
						$count = count( $options['posts'] );
						printf( __('%d manually selected posts', 'fapro'), $count );
					break;
					case 'image':
						$count = count( $options['images'] );
						printf( __('%d manually selected images', 'fapro'), $count );
					break;	
				}				
			break;	
			case 'theme':
				$options = fa_get_slider_options( $post_id, 'theme' );
				$name = isset( $options['details']['theme_config']['name'] ) ? $options['details']['theme_config']['name'] : ucfirst( $options['active'] );
				printf( __('%s', 'fapro'), $name );
			break;	
			case 'auto_display':
				$options = fa_get_slider_options( $post_id, 'display' );
				$output = array();
				if( $options['everywhere'] ){
					$output[] = __('Everywhere', 'fapro');
				}else{
					if( $options['home'] ){
						$output[] = __( 'Homepage', 'fapro' );
					}
					
					if( $options['all_pages'] ){
						$output[] = __( 'All single post/pages', 'fapro' );
					}else{
						if( $options['posts'] ){
							$count = 0;
							foreach( $options['posts'] as $posts ){
								$count += count( $posts );
							}					
							$output[] = sprintf( __( '%d posts/pages', 'fapro' ), $count );
						}
					}

					if( $options['all_categories'] ){
						$output[] = __( 'All archive pages', 'fapro' );
					}else{
						if( $options['tax'] ){
							$count = 0;
							foreach( $options['tax'] as $categories ){
								$count += count( $categories );
							}					
							$output[] = sprintf( __( '%d category pages', 'fapro' ), $count );
						}
					}						
				}
				
				if( $output ){
					echo implode(', ', $output);
				}else{
					echo '-';
				}				
				
			break;	
		}		
	}
	
	
	/**
	 * Load JavaScript needed on slide editing screen.
	 * Used by function $this->post_edit_assets.
	 */
	private function load_slide_assets(){
		fa_load_admin_style('slide-edit');
		$video_handle = fa_load_script( 'video-player2', array( 'jquery' ) );
		/**
		 * Video player script action. Allows third party plugins to load
		 * other assets.
		 */
		do_action( 'fa_embed_video_script_enqueue' );
		
		$handle = fa_load_admin_script( 'slide-edit', array( 'jquery', $video_handle ) );
		
		wp_localize_script( $handle, 'faEditSlide', array(
			'id_prefix' 	=> $this->meta_box_prefix, // meta boxes prefix
			'wp_nonce' 		=> $this->wp_ajax->get_nonce('video_query'),
			'ajax_action' 	=> $this->wp_ajax->get_action('video_query'),	
			
			'remove_video_nonce' 		=> $this->wp_ajax->get_nonce('remove_video'),
			'remove_video_ajax_action' 	=> $this->wp_ajax->get_action('remove_video'),
			
			/*
			'image_nonce' 	=> $this->wp_ajax->get_nonce('import_image'),
			'image_action' 	=> $this->wp_ajax->get_action('import_image'),	
			*/	
		
			'remove_image_nonce' 	=> $this->wp_ajax->get_nonce('remove_slide_image'),
			'remove_image_action' 	=> $this->wp_ajax->get_action('remove_slide_image'),
		
			'messages' => array(
				'empty_video_query' => __('Please select source and enter video ID.', 'fapro'),
				'loading_video' 	=> __('Querying for video ...', 'fapro'),
				'querying_video'	=> __('Not done yet, please wait...', 'fapro'),
				'query_error' 		=> __('There was an error, please try again', 'fapro'),
				'removing_video'	=> __('Removing video ...', 'fapro')
			)	
		));

		wp_localize_script( $handle, 'faEditSlider', array(
			'assign_image_nonce' 		=> $this->wp_ajax->get_nonce('assign_slide_image'),	
			'assign_image_ajax_action' 	=> $this->wp_ajax->get_action('assign_slide_image'),			
		));
		
	}	
	
	/**
	 * Save post action callback. Stores the slide options on post meta. 
	 *
	 * @param int $post_id
	 * @param object $post
	 * @param bool $update
	 */
	public function save_slide( $post_id, $post, $update ){
		if( !current_user_can('edit_fa_items', $post_id) ){
			return;
		}
		
		// when saving post, check if the post is set on mixed content for sliders.
		// if it is, remove the slider cache to force it to update the contents
		if( $update ){
			$args = array(
				'post_type' 	=> parent::get_type_slider(),
				'fields' 		=> 'ids',
				'post_status' 	=> 'publish',
				'nopaging'		=> true,
				'posts_per_page'=> -1
			);
			$query = new WP_Query( $args );
			if( $query->posts ){
				foreach( $query->posts as $slider_id ){
					$options = fa_get_slider_options( $slider_id );	
					if( in_array( $post_id, $options['slides']['posts'] ) ){
						delete_transient( $this->slider_transient_prefix . $slider_id );
					}			
				}
			}						
		}
		
		// check for the nonce presence
		if( !isset( $_POST['fa-slide-settings-nonce'] ) ){
			return;
		}
		
		if( !isset( $_POST['fa_slide']['url'] ) || empty( $_POST['fa_slide']['url'] ) ){
			$_POST['fa_slide']['link_to_post'] = 1;			
		}
		
		check_admin_referer('fa-slide-options-save', 'fa-slide-settings-nonce');
		
		fa_update_slide_options( $post_id, $_POST['fa_slide'] );
		
		/**
		 * Action on slide save
		 * @param int $post_id - ID of post being saved
		 * @param object $post - object of post being saved
		 * @param bool $update - is update or new post
		 */
		do_action('fa-save-slide', $post_id, $post, $update);
	}
	
	/**
	 * Add extra columns to admin slides table
	 * @param array $columns
	 */
	public function extra_slide_columns( $columns ){
		
		$tax_col = 'taxonomy-' . parent::get_slide_tax();		
		$columns = array(
			'cb' 		=> $columns['cb'],
			'title'		=> $columns['title'],
			'video'		=> __('Video', 'fapro'),	
			'image'		=> __('Image', 'fapro'),
			$tax_col 	=> $columns[ $tax_col ],
			'author' 	=> $columns['author'],
			'date' 		=> $columns['date']
		);
		
		fa_load_admin_style( 'list-tables' );
		
		return $columns;
	}
	
	/**
	 * Output extra columns for slides table
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function output_extra_slide_columns( $column_name, $post_id ){
		switch( $column_name ){
			case 'video':
				$options = fa_get_slide_options( $post_id );
				if( empty( $options['video']['source'] ) || empty( $options['video']['video_id'] ) ){
					echo '&#8212;';
				}		

				$url = fa_video_url( $options['video']['video_id'], $options['video']['source'] );
				printf( '<a href="%s" target="fa_view_on_vimeo">%s</a>', ( $url ? $url : '#' ), ucfirst( $options['video']['source'] ) );				
			break;
			case 'image':
				$options = fa_get_slide_options( $post_id );
				
				$img_url = '';
				if( !empty( $options['image'] ) ){
					$attachment = wp_get_attachment_image_src( $options['image'], 'thumbnail' );
					if( $attachment && !is_wp_error( $attachment ) ){
						$img_url = $attachment[0];
					}
				}else{
					$attachment = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ) );
					if( $attachment && !is_wp_error( $attachment ) ){
						$img_url = $attachment[0];
					}				
				}
				
				if( empty( $img_url ) ){
					echo '&#8212;';
					return;
				}
				
				printf( '<img src="%s" height="100" />', $img_url );
				
			break;	
		}		
	}
	
	/**
	 * Edit attachment callback function.
	 * Removes the cache of sliders if image is found into the contents of sliders.
	 * This forces the slider to regenerate the contents.
	 * 
	 * @param $post_id
	 */
	public function edit_attachment( $post_id ){
		if( !current_user_can('edit_fa_items', $post_id) ){
			return;
		}
		
		// when saving attachments, check if the attachment is set on image content for sliders.
		// if it is, remove the slider cache to force it to update the contents		
		$args = array(
			'post_type' 	=> parent::get_type_slider(),
			'fields' 		=> 'ids',
			'post_status' 	=> 'publish',
			'nopaging'		=> true,
			'posts_per_page'=> -1
		);
		$query = new WP_Query( $args );
		if( $query->posts ){
			foreach( $query->posts as $slider_id ){
				$options = fa_get_slider_options( $slider_id );	
				if( in_array( $post_id, $options['slides']['images'] ) ){
					delete_transient( $this->slider_transient_prefix . $slider_id );
				}			
			}
		}				
	}
	
	/**
	 * Edit image callback function.
	 * Removes the cache of sliders if image is found into the contents of sliders.
	 * This forces the slider to regenerate the contents.
	 * 
	 * @uses $this->edit_attachment()
	 */
	public function save_image( $null, $filename, $image, $mime_type, $post_id ){
		$this->edit_attachment($post_id);
		return $null;
	}
	
	/**
	 * Registers plugin meta boxes on allowed post types to be used in FA sliders.
	 */
	public function register_posts_meta_boxes( $post_type, $post ){
		// display only on allowed post types
		$post_types = fa_allowed_post_types();
		if( !in_array( $post_type  ,  $post_types ) ){
			return;
		}
		
		// if not allowed to edit slide on post edit, return true only for post type slide
		if( !fa_allowed_slide_edit() && parent::get_type_slide() != $post_type ){
			return;
		}
		
		// Slide video attachment meta box
		add_meta_box(
			$this->meta_box_prefix . '-slide-settings', 
			__('Featured Articles PRO - slide settings', 'fapro'), 
			array( $this, 'meta_box_slide_settings' ));
		
		// Slide video attachment meta box
		add_meta_box(
			$this->meta_box_prefix . '-slide-video-query', 
			__('Featured Articles PRO - video', 'fapro'), 
			array( $this, 'meta_box_slide_video' ),
			null,
			'side');
		
		// allow help meta box only on custom post type slide
		if( parent::get_type_slide() == $post_type ){
			// help links meta box
			add_meta_box(
				$this->meta_box_prefix . '-codeflavors-docs',
				__('Docs & Help', 'fapro'  ),
				array( $this, 'meta_box_docs' ),
				null,
				'side',
				'low'
			);	
		}			
	}
	
	/**
	 * Slide settings meta box callback. Allows setting link more text, url and others
	 * @param object $post - current post being edited
	 */
	public function meta_box_slide_settings( $post ){
		// get the slide options
		$options = fa_get_slide_options( $post->ID );
		
		$template = fa_metabox_path('slide-settings');
		include_once $template;
	}
	
	/**
	 * Slide video attachment meta box. Allows attaching video content to slides.
	 * @param object $post - current post being edited
	 */
	public function meta_box_slide_video( $post ){
		// get the slide options
		$options = fa_get_slide_options( $post->ID );
		
		$template = fa_metabox_path('slide-video-query');
		include_once $template;
	}
	
	/**
	 * Load callback for dynamic areas page
	 */
	public function on_dynamic_slider_areas_load(){
		if( isset( $_REQUEST['action'] ) ){
			switch( $_REQUEST['action'] ){
				case 'delete':
					if( !check_admin_referer( 'fa_remove_dynamic_area', 'fa_nonce' ) ){
						wp_die( 'Cheatin&#8217; uh?' );
					}
					
					$area = isset( $_GET['area'] ) ? fa_sanitize_hook_id( $_GET['area'] ) : false;
					$areas = fa_get_options( 'hooks' );
					if( array_key_exists( $area , $areas ) ){
						unset( $areas[ $area ] );
						fa_update_options( 'hooks', $areas );
					}
					
					$url = html_entity_decode( menu_page_url( 'fapro_hooks', false ) );
					wp_redirect( $url );
					die();					
				break;	// case 'delete'
				case 'create':
					if( !check_admin_referer( 'fa_create_dynamic_area', 'fa_area_nonce' ) ){
						wp_die( __('Something went wrong. Hit back button in your browser and try again.', 'fapro' ) );
					}
					
					$hook_name = !empty( $_POST['hook_name'] ) ? fa_sanitize_hook_id( $_POST['hook_name'] ) : false;
					if( $hook_name ){
						$description 	= sanitize_text_field( $_POST['hook_description'] );
						$name 			= sanitize_text_field( $_POST['hook_name'] );
						$settings = fa_get_options( 'hooks' );
						$settings[ $hook_name ] = array(
							'name' => $name,
							'description' => $description,
							'sliders' 		=> array()
						);						
						fa_update_options( 'hooks', $settings );
					}
					
					$url = html_entity_decode( menu_page_url( 'fapro_hooks', false ) );
					wp_redirect( $url );
					die();
				break; // case 'create'
				case 'edit':
					if( !check_admin_referer( 'fa_create_dynamic_area', 'fa_area_nonce' ) ){
						wp_die( __('Something went wrong. Hit back button in your browser and try again.', 'fapro' ) );
					}
					$area 	= fa_sanitize_hook_id( $_POST['area'] );
					$areas 	= fa_get_options( 'hooks' );
					if( array_key_exists( $area , $areas ) ){
						$description 	= sanitize_text_field( $_POST['hook_description'] );
						$name 			= sanitize_text_field( $_POST['hook_name'] );
						$areas[ $area ] = array(
							'name' => $name,
							'description' => $description,
							'sliders' => $areas[ $area ]['sliders']
						);						
						fa_update_options( 'hooks', $areas );
					}
					$url = html_entity_decode( menu_page_url( 'fapro_hooks', false ) );
					wp_redirect( $url );
					die();
					
				break; // case 'edit'	
			}			
		}
		
		
		fa_load_admin_style('dynamic-areas');
		$handle = fa_load_admin_script(
			'dynamic-areas', 
			array( 
				'jquery', 
				'jquery-ui-sortable', 
				'jquery-ui-draggable', 
				'jquery-ui-droppable' 
			)
		);
		
		wp_localize_script($handle, 'fa_dynamic_areas', array(
			'assign_action' => $this->wp_ajax->get_action('assign_to_area'),
			'assign_nonce' 	=> $this->wp_ajax->get_nonce('assign_to_area'),
			'messages' => array(
				'delete_confirm' => __("Are you sure you want to delete this area? \nAny sliders published in this area will stop being displayed on your website.", 'fapro')
			)		
		));
	}
	
	/**
	 * Dynamic slider areas admin page
	 */
	public function dynamic_slider_areas(){
		$areas = fa_get_options( 'hooks' );
		$template = fa_template_path('dynamic-slider-areas');
		
		$name 			= '';
		$description 	= '';
		
		if( isset( $_GET['edit'] ) ){
			$edit_area = array_key_exists( $_GET['edit'] , $areas) ? $areas[ $_GET['edit'] ] : false;
			if( !$edit_area ){
				unset( $edit_area );
			}else{
				$name = $edit_area['name'];
				$description = $edit_area['description'];
				$area_edited = $_GET['edit'];
			}
		}
		
		include_once $template;
	}
	
	/**
	 * Themes edit page load callback
	 */
	public function on_themes_edit_load(){
		if( isset( $_POST['fa_nonce'] ) ){
			if( check_admin_referer( 'fa-theme-editor-preview-options-save', 'fa_nonce' ) ){
				fa_update_options( 'theme_editor', $_POST );				
				$url = add_query_arg( array( 'message' => 701 ) , html_entity_decode( menu_page_url( 'fapro_themes', false ) ) );
				wp_redirect( $url );
				die();
			}			
		}
		$handle = fa_load_admin_script('themes-edit', array( 'jquery-ui-tabs' , 'jquery' ) );		
		fa_load_admin_style('themes-edit');		
	}
	
	/**
	 * Themes color editor admin page
	 */
	public function themes_manager(){
		$themes = fa_get_themes();	
		$options = fa_get_options( 'theme_editor' );	
		$template = fa_template_path('themes');
		include_once $template;
	}
	
	/**
	 * Theme customizer page load callback.
	 * Prevent WP admin to load the default header and footer.
	 */
	public function on_theme_customizer_load(){
		$_GET['noheader'] = true;
		if( !defined( 'IFRAME_REQUEST' ) ){
			define( 'IFRAME_REQUEST', true );
		}
		
		// Add the action to the footer to output the modal window.
        add_action( 'admin_footer', array( $this, 'tax_selection_modal' ) );
		
		// if saving action wasn't triggered, stop
		if( !isset( $_POST['fa_nonce'] ) ){
			return;
		}
		// check referer
		if( !check_admin_referer('fa-save-color-scheme', 'fa_nonce') ){
			wp_die( __( 'Nonce error, please try again.', 'fapro') );
		}
		
		require_once fa_get_path('includes/admin/libs/class-fa-theme-editor.php');
		$editor = new FA_Theme_Editor( $_POST['theme'] );
		if( !isset( $_GET['color'] ) || empty( $_GET['color'] ) ){
			$edit = false;			
		}else{
			$edit = $_GET['color'];
		}
		
		$result = $editor->save_color_scheme( $_POST['color_name'] , $edit, $_POST );
		
		if( is_wp_error( $result ) ){
			$message = $result->get_error_message();
			wp_die( $message );				
		}else{
			$redirect = add_query_arg( array(
				'theme' => $_POST['theme'],
				'color' => $result
			), menu_page_url('fa-theme-customizer', false));
			wp_redirect( $redirect );
			die();
		}				
	}
	
	/**
	 * Theme color customizer admin page callback
	 */
	public function theme_customizer(){
		
		require_once fa_get_path('includes/admin/libs/class-fa-theme-editor.php');
		$editor = new FA_Theme_Editor( $_GET['theme'] );
		if( !isset( $_GET['color'] ) || empty( $_GET['color'] ) ){
			$values = $editor->get_default_color_rules( $_GET['theme'] );
		}else {
			$values = $editor->get_stylesheet_color_rules( $_GET['color'] );
		}
		
		// if something went wrong, bail out
		if( is_wp_error( $values ) ){
			$message =  $values->get_error_message() . '<br />';
			$message.= sprintf( '<a href="%s">%s</a>', menu_page_url( 'fapro_themes', false ), __( 'Back to slider themes', 'fapro' ) ); 
			wp_die( $message );
		}
		
		$style_rules = $editor->get_theme_styles( $_GET['theme'] );		
		$theme = $editor->get_the_theme( $_GET['theme'] );
		
		// add styles and scripts		
		wp_enqueue_style('customize-controls');
		wp_enqueue_script('accordion');
		$handle = fa_load_admin_script( 'theme-editor', array( 'jquery', 'jquery-effects-shake' ) );
		wp_localize_script( $handle, 'faEditSlider', array(
			'assign_image_nonce' 		=> $this->wp_ajax->get_nonce('assign_theme_image'),	
			'assign_image_ajax_action' 	=> $this->wp_ajax->get_action('assign_theme_image'),			
		));
		
		fa_load_admin_style('theme-editor');
		
		if( isset( $_GET['color'] ) && !empty( $_GET['color'] ) ){
			$theme_color = $_GET['color'];			
		}
		
		$template = fa_template_path('theme-editor');
		include_once $template;
		
		// the template displays without admin header/footer, exit after including it
		die();
	}
	
	/**
	 * Callback function on settings page load
	 */
	public function on_page_settings_load(){
		
		if( isset( $_POST['fa_nonce'] ) && check_admin_referer('fapro_save_settings', 'fa_nonce') ){
			if( !current_user_can('manage_options') ){
				wp_die( __('Sorry, you are not allowed to do this.', 'fapro'), __('Access denied', 'fapro') );
			}
			
			// update general settings
			$result = fa_update_options('settings', $_POST);
			if( is_wp_error( $result ) ){
				$this->errors = $result;
			}			
			// register license key
			$result = fa_update_options('license', array( 'license_key' => $_POST['license_key'] ));
			if( is_wp_error( $result ) ){
				$this->errors = $result;
			}				
			// save API keys
			$result = fa_update_options('apis' , $_POST);
			if( is_wp_error( $result ) ){
				$this->errors = $result;
			}
			
			$this->save_caps();
			if( !$this->errors && !is_wp_error( $this->errors ) ){
				$url = add_query_arg( array('message' => 801) , html_entity_decode( menu_page_url( 'fapro_settings', false ) ) );
				wp_redirect( $url );
				die();				
			}						
		}
		
		fa_load_template_style( 'settings' );
		fa_load_admin_script( 'tabs', array('jquery', 'jquery-ui-tabs') );		
	}
	
	/**
	 * Save capabilities
	 */
	private function save_caps(){
		if( isset( $_POST['fa_nonce'] ) ){
			if( check_admin_referer('fapro_save_settings', 'fa_nonce') ){
				if( !current_user_can('manage_options') ){
					wp_die( __('Sorry, you are not allowed to do this.', 'fapro'), __('Access denied', 'fapro') );
				}

				// get roles
				global $wp_roles;				
				$roles = $wp_roles->get_names();
				// get plugin capabilities
				$capabilities = parent::get_caps();
				// remove administrator and subscriber roles
				unset( $roles['administrator'] );		
				// remove capabilities
				foreach( $roles as $role => $name ){
					$r = get_role( $role );
					foreach( $capabilities as $cap ){
						$r->remove_cap($cap);
					}
				}
				unset( $roles['subscriber'] );
				
				if( isset( $_POST['caps'] ) ){
					// allow capabilities for given roles
					foreach( $roles as $role => $name ){
						$r = get_role( $role );
						if( isset( $_POST['caps'][ $role ] ) ){
							foreach( $capabilities as $cap ){
								if( isset( $_POST['caps'][ $role ][ $cap ] ) ){
									$r->add_cap($cap);
								}else{
									$r->remove_cap($cap);
								}	
							}							
						}else{
							foreach( $capabilities as $cap ){
								$r->remove_cap($cap);
							}
						}
					}					
				}// end if				
			}// end if check admin referer			
		}// end if		
	}
	
	/**
	 * Plugin settings admin page
	 */
	public function page_settings(){
		// get user roles
		global $wp_roles;
		$roles = $wp_roles->get_names();
		// remove administrator and subscriber
		unset( $roles['administrator'], $roles['subscriber'] );
		// get the plugin settings
		$settings = fa_get_options('settings');	
		// get CodeFlavors license key details
		$license = fa_get_options('license');	
		// get any available API keys
		$api_keys = fa_get_options('apis');
		// load the template
		$template = fa_template_path( 'settings' );
		include_once $template;
	}
	
	/**
	 * Display any errors returned by the plugin
	 */
	public function show_errors(){
		if( !is_wp_error( $this->errors ) ){
			return;
		}
		$codes = $this->errors->get_error_codes();				
?>
<div class="error">
	<p>
	<?php foreach ($codes as $code):?>
		<?php echo $this->errors->get_error_message( $code );?><br />
	<?php endforeach;?>
	</p>
</div>
<?php	
	}// show_errors()
	
	/**
	 * Adds tinyce plugins to editor
	 */
	public function tinymce(){
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
	 	
		// Don't load unless is post editing (includes post, page and any custom posts set)
		$screen = get_current_screen();
		if( 'post' != $screen->base || $this->get_type_slide() == $screen->post_type ){
			return;
		}  

		// Add only in Rich Editor mode
		if ( version_compare( get_bloginfo( 'version' ) , '4', '>=' ) && get_user_option('rich_editing') == 'true') {
	   		add_filter('mce_external_plugins', array( $this, 'tinymce_plugins' ) );
		    add_filter('mce_buttons', array( $this, 'tinyce_buttons' ) );
		    add_filter('mce_css', array( $this, 'tinymce_css' ) );
	   }
	}
	
	/**
	 * Filter mce_buttons callback.
	 */
	public function tinyce_buttons( $mce_buttons ){
		array_push( $mce_buttons, 'separator', 'fa_slider' );
		return $mce_buttons;
	}
	
	/**
	 * Filter mce_external_plugins callback function.
	 */
	public function tinymce_plugins( $plugin_array ) {
		$plugin_array['fa_slider'] = fa_tinymce_plugin_url( 'fa_slider' );
		return $plugin_array;
	}
	
	/**
	 * Filter mce_css callback function.
	 */
	public function tinymce_css( $css ){
		$css .= ',' . fa_tinymce_plugin_style( 'fa_slider' );
		return $css;
	}
	
	/**
	 * Add tinyMce plugin translations
	 */
	public function tinymce_languages( $locales ){
		$locales['fa_slider'] = fa_get_path( 'assets/admin/js/tinymce/fa_slider/langs/langs.php' );
		return $locales;
	}
	
	/**
	 * Register alternative meta box for WP version prior to WP 4
	 * @param string $post_type
	 * @param object $post
	 */
	public function register_slider_shortcode_meta_box( $post_type, $post ){
		if( parent::get_type_slider() == $post_type || parent::get_type_slide() == $post_type ){
			return;
		}
		
		// Slide video attachment meta box
		add_meta_box(
			$this->meta_box_prefix . '-slider-shortcode', 
			__('Featured Articles PRO - slider shortcode', 'fapro'), 
			array( $this, 'meta_box_slider_shortcode' ),
			null,
			'side'
		);
		
		fa_load_admin_script( 'insert-shortcode' );
	}
	
	/**
	 * Meta box callback for slider shortcode
	 * @param object $post
	 */
	public function meta_box_slider_shortcode( $post ){
		// output the sliders in variable
	    $sliders = fa_get_sliders('publish');
	    $options = array();
	    foreach( $sliders as $slider ){
	    	$text = empty( $slider->post_title ) ? '(' . __('no title', 'fapro') . ')' : esc_attr( $slider->post_title );
	    	$options[] = '<option value="' . $slider->ID . '">' . $text . ' (#' . $slider->ID . ')</option>';
	    }
?>
<label for="fa-slider-shortcode"><?php _e( 'Select slider', 'fapro' );?> :
<?php if( $options ):?>
<select id="fa-slider-shortcode" name="fa-slider-shortcode">
	<option value=""><?php _e( 'Select', 'fapro' );?></option>
	<?php echo implode( "\n", $options );?>
</select>
</label>
<p style="text-align:right;"><input type="button" id="fa-insert-shortcode" value="<?php _e('Insert shortcode', 'fapro');?>" class="button" /></p>
<?php else:?>
	<em><?php _e( 'No published sliders found', 'fapro' );?></em>
</label>
<?php endif;?>
<?php		
	}
	
	/**
	 * Callback for filter enter_title_here that controls the label on post edit screen
	 * @param string $label
	 * @param object $post
	 */
	public function post_title_label( $label, $post ){
		switch( $post->post_type ){
			// slide edit title label
			case parent::get_type_slide():
				return __('Enter slide title', 'fapro');
			break;
			// slider edit title label
			case parent::get_type_slider():
				return __('Slider title', 'fapro');
			break;
			// return the default label
			default:
				return $label;
			break;
		}
	}
	
	/**
	 * Allows hooking to action fa_admin_notices to display notices on plugin pages.
	 */
	public function admin_notices(){
		if( !isset( $_GET['post_type'] ) || ( parent::get_type_slider() != $_GET['post_type'] && parent::get_type_slide() != $_GET['post_type'] ) ){
			return;
		}
		/**
		 * Action that allows hooking to it to display admin notices only on plugin pages.
		 * @param null
		 */
		do_action( 'fa_admin_notices' );
	}
	
	/**
	 * Display an admin notice on plugin pages if license key is empty
	 */
	public function fa_admin_notices(){
		$settings = fa_get_options( 'license' );
		if( empty( $settings['license_key'] ) ){
		?>
		<div class="error">
			<p><?php _e('You are using the PRO version of this plugin. Please enter your CodeFlavors plugin license key. License can be entered on Settings page under tab License.', 'fapro');?></p>
		</div>
		<?php
			return;
		}
		
		$details = fa_check_details();
		if( is_wp_error( $details ) ){			
		?>
		<div class="error">
			<p><?php printf( __('While checking for plugin updates, we encountered this error: %s', 'fapro'), $details->get_error_message() );?></p>
		</div>
		<?php 
		}else if( 1 != $details['ok'] ){
		?>
		<div class="error">
			<p><?php _e('Your <strong>Featured Articles PRO Wordpres plugin</strong> license is invalid. For clarifications on why this is happening, <a href="http://www.codeflavors.com/contact/" target="_blank">please contact us</a>.');?></p>
		</div>	
		<?php	
		}else if( version_compare( FA_VERSION, $details['version'], '<' ) ){
			
			
			
		?>
		<div class="updated">
			<p><?php _e('A new update for <strong>Featured Articles PRO</strong> has been released. If not available already, you should soon be able to automatically update the plugin from your Plugins page.', 'fapro');?></p>
		</div>
		<?php
		}	
	}
	
	/**
	 * Add meta description to plugin row in plugins page
	 * @param array $meta
	 * @param string $file
	 */
	public function plugin_meta( $meta, $file ){
		// add Settings link to plugin actions
		$plugin_file = plugin_basename( FA_PATH . '/index.php' );
		// check if FA Lite is installed and disable activate link
		$lite_file = str_replace( 'pro-3', 'lite', $plugin_file);
		
		if( $file == $lite_file ){
			$meta[] = '<span class="file-error">' . __("You can't activate Lite while Featured Articles PRO is active.", 'fapro') . '</span>';			
		}
		
		// check if 2.X version of the plugin is installed and recommet removal
		$files = array(
			'pro' 	=> str_replace( array( '-3', 'index.php' ), array( '', 'main.php' ), $plugin_file ),
			'lite' 	=> str_replace( array( 'pro-3', 'index.php' ), array( 'lite', 'main.php' ), $plugin_file )
		);
		if( in_array( $file, $files ) ){
			$meta[] = '<span class="file-error">' . __("You should remove this version of the plugin (<strong>do not delete the data</strong> when removing the plugin).", 'fapro') . '</span>';
		}
		
		return $meta;
	}
	
	/**
	 * Add extra actions links to plugin row in plugins page
	 * @param array $links
	 * @param string $file
	 */
	public function plugin_action_links( $links, $file ){
		// add Settings link to plugin actions
		$plugin_file = plugin_basename( FA_PATH . '/index.php' );
		if( $file == $plugin_file ){
			$links[] = sprintf( '<a href="%s">%s</a>', menu_page_url( 'fapro_settings' , false), __('Settings', 'fapro') );
		}
		
		// check if FA Lite is installed and disable activate link
		$lite_file = str_replace( 'pro-3', 'lite', $plugin_file);
		if( $file == $lite_file ){
			unset( $links['activate'] );
		}		
		
		// check if 2.X version of the plugin is installed
		$files = array(
			'pro' 	=> str_replace( array( '-3', 'index.php' ), array( '', 'main.php' ), $plugin_file ),
			'lite' 	=> str_replace( array( 'pro-3', 'index.php' ), array( 'lite', 'main.php' ), $plugin_file )
		);
		if( in_array( $file, $files ) ){
			unset( $links['activate'] );
		}
		
		return $links;
	}
}