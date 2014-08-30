<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class to manipulate and transform strings
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class String
{

	/**
	 * Shortens a string to the given length and adds ... at the end of string
	 * @param string $string
	 * @param int $length
	 * @param string $addition
	 * @return string
	 */
	public static function shortenString(&$string, $length, $addition = ' [...]')
	{
		// Shorten only what is longer than the length
		if (strlen($string) < $length)
			return $string;
			
			// Shorten string by length
		$string = substr($string, 0, $length);
		
		// Shorten further until last occurence of a ' '
		$string = substr($string, 0, strrpos($string, ' '));
		
		// Add addition
		$string .= $addition;
		
		// Done.
		return $string;
	}

	/**
	 * Converts stings with underscores into case sensitiv strings.
	 * Important: This Converter is one of the most important in the complete framework
	 * because it's used on each request for getting the names of app, controller
	 * and function.
	 *
	 * @param string $val
	 * @return string
	 * @tutorial <code>
	 *		   <?php
	 *
	 *		   $string = 'my_name';
	 *		   $string = String::camelize($string);
	 *
	 *		   echo $string;
	 *		   ?>
	 *		   </code>
	 *		   Result: MyName
	 */
	public static function camelize(&$string, $upper_first = true)
	{
		// even if there is no underscore in string, the first char will be converted to uppercase
		if (strpos($string, '_') == 0 && $upper_first == true)
		{
			$string = ucwords($string);
		}
		else
		{
			$string = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($string))));
			
			if ($upper_first == false)
				$string = lcfirst($string);
		}
		
		return $string;
	}

	/**
	 * Converts camel case strings into underscored strings.
	 * Important: This Converter is one of the most important in the complete framework
	 * because it's used on each request for getting the names of app, controller
	 * and function.
	 *
	 * @param string $val
	 * @return string Example:
	 *		 IN: MyName | OUT: my_name
	 *		 IN: ThisIsATest | OUT: this_is_a_test
	 */
	public static function uncamelize($string)
	{
		$string = (string) $string;
		
		// set first letter to lowercase
		$string[0] = strtolower($string[0]);
		
		// replace all other with _{letter}
		$string = preg_replace_callback('/([A-Z])/', function ($c)
		{
			return '_' . strtolower($c[1]);
		}, $string);
		
		$string = trim(preg_replace('@[_]{2,}@', '_', $string), '_');
		
		return $string;
	}

	/**
	 * Normalizes a string
	 * @param string $string
	 * @return string
	 */
	public static function normalizeString($string)
	{
		$table = array(
			'Š' => 'S', 
			'š' => 's', 
			'Đ' => 'Dj', 
			'đ' => 'dj', 
			'Ž' => 'Z', 
			'ž' => 'z', 
			'Č' => 'C', 
			'č' => 'c', 
			'Ć' => 'C', 
			'ć' => 'c', 
			'À' => 'A', 
			'Á' => 'A', 
			'Â' => 'A', 
			'Ã' => 'A', 
			'Ä' => 'Ae', 
			'Å' => 'A', 
			'Æ' => 'A', 
			'Ç' => 'C', 
			'È' => 'E', 
			'É' => 'E', 
			'Ê' => 'E', 
			'Ë' => 'E', 
			'Ì' => 'I', 
			'Í' => 'I', 
			'Î' => 'I', 
			'Ï' => 'I', 
			'Ñ' => 'N', 
			'Ò' => 'O', 
			'Ó' => 'O', 
			'Ô' => 'O', 
			'Õ' => 'O', 
			'Ö' => 'Oe', 
			'Ø' => 'Oe', 
			'Ù' => 'U', 
			'Ú' => 'U', 
			'Û' => 'U', 
			'Ü' => 'Ue', 
			'Ý' => 'Y', 
			'Þ' => 'B', 
			'ß' => 'Ss', 
			'à' => 'a', 
			'á' => 'a', 
			'â' => 'a', 
			'ã' => 'a', 
			'ä' => 'ae', 
			'å' => 'a', 
			'æ' => 'a', 
			'ç' => 'c', 
			'è' => 'e', 
			'é' => 'e', 
			'ê' => 'e', 
			'ë' => 'e', 
			'ì' => 'i', 
			'í' => 'i', 
			'î' => 'i', 
			'ï' => 'i', 
			'ð' => 'o', 
			'ñ' => 'n', 
			'ò' => 'o', 
			'ó' => 'o', 
			'ô' => 'o', 
			'õ' => 'o', 
			'ö' => 'oe', 
			'ø' => 'oe', 
			'ù' => 'u', 
			'ú' => 'u', 
			'û' => 'u', 
			'ü' => 'ue', 
			'ý' => 'y', 
			'ý' => 'y', 
			'þ' => 'b', 
			'ÿ' => 'y', 
			'Ŕ' => 'R', 
			'ŕ' => 'r'
		);
		
		return strtr($string, $table);
	}
}
?>
