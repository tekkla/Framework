<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\FormElementAbstract;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Input Form Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Input extends FormElementAbstract
{

    // element specific value for
    // type: text|hidden|button|submit
    // default: text
    protected $type = 'text';
    protected $element = 'input';
    protected $data = array(
    	'web-control' => 'input',
    );

    public function setType($type)
    {
        $this->type = $type;
        $this->addAttribute('type', $type);
        $this->addData('web-control', $type == 'hidden' ? 'hidden' : 'input');

        return $this;
    }

    /*+
     * Returns the input type attribute
     */
    public function getType()
    {
    	return $this->type;
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

	public function isMultiple($bool = true)
	{
		if ($bool == true)
			$this->addAttribute('multiple');
		else
			$this->removeAttribute('multiple');
	}

    public function build($wrapper = null)
    {
        $this->addAttribute('type', $this->type);
        return parent::build($wrapper);
    }
}
?>
