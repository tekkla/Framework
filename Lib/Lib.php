<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class to store framework global functions
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @version 0.9
 * @package WebExt
 * @subpackage Lib
 * @copyright 2014 by author
 * @license BSD
 * @todo Try to put the methods to a better place so this file is not longer needed
 */
class Lib
{

	/**
	 * Cleans up user input from "evil code".
	 * It does much for you, but always
	 * have in mind, that there is no 100% security.
	 * @param $data
	 * @return Ambigous <string, Mixed, String>
	 */
	public static function sanitizeUserInput($data)
	{
		$filter = new Inputfilter();
		return $filter->process($data);
	}

	/**
	 * Converts an array into an Data object.
	 * This method works recursive.
	 * @param array $data
	 * @return Data
	 */
	public static function toObject($data)
	{
		// Return $data when it is already an object
		if (is_object($data))
			return $data;

		$data = new Data($data);

		foreach ( $data as $key => $val )
		{
			if (self::isSerialized($val))
				$val = unserialize($val);

			if (is_array($val))
				$val = self::toObject($val);

			$val = empty($val) && strlen($val) == 0 ? '' : $val;

			$data->{$key} = $val;
		}

		return $data;
	}

	/**
	 * Converts an object and it's public members recursively into an array.
	 * Use this if you want to convert objects into array.
	 * @param object $obj
	 * @return array
	 */
	public static function fromObjectToArray($obj)
	{
		if (!is_object($obj))
			return $obj;

		$out = array();

		foreach ( $obj as $key => $val )
		{
			if (is_object($val))
				$out[$key] = self::fromObjectToArray($val);
			else
				$out[$key] = $val;
		}

		return $out;
	}

	/**
	 * Tests if an input is valid PHP serialized string.
	 *
	 * Checks if a string is serialized using quick string manipulation
	 * to throw out obviously incorrect strings. Unserialize is then run
	 * on the string to perform the final verification.
	 *
	 * Valid serialized forms are the following:
	 * <ul>
	 * <li>boolean: <code>b:1;</code></li>
	 * <li>integer: <code>i:1;</code></li>
	 * <li>double: <code>d:0.2;</code></li>
	 * <li>string: <code>s:4:"test";</code></li>
	 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
	 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
	 * <li>null: <code>N;</code></li>
	 * </ul>
	 * @param string $value test for serialized form
	 * @param mixed $result unserialize() of the $value
	 * @return boolean if $value is serialized data, otherwise false
	 */
	public static function isSerialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value))
			return false;

		// Empty strings cannot get unserialized
		if (strlen($value) === 0)
			return false;

		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;')
		{
			$result = false;
			return true;
		}

		$length = strlen($value);
		$end = '';

		switch ($value[0])
		{
			case 's' :
				if ($value[$length - 2] !== '"')
				{
					return false;
				}
			case 'b' :
			case 'i' :
			case 'd' :
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a' :
			case 'O' :
				$end .= '}';

				if ($value[1] !== ':')
				{
					return false;
				}

				switch ($value[2])
				{
					case 0 :
					case 1 :
					case 2 :
					case 3 :
					case 4 :
					case 5 :
					case 6 :
					case 7 :
					case 8 :
					case 9 :
						break;

					default :
						return false;
				}
			case 'N' :
				$end .= ';';

				if ($value[$length - 1] !== $end[0])
				{
					return false;
				}
				break;

			default :
				return false;
		}

		if (($result = @unserialize($value)) === false)
		{
			$result = null;
			return false;
		}

		return true;
	}

	/**
	 * Executes object method by using Reflection
	 * @throws MethodNotExistsError
	 * @throws ParameterNotSetError
	 * @return mixed
	 */
	public static function invokeMethod(&$obj, $method, $param = array())
	{
		// Look for the method in object. Throw error when missing.
		if (!method_exists($obj, $method))
			Throw new Error('Method not found.', 5000, array(
				$method,
				$obj
			));

			// Convert possible parameter object to array
		$param = self::fromObjectToArray($param);

		// Get reflection method
		$method = new \ReflectionMethod($obj, $method);

		// Init empty arguments array
		$args = array();

		// Get list of parameters from reflection method object
		$method_parameter = $method->getParameters();

		// Let's see what arguments are needed and which are optional
		foreach ( $method_parameter as $parameter )
		{
			// Get current paramobject name
			$param_name = $parameter->getName();

			// Parameter is not optional and not set => throw error
			if (!$parameter->isOptional() && !isset($param[$param_name]))
				Throw new Error('Missing parameter', 2001, array(
					$param_name
				));

				// If parameter is optional and not set, set argument to null
			$args[] = $parameter->isOptional() && !isset($param[$param_name]) ? null : $param[$param_name];
		}

		// Return result executed method
		return $method->invokeArgs($obj, $args);
	}
}
?>
