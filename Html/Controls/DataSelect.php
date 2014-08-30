<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\App;
use Web\Framework\Lib\Lib;
use Web\Framework\Html\Form\Select;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Creates a data driven select element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 */
final class DataSelect extends Select
{
	/**
	 * The data from which the options of the select will be created
	 *
	 * @var array
	 */
	private $datasource;

	/**
	 * How to use the data in the selects option
	 *
	 * @var string
	 */
	private $datatype;

	/**
	 * The value which should causes an option to be selected.
	 * Can be a value or an array of values
	 *
	 * @var mixed
	 */
	private $selected;

	/**
	 * Sets a datasource
	 *
	 * @param string $app
	 *			Name of app the model is of
	 * @param string $model
	 *			Name of model
	 * @param string $func
	 *			Action to run on model
	 * @param string $params
	 *			Array of parameter used by the model
	 * @param string $datatype
	 *			How to use the modeldata in the select options (value and inner value)
	 * @return \Web\Framework\Html\Controls\DataSelect
	 */
	public function setDataSource($app_name, $model, $func, $param = array(), $datatype = 'assoc')
	{
		// Create model object
		$model = App::create($app_name)->getModel($model);

		// Get data from model and use is as datasource
		$this->datasource = Lib::invokeMethod($model, $func, $param);

		// Set the dataype
		$this->datatype = $datatype;

		return $this;
	}

	/**
	 * Set one or more values to set as selected
	 *
	 * @param
	 *			int|string|array
	 * @return \Web\Framework\Html\Controls\DataSelect
	 */
	public function setSelectedValue($selected)
	{
		$this->selected = $selected;
		return $this;
	}

	/**
	 * Builds and returna html code
	 *
	 * @see \Web\Framework\Html\Form\Select::build()
	 */
	public function build()
	{
		foreach ( $this->datasource as $val => $inner )
		{
			$option = $this->createOption();

			// inner will always be used
			$option->setInner($inner);

			// if we have an assoc datasource we use the value attribute
			if ($this->datatype == 'assoc')
				$option->setValue($val);

				// in dependence of the data type is value to be selected $val or $inner
			if (isset($this->selected))
			{
				// A list of selected?
				if (is_array($this->selected))
				{
					if (array_search(($this->datatype == 'assoc' ? $val : $inner), $this->selected))
						$option->isSelected(1);
				}
				// Or a value to look for?
				else
				{
					if ($this->selected == ($this->datatype == 'assoc' ? $val : $inner))
						$option->isSelected(1);
				}
			}
		}

		return parent::build();
	}
}
?>
