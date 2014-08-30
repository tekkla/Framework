<?php
namespace Web\Framework\Lib;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class: Security
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Security
{
	/**
	 * Global method to prove user access.
	 * @param string|array $perms
	 * @param string $mode
	 * @return boolean
	 */
	public static function checkAccess($perms=array(), $mode = 'smf', $force = false)
	{
		// Perms check with boolean result
		if ($mode == 'smf' && $force == false)
			return allowedTo($perms);

			// Perms check with error result
		if ($mode == 'smf' && $force == true)
			isAllowedTo($perms);
	}
}
?>
