<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Error;
use Web\Framework\Html\Elements\FormElement;

class Input extends FormElement
{

    // element specific value for
    // type: text|hidden|button|submit
    private $type;

    /**
     *
     * @param string $name
     * @param string $type
     * @return \Web\Framework\Html\Form\Input
     */
    public static function factory($name)
    {
        $obj = new Input();
        $obj->setName($name);
        return $obj;
    }

    function __construct()
    {
        $this->setElement('input');
        $this->setType('text');
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->addAttribute('type', $type);
        $this->addData('web-control', $type == 'hidden' ? 'hidden' : 'input');

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

    public function setSize($size)
    {
        if (!is_int($size))
            Throw new Error('Framework: Input size needs to be an integer.');

        $this->addAttribute('size', $size);
        return $this;
    }

    public function setMaxlenght($maxlenght)
    {
        if (!is_int($maxlenght))
            Throw new Error('Framework: Input maxlenght needs to be an integer.');

        $this->addAttribute('maxlenght', $maxlenght);
        return $this;
    }

    public function setPlaceholder($placeholder)
    {
        $this->addAttribute('placeholder', $placeholder);
        return $this;
    }

    public function isChecked()
    {
        $this->addAttribute('checked', 'checked');
        return $this;
    }

    public function build($wrapper = null)
    {
        return parent::build($wrapper);
    }

    public function isMultiple($bool = true)
    {
        if ($bool == true)
            $this->addAttribute('multiple');
        else
            $this->removeAttribute('multiple');
    }
}
?>
