<?php
namespace Web\Framework\Lib;

use Web\Framework\Html\Controls\ModalWindow;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Ajax commands which are managed by framework.js
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
final class Ajax
{
	/**
	 * Storage for ajax commands
	 * @var \stdClass
	 */
	private static $ajax = array();
	
	/**
	 * Kind of command
	 * @var string
	 */
	private $type = 'dom';
	
	/**
	 * The documents DOM ID the ajax content should go in
	 * @var string
	 */
	private $selector = '';
	
	/**
	 * Parameters to pass into the controlleraction
	 * @var array
	 */
	private $args = array();
	
	/**
	 * The type of the current ajax.
	 * @var string
	 */
	private $fn = 'html';

	/**
	 * Factory method
	 * @return \Web\Framework\Lib\Ajax
	 */
	public static function factory()
	{
		return new self();
	}

	public static function command($definition = array())
	{
		if (!$definition)
			Throw new Error('No defintion for ajax command found.', 1000);
		
		self::factory()->add($definition);
	}

	public function setArgs($args = array())
	{
		$this->args = $args;
		return $this;
	}

	public function addArg($val)
	{
		$this->args[] = $val;
		return $this;
	}

	/**
	 * Set ajax command group type
	 * @param $type
	 */
	public function setType($type = 'dom')
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set DOM id of target
	 * @param $target
	 */
	public function setSelector($selector)
	{
		$this->selector = $selector;
		return $this;
	}

	/**
	 * Set content of ajax
	 * @param $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function setFunction($fn = 'html')
	{
		$this->fn = $fn;
		return $this;
	}

	/**
	 * Builds ajax definition and adds it to the ajaxlist
	 */
	public function add($definition = array())
	{
		// Command vars counter
		static $counter = 0;
		
		if ($definition)
		{
			foreach ( $definition as $property => $value )
				if (property_exists($this, $property))
				{
					if ($property == 'args' && !is_array($value))
						$value = array(
							$value
						);
					
					$this->{$property} = $value;
				}
		}
		
		// Create alert on missing target when type is in need-target list
		if ($this->type == 'dom' && !$this->selector)
		{
			self::console('Your DOM ajax response needs a selector but none is set. Aborting.');
			return;
		}
		
		// Create modal content on type of modal
		if ($this->fn == 'modal')
		{
			$modal = ModalWindow::factory();
			$modal->setContent($this->content);
			
			if (isset($this->cmd_vars['title']))
				$modal->setTitle($this->cmd_vars['title']);
			
			$this->args = $modal->build();
		}
		
		$cmd = new \stdClass();
		
		$cmd->f = $this->fn;
		$cmd->a = is_array($this->args) ? $this->args : array(
			$this->args
		);
		
		// Publish ajax definition to ajaxlist
		if ($this->type == 'dom')
		{
			$cmd->s = $this->selector;
			
			self::$ajax['dom'][$this->selector][] = $cmd;
		}
		else
			self::$ajax['act'][] = $cmd;
			
			// Raise ajax counter
		$counter++;
	}

	/**
	 * Builds the ajax command structure
	 */
	public static function process()
	{
		// Add messages
		$messages = Message::getMessages();
		
		if ($messages)
		{
			foreach ( $messages as $message )
				self::command(array(
					'type' => 'dom', 
					'args' => $message->build(), 
					'selector' => '#web-message', 
					'fn' => 'append'
				));
		}
		
		// Output is json encoded
		return json_encode(self::$ajax);
	}

	/**
	 * Returns the complete ajax command stack as it is
	 * @return array
	 */
	public static function getCommandStack()
	{
		return self::$ajax;
	}
	
	// # PREDEFINED METHODS ##############################################################################################
	

	/**
	 * Create an msgbox in browser
	 * @param $msg
	 */
	public static function alert($msg)
	{
		self::command(array(
			'type' => 'act', 
			'fn' => 'alert', 
			'args' => $msg
		));
	}

	/**
	 * Start a controller run
	 * @param $ctrl
	 * @param $action
	 * @param $target
	 */
	public static function call($app_name, $controller, $action, $target = '', $param = array())
	{
		self::command(array(
			'selector' => $target, 
			'args' => App::create($app_name)->getController($controller)->run($action, $param)
		));
	}

	/**
	 * Create a HTML ajax which changes the html of target selector
	 * @param $target Selector to be changed
	 * @param $content Content be used
	 * @param $mode Optional mode how to change the selected element. Can be: replace(default) | append | prepend | remove | after | before
	 */
	public static function html($selector, $content)
	{
		self::command(array(
			'selector' => $selector, 
			'args' => $content
		));
	}

	/**
	 * Send an error to the web_error div
	 * @param unknown_type $error
	 */
	public static function error($error)
	{
		self::command(array(
			'selector' => '#web-message', 
			'fn' => 'append', 
			'args' => 'error'
		));
	}

	/**
	 * Change a DOM attribute
	 * @param $target => DOM id
	 * @param $attribute => attribute name
	 * @param $content
	 * @param $mode optional => the edit mode replace(default)|append|prepend|remove
	 * @todo
	 *
	 */
	public static function attrib($selector, $attribute, $value)
	{
		self::command(array(
			'type' => 'dom', 
			'selector' => $selector, 
			'fn' => 'attr', 
			'args' => array(
				$attribute, 
				$value
			)
		));
	}

	/**
	 * Change css property of dom element
	 * @param $target => DOM id
	 * @param $content
	 * @param $mode optional => the edit mode replace(default)|append|prepend|remove
	 */
	public static function css($selector, $property, $value)
	{
		self::command(array(
			'type' => 'dom', 
			'selector' => $selector, 
			'fn' => 'css', 
			'args' => array(
				$property, 
				$value
			)
		));
	}

	/**
	 * Change css property of dom element
	 * @param $target => DOM id
	 * @param $content
	 * @param $mode optional => the edit mode replace(default)|append|prepend|remove
	 */
	public static function addClass($selector, $class)
	{
		self::command(array(
			'type' => 'dom', 
			'selector' => $selector, 
			'fn' => 'addClass', 
			'args' => $class
		));
	}

	/**
	 * Calls a page refresh by loading the provided url.
	 * Calls location.href="url" in page.
	 * @param string|Url $url Can be an url as string or an Url object on which the getUrl() method is called
	 */
	public static function refresh($url)
	{
		if ($url instanceof Url)
			$url = $url->getUrl();
		
		self::command(array(
			'type' => 'act', 
			'fn' => 'refresh', 
			'args' => $url
		));
	}

	/**
	 * Creates ajax response to load a js file.
	 * @param string $file Complete url of file to load
	 */
	public static function loadScript($file)
	{
		self::command(array(
			'type' => 'act', 
			'fn' => 'load_script', 
			'args' => $file
		));
	}

	/**
	 * Create console log output
	 * @param string $msg
	 */
	public static function console($msg)
	{
		self::command(array(
			'type' => 'act', 
			'fn' => 'console', 
			'args' => $msg
		));
	}

	/**
	 * Creates a print_r console output of provided $var
	 * @param mixed $var
	 */
	public static function dump($var)
	{
		self::command(array(
			'type' => 'act', 
			'fn' => 'dump', 
			'args' => print_r($var, true)
		));
	}
}
?>
