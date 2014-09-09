<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\Abstracts\FormElementAbstract;
use Web\Framework\Html\Form\Option;
use Web\Framework\Html\Form\Checkbox;
use Web\Framework\Lib\Error;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Creates a optiongroup control
 * It is a set of checkboxes grouped together.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 * @final
 */
final class OptionGroup extends FormElementAbstract
{
	/**
	 * Options storage
	 * @var array
	 */
	private $options = array();

	/**
	 * Add an option to the optionslist and returns a reference to it.
	 * @return Option
	 */
	public function &createOption()
	{
		$unique_id = uniqid('option_');

		$this->options[$unique_id] = Option::factory();

		return $this->options[$unique_id];
	}

	/**
	 * Builds the optiongroup control and returns the html code
	 * @see \Web\Framework\Lib\Html::build()
	 * @return string
	 */
	public function build()
	{
		if (empty($this->options))
			Throw new Error('OptionGroup Control: No Options set.');

		$html = '';

		foreach ( $this->options as $option )
		{
			$html .= '<div class="checkbox">';

			// Create name of optionelement
			$option_name = $this->getName() . '[' . $option->getValue() . ']';
			$option_id = $this->getId() . '_' . $option->getValue();

			// Create checkox
			$control = Checkbox::factory($option_name)->setId($option_id)->setValue($option->getValue())->addAttribute('title', $option->getInner());

			// If value is greater 0 this checkbox is selected
			if ($option->isSelected())
				$control->isChecked(1);

				// Build control
			$html .= '<label>' . $control->build() . $option->getInner() . '</label>';

			$html .= '</div>';
		}

		return $html;
	}
}
?>
