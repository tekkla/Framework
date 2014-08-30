<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');
	
	// Used classes
use Web\Framework\Lib\Abstracts\SingletonAbstract;

/**
 * Interface class to SMFs global $context var
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Context extends SingletonAbstract
{

	/**
	 * Wrapper method to access a key in $context[user]
	 * @param string $key
	 * @return mixed
	 */
	public static function User($key)
	{
		return self::getByKey('user', $key);
	}

	/**
	 * Wrapper method to access a key in $context[user_info]
	 * @param string $key
	 * @return mixed
	 */
	public static function getUserinfo($key)
	{
		return self::getByKey('userinfo', $key);
	}

	/**
	 * Wrapper method to access a key in $context[settings]
	 * @param string $key
	 * @return mixed
	 */
	public static function getSettings($key)
	{
		return self::getByKey('settings', $key);
	}

	/**
	 * Wrapper method to access a key in $context[mod_settings]
	 * @param string $key
	 * @return mixed
	 */
	public static function getModSettings($key)
	{
		return self::getByKey('modsettings', $key);
	}

	/**
	 * Wrapper method to access a key in $context[session]
	 * @param string $key
	 * @return mixed
	 */
	public static function getSession($key)
	{
		return self::getByKey('session', $key);
	}

	/**
	 * Getter interface to $context with maximum depth of 3 dimensions
	 * @todo Same as setter. Do not like it much either...
	 * @throws Error
	 * @return mixed
	 */
	public static function getByKey()
	{
		global $context;
		
		$num_args = func_num_args();
		$args = func_get_args();
		
		if ($num_args == 1 && array_key_exists($args[0], $context))
			return $context[$args[0]];
		
		if ($num_args == 2 && array_key_exists($args[0], $context) && array_key_exists($args[1], $context[$args[0]]))
			return $context[$args[0]][$args[1]];
		
		Throw new Error('Requested $context key does not exist.');
	}

	/**
	 * Setter interface to $context with maximum of 3 dimensions.
	 * @todo Do not like this very much... try to think different
	 * @throws Error
	 */
	public static function setTo()
	{
		global $context;
		
		$num_args = func_num_args();
		$args = func_get_args();
		
		if ($num_args < 2)
			Throw new Error('Setting a $context[] value needs at least 2 parameters.');
		
		if ($num_args == 2)
			$context[$args[0]] = $args[1];
		
		if ($num_args == 3)
		{
			if (!isset($context[$args[0]]))
				$context[$args[0]] = array();
			
			$context[$args[0]][$args[1]] = $args[2];
		}
	}

	/**
	 * Adds a string / array of strings to $context['html_headers']
	 * @param string|array $val
	 */
	public static function addHtmlHeader($val)
	{
		global $context;
		
		if (is_array($val))
			$val = trim(implode(PHP_EOL, $val));
		
		if (!isset($context['html_headers']))
			$context['html_headers'] = '';
		
		$context['html_headers'] .= $val;
	}

	/**
	 * Sets META pagetitle.
	 * Checks framework config for boardname to add pagetitle
	 * @param string $page_title
	 */
	public static function setPageTitle($page_title)
	{
		global $mbname;
		self::setTo('page_title', $page_title . ' // ' . $mbname);
	}

	/**
	 * Sets META keywords.
	 * @param string|array $keywords Keywords sepreated by comma. If array, the elements will be joined by comma.
	 */
	public static function setPageKeywords($keywords)
	{
		if (is_array($keywords))
			$keywords = implode(', ', $keywords);
		
		self::setTo('page_keywords', $keywords);
	}

	/**
	 * Sets META page descriptions
	 * @param string $description
	 */
	public static function setPageDescription($description)
	{
		self::setTo('page_description', $description);
	}

	/**
	 * Returns complete $context
	 * @return array
	 */
	public static function getAll()
	{
		global $context;
		return $context;
	}

	/**
	 * Returns complete server info or one specific key
	 * @param string $key Optional key to get
	 * @return string array
	 */
	public static function getServer($key = null)
	{
		$data = self::getByKey('server');
		return isset($key) ? $data[$key] : $data;
	}

	/**
	 * Adds a text / link to the linktree.
	 * @param string $name
	 * @param string $url
	 */
	public static function addLinktree($name, $url = null)
	{
		global $context;
		
		$context['linktree'][] = array(
			'url' => $url, 
			'name' => $name
		);
	}

	/**
	 * Adds a string to the copyright info
	 * @param string $text
	 */
	public static function addCopyright($text)
	{
		global $context;
		$context['copyrights']['mods'][] = $text;
	}
}
?>
