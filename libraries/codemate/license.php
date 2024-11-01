<?php

/**
 * Class for License
 * @require PHP 5.3.0 or later
 * @author Art Layese <art@codemate.net>
 */

if (!class_exists('CodeMate_License')) {
abstract class CodeMate_License
{

	protected $file = null;
	protected $version = null;
	protected $name = null;
	protected $author = null;
	protected $store_url = null;
	protected $license_key = null;
	protected $license_status = null;

	protected $option_license_status = null;
	protected $plugin_name = null;
	protected $settingsName = null;

	abstract public static function getInstance($settingsName, $file, $version, $name, $author, $store_url);

	protected function __construct($settingsName, $file, $version, $name, $author, $store_url)
	{
		$this->file = $file;
		$this->version = $version;
		$this->name = $name;
		$this->plugin_name = strtolower(str_replace(' ', '-', $this->name));
		$this->settingsName = $settingsName.'-license';
		$this->author = $author;
		$this->store_url = $store_url;
		$option = get_option($this->settingsName);
		$this->license_key = isset($option['key']) ? $option['key'] : '';
		$this->option_license_status = $this->settingsName.'-status';
		$this->license_status = trim(get_option($this->option_license_status));
		add_action('admin_init', array($this, 'init'), 0);
		add_action('admin_menu', array($this, 'menu'));
		add_action('admin_init', array($this, 'registerOptions'));
		add_action('admin_init', array($this, 'activate'));
		add_action('admin_init', array($this, 'deactivate'));
	}

	public function init(){}

	/************************************
	 * the code below is just a standard
	 * options page. Substitute with
	 * your own.
	 ************************************/

	public function menu(){}


	public function registerOptions()
	{
	}

	public function sanitizeLicense($new)
	{
		if ($this->license_key && $this->license_key != $new) {
			delete_option($this->option_license_status); // new license has been entered, so must reactivate
		}
		return $new;
	}


	/************************************
	 * this illustrates how to activate
	 * a license key
	 ************************************/

	public function activate()
	{
		if ($this->activateNow()) {
			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $this->license_key,
				'item_name' => urlencode($this->name), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

			// make sure the response came back okay
			if (is_wp_error($response))
				return false;

			// decode the license data
			$license_data = json_decode(wp_remote_retrieve_body($response));

			// $license_data->license will be either "valid" or "invalid"
			update_option($this->option_license_status, $license_data->license);
		}
	}


	/***********************************************
	 * Illustrates how to deactivate a license key.
	 * This will descrease the site count
	 ***********************************************/

	public function deactivate()
	{

		// listen for our activate button to be clicked
		if ($this->deactivateNow()) {
			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $this->license_key,
				'item_name' => urlencode($this->name), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

			// make sure the response came back okay
			if (is_wp_error($response))
				return false;

			// decode the license data
			$license_data = json_decode(wp_remote_retrieve_body($response));

			// $license_data->license will be either "deactivated" or "failed"
			if ($license_data->license == 'deactivated')
				delete_option($this->option_license_status);
		}
	}


	/************************************
	 * this illustrates how to check if
	 * a license key is still valid
	 * the updater does this for you,
	 * so this is only needed if you
	 * want to do something custom
	 ************************************/

	public function check()
	{
		global $wp_version;

		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $this->license_key,
			'item_name' => urlencode($this->name),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

		if (is_wp_error($response))
			return false;

		$license_data = json_decode(wp_remote_retrieve_body($response));

		if ($license_data->license == 'valid') {
			echo 'valid'; exit;
			// this license is still valid
		}
		else {
			echo 'invalid'; exit;
			// this license is no longer valid
		}
	}

	public function activateNow()
	{
		$ok = false;
		if (isset($_POST[$this->settingsName])) {
			$license = sanitize_key($_POST[$this->settingsName]);
			if (isset($license['activate']) && check_admin_referer($this->settingsName.'-activate_nonce', $this->settingsName.'-activate_nonce'))
				$ok = true;
		}
		return $ok;
	}

	public function deactivateNow()
	{
		$ok = false;
		if (isset($_POST[$this->settingsName])) {
			$license = sanitize_key($_POST[$this->settingsName]);
			if (isset($license['deactivate']) && check_admin_referer($this->settingsName.'-deactivate_nonce', $this->settingsName.'-deactivate_nonce'))
				$ok = true;
		}
		return $ok;
	}

}
}

