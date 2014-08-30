<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * File error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
final class FileError extends ErrorAbstract
{
	protected $codes = array(
		2000 => 'General', 
		2001 => 'FileNotFound', 
		2002 => 'FileAlreadyExists'
	);
}
?>
