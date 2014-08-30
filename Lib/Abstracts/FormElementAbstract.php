<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\String;
use Web\Framework\Lib\Errors\NoValidParameterError;
use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Html\Form\Label;

class FormElementAbstract extends HtmlAbstract
{
	
	/**
	 * Name of app the form is used in
	 * @var unknown
	 */
	private $app;
	
	/**
	 * Model name the element is bound to
	 * @var string
	 */
	private $model;
	
	/**
	 * Name of the field in model the element is related to
	 * @var string
	 */
	private $field;
	
	/**
	 * Description for the help block
	 * @var string
	 */
	private $description;
	
	/**
	 * Flag for binding element to a model field or not
	 * @var bool
	 */
	private $bound = true;
	
	/**
	 * Width for the element
	 * @var string
	 */
	private $element_width;
	
	/**
	 * Value for creation an hidden field for the original value
	 * Good for comparing before and then values
	 * @var bool
	 */
	private $compare_value;
	
	/**
	 * Signals that we want a label or not
	 * @var bool
	 */
	private $use_label = true;
	
	/**
	 * Public html object of a form label
	 * @var Label
	 */
	public $label;

	/**
	 * Flags this element to use no label
	 */
	public function noLabel()
	{
		unset($this->label);
		$this->use_label = false;
		return $this;
	}

	/**
	 * Creates a Label html object and injects it into the element
	 * @param string $label_text The text to show as label
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setLabel($label_text)
	{
		$this->label = new Label();
		$this->label->setInner($label_text);
		$this->use_label = true;
		return $this;
	}

	/**
	 * Returns the inner value of label or false if label is not set.
	 * @return Ambigous <boolean, string>
	 */
	public function getLabel()
	{
		return isset($this->label) ? $this->label->getInner() : false;
	}

	/**
	 * Returns the state of label using.
	 * @return boolean
	 */
	public function hasLabel()
	{
		return $this->use_label;
	}

	/**
	 * Set the app name this element is from
	 * @param string $app
	 */
	public function setApp($app_name)
	{
		$this->app_name = String::uncamelize($app_name);
		return $this;
	}

	/**
	 * Returns the name of the set app
	 * @return string
	 */
	public function getApp()
	{
		if (!isset($this->app_name))
			Throw new Error('App name was not set for this form and cannot be returned');
		
		return $this->app_name;
	}

	/**
	 * Set the app this element is from
	 * @param string $app
	 */
	public function setModelName($model_name)
	{
		$this->model_name = String::uncamelize($model_name);
		return $this;
	}

	/**
	 * Returns the mnodel name set
	 * @throws Error
	 * @return string
	 */
	public function getModel()
	{
		if (!isset($this->model_name))
			Throw new Error('Model name was not set for element and cannot be returned');
		
		return $this->model_name;
	}

	/**
	 * Set the field this element is bound to
	 * @param string $app
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setField($field_name)
	{
		$this->field_name = string::uncamelize($field_name);
		return $this;
	}

	/**
	 * Set the field this element is bound to
	 * @throws Error
	 * @return string
	 */
	public function getField()
	{
		if (!isset($this->field_name))
			Throw new Error('There is no field name bount onto this element which can be returned.');
		
		return $this->field_name;
	}

	/**
	 * Create the name of the form element by using names of app, $model and field
	 * @throws Error
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function createName()
	{
		if (!isset($this->app_name))
			throw new Error('No app name set for your form control.');
		
		if (!isset($this->model_name))
			throw new Error('No model name set for your form control.');
		
		if (!isset($this->field_name))
			throw new Error('No field name set for your form control.');
		
		$this->setName('web[' . $this->app_name . '][' . $this->model_name . '][' . $this->field_name . ']');
		
		return $this;
	}

	/**
	 * Creates the dom id using app, model and field names
	 * @throws Error
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function createId()
	{
		if (!isset($this->app_name))
			throw new Error('No app name set for your form control.');
		
		if (!isset($this->model_name))
			throw new Error('No model name set for your form control.');
		
		if (!isset($this->field_name))
			throw new Error('No field name set for your form control.');
		
		$this->setId('web_appform_' . $this->app_name . '_' . $this->model_name . '_' . $this->field_name);
		
		return $this;
	}

	/**
	 * Add autofocus attribute
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setAutofocus()
	{
		$this->addAttribute('autofocus');
		return $this;
	}

	/**
	 * Declare this element as unbound, so the FormDesigner does not need to
	 * try to fill it with data from the Model.
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setUnbound()
	{
		$this->bound = false;
		return $this;
	}

	/**
	 * Returns the current bound state
	 * @return boolean
	 */
	public function isBound()
	{
		return $this->bound;
	}

	/**
	 * Returns the type attribute but only if a setType() method exists in the childclass and the type attribute isset.
	 * This method is used to determine the type of input form elements.
	 *
	 * @return string
	 * @throws Error
	 */
	public function getType()
	{
		if (method_exists($this, 'setType'))
			return $this->getAttribute('type');
	}

	/**
	 * Set a description which will be used as a help block
	 * @param sting $text
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setDescription($text)
	{
		$this->description = $text;
		return $this;
	}

	/**
	 * Returns set state of description
	 */
	public function hasDescription()
	{
		return isset($this->description);
	}

	/**
	 * Returns the set description string
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Handles the creation state of an hidden element for comparision.
	 * If $compare parameter not set, the method returns the current state.
	 * @param boolean $state
	 * @return boolean \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function hasCompare()
	{
		return isset($this->compare_value);
	}

	/**
	 * Sets compare field value
	 * @param mixed $compare_value
	 * @return \Web\Framework\Lib\Abstracts\FormElementAbstract
	 */
	public function setCompare($compare_value)
	{
		$this->compare_value = $compare_value;
		return $this;
	}

	/**
	 * Returns compare field value
	 * @return boolean
	 */
	public function getCompare()
	{
		return $this->compare_value;
	}

	/**
	 * Assign an bootstrap element width.
	 * @param string $element_width BS grid sizes like "sm-3" or "lg-5". Needed "col-" will be added by the method.
	 * @throws NoValidParameterError
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setElementWidth($element_width = 'sm-3')
	{
		$sizes = array(
			'xs', 
			'sm', 
			'md', 
			'lg'
		);
		$allowed_widths = array();
		
		foreach ( $sizes as $size )
		{
			for($i = 1; $i < 13; $i++)
				$allowed_widths[] = $size . '-' . $i;
		}
		
		if (!in_array($element_width, $allowed_widths))
			throw new Error('Element with is no valid', 1000, array(
				$element_width, 
				$allowed_widths
			));
		
		$this->element_width = 'col-' . $element_width;
		return $this;
	}

	/**
	 * Returns a set element width or boolean false if not set.
	 * @return Ambigous <boolean, string>
	 */
	public function getElementWidth()
	{
		return isset($this->element_width) ? $this->element_width : false;
	}

	/**
	 * Checks for a set element width
	 */
	public function hasElementWidth()
	{
		return isset($this->element_width);
	}

	/**
	 * Sets an input mask for the elements
	 * @param string $mask
	 * @return \Web\Framework\Html\Elements\FormElementAbstract
	 */
	public function setMask($mask)
	{
		$this->addData('web-form-mask', $mask);
		return $this;
	}

	/**
	 * Disabled attribute setter and checker.
	 * Accepts parameter "null", "0" and "1".
	 * "null" means to check for a set disabled attribute
	 * "0" means to remove disabled attribute
	 * "1" means to set disabled attribute
	 * @param int $state
	 * @return \Web\Framework\Html\Form\Option
	 */
	public function isDisabled($state = null)
	{
		$attrib = 'disabled';
		
		if (!isset($state))
			return $this->checkAttribute($attrib);
		
		if ($state == 0)
			$this->removeAttribute($attrib);
		else
			$this->addAttribute($attrib);
		
		return $this;
	}
}
?>
