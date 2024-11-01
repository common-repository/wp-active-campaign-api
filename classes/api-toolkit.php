<?php

/**
 * API Toolkit.
 * 
 * @version 1.0.0
 */

Class API_Toolkit
{

	/**
	 * Load our files base on the path directory.
	 *
	 * @param string $path        Directory path
	 * @param string $file_name   Name of the file
	 * @param array  $ignore      Files to ignore
	 */
	public static function load_files($path, $ignore = array(), $file_name = '*.php')
	{
		foreach (glob($path . $file_name) as $file) {
			if (!in_array($file, $ignore))
				require_once $file;
		}
	}

	/**
	 * Copy files from a directory to a different directory.
	 * Ideal for moving plugin templates for the current theme.
	 *
	 * @param type $source
	 * @param type $dest
	 */
	public static function copy($source, $dest)
	{
		foreach (glob($source . '*.php') as $file) {
			copy($file, str_replace($source, $dest, $file));
		}
	}

	/**
	 * List of array for USA
	 *
	 * @return array States
	 */
	public static function state_lists()
	{
		$states = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);

		return $states;
	}

}

