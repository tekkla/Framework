<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Wrapper class for accessing some SMF stuff
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Smf
{

	/**
	 * Includes a needed source file.
	 * Parameter can be an array of files to include.
	 * @param string $file
	 */
	public static function useSource($file)
	{
		if (is_array($file))
			foreach ( $file as $include )
				require_once SOURCEDIR . '/' . $include . '.php';
		else
			require_once SOURCEDIR . '/' . $file . '.php';
	}

	/**
	 * Function to load a template language file
	 * @param string $template_name
	 */
	public static function loadLanguage($template_name)
	{
		loadLanguage($template_name);
	}

	public function Settings($key)
	{
		global $settings;
		return isset($settings[$key]) ? $settings[$key] : false;
	}
}
?>
