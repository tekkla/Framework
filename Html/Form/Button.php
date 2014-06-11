<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Html\Elements\FormElement;
use Web\Framework\Lib\Errors\NoValidParameterError;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a html object for uses as button in forms.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Button extends FormElement
{
    /**
     * Name of icon to use
     * @var
     */
    private $button_icon;

    /**
     * Type of button
     * @var string
     */
    private $type = 'button';

    /**
     * Type
     * @var string
     */
    private $button_display = 'default';

    /**
     * Size
     * @var string
     */
    private $button_size;

    /**
     * Factory method
     * @param string $name
     * @return \Web\Framework\Html\Form\Button
     */
    public static function factory($name = null)
    {
        $obj = new Button();

        if ($name)
            $obj->setName($name);

        return $obj;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setElement('button');
        $this->addCss('btn');
        $this->addData('web-control', 'button');
    }

    /**
     * Sets button value
     * @param unknown $value
     * @return \Web\Framework\Html\Form\Button
     */
    public function setValue($value)
    {
        $this->addAttribute('value', $value);
        return $this;
    }

    /**
     * Sets name of the fontawesome icon to use with the button.
     * @param string $$button_icon Name of the icon without the leadin "fa-"
     * @return \Web\Framework\Html\Form\Button
     */
    public function useIcon($button_icon)
    {
        $this->button_icon = $button_icon;
        return $this;
    }

    /**
     * Sets buttontype to: default
     * @return \Web\Framework\Html\Form\Button
     */
    public function isDefault()
    {
        $this->button_type = 'default';
        return $this;
    }

    /**
     * Sets buttontype to: primary
     * @return \Web\Framework\Html\Form\Button
     */
    public function isPrimary()
    {
        $this->button_type = 'primary';
        return $this;
    }

    /**
     * Sets buttontype to: danger
     * @return \Web\Framework\Html\Form\Button
     */
    public function isDanger()
    {
        $this->button_type = 'danger';
        return $this;
    }

    /**
     * Sets buttontype to: info
     * @return \Web\Framework\Html\Form\Button
     */
    public function isInfo()
    {
        $this->button_type = 'info';
        return $this;
    }

    /**
     * Sets buttontype to: warning
     * @return \Web\Framework\Html\Form\Button
     */
    public function isWarning()
    {
        $this->button_type = 'warning';
        return $this;
    }

    /**
     * Sets buttontype to: success
     * @return \Web\Framework\Html\Form\Button
     */
    public function isSuccess()
    {
        $this->button_type = 'success';
        return $this;
    }

    /**
     * Sets buttontype to: link
     * @return \Web\Framework\Html\Form\Button
     */
    public function isLink()
    {
        $this->button_type = 'link';
        return $this;
    }

    /**
     * Set button size to: xs
     * @return \Web\Framework\Html\Form\Button
     */

    public function sizeXs()
    {
        $this->button_size = 'xs';
        return $this;
    }

    /**
     * Set button size to: sm
     * @return \Web\Framework\Html\Form\Button
     */
    public function sizeSm()
    {
        $this->button_size = 'sm';
        return $this;
    }

    /**
     * Set button size to: md
     * @return \Web\Framework\Html\Form\Button
     */
    public function sizeMd()
    {
    	$this->button_size = 'md';
    	return $this;
    }

    /**
     * Set button size to: lg
     * @return \Web\Framework\Html\Form\Button
     */
    public function sizeLg()
    {
        $this->button_size = 'lg';
        return $this;
    }

    /**
     * Sets element type to: button (default)
     * @return \Web\Framework\Html\Form\Button
     */
    public function isButton()
    {
        $this->type = 'button';
        return $this;
    }

    /**
     * Sets element type to: submit
     * @return \Web\Framework\Html\Form\Button
     */
    public function isSubmit()
    {
        $this->type = 'submit';
        return $this;
    }

    /**
     * Sets element type to: reset
     * @return \Web\Framework\Html\Form\Button
     */
    public function isReset()
    {
        $this->type = 'reset';
        return $this;
    }

    /**
     * Sets element type
     * @param string $type Type of element (submit, reset or button)
     * @throws NoValidParameterError
     * @return \Web\Framework\Html\Form\Button
     */
    public function setType($type)
    {
        $types = array(
            'submit',
            'reset',
            'button'
        );

        if (!in_array($type, $types))
        	Throw new Error('Wrong button type set.', 1000, array($type, $types));

        $this->type = $type;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Web\Framework\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build($wrapper = null)
    {
        $this->addAttribute('type', $this->type);

        if (isset($this->button_icon))
            $this->setInner('<i class="fa fa-' . $this->button_icon . '"></i> ' . $this->getInner());

        $this->addCss('btn-' . $this->button_display);

        if (isset($this->button_size))
            $this->addCss('btn-' . $this->button_size);

        return parent::build($wrapper);
    }
}
?>
