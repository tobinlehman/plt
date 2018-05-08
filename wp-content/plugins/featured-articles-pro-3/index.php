<?php 
/*
Plugin Name: Featured articles PRO 3
Plugin URI: http://www.codeflavors.com/featured-articles-pro/
Description: Create beautiful slideshows in WordPress from any already existing posts or pages. Now at version 3.
Author: CodeFlavors
Version: 3.1.5.1
Author URI: http://www.codeflavors.com
*/

if( defined( 'FA_PATH' ) ){
	/**
	 * Display a notice if both Lite and PRO versions are active
	 */
	function fa_double_ver_notice(){
		$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=featured-articles-lite/index.php', 'deactivate-plugin_' . 'featured-articles-lite/index.php' );
		?>
		<div class="notice notice-error is-dismissible">
	        <p>
	        	<?php _e( 'You have successfully installed and activated <strong>Featured Articles PRO</strong> by CodeFlavors but the plugin is not fully functional yet.', 'fapro' );?><br />
	        	<?php printf( __( 'In order to benefit of all its features, please %sdeactivate Featured Articles Lite%s.', 'fapro' ), '<a href="' . $deactivate_url . '">', '</a>' ); ?>
	        </p>
	    </div>
		<?php
	}	
	add_action( 'admin_notices', 'fa_double_ver_notice' );
	return;
}

// store plugin version
define('FA_VERSION', '3.1.5.1');
// plugin path
define('FA_PATH', plugin_dir_path(__FILE__));
// plugin url
define('FA_URL', plugin_dir_url(__FILE__));
// will load all JS files in front-end minified when false
define( 'FA_SCRIPT_DEBUG', false );
// will load minified JS files when false
define( 'FA_SCRIPT_DEBUG_ADMIN', false );
// will load minified CSS when false
define( 'FA_CSS_DEBUG', false );

/**
 * Do not delete this. It is used to activate your license
 */
define('CODEFLAVORS_CLIENT_CODE', 'b2a9f7f77d33c9575914eabc3345eb1c');
define('CODEFLAVORS_UPDATES_URL', 'http://codeflavors.com');

// regular functions needed in both front-end and back-end
require_once path_join( FA_PATH, 'includes/functions.php' );
// deprecated functions needed in both front-end and back-end
require_once fa_get_path( 'includes/deprecated.php' );
// custom post type class
require_once fa_get_path( 'includes/libs/class-fa-custom-post-type.php' );

/**
 * Start the plugin
 */
class FA_Pro extends FA_Custom_Post_Type{
	
	public function __construct(){
		// init function
		add_action('init', array( $this, 'on_init' ), -999);
		// plugin activation hook. Used to store some initial plugin data useful on plugin updates to perform maintenance
		register_activation_hook(__FILE__, array( $this, 'on_activation' ));
		// for attachments, add a filter to accomodate SSL
		add_filter('wp_get_attachment_url', array( $this, 'attachment_image_url' ), 10, 2);		
		// load preloaders if set in admin settings
		add_action( 'wp_enqueue_scripts', array( $this, 'slides_preload' ), 100 );		
		// display sliders on loop_start
		add_filter( 'loop_start', array( $this, 'show_on_loop_start' ), -999, 1 );
		// filter sliders display to check where to display them according to display settings
		add_filter( 'fa_display_slider', array( $this, 'dynamic_areas_filter' ), 1, 3 );
		// load the widgets
		add_action( 'widgets_init', array( $this, 'load_widgets' ) );
		
		// do some processing on scripts
		add_action( 'wp_footer', array( $this, 'footer_scripts' ), -999 );
		
		// load the compatibilites
		require_once fa_get_path( 'includes/third-party-compatibility.php' );
	}
	
	/**
	 * Perform some maintenance on scripts loaded by the plugin.
	 */
	public function footer_scripts(){
		/**
		 * By default, WP loads jquery-ui-dialog with lots of dependencies. Many of them aren't needed
		 * by the plugin script so the plugin will try to register a different jquery-ui-dialog handle 
		 * with fewer dependencies.
		 * If until reaching the footer some other plugin loads jquery-ui-dialog, the plugin will use this handle.
		 */
		if( wp_script_is( 'jquery-ui-dialog', 'enqueued' ) || wp_script_is( 'jquery-ui-dialog', 'done' ) ){
			global $wp_scripts;	
			foreach( $wp_scripts->registered as $handle => $data ){
				$key = array_search( 'jquery-ui-dialog-fapro' , $data->deps );
				if( $key ){
					$wp_scripts->registered[ $handle ]->deps[ $key ] = 'jquery-ui-dialog';
				}				
			}			
		}	
	}
	
	/**
	 * Display sliders above the main loop in page
	 * @param object $query
	 */
	public function show_on_loop_start( $query ){
		// don't display on admin pages, previews or if it's not main query
		if ( is_admin() || !$query->is_main_query() ){
			return;
		}
		/**
		 * Filter to prevent dynamic areas globally from being displayed
		 * @var bool
		 */
		$show = apply_filters( 'fa_display_dynamic_areas', true, 'loop_start' );
		if( !$show ){
			return;
		}		
		
		$settings 	= fa_get_options( 'hooks' );
		$sliders 	= (array) $settings['loop_start']['sliders'];
		if( $sliders ){
			foreach( $sliders as $slider_id ){
				fa_display_slider( $slider_id, 'loop_start' );
			}
		}		
	}
	
	/**
	 * Filter fa_display_slider callback function. Verifies if the currently
	 * displayed slider is inside a dynamic area and if true, if it should display
	 * on the current page according to user settings.
	 * 
	 * @param bool $show - show slider
	 * @param int $slider_id - ID of slider
	 * @param bool/string $dynamic_area - if displayed in dynamic area, this parameter contains the area ID
	 */
	public function dynamic_areas_filter( $show, $slider_id, $dynamic_area ){
		// only registered plugin areas should be managed by this function.
		// if any other area, allow it to display
		$areas = fa_get_options( 'hooks' );
		if( empty( $dynamic_area ) || !$dynamic_area || !array_key_exists( $dynamic_area, $areas ) ){
			return $show;
		}
		
		// check for the ID
		if( !$slider_id ){
			return false;
		}
		// get slider options
		$options = fa_get_slider_options( $slider_id, 'display' );
		
		// if slider is allowed everywhere, show it
		if( $options['everywhere'] ){
			return true;
		}
		
		// check if slider should display on homepage
		if( is_front_page() ){
			// allow previews on front page
			if( fa_is_preview() && $slider_id == $_GET['slider_id'] ){
				return true;
			}			
			return $options['home'];
		}
		
		// display on archive pages
		if( is_archive() ){
			
			// allow display on all categories
			if( $options['all_categories'] ){
				return true;
			}
			
			// if no categories are set, bail out
			if( !$options['tax'] ){
				return false;
			}
			// merge all types of tags into one array
			$categories = array();
			foreach( $options['tax'] as $tags ){
				$categories = array_merge( $categories , $tags );
			}
			// check categories
			if( is_category( $categories ) ){
				return true;
			}
			// check taxonomies
			if( is_tax() || is_tag() ){
				$tax_id = get_queried_object()->term_id;
				return in_array( $tax_id , $categories );
			}			
		}
		// display on singular post/page
		if( is_singular() ){
			
			// allow display on all pages
			if( $options['all_pages'] ){
				return true;
			}
			
			if( !$options['posts'] ){
				return false;
			}
			$post_id = get_queried_object()->ID;
			$post_ids = array();
			foreach( $options['posts'] as $posts ){
				$post_ids = array_merge( $post_ids , $posts );
			}
			if( in_array( $post_id, $post_ids ) ){
				return true;
			}
		}
	}
	
	/**
	 * widgets_init callback function. 
	 * Loads the slider widget class
	 */
	public function load_widgets(){
		if( !class_exists( 'FA_Widgets' ) ){
			require_once fa_get_path('includes/libs/class-fa-widgets.php');
		}
		// register the slideshow widget
		register_widget( 'FA_Widgets' );
	}
	
	/**
	 * wp_get_attachment_url callback. Modifies links in case is_ssl()
	 * @param string $url
	 * @param int $post_id
	 */
	public function attachment_image_url( $url, $post_id ){
		if( is_ssl() ){
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}
	
	/**
	 * Enqueues stylesheet and js file to preload sliders
	 */
	public function slides_preload(){
		$settings = fa_get_options( 'settings' );
		if( !$settings['preload_sliders'] ){
			return;
		}

		/**
		 * Filter that allows different styling for preloading sliders.
		 * @var false/string
		 */
		$styles = apply_filters( 'fa-preload-styles' , false );		
?>
<style type="text/css">
.fa-slideshow.slider-loading{
<?php if( !$styles ):?>
	background-image:url( <?php echo fa_get_uri( 'assets/front/images/loading.gif' )?> )!important;
	background-position:center center!important;
	background-repeat:no-repeat!important;
	background-color:#000!important;
<?php else:?>
	<?php echo $styles;?>	
<?php endif;?>
}
.fa-slideshow.slider-loading :nth-child(odd),
.fa-slideshow.slider-loading :nth-child(even){
	visibility:hidden!important;
}
</style>		
<?php
	}
	
	/**
	 * Init callback. This should be the first to start within the plugin.
	 */
	public function on_init(){
		// load when not in admin area
		if( !is_admin() ){
			// start custom post type class
			parent::__construct();
			// add the shortcodes
			require_once fa_get_path('includes/libs/class-fa-shortcodes.php');
			new FA_Shortcodes();
		}
		
		// only for admin area
		if( is_admin() || fa_is_preview() ){
			// localization - needed only for admin area
			load_plugin_textdomain( 'fapro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			// allow admins to manage all areas of the plugin
			add_action('set_current_user', array( $this, 'allow_admins' ));
			// add admin specific functions
			require_once fa_get_path( 'includes/admin/functions.php' );
			// add administration management
			require_once fa_get_path( 'includes/admin/libs/class-fa-admin.php' );
			// start the administration area
			global $fa_admin;
			$fa_admin = new FA_Admin();
		}
        
		// add compatibilities
		require_once fa_get_path( 'includes/compatibility.php' );
		
		if( is_admin() ){
			if( !defined( 'CODEFLAVORS_CLIENT_CODE' ) || !defined( 'CODEFLAVORS_UPDATES_URL' ) ){
				return; 	
			}
			include_once fa_get_path('includes/admin/libs/class-codeflavors-upgrade.php');			
			$options 	= fa_get_options('license');
			$plugin 	= plugin_basename( __FILE__ );
			$slug 		= dirname( $plugin );
			$args = array(
				'url'				=> home_url(),
				'code' 				=> $options['license_key'],
				'client' 			=> CODEFLAVORS_CLIENT_CODE,
				'current_version' 	=> FA_VERSION,
				'file_id' 			=> 1
			);			
			new CodeFlavors_Upgrade( CODEFLAVORS_UPDATES_URL . '/notifications/update-notifications/', $plugin, $slug, $args );
			new CodeFlavors_Update_Details( CODEFLAVORS_UPDATES_URL . '/plugin-details/', $plugin, $slug, $args );			
		}		
	}
	
	/**
	 * Plugin activation hook callback function
	 */
	public function on_activation(){
		// give full access to administrators on plugin activation
		parent::set_capabilities();
		$this->allow_admins();
		
		// get the current option
		$option = fa_get_options('plugin_details');
		// set the current plugin details
		$plugin_details = array(
			'version'	 	=> FA_VERSION,
			'wp_version'	=> get_bloginfo('version'),
			'activated_on' 	=> current_time('mysql'),
		);
		
		/**
		 * Action on plugin activation that allows maintenance related actions.
		 * 
		 * @param $option - current option stored in plugin settings
		 * @param $plugin_details - the new option that is going to be saved
		 */
		do_action( 'fa_pro_activation', $option, $plugin_details );
		
		// update the option	
		fa_update_options('plugin_details', $plugin_details);
		
		// pre 3.0 plugin options verification
		$old_options 	= get_option( 'fa_plugin_details', array() );
		$updated 		= fa_get_options('updated');
		if( $old_options && FA_VERSION != $updated['to'] ){
			$this->on_init();	
			include_once  fa_get_path('includes/libs/class-fa-update.php');
			new FA_Update();
			// flag plugin as updated to prevent update from running again if previous plugin version reactivated
			fa_update_options('updated', array( 'from' => $old_options['version'], 'to' => FA_VERSION ));			
		}
	}
	
	/**
	 * Give administrators full access to all plugin pages
	 */
	public function allow_admins(){
		$caps = parent::get_caps();;
		
		// give permission to administrator to change slider settings
		if( current_user_can('manage_options') ){
			global $wp_roles;
			foreach ( $caps as $cap ){
				if( !current_user_can($cap) ){
					$wp_roles->add_cap('administrator', $cap);	
				}
			}			
		}	
	}
	
	/**
	 * Returns slide post type
	 */
	public function post_type_slide(){
		return parent::get_type_slide();
	}
	
	/**
	 * Returns slider post type
	 */
	public function post_type_slider(){
		return parent::get_type_slider();
	}
	
	/**
	 * Returns slide taxonomy name
	 */
	public function slide_tax(){
		return parent::get_slide_tax();
	}
	
}
global $fa_pro;
$fa_pro = new FA_Pro();
