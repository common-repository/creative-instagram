<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

class SPF_Instagram_Api{

	protected $response;
	private $postid,$access_token,$user_id,$url,$count,$next_url;

	public function __construct( $postid, $type, $next_url, $next_num = 33   ){

		$this->postid = $postid;		
		$this->next_url = $next_url;

		$this->Access_ID();
		$this->set_url( $type, $next_num );	
		
	}

	public function Access_ID(){

		$data = get_option( 'spf_instagram_settings', array() );		
		$this->user_id = $this->SNL_Maybe_Clean($data['user_id']);
		$this->access_token =  $this->SNL_Maybe_Clean($data['access_token']);
		
	}

	public function set_url_from_args( $url ) {
		$this->url = $url;
	}

	public function get_url() {
		return $this->url;
	}

	public function is_wp_error() {
		return is_wp_error( $this->response );
	}

	public function is_instagram_error( $response = false ) {

		if ( ! $response ) {
			$response = $this->response;
		}

		return (isset( $response['error'] ));
	}

	protected function set_url( $type, $num = '' ){

		$num = ! empty( $num ) ? (int)$num : 33;

		if ( $type === 'header' ) {

			$url = 'https://graph.instagram.com/me?fields=id,username,media_count&access_token=' . $this->access_token;	

		}elseif($type === 'footer'){

			$url = "https://graph.instagram.com/".$this->user_id."/media?fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D&limit=".$num."&access_token=".$this->access_token;
		
		}elseif( $type === 'next_post'){

			$url = "https://graph.instagram.com/v1.0/".$this->user_id."/media?access_token=".$this->access_token."&fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D&limit=".$num."&after=".$this->next_url;

		}	

		$this->set_url_from_args( $url );	
	}

	public function connect() {

		$args = array(
			'timeout' => 60,
			'sslverify' => false
		);
		$response = wp_remote_get( $this->url, $args );

		if ( ! is_wp_error( $response ) ) {
			
			$response = json_decode( str_replace( '%22', '&rdquo;', $response['body'] ), true );
		}

		$this->response = $response;
	}

	public function SNL_Maybe_Clean( $maybe_dirty ) {

		if ( substr_count ( $maybe_dirty , '.' ) < 3 ) {
			return str_replace( '634hgdf83hjdj2', '', $maybe_dirty );
		}

		$parts = explode( '.', trim( $maybe_dirty ) );
		$last_part = $parts[2] . $parts[3];
		$cleaned = $parts[0] . '.' . base64_decode( $parts[1] ) . '.' . base64_decode( $last_part );

		return $cleaned;
	}

	public function get_data() {

		if (!empty($this->response['data'])) {
			return $this->response['data'];
		} else {
			return $this->response;
		}
	}

	public function get_next_page() {

		if(! empty( $this->response['paging']['cursors']['after'] )){
			return $this->response['paging']['cursors']['after'];
		}

		return false;
	}	

}


?>