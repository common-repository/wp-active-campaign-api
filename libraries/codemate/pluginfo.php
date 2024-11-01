<?php

/**
 * Class for Plugin info
 * @require PHP 5.3.0 or later
 * @author Art Layese <art@codemate.net>
 */

if (!class_exists('CodeMate_Pluginfo')) {
class CodeMate_Pluginfo
{
	/**
	 * Unique plugin id
	 */
	public $id;

	/**
	 * Holder for TEXT_DOMAIN plugin constant
	 */
	public $textDomain;

	/**
	 * Holder for SETTINGS_NAME plugin constant
	 */
	public $settingsName;

	/**
	 * Holder for name/label of an Option page
	 */
	public $optionName;
}
}

