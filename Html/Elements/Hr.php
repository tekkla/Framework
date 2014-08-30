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
 * @subpackage Html\Element
 * @license BSD
 * @copyright 2014 by author
 */
class Hr extends HtmlAbstract
{
	protected $element = 'hr';
}
?>
