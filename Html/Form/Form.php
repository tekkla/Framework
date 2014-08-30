<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Creates a form html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
class Form extends HtmlAbstract
{
	protected $element = 'form';
	protected $attribute = array(
		'role' => 'form', 
		'method' => 'post', 
		'entype' => 'multipart/form-data'
	);

	/**
	 * Set the name of a route to compile as action url
	 * @param string $route
	 * Name of route to compile
	 * @param array|stdClass $params
	 * Parameter to use in route to compile
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setActionRoute($route, $params = null)
	{
		// Store routename for later use
		$this->route = $route;
		
		// Compile route and set url as action url
		$this->attribute['action'] = Url::factory($route, $params)->getUrl();
		
		return $this;
	}

	/**
	 * Set the form method attribute.
	 * Use 'post' or 'get'.
	 * Form elements are using post by default.
	 * @param string $method
	 * Value for the method attribute of from
	 * @throws NoValidParameterError
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setMethod($method)
	{
		$methods = array(
			'post', 
			'get'
		);
		
		// Safety first. Only allow 'post' or 'get' here.
		if (!in_array($method, $methods))
			Throw new Error('Wrong method set.', 1000, array(
				$method, 
				$methods
			));
		
		$this->attribute['method'] = $method;
		return $this;
	}

	/**
	 * Set the form method attribute.
	 * Use 'post' or 'get'.
	 * Form elements are using post by default.
	 * @param string $method
	 * Value for the method attribute of from
	 * @throws NoValidParameterError
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setEnctype($enctype)
	{
		$enctypes = array(
			'application/x-www-form-urlencoded', 
			'multipart/form-data', 
			'text/plain'
		);
		
		// Safety first. Only allow 'post' or 'get' here.
		if (!in_array($enctype, $enctypes))
			Throw new Error('Wrong method set.', 1000, array(
				$enctype, 
				$enctypes
			));
		
		$this->attribute['enctype'] = $enctype;
		return $this;
	}

	/**
	 * Set form accept charset attribute
	 * @param string $accept_charset 
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setAcceptCharset($accept_charset)
	{
		$this->attribute['accept_charset'] = $accept_charset;
		return $this;
	}

	/**
	 * Set form target attribute
	 * @param string $target 
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setTarget($target)
	{
		$this->attribute['target'] = $target;
		return $this;
	}

	/**
	 * Set autoomplete attribute with state 'on' or 'off'
	 * @param string $state 
	 * @throws NoValidParameterError
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function setAutocomplete($state = 'on')
	{
		$states = array(
			'on', 
			'off'
		);
		
		if (!in_array($state, $states))
			Throw new Error('Wrong autocomplete state.', 1000, array(
				$state, 
				$states
			));
		
		$this->attribute['autocomplete'] = $state;
		return $this;
	}

	/**
	 * Deactivates form validation by setting "novalidate" attribute
	 * @return \Web\Framework\Html\Elements\Form
	 */
	public function noValidate()
	{
		$this->attribute['novalidate'] = false;
		return $this;
	}
}
?>
