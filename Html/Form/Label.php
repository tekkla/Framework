<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\FormElementAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Label Form Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Label extends FormElementAbstract
{
	protected $element = 'label';

	public static function factory($for, $inner=null)
	{
		$obj = new Label();
		$obj->setFor($for);

		if (isset($inner))
			$obj->setInner($inner);
		else
			$obj->setInner($for);

		return $obj;
	}

	public function setFor($for)
	{
		$this->removeAttribute('for');
		$this->addAttribute('for', $for);
		return $this;
	}
}
?>
