<?php
namespace Web\Framework\Lib;

/**
 * Simple getter class for $settings
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Settings
{
	public static function get($key)
	{
		global $settings;
		if (isset($settings[$key]))
			return $settings[$key];
	}
}
?>
