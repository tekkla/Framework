<?php
namespace Web\Framework\Html\Form;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Checkbox Form Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Checkbox extends Input
{
	protected $type = 'checkbox';
	protected $element = 'input';
	protected $data = array(
		'web-control' => 'checkbox'
	);
}
?>
