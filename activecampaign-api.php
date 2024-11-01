<?php
/**
 * Plugin Name: WP2AC API
 * Plugin URI: http://campaignplugins.com/downloads/wp-activecampaign-api
 * Description: WordPress Active Campaign API plugin
 * Author: Trumani
 * Author URI: https://trumani.com
 * Version: 1.1.7.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!defined('ACAP_PREFIX'))
	define('ACAP_PREFIX', 'acap_'); // Our prefix

if (!defined('ACAP_TEXTDOMAIN'))
	define('ACAP_TEXTDOMAIN', 'acap-active-campaign'); // Domain for translating text

/**
 * Defined Plugin url and path.
 */
if (!defined('ACAP_URL'))
	define('ACAP_URL', plugin_dir_url(__FILE__));

if (!defined('ACAP_PATH'))
	define('ACAP_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
/**/

/**
 * Defined Plugin library url and path.
 * Houses all the collective framework (not authored by me) that is essential for this plugin. 
 */
if (!defined('ACAP_LIB_URL'))
	define('ACAP_LIB_URL', ACAP_URL . '/libraries');

if (!defined('ACAP_LIB_PATH'))
	define('ACAP_LIB_PATH', ACAP_PATH . '/libraries');
/**/

/**
 * Plugin JavaScript url and path
 */
if (!defined('ACAP_JS_URL'))
	define('ACAP_JS_URL', ACAP_URL . 'js');
/**/

require_once('classes/api-toolkit.php');

if (!class_exists('WP_Active_Campaign')) {

	class WP_Active_Campaign extends API_Toolkit {
		
		const VERSION = '1.1.7.1';
		const NAME = 'WP ActiveCampaign API';
		const AUTHOR = 'Brecht Palombo';
		const STORE_URL = 'https://campaignplugins.com';
		const LICENSE_KEY = 'FREE';

		public static $endpoint;
		public static $api_key;
		public static $acsf;
		public static $acap;

		public function __construct() {

			require_once(ACAP_LIB_PATH .'/wp-sf-settings/classes/sf-class-settings.php');
			self::$acsf = new SF_Settings_API('acap_activecampaign_api', 'WP2AC API', 'options-general.php', __FILE__);
			self::$acsf->load_options(ACAP_LIB_PATH . '/wp-sf-settings/sf-options.php');

			require_once(ACAP_LIB_PATH .'/activecampaign-api-php/includes/ActiveCampaign.class.php');
			self::$acap = new ActiveCampaignAPI(self::$acsf->get_option(ACAP_PREFIX . 'endpoint'), self::$acsf->get_option (ACAP_PREFIX . 'api_key'));
			self::$acap->track_actid = self::$acsf->get_option(ACAP_PREFIX . 'event_id');
			self::$acap->track_key   = self::$acsf->get_option(ACAP_PREFIX . 'event_key');

			// Putting it all together...
	        add_action('wp_enqueue_scripts', array($this, 'site_tracking_js_script'));
			add_action('wp_ajax_acap_events', array($this, 'ajax_activecampaign_events'));
			add_action('wp_ajax_nopriv_acap_events', array($this, 'ajax_activecampaign_events'));
			add_filter('query_vars', array($this, 'add_email_to_query'));

			// You can't call register_activation_hook() inside a function hooked to the 'plugins_loaded' or 'init' hooks
			register_activation_hook(__FILE__, array($this, 'activate'));

		}

		public function activate() {

			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' => self::LICENSE_KEY,
				'item_name' => urlencode(self::NAME),
				'url'       => home_url()
			);

			wp_remote_post(self::STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

		}

		/**
		 * Handles our AJAX Active Campaign Events.
		 * Submit our event data.
		 */
		public function ajax_activecampaign_events() {

			$email = null;

			if (isset($_COOKIE['acap_email']) && !empty($_COOKIE['acap_email'])) {
				$email = $_COOKIE['acap_email'];
			}

			if (is_user_logged_in()) {
				
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;

			}

			if ($email) {

				$data = array(
					'event' => sanitize_text_field($_POST['event']),
					'eventdata' => sanitize_text_field($_POST['data']),
				);

				self::$acap->track_email = $email;
				$response = self::request('tracking/log', $data);
				echo json_encode($response);
				self::$acap->track_email = '';
				die();

			}

		}

		/**
		 * Do an event tracking
		 *
		 * @param  array $data
		 * @return array $response
		 */
		public static function ac_event($data) {
			
			$response = self::request('tracking/log', $data);
			return $response;

		}

		/**
		 * Perform an API requests
		 *
		 * @param   string  $string
		 * @param   array   $para
		 * @return  array   Results
		 */
		public static function request($string, $para = array()) {

			$results = self::$acap->api($string, $para);
			return $results;

		}

		/**
		 * Register site tracking script.
		 * Localize current log user's email or $_GET['email'].
		 */
		public static function site_tracking_js_script() {

			wp_register_script('acap-site-tracking-js', ACAP_JS_URL . '/acap_site_tracking.js', array('jquery'), '1.0', TRUE);

		    // Register event tracking script.
	        // We localize admin-ajax.php to handle AJAX requests and trigger AC events.
			$ajaxurl = admin_url('admin-ajax.php');
			wp_localize_script('acap-site-tracking-js', 'ajaxurl', $ajaxurl);

			$actid = self::$acsf->get_option(ACAP_PREFIX . 'event_id');
			$email = null;

			if (isset($_COOKIE['acap_email']) && !empty($_COOKIE['acap_email'])) {
				$email = $_COOKIE['acap_email'];
			}

			if (isset($_GET['email']) && !empty($_GET['email'])) {
				$email = sanitize_email($_GET['email']);
				$expires = time() + (20 * 365 * 24 * 60 * 60);
				@setcookie('acap_email', $email, $expires, '/');
			}

			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;
			}

			wp_localize_script('acap-site-tracking-js', 'trackcmp_actid', $actid);
			$dont_show_tracking_code = (string) self::$acsf->get_option(ACAP_PREFIX . 'dont_show_tracking_code');
			wp_localize_script('acap-site-tracking-js', 'dont_show_tracking_code', $dont_show_tracking_code);
			wp_enqueue_script('acap-site-tracking-js');

		}

		/**
		 * Add our Email variable to the WP_Query
		 *
		 * @param  array $vars
		 * @return array        Updated query variable
		 */
		public static function add_email_to_query($vars) {

			$vars[] = 'email';
			return $vars;

		}

	}

	$wp_activecampaign = new WP_Active_Campaign();

}