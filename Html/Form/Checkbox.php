<?php

namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Interfaces\HtmlInterface;

use Web\Framework\Html\Elements\FormElement;

class Checkbox extends FormElement implements HtmlInterface
{

	// element specific value for
	// type: text|hidden|button|submit
	private $type;

	public static function factory($name)
	{
		$obj = new Checkbox();
		$obj->setName($name);
		return $obj;
	}

	function __construct()
	{
		$this->setElement('input');
		$this->setType('checkbox');
		$this->addData('web-control', 'checkbox');
	}

	public function setValidation($expression)
	{
		$this->addCss('validate[' . $expression . ']');
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->addAttribute('type', $type);
		return $this;
	}

	public function setValue($value)
	{
		$this->addAttribute('value', $value);
		return $this;
	}

	public function getValue()
	{
		return $this->getAttribute('value');
	}

	public function isChecked($state = null)
	{
		$attrib = 'checked';

		if (!isset($state))
			return $this->checkAttribute($attrib);

		if ($state==0)
			$this->removeAttribute($attrib);
		else
			$this->addAttribute($attrib, false);

		return $this;
	}
}

?>