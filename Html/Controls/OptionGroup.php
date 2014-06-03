<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\Error;
use Web\Framework\Html\Elements\FormElement;
use Web\Framework\Html\Form\Label;
use Web\Framework\Html\Form\Option;
use Web\Framework\Html\Form\Checkbox;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a optiongroup control
 * It is a set of checkboxes grouped together.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class OptionGroup extends FormElement
{
    /**
     * Options storage
     * @var array
     */
    private $options = array();

    /**
     * Returns an OptionGroup Object
     * @param string $model
     * @return \web\framework\Html\controls\OptionGroup
     */
    public static function factory($name)
    {
        $obj = new OptionGroup();
        $obj->setName($name);
        return $obj;
    }

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
    public function build($wrapper = null)
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

            // Create label
            $html .= Label::factory($control->getId(), $option->getInner() . ' ' . $control->build())->build();

            $html .= '</div>';
        }

        return $html;
    }
}
?>
