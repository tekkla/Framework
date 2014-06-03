<?php
namespace Web\Framework\Lib;

use Framework\Lib\Errors\MissingModelError;
use Framework\Lib\Errors\MissingModelDefinitionError;
use Framework\Lib\Errors\MissingModelDataError;
use Web\Framework\Lib\Errors\NoValidParameterError;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Validator class to validate model data
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 * @todo Split into validator and function?
 */
class Validator extends Lib
{
	/**
	 * The field name to check
	 * @var mixed
	 */
	private $fld;

	/**
	 * The value to check
	 * @var mixed
	 */
	private $value;

	/**
	 * The associated mdoel
	 * @var Model
	 */
	private $model;

	/**
	 * Simple name of validation function
	 * @var unknown
	 */
	private $func;

	/**
	 * Array of params to send to validation functions
	 * @var unknown
	 */
	private $params=array();

	/**
	 * Possible rule message
	 * @var string
	 */
	private $msg;

	/**
	 * The current validation error message
	 * @var unknown
	 */
	private $error;

	/**
	 * Array of shorthand function names and the corresponding validator methods
	 * @var array
	 */
	private $functions = array(

		// existance
		'required' 	=> '__Required',
		'empty'		=> '__Empty',
		'blank'		=> '__Blank',

		// sizes
		'min'		=> '__Min',
		'max'		=> '__Max',
		'range'		=> '__Range',

		// date	/ time
		'date'		=> '__Date',
		'date-iso'	=> '__DateIso',
		'datetime'	=> '__DateTime',

		'time'		=> '__Time',
		'time-24'	=> '__Time24',

		// contact
		'phone'		=> '__Phone',

		// strings
		'alpha'		=> '__OnlyLetter',
		'alnum'		=> '__OnlyLetterNumber',

		// web
		'mail'		=> '__Email',
		'url'		=> '__Url',
		'ip4'		=> '__IpV4',

		// regexp
		'regex'		=> '__CustomRegExp',

		// numbers
		'num'		=> '__OnlyNumber',
		'int'		=> '__Integer',

		'float'		=> '__Float',

		'function'	=> '__CustomFunction'
	);

	/**
	 * Injector for the model to check.
	 * @param Model $model
	 */
	public function attachModel(Model &$model)
	{
		$this->model = $model;
	}

	/**
	 * Starts validation process for the set field and content
	 * If a check fails, a message will be set in the messegaes storage.
	 */
	public function validate()
	{
		// No model, no validation, but error :P
		if (!isset($this->model))
			Throw new MissingModelError('validator');

		// No data to validate? Throw error.
		if (!$this->model->data)
			Throw new MissingModelDataError('validator');

		// No column definition in model? Throw error.
		if (!isset($this->model->columns) && $this->model->getColumns() == false)
			Throw new MissingModelDefinitionError('validator');

		// Try to validate all model data values
		foreach ($this->model->data as $fld => $val)
		{
			// Have a look for a set validation rule on this field.
			// Not set field means skip this field.
			if (!isset($this->model->validate[$fld]))
				continue;

			// Only check fields, that exist in the definition
			/*if (!isset($this->model->Definition->{$fld}))
			{
				error_log(sprintf(Txt::get('web_model_error_field_not_exist'), $fld, $this->name));
				continue;
			}*/

			// Our current fieldname
			$this->field = $fld;

			// Our current value (trimmed)
			$this->value = trim($val);

			// Get the rules for this field from model
			$rules = $this->model->validate[$fld];

			// Convert single string validation defs to array
			if (!is_array($rules))
				$rules = array($rules);

			// Walk through the rules set in models validate property
			foreach ($rules as $rule)
			{
			    // Array type rules are for checks where the func needs one or more parameter
				// So $rule[0] is the func name and $rule[1] the parameter.
				// Parameters can be of type array where the elements are used as function parameters in the .. they are set.
				if (is_array($rule))
				{
					// Get the functionname
					$this->func = $rule[0];

					// Parameters set?
					if (isset($rule[1]))
						$this->params = !is_array($rule[1]) ? array($rule[1]) : $rule[1];
					else
						$this->params = array();

					// Custom error message
					if (isset($rule[2]))
						$this->msg = $rule[2];
				}
				else
				{
					$this->func = $rule;
					$this->params = array();
					unset($this->msg);
				}

				// Calling the validation function
				call_user_func_array(array($this, $this->functions[$this->func]), $this->params);

				// Current rules message overwrites maybe set error values
				if (isset($this->msg))
					$this->error = Txt::get($this->msg);

				// If no error message is set, use the default validato error
				if (!isset($this->error))
					$this->error = Txt::get('web_validator_error');

				// Is the validation result negative eg false?
				if ($this->result == false)
				{
					// Validation ends with an error
					$this->model->addError($fld, $this->error);

					// Clear msg and error properties
					unset($this->msg, $this->error);

					// Is the clear flag set in rule, the content of the field will be cleared
					// This is useful for password fields or all other fields where you want
					// to force the user to retype data.
					if(isset($rule['clear']))
						$this->model->data->{$fld} = null;

					// Stop rest of validation if stop flag isset in rule
					if (isset($rule['stop']))
						break;
				}
			}
		}
	}

	// -------------------------------------------------------------
	// Validation methods
	// -------------------------------------------------------------

	/**
	 * Checks for a field to be set and empty.
	 */
	private function __Required()
	{
		$this->result = isset($this->model->data->{$this->field}) && !empty($this->model->data->{$this->field});
		$this->error = Txt::get('web_validator_required');
	}

	/**
	 * Checks for empty value but treats 0, -0, 0.0 as existing values
	 */
	private function __Blank()
	{
		$this->result = $this->value !== '' ? true : false;
		$this->error = Txt::get('web_validator_blank');
	}

	/**
	 * Checks for empty value like the php function empty()
	 */
	private function __Empty()
	{
		$this->result = empty($this->value) ? ($this->value == '0'.$this->value ? true : false) : true;
		$this->error = Txt::get('web_validator_empty');
	}

	/**
	 * Checks the values for the minimum length (string) or amount (numeric) given by the parameter
	 * @param int $min
	 */
	private function __Min($min, $type='string')
	{
		if ($type=='string')
			$this->__TxtMinLength($min);
		else
			$this->__NumberMin($min);
	}

	/**
	 * Checks the values for the maximum length (string) or amount (numeric) given by the parameter
	 * @param int $max
	 */
	private function __Max($max, $type='string')
	{
		if ($type=='string')
			$this->__TxtMaxLength($max);
		else
			$this->__NumberMax($max);
	}

	/**
	 * Checks the value for the minimum and maximum length (string) or amount (numeric) given by the parameters
	 * @param int $min
	 * @param int $max
	 */
	private function __Range($min, $max, $type='string')
	{
		if ($type=='string')
			$this->__TxtLengthBetween($min, $max);
		else
			$this->__NumberRange($min, $max);
	}

	/**
	 * Checks the value to be valid date by trying to convert it into timestamp.
	 */
	private function __Date()
	{
		$this->result = strtotime($this->value) === false ? false : true;
		$this->error = Txt::get('web_validator_date');
	}

	/**
	 * Checks the value to be valid date/time by trying to convert it into timestamp.
	 */
	private function __DateTime()
	{
		$this->result = strtotime($this->value) === false ? false : true;
		$this->error = Txt::get('web_validator_datetime');
	}

	/**
	 * Compares the value aginst the ISO dateformat (YYYY-mm-dd)
	 */
	private function __DateIso()
	{
		if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $this->value, $parts) == true)
		{
			$this->result = false;

			// Build time from parts
			$time = mktime(0, 0, 0, $parts[2], $parts[3], $parts[1]);

			// Build time from value
			$input_time = strtotime($this->value);

			// Compare both timestamps
			if($input_time == $time)
				$this->result = true;
		}
		else
		{
			// No matches found
			$this->result = false;
		}

		$this->error = Txt::get('web_validator_date_iso');
	}

	/**
	 * Check for a minimum text lenth
	 * @param unknown $min
	 */
	private function __TxtMinLength($min)
	{
		$this->result = strlen($this->value) >= $min;
		$this->error = sprintf(Txt::get('web_validator_textminlength'), $min);
	}

	/**
	 * Checks the length of the value against the given lenght ($max)
	 * @param int $max
	 */
	private function __TxtMaxLength($max)
	{
		$this->result = strlen( (string) $this->value) <= $max;
		$this->error = sprintf(Txt::get('web_validator_textmaxlength'), $max);
	}

	/**
	 * Checks the length of the value to be within min ($min) and max ($max) lenght.
	 * @param int $min
	 * @param int $max
	 */
	private function __TxtLengthBetween($min, $max)
	{
	    $value = (string) $this->value;

		$this->result = strlen($value) >= $min && strlen($value) <= $max;
		$this->error = sprintf(Txt::get('web_validator_textrange'), $min, $max, strlen($this->value));
	}

	/**
	 * Checks the value against the 24-hour notation
	 */
	private function __Time24()
	{
		$regexp = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_time24');
	}

	private function __Phone()
	{
		$regexp = '/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_phone');
	}

	/**
	 * Checks the value to be a valid email adress
	 */
	private function __Email()
	{
		$this->result = filter_var($this->value, FILTER_VALIDATE_EMAIL);
		$this->error = Txt::get('web_validator_email');
	}
	/**
	 * Checks the value to be of type integer
	 */
	private function __Integer()
	{
		$this->result = is_int($this->value);
		$this->error = Txt::get('web_validator_integer');
	}

	/**
	 * Checks the value to be of type float
	 */
	private function __Float()
	{
		$this->result = is_float($this->value);
		$this->error = Txt::get('web_validator_float');
	}

	/**
	 * Checks the value to be bigger or equal to parameter ($min)
	 * @param unknown $min
	 */
	private function __NumberMin($min)
	{
		$this->result = $this->value >= $min;
		$this->error = sprintf(Txt::get('web_validator_numbermin'), $min);
	}

	/**
	 * Check the value to be smaller or equal to parameter ($max)
	 * @param int $max
	 */
	private function __NumberMax($max)
	{
		$this->result = $this->value <= $max;
		$this->error = sprintf(Txt::get('web_validator_numbermax'), $max);
	}

	/**
	 * Check the value to be within a range of numbers ($min, $max)
	 * @param int $min
	 * @param int $max
	 */
	private function __NumberRange($min, $max)
	{
		$this->result = $this->value >= $min && $this->value <= $max;
		$this->error = sprintf(Txt::get('web_validator_numberrange'), $min, $max);
	}

	/**
	 * Checks the value to be a valid number
	 */
	private function __Number()
	{
		$regexp = '/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_number');
	}

	/**
	 * Checks the value to by a valid IpV4 adress
	 */
	private function __IpV4()
	{
		$regexp = '/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_ipv4');
	}

	/**
	 * Checks the value to be a valid url
	 */
	private function __Url()
	{
		$regexp = '/^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_url');
	}

	/**
	 * Checks the value to be only of numbers
	 */
	private function __OnlyNumber()
	{
		$regexp = '/^[0-9\ ]+$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_number');
	}

	/**
	 * Checks the value to be only of letters
	 */
	private function __OnlyLetter()
	{
		$regexp = '/^[a-zA-Z\ \']+$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_alpha');
	}

	/**
	 * Checks the value to be only of letters and numbers
	 */
	private function __OnlyLetterNumber()
	{
		$regexp = '/^[0-9a-zA-Z]+$/';
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_alnum');
	}

	/**
	 * Checks the value against a custom regexp
	 */
	private function __CustomRegExp($regexp)
	{
		$this->result = filter_var($this->value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
		$this->error = Txt::get('web_validator_customregex');
	}

	/**
	 * Checks the value against a comparision value. The comparemode can be defined by use.
	 */
	private function __Compare($to_compare_with, $mode='=')
	{
	    $modes = array(
	        '=',
	        '>',
	        '<',
	        '>=',
	        '<='
	    );

	    if (!in_array($mode, $modes))
	        Throw new NoValidParameterError($mode, $modes);

		switch ($mode)
		{
			case '=':
				$this->result = $this->value == $to_compare_with;
				break;

			case '>':
				$this->result = $this->value > $to_compare_with;
				break;

			case '<';
				$this->result = $this->value < $to_compare_with;
				break;

			case '>=':
				$this->result = $this->value >= $to_compare_with;
				break;

			case '<=':
				$this->result = $this->value <= $to_compare_with;
				break;
		}

		$this->error = sprintf(Txt::get('web_validator_compare'), $this->value, $to_compare_with, $mode);
	}

	/**
	 * Checks the value against a compare value to proof both to be equal in type and value.
	 * Compares only strings and numbers. All other types will return false.
	 * @param string|number $to_compare
	 */
	private function __Equals($to_compare)
	{
	    $value = $this->value;

		if (is_string($value) && is_string($to_compare))
			$this->result = $value == $to_compare ? true : false;

		elseif ((is_int($value) && is_int($to_compare)) || (is_float($value) &&  is_float($to_compare)))
			$this->result = $value == $to_compare ? true : false;
		else
			$this->result = false;

		$this->error = Txt::get('web_validator_equals');
	}

	/**
	 * Checks the value typsave to be longer/bigger than the compare value.
	 * Only strings and numbers can be compared. All othe types returns false.
	 * @param string|number $to_compare
	 */
	private function __Bigger($to_compare)
	{
	    $value = $this->value;

		if (is_string($value) && is_string($to_compare))
			$this->result = strlen($value) > strlen($to_compare) ? true : false;
		elseif ((is_int($value) && is_int($to_compare))  || (is_float($value) && is_float($to_compare)))
			$this->result = $value > $to_compare ? true : false;
		else
			$this->result = false;

		$this->error = Txt::get('web_validator_bigger');
	}

	/**
	 * Checks the value typsave to be shorter/smaller than the compare value.
	 * Only strings and numbers can be compared. All othe types returns false.
	 * @param string|number $to_compare
	 */
	private function __Smaller($to_compare)
	{
	    $value = $this->value;

		if (is_string($value) && is_string($to_compare))
			$this->result = strlen($value) < strlen($to_compare) ? true : false;
		elseif ((is_int($value) && is_int($to_compare) || is_float($value) && is_float($to_compare)))
			$this->result = $value > $to_compare ? true : false;
		else
			$this->result = false;

		$this->error = Txt::get('web_validator_smaller');
	}

	/**
	 * Compares the value against a compare value by type and lenghts.
	 * Only strings and numbers can be compared. All othe types returns false.
	 * @param string|number $to_compare
	 */
	private function __BiggerOrEqual($to_compare)
	{
	    $value = $this->value;

		if (is_string($value) && is_string($to_compare))
			$this->result = strlen($value) >= strlen($to_compare) ? true : false;

		elseif ((is_int($value) && is_int($to_compare)) || (is_float($this->value) && is_float($to_compare)))
			$this->result = $this->value >= $to_compare ? true : false;
		else
		    $this->result = false;

		$this->error = Txt::get('web_validator_bigger_or_equal');
	}

	/**
	 * Uses a model function to validate value.
	 * @param string $model_function The name of function in model
	 * @param string $message Message to show on fail. Uses modelapp relatet string for translation.
	 */
	private function __CustomFunction($model_function, $message)
	{
	    $result = $this->model->{$model_function}($this->value);

		$this->result = is_bool($result) ? $result : false;
		$this->error = $this->model->txt($message);
	}
}
?>
