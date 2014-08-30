<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Wrapper class to access SMF user information from one point
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class User
{
	/**
	 * Returns users login state
	 * @return bool
	 */
	public static function isLogged()
	{
		$user = Context::getByKey('user');
		return $user['is_logged'];
	}

	/**
	 * Returns users admin state
	 * @return boolean
	 */
	public static function isAdmin()
	{
		$user = Context::getByKey('user');
		return $user['is_admin'] ? true : false;
	}

	/**
	 * Returns specific informations from $user_info
	 * @param string $key
	 * @return mixed
	 */
	public static function getInfo($key)
	{
		global $user_info;

		if (isset($user_info[$key]))
			return $user_info[$key];
	}

	/**
	 * Returns current users id.
	 * Returns 0 when user ist not logged in.
	 * @return number
	 */
	public static function getId()
	{
		return self::isLogged() ? self::getInfo('id') : 0;
	}

	/**
	 * Returns groups user is in
	 */
	public function getGroups()
	{
		global $user_info;
		return isset($user_info['groups']) ? $user_info['groups'] : false;
	}
}
?>
