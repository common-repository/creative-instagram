<?php 
/*
* Plugin Name: Social Portfolio Feed 
* Version: 2.0
* Description: Display instagram beautifully clean gallery, customizable, and responsive With amazing and innovative effects.
* Author: webdzier
* Author URI: http://webdzier.com
* Plugin URI: http://webdzier.com/plugins/
* Requires at least: 4.8
* Requires PHP: 5.6
* Text Domain: social-portfolio-feed
* Domain Path: /languages
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('SNL_URL',plugin_dir_url(__FILE__));
define('SNL_PATH',plugin_dir_path(__FILE__)); 
define('SPF_VERSION','1.9'); 

class SPF_Instagram_Gallery{

	private static $instance;

	public static function get_instance() {

		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	private function __construct() {

		add_action( 'plugins_loaded', array(&$this, 'Spf_Admin_Area'));	

		add_action('plugins_loaded', array(&$this,'Spf_Translation'));			
	}

	public function Spf_Admin_Area(){

		if( current_user_can( 'manage_options' )){		

			add_action( 'admin_menu', array(&$this,'Spf_Dashboard_Menu_Create' ),99 );	

			add_action('admin_enqueue_scripts', array(&$this,'Spf_Admin_Scripts'));

			add_action('init', array(&$this,'Spf_Display_Settings_Save'));

			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this,'Spf_Settings_Link'), 10, 5 );	
		}
	}	

	public function Spf_Translation() {
		load_plugin_textdomain('social-portfolio-feed', FALSE, dirname( plugin_basename(__FILE__)).'/languages/' );
	}

	public function Spf_Admin_Scripts(){
		
		wp_enqueue_style('spf-admin',SNL_URL.'assets/css/admin-style.css');
		wp_enqueue_script('jquery');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_enqueue_script('spf-admin',SNL_URL.'assets/js/admin-script.js');			
		
	}	

	public function spf_default_setting(){

		$default = array(
			'access_token'		=>'', 	
			'user_id' => '',			
			'header_status'		=>true,
			'gallery_img'		=>'rectangle',
			'more_button_text'		=>__('Load More', 'social-portfolio-feed'),
			'follow_button_text'		=>__('Follow Me', 'social-portfolio-feed'),		
			'SNL_border_show'		=>'no', 							
			'SNL_custom_css'		=>''					
		);

		return $default;
	}	

	public function get_settings(){

		$settings = get_option( 'spf_instagram_settings', array() );

		$default = $this->spf_default_setting();			

		return wp_parse_args($settings, $default);
	}
	

	public function Spf_Settings_Link( $links, $file ) {

		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=social-portfolio-feed') ) .'">Settings</a>';
		return $links;
	}

	public function Spf_Dashboard_Menu_Create(){

		add_menu_page(
			__( 'Social Portfolio Feed', 'social-portfolio-feed' ),
			__( 'Social Portfolio Feed', 'social-portfolio-feed' ),
			'manage_options',
			'social-portfolio-feed',
			array(&$this,'Spf_Seattings_Page'),
			'dashicons-camera',
			67
		);

		add_submenu_page(
			'social-portfolio-feed', 
			__( 'Settings', 'social-portfolio-feed' ), 
			__( 'Settings', 'social-portfolio-feed' ), 
			'manage_options', 
			'spf_setings', 
			array( $this, 'Spf_Submenu_Page' )
		);

	}

	public function Spf_Seattings_Page(){

		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h2><?php esc_html_e('Social Portfolio Feed', 'social-portfolio-feed') ?></h2>
			<?php settings_errors(); ?>

			<?php
			if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = esc_html($_GET[ 'tab' ]);
			} else{
				$active_tab = 'configuration';
			}
			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=social-portfolio-feed&tab=configuration" class="nav-tab <?php echo $active_tab == 'configuration' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Configuration', 'social-portfolio-feed' ); ?></a>

				<a href="?page=social-portfolio-feed&tab=display_settings" class="nav-tab <?php echo $active_tab == 'display_settings' ? 'nav-tab-active' : ''; ?>">
					<?php _e('Display Settings', 'social-portfolio-feed') ?>
				</a>
			</h2>

			<form class="spf-form" method="post" action="">

				<?php $nonce = wp_create_nonce( 'instagram-setting-nonce' );  ?>

				<input type="hidden" name="_mywpnonce" value="<?php echo esc_attr($nonce)?>">

				<?php

				switch ( $active_tab ){

					case 'configuration' :

					$this->Spf_Settings_Confogure();					

					break;

					case 'display_settings' :

					$this->Spf_Display_Dettings();					

					break;

				}				

				submit_button();				

				?>

			</form>

			<p><?php _e('Use below shortcode in any Page/Post to publish your Instagram gallery','social-portfolio-feed') ?></p>
			<input type="text" value="[social-feed]" size="15" readonly="readonly" style="text-align: center;" onclick="this.focus();this.select()" >

		</div>
		<?php
	}

	public function Spf_Submenu_Page(){

	}

	public function Spf_Settings_Confogure(){

		$settings = $this->get_settings();

		?>

		<h2><?php _e( 'Configuration', 'social-portfolio-feed' ); ?></h2>

		<?php _e('User Id :','social-portfolio-feed'); ?></label>

		<input type="text" name="user_id" value="<?php echo esc_attr($settings['user_id']) ?>">

		<label><?php _e('Access Token :','social-portfolio-feed'); ?></label>

		<input type="text" name="access_token" value="<?php echo esc_attr($settings['access_token']) ?>">		

		<?php
	}


	public function Spf_Display_Dettings(){

		$settings = $this->get_settings();		

		?>
		<h2><?php _e( 'Display Settings', 'social-portfolio-feed' ); ?></h2>

		<div class='spf-input'>

			<label><?php _e('Header','social-portfolio-feed'); ?></label>

			<input type="radio" id="show-header" value="1" name="header_status" <?php checked($settings['header_status'], 1) ?>>
			<label for="show-header" class="label_m_right"><?php _e('Show','social-portfolio-feed'); ?> </label>

			<input type="radio" value="0" id="hide-header" name="header_status" <?php checked($settings['header_status'], 0) ?>>
			<label for="hide-header"><?php _e('Hide','social-portfolio-feed'); ?></label>	
		</div>

		<div class='spf-input'>

			<label><?php _e('Gallery Image Layout :','social-portfolio-feed'); ?></label>

			<input type="radio" id="img-rectangle" value="rectangle" name="gallery_img" <?php checked($settings['gallery_img'], 'rectangle') ?>><label for="img-rectangle" class="label_m_right"><?php _e('Rectangle','social-portfolio-feed'); ?> </label>

			<input type="radio" value="circle" id="img-circle" name="gallery_img" <?php checked($settings['gallery_img'], 'circle') ?>><label for="img-circle"><?php _e('Circle','social-portfolio-feed'); ?></label>	
		</div>

		

		<div class='spf-input'>
			<label><?php _e('More Button Text','social-portfolio-feed'); ?></label>

			<input type="text" name="more_button_text" value="<?php echo esc_attr($settings['more_button_text']) ?>">
		</div>

		<div class='spf-input'>

			<label><?php _e('Follow Button Text','social-portfolio-feed'); ?></label>

			<input type="text" name="follow_button_text" value="<?php echo esc_attr($settings['follow_button_text']) ?>">

		</div>		

		<?php
	}

	public function Spf_Display_Settings_Save() {
		global $pagenow;

		$settings = $this->get_settings();		

		if ( isset( $_POST['_mywpnonce'] ) && wp_verify_nonce( $_POST['_mywpnonce'], 'instagram-setting-nonce' ) ) {			

			if(isset($_POST['access_token'])){
				$settings['access_token'] = sanitize_text_field($_POST['access_token']);
			}

			if(isset($_POST['user_id'])){
				$settings['user_id'] = sanitize_text_field( $_POST['user_id'] );
			}

			if(isset($_POST['gallery_img'])){
				$settings['gallery_img'] = sanitize_text_field( $_POST['gallery_img'] );
			}

			if(isset($_POST['more_button_text'])){
				$settings['more_button_text'] = sanitize_text_field( $_POST['more_button_text']);
			}	

			if(isset($_POST['follow_button_text'])){
				$settings['follow_button_text'] = sanitize_text_field($_POST['follow_button_text']);
			}	

			if(isset($_POST['header_status'])){
				$settings['header_status'] = sanitize_text_field($_POST['header_status']);
			}			

			//code to filter html goes here
			$updated = update_option( "spf_instagram_settings", $settings );		

		}

	}		

}

if ( ! function_exists( 'SPF_Use_Instagram' ) ) {

	function SPF_Use_Instagram( $debug = false ) {

		return SPF_Instagram_Gallery::get_instance();
	}
}

$GLOBALS['SPF_Instagram_Gallery'] = SPF_Use_Instagram();

require_once(SNL_PATH.'include/shortcode.php');

//require_once(SNL_PATH.'include/widget-instagram.php');


?>