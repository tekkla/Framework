<?php

namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Interfaces\HtmlInterface;
use Web\Framework\Html\Elements\FormElement;
use Web\Framework\Lib\Error;

/**
 *
 * @author michael
 *
 */
class Option extends FormElement implements HtmlInterface
{

	public static function factory()
	{
		return new Option();
	}

	function __construct()
	{
		$this->setElement('option');
		$this->addData('web-control', 'option');
	}

	/**
	 * Selected attribute setter and checker. Accepts parameter "null", "0" and "1".
	 * "null" means to check for a set disabled attribute
	 * "0" means to remove disabled attribute
	 * "1" means to set disabled attribute
	 * @param int $state
	 * @return \Web\Framework\Html\Form\Option
	 */
	public function isSelected($state=null)
	{
		$attrib = 'selected';

		if (!isset($state))
			return $this->checkAttribute($attrib);

		if ($state==0)
			$this->removeAttribute($attrib);
		else
			$this->addAttribute($attrib, false);

		return $this;
	}

	/**
	 * Sets value of option
	 * @param string|number $value
	 * @return \Web\Framework\Html\Form\Option
	 */
	public function setValue($value)
	{
		if ($value===null)
			Throw new Error('Your are not allowed to set a NULL as value for a html option.');
		
		$this->addAttribute('value', $value);
		return $this;
	}

	/**
	 * Gets value of option
	 * @return \Web\Framework\Html\Form\Option
	 */
	public function getValue()
	{
		return $this->getAttribute('value');
	}

}

?>