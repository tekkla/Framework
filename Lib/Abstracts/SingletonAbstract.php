<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Abstracts\ClassAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Abstract class for singleton patterns
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Abstracts
 * @namespace Web\Framework\Lib\Abstracts
 */
abstract class SingletonAbstract extends ClassAbstract
{
	/**
	 * Instance storage
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Creates the singleton instance of the class
	 */
	final public static function getInstance()
	{
		// Get the name of the child class
		$class = get_called_class();

		// Create class object it does not exist and add it to instance storage
		if (!isset(self::$instances[$class]))
			self::$instances[$class] = new $class();

		// Return class object from instance storage
		return self::$instances[$class];
	}

	protected function __construct()
	{
	}

	private function __clone()
	{
	}
}
?>
