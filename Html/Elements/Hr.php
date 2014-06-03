<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a horizontal ruler (<hr>) html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Hr extends HtmlAbstract
{
	public static function factory()
	{
		return new Hr();
	}

	function __construct()
	{
		$this->setElement('hr');
	}
}
?>
