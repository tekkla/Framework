<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\MvcAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Basic view class.
 * Each app view has to be a child of this class.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 * @todo Split into validator and function?
 */
class View extends MvcAbstract
{
    /**
     * Storage for lazy view vars to access by __get()
     * @var array
     */
    private $__magic_vars = array();

    /**
     * Factory pattern for new views
     * @param string $key+
     * @return View
     */
    public static function factory(App $app, $view_name)
    {
        $class_name = ($app->isSecure() ? '\\Web\\Framework\\AppsSec' : '\\Web\Apps') . "\\" . $app->getName() . '\\View\\' . $view_name . 'View';

        /* @var $View View */
        $view = new $class_name($view_name);

        $view->injectApp($app);

        return $view;
    }

    /**
     * Making the contructor private to only allow object creation by Factory method
     * @param string $app
     */
    protected function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Renders the view and returns the result
     * @param string $func Name of render method
     * @param array $params Optional: Parameterlist to pass to render function
     */
    public function render($func, $params=array())
    {
        if (!method_exists($this, $func))
            return false;

        return Invoker::run($this, $func, $params);
    }

    /**
     * Passes a value by name to the view.
     * If $val is an obect, it will be
     * checked for a build() method. Does is exist, it will be called and
     * the return value stored as value for the views var.
     * @param string $key
     * @param $val
     */
    public function setVar($key, $val)
    {
        // Objects with Create methods can be passed as object, because the
        // create method is called automatically
        if (is_object($val) && method_exists($val, 'build'))
            $val = $val->build();

        // Pass a model object as view var and only the data will be used.
        if (is_object($val) && $val instanceof Model)
            $val = $val->data;

        // Another lazy thing. It's for accessing vars in the view by $this->var_name
        $this->__magic_vars[$key] = $val;
    }

    /**
     * Checks if the $var exists in the view.
     * @param string $var
     * @return boolean
     */
    public function isVar($var)
    {
        return isset($this->__magic_vars[$var]);
    }

    /**
     * Magic method for setting the view vars
     * @param string $var
     * @param mixed $val0
     */
    public function __set($var, $val)
    {
        $this->setVar($var, $val);
    }

    /**
     * Magic method for accessing the view vars
     * @param string $var
     * @return Ambigous <boolean, multitype:>
     */
    public function __get($var)
    {
        return isset($this->__magic_vars[$var]) ? $this->__magic_vars[$var] : 'var:' . $var;
    }

    /**
     * Magic isset
     * @param string $key
     */
    public function __isset($key)
    {
        return isset($this->__magic_vars[$key]);
    }
}
?>
