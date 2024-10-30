<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class SPF_Shortcode{

	private $post_id = 0;
	private $username = false;
	private $follow_link = false;
	private $settings = array();

	public function __construct(){

		$this->settings = $this->get_settings();

		add_action( 'plugins_loaded', array(&$this,'Instagram_Api_Init' ));

		add_shortcode( 'social-feed', array(&$this,'User_Display_Gallery' ));

		add_action('wp_enqueue_scripts', array(&$this,'Display_Enqueue'));

		add_action('wp_enqueue_scripts', array(&$this,'Custom_Style_Enqueue'));

		add_action( 'wp_ajax_spf_get_next_post_set', array(&$this,'spf_get_next_post_set' ));
		add_action( 'wp_ajax_nopriv_spf_get_next_post_set', array(&$this,'spf_get_next_post_set' )); 	

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

	public function Display_Enqueue(){

		wp_enqueue_script('jquery');

		wp_enqueue_style('spf-font', SNL_URL .'assets/css/font-awesome.css',array(), SPF_VERSION);

		wp_enqueue_style('spf-display', SNL_URL .'assets/css/display-style.css',array(), SPF_VERSION);

		wp_register_script( 'spf-loadmore-js',SNL_URL. 'assets/js/loadmore.js',array(), SPF_VERSION);		

		wp_localize_script( 
			'spf-loadmore-js', 
			'spf', 
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'spf-instagram-nonce' )
			)
		);

		wp_enqueue_script( 'spf-loadmore-js' );		

	}

	public function Custom_Style_Enqueue(){

		if( 'circle' == $this->settings['gallery_img']){

			$custom_css = ".spf-single-img img{width: 200px;border-radius: 50%;height: 200px;}";

			wp_add_inline_style( 'spf-display', $custom_css );
		}		
	}

	public function Instagram_Api_Init(){

		require_once(SNL_PATH.'classes/instagram_api.php'); 
	}

	public function spf_get_next_post_set( $postid ){		

		$nonce = esc_html( $_POST['_spfnonce'] ); 

		if( isset($nonce) && wp_verify_nonce($nonce, 'spf-instagram-nonce')){			

			$next_url = esc_html($_POST['next_url']);

			$obj = new SPF_Instagram_Api( $this->postid, 'next_post', $next_url );
			$obj->connect();
			$data = $obj->get_data();
			$next_page = $obj->get_next_page();

			$result = array('data'=> $data, 'next_page'=>$next_page);

			wp_send_json($result);			
		}

		wp_die();

	}

	public function User_Display_Gallery( $postid ){

		ob_start();

		?>			
		<div id="spf-insagram" class="spf-insagram-wrapper spf-insta">
			<?php 

			if($this->settings['header_status']){

				$this->get_header_html();
			}
			
			$this->get_footer_html();
			?>
		</div>

		<?php		

		return ob_get_clean();	

	}

	protected function get_header_html(){		

		$header = new SPF_Instagram_Api($this->post_id, 'header', $next_url ='' );
		$header->connect();
		$data = $header->get_data();

		$this->username = $data['username'];
		$this->follow_link = 'https://www.instagram.com/'.$this->username;

		if(!empty($this->username)){		

			$icon = SNL_URL.'assets/img/instagram.png';

			?>
			<div class="spf-header">

				<div class="spf-header-icon">
					<a href="<?php echo esc_url($this->follow_link)?>">
						<img src="<?php echo esc_url($icon)?>">
					</a>
				</div>

				<div class="spf-header-text">
					<h5 class="spf-header-title"> 
						<?php echo esc_html($this->username) ?>
					</h5>
				</div>


			</div>

			<?php
		}
	}

	protected function get_footer_html(){		

		$obj = new SPF_Instagram_Api($this->post_id, 'footer', $next_url ='' );
		$obj->connect();
		$data = $obj->get_data();
		$next_data = $obj->get_next_page();		

		if(!empty($data)){
			?>
			<div id="spf-posts">
				<?php 

				foreach ($data as $key => $value) {

					if( 'IMAGE' === $value['media_type']){

						$media_url = $value['media_url'];

						echo sprintf('<div class="spf-single-img"><img src="%1$s" class="spf-responsive"/></div>', esc_url($media_url));

					}elseif( 'CAROUSEL_ALBUM' === $value['media_type']){

					}elseif( 'VIDEO' === $value['media_type']){

					}

				}
				?>	

			</div>

			<?php

			if(!empty($next_data)){

				echo sprintf('<div id="spf-loadmore"><a class="spf-load-button" href="javascript:void(0)" data-next_url="%1$s"> %2$s </a><a class="spf-follow-button" href="%3$s" target="_blank"> %4$s </a></div>', 
					esc_attr($next_data),
					esc_html($this->settings['more_button_text']),
					esc_url($this->follow_link),
					esc_html($this->settings['follow_button_text'])
				);
			}
		}
	}
}



new SPF_Shortcode();
?>