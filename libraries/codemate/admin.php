<?php

require_once 'pluginfo.php';

/**
 * Class for an Admin page
 * @require PHP 5.3.0 or later
 * @author Art Layese <art@codemate.net>
 */

if (!class_exists('CodeMate_Admin')) {
abstract class CodeMate_Admin
{

	/**
	 * object holder for plugin information
	 * @access protected static
	 * @var $instance
	 */
	protected $pluginfo = null;

	/**
	 * class constructor, make it protected so that it will not be instantiated
	 * @access protected
	 */
	protected function __construct()
	{
		$this->initPluginfo();
		add_action('admin_menu', array($this, 'menu'));
		add_action('admin_init', array($this, 'init'));
	}

	/**
	 * make it protected so that it will not be cloned
	 * @access protected
	 */
	protected function __clone()
	{
	}

	/**
	 * initialize plugin information
	 * @return CodeMatePluginfo
	 */
	abstract public function initPluginfo();

	/**
	 * callback for admin_init event. initialize settings page
	 */
	abstract public function init();

	/**
	 * Returns singleton instance of this class
	 * @access public static
	 */
	abstract public static function getInstance();

	/**
	 * callback for Tab definitions
	 */
	abstract public function getTabs();

	/**
	 * callback for admin_init event. initialize settings page
	 */
	public function initLicense()
	{
		add_action('update_option_'.$this->pluginfo->id.'-license', array($this, 'updatedLicense'), 10, 2);
		register_setting('settings-group-'.$this->pluginfo->id.'-license', $this->pluginfo->id.'-license', array($this, 'validateLicense'));
		add_action($this->pluginfo->id  . '_options_tab-license', array($this, 'licenseSettings'));
	}

	// Start callbacks section
	/**
	 * Callback for common textbox
	 * @param array $args Input field name and its value
	 */
	public function callbackTextbox($args)
	{
		echo '<input type="text" name="', $args['name'], '" value="'.esc_attr($args['value']).'" placeholder="'.htmlentities($args['label']).'" style="'.(isset($args['style'])?$args['style']:'').'" />';
	}

	/**
	 * Callback for common hidden
	 * @param array $args Input field name and its value
	 */
	public function callbackHidden($args)
	{
		echo '<input type="hidden" name="', $args['name'], '" value="'.esc_attr($args['value']).'" />';
	}

	/**
	 * Callback for common textbox with hidden
	 * @param array $args Input field name and its value
	 */
	public function callbackTextboxWithHidden($args)
	{
		echo '<input type="text" name="', $args['name'], '" value="'.esc_attr($args['value']).'" placeholder="'.htmlentities($args['label']).'" />';
		echo '<input type="hidden" name="', $args['hidden_name'], '" value="'.esc_attr($args['hidden_value']).'" />';
	}

	public function callbackCheckbox($args)
	{
		echo '<input type="checkbox" name="', $args['name'], '" value="1" '.(($args['value'] == '1')?'checked':'').'>';
	}

	public function callbackCheckTextboxes($args)
	{
		echo '<input type="checkbox" name="', $args[0]['name'], '" value="1" '.(($args[0]['value'] == '1')?'checked':'').'>';
		echo '<input type="text" name="', $args[1]['name'], '" value="'.esc_attr($args[1]['value']).'" placeholder="'.htmlentities($args[1]['label']).'" />';
	}

	/**
	 * Callback for dropdown fields
	 * @param array $args Input field name and its value including its dropdown options
	 */
	public function callbackDropdown($args)
	{
		echo '<select name="', $args['name'], '">';
		foreach ($args['options'] as $key => $value)
			echo '	<option value="', $key, '" ', (($key == $args['value']) ? 'selected="selected"' : ''),
				'>', esc_html($value), '</option>';
		echo '</select>';
	}

	/**
	 * Callback for common submit bitton
	 * @param array $args Input field name and its value
	 */
	public function callbackSubmitButton($args)
	{
		$nonce = str_replace('[', '-', $args['name']);
		$nonce = str_replace(']', '', $nonce);
		wp_nonce_field($nonce.'_nonce', $nonce.'_nonce');
		if (isset($args['active']) && $args['active'])
			echo '<span style="color:green;">'.__('active', $this->pluginfo->textDomain).'</span>';
		echo '<input type="submit" name="', $args['name'], '" value="'.esc_attr($args['value']).'" placeholder="'.htmlentities($args['label']).'" />';
	}
	// End callbacks section

	/**
	 * callback for admin_menu event. set up menus
	 */
	public function menu()
	{
		if (current_user_can('manage_options')) {
			add_submenu_page(
				'options-general.php',
				$this->pluginfo->optionName,
				$this->pluginfo->optionName,
				'manage_options',
				$this->pluginfo->settingsName.'-settings',
				array($this, 'initSettingsPage')
			);
		}
	}

	/**
	 * HTML header
	 */
	protected function templateHeader()
	{
?>

<style>
.left_cont, .right_cont {
	width: 45%;
	padding: 0 2%;
	margin: 0 auto;
	float: left;
	position: relative;
	display: block;
}
.ads_cont {
	padding-left: 10%;
}
</style>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo $this->pluginfo->optionName; ?></h2>
	<h2 class="nav-tab-wrapper">
		<?php echo $this->displayTabs(); ?>
	</h2>

<?php
	}

	/**
	 * HTML body
	 *
	 * @return unknown
	 */
	protected function templateBody()
	{
		$tabs = $this->getAllTabs();
		$tabname = !empty($_GET['tab']) ? $_GET['tab'] : $tabs[0]['slug'];
?>
		<div class="left_cont">
			<form method="post" action="options.php">
				<?php //settings_fields($this->pluginfo->id  . '_options_nonce'); ?>
<?php
					do_action($this->pluginfo->id  . '_options_tab-' . $tabname);
					settings_fields('settings-group-'.$this->pluginfo->id.'-'.$tabname);
					do_settings_sections($this->pluginfo->id.'-settings');

?>
				<?php submit_button(); ?>
			</form>
		</div>
<?php
	}

	/**
	 * HTML footer
	 */
	protected function templateFooter()
	{
		echo '</div>';
	}

	/**
	 * Create the settings page
	 */
	public function initSettingsPage()
	{
		$this->templateHeader();
		$this->templateBody();
		$this->templateFooter();
	}

	/**
	 * Retrieve tabs
	 *
	 * @return array
	 */
	protected function getAllTabs()
	{
		$tabs = (array)$this->getTabs();
		$tabs[] = 'License';
		$sanitizedTabs = array();
		foreach ($tabs as $tab) {
			$sanitizedTabs[] = array(
				'name' => $tab,
				'slug' => sanitize_title($tab)
			);
		}
		return $sanitizedTabs;
	}

	/**
	 * Heading for navigation
	 *
	 * @return string
	 */
	protected function displayTabs()
	{
		$tabs = $this->getAllTabs();
		$tabname = !empty($_GET['tab']) ? $_GET['tab'] : $tabs[0]['slug'];
		$menu = '';
		foreach ($tabs as $tab) {
			$class = $tabname == $tab['slug'] ? 'nav-tab-active' : '';
			$fields = array(
				'tab'  => $tab['slug'],
			);
			$query = http_build_query(array_merge($_GET, $fields));
			$menu .= sprintf('<a id="%s-tab" class="nav-tab %s" title="%s" href="?%s">%s</a>', $tab['slug'], $class, $tab['name'], $query, esc_html($tab['name']));
		}
		return $menu;
	}

	/**
	 * Callback for license section
	 */
	public function callbackLicenseSection()
	{
		echo '';
	}

	/**
	 * Callback for input validation or sanitation of inputs
	 * @param array $input POST data
	 * @return array $output Sanitized inputs
	 */
	public function validateLicense($input)
	{
		return $input;
	}

	public function updatedLicense($oldValue, $newValue)
	{
	}

	public function licenseSettings()
	{
		$licenseSection = 'settings-section-'.$this->pluginfo->id;
		add_settings_section($licenseSection, '',
			array($this, 'callbackLicenseSection'), $this->pluginfo->id.'-settings');

		$licenseInfo = $this->getLicenseInfo();
		$key = 'key';
		$value = 'License Key';
		$licenseKey = $licenseInfo[$key];

		$licenseStatusKey = 'status';
		$licenseStatus = $licenseInfo[$licenseStatusKey];

		add_settings_field($key,
			$value,
			//array($this, 'callbackTextboxWithHidden'),
			array($this, 'callbackTextbox'),
			$this->pluginfo->id.'-settings',
			$licenseSection,
			array(
				'label' => $value,
				'name' => $this->pluginfo->id.'-license['.$key.']',
				'value' => $licenseKey,
				'style' => 'width:300px;',
			)
		);

		if ($licenseKey) {
			$active = ($licenseStatus !== false && $licenseStatus == 'valid');

			if ($active) {
				$key = 'deactivate';
				$value = 'Dectivate License';
			}
			else {
				$key = 'activate';
				$value = 'Activate License';
			}
			add_settings_field($key,
				$value,
				array($this, 'callbackSubmitButton'),
				$this->pluginfo->id.'-settings',
				$licenseSection,
				array(
					'label' => $value,
					'name' => $this->pluginfo->id.'-license['.$key.']',
					'value' => $value,
					'active' => $active,
				)
			);
		}
	}

	/**
	 * callback for License Information
	 */
	public function getLicenseInfo()
	{
		return array(
			'key' => CodeMate_Helper::settings('key', $this->pluginfo->id.'-license'),
			'status' => CodeMate_Helper::getSettings($this->pluginfo->settingsName.'-license-status'),
		);
	}

}
}

