<?php 
/*
Plugin Name: YASSP Social Share
Description: Yet Another Social Sharing Plugin
Author: Stefan Olaru
Version: 0.1
Author URI: http://stefanolaru.com/
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class Yassp {
	
	public static $version = '0.1';
	
	function __construct() {
		
		// install/uninstall hooks
		register_activation_hook( __FILE__, array('Yassp', 'yassp_install'));
		register_deactivation_hook( __FILE__, array('Yassp', 'yassp_deactivate'));
		register_uninstall_hook( __FILE__, array('Yassp', 'yassp_uninstall'));
		
		add_action('init', array($this, 'plugin_actions'));
		
		// add shortcode
		add_shortcode('yassp', array($this, 'shortcode'));
	}
	
	public function plugin_actions() {
		// add actions
		add_action( 'wp_ajax_yassp_likes', array($this, 'count_likes'));
		add_action( 'wp_ajax_nopriv_yassp_likes', array($this, 'count_likes'));
		
		// footer output
		add_action('wp_footer', array($this, 'add_script_vars'));
		
		// enqueue styles & scripts
		add_action('wp_enqueue_scripts', array($this, 'include_css_js'), 9999);	
	}
	
	public function count_likes() {
		
		if(empty($_GET['url'])) {
			wp_die();
		}
		
		$likes = array();
		
		if(!empty($_GET['networks'])) {
		
			foreach($_GET['networks'] as $network) {
				if($network == 'facebook') {
					$response = wp_remote_get('http://graph.facebook.com/?id='.$_GET['url']);
					
					if(is_array($response)) {
						$response = json_decode($response['body'], true);
						
						if(isset($response['shares'])) {
							$likes[$network] = $response['shares'];
						}
						
					}
				}
				if($network == 'twitter') {
					$response = wp_remote_get('http://urls.api.twitter.com/1/urls/count.json?url='.$_GET['url']);
					
					if(is_array($response)) {
						$response = json_decode($response['body'], true);
						
						if(isset($response['count'])) {
							$likes[$network] = $response['count'];
						}
						
					}
				}
				if($network == 'linkedin') {
					$response = wp_remote_get('http://www.linkedin.com/countserv/count/share?format=json&url='.$_GET['url']);
					
					if(is_array($response)) {
						$response = json_decode($response['body'], true);
						
						if(isset($response['count'])) {
							$likes[$network] = $response['count'];
						}
						
					}
				}
				if($network == 'googleplus') {
					//
					$response = wp_remote_get('https://plusone.google.com/_/+1/fastbutton?url='.$_GET['url']);
					
					if(is_array($response)) {
						
						preg_match_all('/window\.__SSR\s\=\s\{c:\s(\d+?)\./', $response['body'], $match, PREG_SET_ORDER);
						
						if(isset($match[0][1])) {
							$likes[$network] = intval($match[0][1]);
						}
						
					}
				}
				if($network == 'pinterest') {
					//
					$response = wp_remote_get('http://api.pinterest.com/v1/urls/count.json?callback=mk&url='.$_GET['url']);
					
					if(is_array($response)) {
						// manipulate response body
						$response['body'] = str_replace(')', '', str_replace('mk(', '', $response['body']));
						
						// json decode body					
						$response = json_decode($response['body'], true);
						
						if(isset($response['count'])) {
							$likes[$network] = $response['count'];
						}
						
					}
				}
			}
		}
		
		wp_send_json($likes);
	}
	
	public function add_script_vars() {
			
			$post_id = get_queried_object_id();
			?>
			<script type="text/javascript">
				var yassp = {
					id: '<?php echo get_queried_object_id(); ?>',
					api_url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
					nonce: '<?php echo wp_create_nonce( 'yassp' ); ?>'
				};
			</script>
			<?php
			
	}
	
	public function include_css_js() {
		if(!is_admin()) {
			// just frontend stuff
			wp_enqueue_style('yassp', plugin_dir_url(__FILE__). 'css/yassp.css', 1);	
			wp_enqueue_script('yassp', plugin_dir_url(__FILE__). 'js/yassp.js', array('jquery'), null, true);
		}
	}
	
	public function shortcode($atts, $content = null) {
	
		global $post;
	
		$networks = !empty($atts['networks'])?explode(',', $atts['networks']):array();
		$has_counter = !empty($atts['counter'])?true:false;
		
		$html = '';
		
		if(!empty($networks)) {
	
			$html .= '<ul id="yassp-share" data-title="'.$post->post_title.'" data-url="'.get_permalink($post->ID).'" data-media="'.wp_get_attachment_url(get_post_thumbnail_id($post->ID)).'">';
				
			foreach($networks as $v) {
				$html .= '<li class="'.$v.'">';
				$html .= '<a href="#" class="yassp-'.$v.'" title="Share on '.ucfirst($v).'">';
				if(!empty($has_counter)) {
					$html .= '<span class="count">0</span>';
				}
				$html .= '</a>';
				$html .= '</li>';
			}
	
			$html .= '</ul>';
		
		}
		
		return $html;
		
	}
	
	public static function yassp_install() {
		
		// add version option
		add_option( 'yassp_version', self::$version );
			
	}
	
	public static function yassp_deactivate() {
		// nothing here yet
		
	}
	
	public static function yassp_uninstall() {
		
		// remove optinoidoptions
		delete_option( 'yassp_version' );
		
	}
	
}

$yassp = new Yassp;

?>