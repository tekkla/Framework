<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\App;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Abstract MVC class.
 * Model, View and Controller libs are children of this class.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Abstracts
 * @namespace Web\Framework\Lib\Abstracts
 */
abstract class MvcAbstract extends ClassAbstract
{
	/**
	 * Name of the MVC object
	 * @var string
	 */
	protected $name;
	
	/**
	 * Hold injected App object this MVC object is used for
	 * @var App
	 */
	public $app;

	/**
	 * MVC objects need an app instance.
	 * @param App $app
	 * @return \Web\Framework\Lib\Abstracts\MvcAbstract
	 */
	public function injectApp(App $app)
	{
		$this->app = $app;
		return $this;
	}

	/**
	 * Sets the name of the MVC object
	 * @param string $name
	 * @return \Web\Framework\Lib\Abstracts\MvcAbstract
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Returns the name of the MVC object.
	 * Throws error when name is not set.
	 * @throws Error
	 * @return string
	 */
	public function getName()
	{
		if (isset($this->name))
			return $this->name;
		
		Throw new Error('Name from MVC component is not set.');
	}

	/**
	 * Returns the name of the App object insite the MVC object.
	 * Throws an error
	 * if the App object is not set.
	 * @throws Error
	 * @return string
	 */
	public function getAppName()
	{
		if (isset($this->app))
			return $this->app->getName();
		
		Throw new Error('MVC component has no set app name.');
	}

	/**
	 * Lazy textfunction for controllers a so you do not have to write the apps name in the wanted textkey
	 *
	 * @param string $key The textkey you want to get the text from without need of app name in it.
	 * @see \Web\Framework\Lib\Txt::get() <code>
	 *	  <?php
	 *	  class Testapp_Controller_MyController extends Controller
	 *	  {
	 *	  public function MyControllerAction()
	 *	  {
	 *	  // use this
	 *	  $mytext = $this->txt('testapp_testtext');
	 *
	 *	  // or lazy
	 *	  $mytext = $this->txt('testtext');
	 *	  }
	 *	  }
	 *	  ?>
	 *	  </code>
	 */
	public function txt($key)
	{
		return Txt::get($key, $this->getAppName());
	}
}
?>
