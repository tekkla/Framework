<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\FormElementAbstract;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Textarea Form Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
final class Textarea extends FormElementAbstract
{
	protected $element = 'textarea';
	protected $data = array(
		'web-control' => 'textarea',
	);

	public function setPlaceholder($placeholder)
	{
		$this->addAttribute('placeholder', $placeholder);
		return $this;
	}

	public function setCols($cols)
	{
		if(!is_int($cols))
			Throw new Error('Framework: Textarea cols need to be integer');

		$this->addAttribute('cols', $cols);
		return $this;
	}

	public function setRows($rows)
	{
		if(!is_int($rows))
			Throw new Error('Framework: Textarea rows need to be integer');

		$this->addAttribute('rows', $rows);
		return $this;
	}

	public function setValidation($expression)
	{
		$this->addCss('validate[' . $expression . ']');
		return $this;
	}

	public function setMaxlength($maxlength)
	{
		if (!is_int($maxlength))
			Throw new Error('Framework: Input maxlenght needs to be an integer.');

		$this->addAttribute('maxlength', $maxlength);
		return $this;
	}
}
?>
