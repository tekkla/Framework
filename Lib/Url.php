<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ClassAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Url class for creating manual urls and by named routes
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Url extends ClassAbstract
{
	
	// WebExt related
	

	/**
	 * Name of route to compile
	 * @var string
	 */
	private $named_route;
	
	/**
	 * Params for route compiling
	 * @var array
	 */
	private $param = array();
	
	/**
	 * App name
	 * @var string
	 */
	private $app;
	
	/**
	 * Controller name
	 * @var string
	 */
	private $ctrl;
	
	/**
	 * Action to call
	 * @var string
	 */
	private $func;
	
	/**
	 * Ajax flag
	 * @var int
	 */
	private $ajax = false;
	
	// ------------------------------------------
	// SMF related
	// ------------------------------------------
	

	/**
	 * SMF action parameter
	 * @var string
	 */
	private $action;
	
	/**
	 * SMF topic parameter
	 * @var int|string
	 */
	private $topic;
	
	/**
	 * SMF board parameter
	 * @var int
	 */
	private $board;
	
	// ------------------------------------------
	// Global
	// ------------------------------------------
	

	/**
	 * Target parameter
	 * @var string
	 */
	private $target;
	
	/**
	 * Anchor parameter
	 * @var string
	 */
	private $anchor;

	/**
	 * Factory method which returns an URL object
	 * @param string $named_route Optional name of route to compile
	 * @param array $param Optional parameters to use on route
	 * @return Url
	 */
	public static function factory($named_route = '', $param = array())
	{
		$url = new Url();
		
		if ($named_route)
		{
			$url->setNamedRoute($named_route);
			
			if ($param)
				$url->setParameter($param);
		}
		
		return $url;
	}

	/**
	 * Factory method which returns an url string based on a compiled named route
	 * @param string $named_route Optional name of route to compile
	 * @param array $param Optional parameters to use on route
	 * @return string
	 */
	public static function getNamedRouteUrl($named_route, $param = array())
	{
		return self::factory($named_route, $param)->getUrl();
	}

	/**
	 * Sets name of route to be compiled
	 * @param string $named_route
	 * @todo Mayve it is a good idea to check for route existance at this point rather than on compiling.
	 * @return \Web\Framework\Lib\Url
	 */
	public function setNamedRoute($named_route)
	{
		$this->named_route = $named_route;
		return $this;
	}

	/**
	 * Flags url to be ajax
	 * @param bootl $bool
	 * @return \Web\Framework\Lib\Url
	 */
	public function setAjax($bool = true)
	{
		$this->ajax = $bool;
		$this->param['is_ajax'] = 1;
		return $this;
	}

	/**
	 * Sets name of app for route compiling.
	 * @param string $app
	 * @return \Web\Framework\Lib\Url
	 */
	public function setApp($app)
	{
		$this->app = $app;
		return $this;
	}

	/**
	 * Sets name of controller for route compiling.
	 * @param string $ctrl
	 * @return \Web\Framework\Lib\Url
	 */
	function setCtrl($ctrl)
	{
		$this->ctrl = $ctrl;
		return $this;
	}

	/**
	 * Sets route function
	 * @param string $func
	 * @return \Web\Framework\Lib\Url
	 */
	function setFunc($func)
	{
		$this->func = $func;
		return $this;
	}

	/**
	 * Sets SMF realted action parameter
	 * @param string $action
	 * @return \Web\Framework\Lib\Url
	 */
	function setAction($action)
	{
		$this->action = $action;
		
		$this->unsetData('board');
		$this->unsetData('topic');
		
		return $this;
	}

	/**
	 * Sets a topic id and flags the url object to create a topic url
	 * @param int $topic Id of topic to create url for
	 * @param int $msg Optional message id
	 * @param string $anchor
	 * @return \Web\Framework\Lib\Url
	 */
	function setTopic($topic, $msg = null, $anchor = null)
	{
		// Extend topic by message id
		if (isset($msg))
			$topic .= '.msg' . $msg;
			
			// Set anchor
		if (isset($anchor))
			$this->anchor = $anchor;
		
		$this->topic = $topic;
		
		// Because we generate a SMF url the controller has to point to SMF
		$this->ctrl = 'smf';
		
		// A set topic requires unset action or board parameter
		$this->unsetData('board');
		$this->unsetData('action');
		
		return $this;
	}

	/**
	 * Sets a board id and flags the url object to create a board url
	 * @param int $board Id of board to create url for
	 * @return \Web\Framework\Lib\Url
	 */
	function setBoard($board)
	{
		$this->board = $board;
		
		// Because we generate a SMF url the controller has to point to SMF
		$this->ctrl = 'smf';
		
		// A set board requires unset action or topic parameter
		$this->unsetData('topic');
		$this->unsetData('action');
		
		return $this;
	}

	/**
	 * Adds target parameter
	 * @param string $target
	 * @return \Web\Framework\Lib\Url
	 */
	function setTarget($target)
	{
		$this->setParameter('target', $target);
		return $this;
	}

	/**
	 * Sets SMF realted subaction (sa) parameter
	 * @param string $sa
	 * @return \Web\Framework\Lib\Url
	 */
	function setSubaction($sa)
	{
		$this->setParameter('sa', $sa);
		return $this;
	}

	/**
	 * Sets SMF realted area parameter
	 * @param string $area
	 * @return \Web\Framework\Lib\Url
	 */
	public function setArea($area)
	{
		$this->setParameter('area', $area);
		return $this;
	}

	/**
	 * Adds one parameter in form of key and value or a list of parameters as assoc array.
	 * Setting an array as $arg1 and leaving $arg2 empty means to add an assoc array of paramters
	 * Setting $arg1 and $arg2 means to set on parameter by name and value.
	 * @var string|array $arg1 String with parametername or list of parameters of type assoc array
	 * @var string $arg2 Needs only to be set when seting on paramters by name and value.
	 * @var bool $reset Optional: Set this to true when you want to reset already existing parameters
	 * @throws Error
	 * @return \Web\Framework\Lib\Url
	 */
	function setParameter($arg1, $arg2 = null, $reset = false)
	{
		if ($reset === true)
			$this->param = array();
		
		if ($arg2 === null && is_array($arg1) && !empty($arg1))
		{
			foreach ( $arg1 as $key => $val )
				$this->param[$key] = $val;
		}
		
		if (isset($arg2))
			$this->param[$arg1] = $arg2;
		
		return $this;
	}

	/**
	 * Same as setParameter but without resetting existing parameters.
	 * @see setParameter()
	 * @return \Web\Framework\Lib\Url
	 */
	public function addParameter($arg1, $arg2 = null)
	{
		$this->setParameter($arg1, $arg2, false);
		return $this;
	}

	/**
	 * Sets name of anchor
	 * @param string $anchor
	 * @return \Web\Framework\Lib\Url
	 */
	function setAnchor($anchor)
	{
		$this->anchor = $anchor;
		return $this;
	}

	/**
	 * Processes all parameters and returns a fully compiled url as string.
	 * @return string
	 */
	function getUrl($definition = array())
	{
		if ($definition)
		{
			foreach ( $definition as $property => $value )
				if (property_exists($this, $property))
					$this->{$property} = $value;
		}
		
		// if action isset, we have a smf url to build
		if (isset($this->action) || isset($this->board) || isset($this->topic))
			return $this->getSmfURL();
		
		if (isset($this->named_route))
			return $this->request->getRouteUrl($this->named_route, $this->param);
		
		return false;
	}

	/**
	 * Returns an SMF style url with action, subactions and so on
	 * @return string
	 */
	private function getSmfUrl()
	{
		// build parameterlist
		$param = array();
		
		foreach ( $this->param as $key => $val )
		{
			if ($key == 'area' || $key == 'sa')
				continue;
			
			$param[] = empty($val) ? $key : $key . '=' . $val;
		}
		
		$anchor = isset($this->anchor) ? '#' . $this->anchor : '';
		
		$param = count($param) > 0 ? '?' . implode(';', $param) : '';
		
		if (isset($this->topic))
			$url_base = '/topic/' . $this->topic . '.html';
		elseif (isset($this->board))
			$url_base = '/board/' . $this->board . '.html';
		else
		{
			$url_parts = array(
				$this->action
			);
			
			if (isset($this->param['area']))
				$url_parts[] = 'area_' . $this->param['area'];
			
			if (isset($this->param['sa']))
				$url_parts[] = 'sa_' . $this->param['sa'];
			
			$url_base = '/' . implode('/', $url_parts) . '/';
		}
		return BOARDURL . $url_base . $param . $anchor;
	}

	/**
	 *
	 * @param unknown $key
	 */
	private function unsetData($key)
	{
		if (isset($this->{$key}))
			unset($this->{$key});
	}

	/**
	 * Converts classical URLs into SEO friendly ones.
	 * Urls like index.php?action=admin will become /admin/.
	 *
	 * @param unknown $match
	 * @return unknown string
	 */
	public static function convertSEF($raw_url)
	{
		// Parse the url
		$parsed = parse_url($raw_url[0]);
		
		// Without any querystring we return the url
		if (!isset($parsed['query']))
			return $raw_url[0];
			
			// Split query string into part
		$query_parts = explode(';', $parsed['query']);
		
		// On no parts the url is return untaimed
		if (empty($query_parts))
			return $raw_url[0];
		
		$parsed['params'] = array();
		
		// Prepare the query parts into a key/value par
		foreach ( $query_parts as $pair )
		{
			if (strpos($pair, '=') !== false)
				list($key, $val) = explode('=', $pair);
			else
				$key = $val = $pair;
			
			$parsed['params'][$key] = $val;
		}
		
		// Empty params or no 'action' set or not 'action' first query part? Return url unchanged
		if (empty($parsed['params']) || !isset($parsed['params']['action']) || key($parsed['params']) != 'action')
			return $raw_url[0];
			
			// All checks done. Lets rewrite the url
		$url = self::factory();
		
		foreach ( $parsed['params'] as $key => $val )
		{
			$method = 'set' . String::camelize($key);
			
			if ($key != 'board' && $key != 'topic' && method_exists($url, $method))
				$url->{$method}($val);
			else
				$url->setParameter($key, $val);
		}
		
		// And finally return the rewritten url
		return $url->getUrl();
	}
}
?>
