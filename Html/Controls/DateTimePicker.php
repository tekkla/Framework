<?php

namespace Web\Framework\Html\Controls;

use Web\Framework\Html\Form\Input;
use Web\Framework\Lib\Javascript;
use Web\Framework\Lib\Error;
use Web\Framework\Lib\Txt;
use Web\Framework\Lib\Cfg;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Creates a Bootstrap datepicker control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 */
final class DateTimePicker extends Input
{
	protected $css = array(
		'web-form-datepicker'
	);

	/**
	 * Default date format: ISO date
	 *
	 * @var string
	 */
	private $format = 'YYYY-MM-DD';

	/**
	 * En/disables the date picker
	 *
	 * @var bool
	 */
	private $option_pick_date = true;

	/**
	 * En/disables the time picker
	 *
	 * @var bool
	 */
	private $option_pick_time = true;

	/**
	 * En/disables the minutes picker
	 *
	 * @var bool
	 */
	private $option_use_minutes = true;

	/**
	 * En/disables the seconds picker
	 *
	 * @var bool
	 */
	private $option_use_seconds = true;

	/**
	 * Minute stepping
	 *
	 * @var int
	 */
	private $option_minute_stepping = 1;

	/**
	 * Minimum date
	 *
	 * @var string
	 */
	private $option_min_date = "1/1/1970";

	/**
	 * Maximum date
	 *
	 * @var string
	 */
	private $option_max_date = 'today +50 years';

	/**
	 * Default language locale
	 *
	 * @var string
	 */
	private $option_language = 'en';

	/**
	 * Default date
	 *
	 * @var string
	 */
	private $option_default_date = '';

	/**
	 * Array of dates that cannot be selected
	 *
	 * @var array
	 */
	private $option_disabled_dates = array();

	/**
	 * Array of dates that can be selected
	 *
	 * @var array
	 */
	private $option_enabled_dates = array(
		'1/1/1970'
	);

	/**
	 * Icons to use
	 */
	private $option_icons = array(
		'time' => 'fa fa-time',
		'date' => 'fa fa-calendar',
		'up' => 'fa fa-chevron-up',
		'down' => 'fa fa-chevron-down'
	);

	/**
	 * Today indicator
	 *
	 * @var bool
	 */
	private $option_show_today = true;

	/**
	 * Use current date.
	 * When true, picker will set the value to the current date/time (respects picker's format)
	 *
	 * @var bool
	 */
	private $option_use_current = true;

	/**
	 * Use "strict" when validating dates
	 *
	 * @var bool
	 */
	private $option_use_strict = false;

	/**
	 * Remember the options set by method
	 *
	 * @var array
	 */
	private $set_options = array();

	/**
	 * Flag to see when translation is already requested to prevent
	 * multiple loading of lang file over different instances of object
	 *
	 * @var bool
	 */
	private static $translation_requested = false;

	/**
	 * Returns set default date
	 *
	 * @return string
	 */
	public function getDefaultDate()
	{
		return $this->option_default_date;
	}

	/**
	 * Sets the default date.
	 * Can be timestamp or DateTime object or string
	 *
	 * @param integer|DateTime|string $date
	 */
	public function setDefaultDate($date)
	{
		$this->option_default_date = $date;
		$this->set_options['default_date'] = 'defaultDate';
		return $this;
	}

	/**
	 * Returns set disabled dates
	 *
	 * @return array
	 */
	public function getDisabledDates()
	{
		return $this->option_disabled_dates;
	}

	/**
	 * Sets disabled dates.
	 * Accepts a single date or a list of dates in an array.
	 *
	 * @param string|array $dates
	 * @return \Web\Framework\Html\Controls\DateTimePicker
	 */
	public function setDisabledDates($dates)
	{
		if (!is_array($dates))
			$dates = array(
				$dates
			);

		$this->option_disabled_dates = $dates;
		$this->set_options['disabled_dates'] = 'disabledDates';
		return $this;
	}

	/**
	 * Return set enabled days
	 *
	 * @return array
	 */
	public function getEnabledDates()
	{
		return $this->option_enabled_dates;
	}

	/**
	 * Sets enabled dates.
	 * Accepts a single date or a list of dates in an array.
	 *
	 * @param string|array $dates
	 * @return \Web\Framework\Html\Controls\DateTimePicker
	 */
	public function setEnabledDates($dates)
	{
		if (!is_array($dates))
			$dates = array(
				$dates
			);

		$this->option_enabled_dates = $dates;
		$this->set_options['enablede_dates'] = 'enabledDates';
		return $this;
	}

	/**
	 * Set flag to use or not use the show today button
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function showToday($bool = null)
	{
		if (isset($bool))
		{
			$this->option_show_today = is_bool($bool) ? $bool : false;
			$this->set_options['show_today'] = 'showToday';
			return $this;
		}
		else
			return $this->option_show_today;
	}

	/**
	 * Set flag to use or not use the current button
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function useCurrent($bool = null)
	{
		if (isset($bool))
		{
			$this->option_use_current = is_bool($bool) ? $bool : false;
			$this->set_options['use_current'] = 'useCurrent';
			return $this;
		}
		else
			return $this->option_show_today;
	}

	/**
	 * Returns set format
	 *
	 * @return the $format
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * Sets format
	 *
	 * @param string $format
	 */
	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * Returns set min date
	 *
	 * @return the $start_date
	 */
	public function getMinDate()
	{
		return $this->option_min_date;
	}

	/**
	 * Sets min date
	 *
	 * @param string $start_date
	 */
	public function setMinDate($start_date)
	{
		$this->option_min_date = $start_date;
		$this->set_options['min_date'] = 'minDate';
		return $this;
	}

	/**
	 * Returns set max dateoption
	 *
	 * @return string
	 */
	public function getMaxDate()
	{
		return $this->option_max_date;
	}

	/**
	 * Sets max date
	 *
	 * @param string $max_date
	 */
	public function setMaxDate($max_date)
	{
		$this->option_max_date = $max_date;
		$this->set_options['max_date'] = 'maxDate';
		return $this;
	}

	/**
	 * Return used dictonary string from SMF language system
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return Txt::get('lang_dictonary', 'SMF');
	}

	/**
	 * Returns set minute stepping option
	 *
	 * @return the $minute_step
	 */
	public function getMinuteStepping()
	{
		return $this->option_minute_stepping;
	}

	/**
	 * Sets minute stepping
	 *
	 * @param number $minute_step
	 */
	public function setMinuteStepping($minute_step)
	{
		if (!is_int($minute_step))
			Throw new Error('Datepicker minute step has to be of type integer');

		if ($minute_step<1||$minute_step>59)
			Throw new Error('Datepicker minute step has to be between 1 and 59.');

		$this->option_minute_stepping = $minute_step;
		$this->set_options['minute_stepping'] = 'minuteStepping';
		return $this;
	}

	/**
	 * Set flag for using datepicker.
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function usePickDate($bool = null)
	{
		if (isset($bool))
		{
			$this->option_pick_date = is_bool($bool) ? $bool : false;
			$this->set_options['pick_date'] = 'pickDate';
		}
		else
			return $this->option_pick_date;
	}

	/**
	 * Set flag for using timepicker.
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function usePickTime($bool = null)
	{
		if (isset($bool))
		{
			$this->option_pick_time = is_bool($bool) ? $bool : false;
			$this->set_options['pick_time'] = 'pickTime';
		}
		else
			return $this->option_pick_time;
	}

	/**
	 * Set flag to use or not use minutes in timepicker.
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function useMinutes($bool = null)
	{
		if (isset($bool))
		{
			$this->option_use_minutes = is_bool($bool) ? $bool : false;
			$this->set_options['use_minutes'] = 'useMinutes';
		}
		else
			return $this->option_use_minutes;
	}

	/**
	 * Set flag to use or not use saeconds int timepicker.
	 * This option is "true" by default.
	 * Calling this method without parameter returns the currently set value.
	 *
	 * @param boolean $bool
	 * @return boolean
	 */
	public function useSeconds($bool = null)
	{
		if (isset($bool))
		{
			$this->option_use_seconds = is_bool($bool) ? $bool : false;
			$this->set_options['use_seconds'] = 'useSeconds';
		}
		else
			return $this->option_use_seconds;
	}

	/**
	 * Gets the dictionary string from $txt and loads the corresponding languagefile for the datepicker
	 *
	 * @return \Web\Framework\Html\Controls\DateTimePicker
	 */
	private function loadTranslation()
	{
		// As translation is an js file, it can only be loaded once.
		// Simple check this and do nothing if tranlsation file is
		// already loaded.
		if (self::$translation_requested==true)
			return;

		$this->option_language = Txt::get('lang_dictionary', 'SMF');
		$this->set_options['language'] = 'language';

		// Load non english languagefile
		if ($this->option_language!='en')
			Javascript::useFile(Cfg::get('Web', 'url_js').'/locale/moment/'.$this->option_language.'.js');

			// Set flag for loaded translation
		self::$translation_requested = true;

		return $this;
	}

	public function build()
	{
		// Get translation
		$this->loadTranslation();

		// Prepare options object
		$options = new \stdClass();

		// Set options which are set active
		foreach ( $this->set_options as $property => $option )
		{
			switch ($property)
			{
				// Check date
				case 'option_disabled_days' :
				case 'option_enabled_dates' :

					$options->{$option} = array();

					foreach ( $this->{$property} as $date )
					{
						if (!is_int($date)||!$date instanceof \DateTime||!is_string($date))
							Throw new Error('Datepicker controls '.$option.' date must by of type integer (timestamp), string or DateTime object.', 1000, array(
								'data' => $date
							));

						if (is_string($date))
							$date = strtotime($date);

						if ($date instanceof \DateTime)
							$date = $date->getTimestamp();

						$options->{$option}[] = date('Y-m-d', $date);
					}

					break;

				default :

					$options->{$option} = $this->{'option_'.$property};
					break;
			}
		}

		$options->format = $this->format;

		// Add options as json encoded data attribute
		$this->addData('web-datepicker-options', json_encode($options));

		return parent::build();
	}

	/**
	 * Experimental to see how good this works
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->build();
	}
}
?>
