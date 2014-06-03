<?php

namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Error;

use Web\Framework\Lib\Interfaces\HtmlInterface;
use Web\Framework\Html\Elements\FormElement;

/**
 *
 * @author michael
 *
 */
class Textarea extends FormElement implements HtmlInterface
{

	public static function factory($name)
	{
		$obj = new Textarea();
		$obj->setName($name);
		return $obj;
	}

	function __construct()
	{
		$this->setElement('textarea');
		$this->addCss(array(
			'form-control',
			'web_form_textarea',
		));
		$this->addData('web-control', 'textarea');
	}

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