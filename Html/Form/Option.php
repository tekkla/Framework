<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\FormElementAbstract;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Option Form Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Option extends FormElementAbstract
{
	protected $element = 'option';
	protected $data = array(
		'web-control' => 'option'
	);

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
