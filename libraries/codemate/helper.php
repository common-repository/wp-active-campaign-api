<?php

/**
 * Helper class
 * @require PHP 5.3.0 or later
 * @author Art Layese <art@codemate.net>
 */

if (!class_exists('CodeMate_Helper')) {

	if (!defined('ACAP_PREFIX'))
		define('ACAP_PREFIX', 'acap_'); // Our prefix
		class CodeMate_Helper {

		public static function getFullUrl()
		{
			return sprintf(
				'%s://%s/%s',
				isset($_SERVER['HTTPS']) ? 'https' : 'http',
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI']
			);
		}

		public static function getActiveCampaignAPIOptions()
		{
			static $options = null;
			if ($options === null) {
				$options = array(
					'endpoint' => null,
					'api_key' => null,
					'event_id' => null,
					'event_key' => null,
				);
				$opts = get_option('wp_activecampaign_api_options');
				foreach ($options as $key => $value) {
					$index = ACAP_PREFIX . $key;
					$options[$key] = isset($opts[$index]) ? $opts[$index] : '';
				}
			}
			return $options;
		}

		public static function settings($key, $optionName)
		{
			$settings = self::getSettings($optionName);
			return (isset($settings[$key]) ? $settings[$key] : null);
		}

		/**
		 * Get the settings or configurations options
		 * @return array Configuration settings/options
		 */
		public static function getSettings($optionName)
		{
			$settings = get_option($optionName);
			return ($settings);
		}

		public static function getActiveCampaignAPI()
		{
			static $acap = null;
			if ($acap === null) {
				$options = self::getActiveCampaignAPIOptions();
				$url = $options['endpoint'];
				$apiKey = $options['api_key'];
				if (strlen($url) == 0 || strlen($apiKey) == 0) {
					$error = 'No settings configured. Please contact administrator.';
					die($error);
				}

				$acap = new ActiveCampaignAPI($url, $apiKey);
			}
			return $acap;
		}

		public static function getActiveCampaignLists()
		{
			$acap = self::getActiveCampaignAPI();
			$params = array(
				'ids' => 'all',
				'full' => 1,
			);
			$list = self::runActiveCampaignAPI($acap, 'list/list', $params);

			if (!$list)
				$list = array();
			$campaignList = array();
			foreach ($list as $item) {
				if (!isset($item->id))
					continue;
				$campaignList[$item->id] = $item->name;
			}
			return $campaignList;
		}

		public static function runActiveCampaignAPI($acap, $action, $params = array())
		{
			ob_start();
			$result = $acap->api($action, $params);
			ob_end_clean();
			return $result;
		}

	}
	
}
//connector for brige plugin
add_filter( 'option_wp_activecampaign_api_options', function( $data ){

	$new_data = array();
	
	foreach($data as $k => $d){
		$new_data[str_replace("acap_", "ac_", $k)] = $d;
	}
	
	return $new_data;
});