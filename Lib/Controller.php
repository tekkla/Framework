<?php
namespace Web\Framework\Lib;

use Web\Framework\Helper\FormDesigner;
use Web\Framework\Lib\Abstracts\MvcAbstract;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Controllers parent class.
 * Each app controller has to be a child of this class.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Controller extends MvcAbstract
{
    /**
     * Type of class
     * @var String
     */
    protected $type = 'Controller';

    /**
     * Signals that the corresponding view will be rendered
     * @var Boolean
     */
    protected $render = true;

    /**
     * Action to render
     * @var String
     */
    private $action = 'Index';

    /**
     * Storage for access rights
     * @var array
     */
    protected $access = array();

    /**
     * Storage for events
     * @var array
     */
    protected $events = array();

    /**
     * The View object
     * @var View
     */
    public $view = false;

    /**
     * Storage for parameter
     * @var Data
     */
    private $param = array();

    /**
     * Stores the controller bound Model object. Is false when controller has no model.
     * @var Model
     */
    public $model;

    /**
     * Falg to signal that this controller is in ajax mode
     * @var bool
     */
    private $is_ajax = false;

    /**
     * Creates new controller object
     * @param string $app Appname of this controller
     * @param string $ctrl Name of this controller
     * @param App $App App to inject into the controller
     * @return Controller Controller object
     */
    public static function factory(App $app, $controller_name)
    {
        if (!isset($app) || (isset($app) && !$app instanceof App))
            Throw new Error('Controller factory needs an app object.');

            // App or framework controller?
        $class = ($app->isSecure() ? '\\Web\\Framework\\AppsSec' : '\\Web\\Apps') . "\\" . $app->getName() . '\\Controller\\' . $controller_name . 'Controller';

        // Create controller object
        return new $class($controller_name, $app);
    }

    /**
     * Hidden constructor.
     * Runs the onLoad eventmethod and inits the internal view and model.
     */
    final protected function __construct($name, App $app)
    {
        // Store name
        $this->name = $name;

        // Run onload event
        $this->runEvent('load');

        // Inject app object
        $this->app = $app;

        // Some controllers do not need a view to render
        // they are recognized by public has_no_view property
        if (!property_exists($this, 'has_no_view'))
            $this->view = View::factory($app, $name);

        // Model to bind?
        $this->model = property_exists($this, 'has_no_model') ? false : $this->app->getModel($name);
    }

    /**
     * Access the apps config data.
     * Setting one parameter means you want to read a value. Both param writes a config value.
     *
     * @param string $key Config to get
     * @param mixed $val Value to set in the apps config
     * @return mixed config value
     */
    final protected function cfg($key = null, $val = null)
    {
        return $this->app->cfg($key, $val);
    }

    /**
     * Access an other app than the app from this controller.
     * It's maybe confusing that I use the method App() and the classmember $App.
     * The explanation is simple: Lazyness. If you want to acces the app of the current controller >> use the classmember. You need access to
     * an other app then use this classmethod.
     *
     * @param string $app pp name
     * @return App
     *
     * @tutorial <code>
     *           <?php
     *           // guess you want to access a model of the current app
     *           $model = $this->app->getModel('MyModel');
     *
     *           // accessing the model of an other app is nearly the same
     *           $model = $this->app('AppNameOfModel')->getModel('OtherModel');
     *           ?>
     *           </code>
     */
    final protected function app($app_name)
    {
        return App::create($app_name);
    }

    /**
     * Runs the requested controller action.
     *
     * @param string $action
     * @param string $param
     * @return boolean unknown void
     */
    final public function run($action='', $param=array())
    {
        // Argument checks and name conversions.
        // If no func is set as arg, use the request action.
        $this->action = empty($action) ? $this->request->getAction() : $action;

        // If accesscheck failed => stop here and return false!
        if ($this->checkControllerAccess() == false)
            return false;

        // We can set the controllers parameter by hand and will have automatic all
        // parameters set by the request handler. If param are set manually, possible dublicates
        // will overwrite controller param copied from request handler.

        // Copy request param to controller param.
        $this->param = empty($param) ? $this->request->getAllParams() : $param;

        // Init return var with boolean false as default value. This default prevents from running the views render()
        // method when the controller action is stopped manually by using return.
        $return = false;

        // run possible before event handler
        $this->runEvent('before');

        // a little bit of reflection magic to pass request param into controller func
        $return = Lib::invokeMethod($this, $this->action, $this->param);

        // run possible after event handler
        $this->runEvent('after');

        // No result to return? Return false
        if (isset($return) && $return == false)
            return false;

        // Render the view and return the result
        if ($this->render === true)
        {
            // Render into own outputbuffer
            ob_start();
            $this->view->render($this->action, $this->param);
            $content = ob_get_clean();

            if (empty($content) && method_exists($this->app, 'onEmpty'))
                $content = $this->app->onEmpty();

            return $content;
        }
    }

    /**
     * Ajax method to send the result of an action a ajax html command.
     * @param string $action Name of the action to call
     * @param array $param Array of parameter to be used in action call
     * @param string $selector Optional jQuery selector to html() the result. Can be overridden by setAjaxTarget() method
     */
    final public function ajax($action='', $param=array(), $selector='')
    {
        $this->is_ajax = true;

        if ($selector)
            $this->ajax->setSelector($selector);

        $content = $this->run($action, $param);

        if ($content)
        {
            $this->ajax->setArgs($content);
            $this->ajax->add();
        }

        return $this;
    }

    /**
     * Redirects from one action to another while resetting data of attached model and clearing all post data from the request handler.
     * @param string $action
     * @param array $param
     */
    final protected function redirect($action, $param = array())
    {
        // Clean data
        $this->cleanUp();

        // Run redirect method
        return $this->run($action, $param);
    }

    /**
     * Method to cleanup data in controllers model and the request handler
     * @param bool $model Flag to clean model data (default: true)
     * @param string $post Flag to clean post data (default: true)
     */
    final protected function cleanUp($model=true, $post=true)
    {
        // Reset model data
        if ($model && isset($this->model))
        	$this->model->reset(true);

        // Reset post data
        if ($post)
            $this->request->clearPost();
    }

    /**
     * Event handler
     * @param string $event
     * @return \Web\Framework\Lib\Controller
     */
    private function runEvent($event)
    {
        if (!empty($this->events[$this->action][$event]))
        {
            if (!is_array($this->events[$this->action][$event]))
                $this->events[$this->action][$event] = array(
                    $this->events[$this->action][$event]
                );

            foreach ( $this->events[$this->action][$event] as $event_func )
               Lib::invokeMethod($this, $event_func, $this->request->getAllParams());
        }

        return $this;
    }

    /**
     * Loads the associated viewobject
     * @param string $app Name of the views app
     * @param string $view Name of the view
     */
    final protected function loadView($view)
    {
        $this->view = View::factory($this->app, $view);
        return $this;
    }

    /**
     * Does an urlredirect but cares about what kind (ajax?) of request was send.
     * @param Url|string $url
     */
    final protected function doRefresh($url)
    {
        if ($url instanceof Url)
            $url = $url->getUrl();

        if ($this->request->isAjax())
        {
        	Ajax::refresh($url);
        	$this->firephp('Ajax refresh command set: ' . $url);
        }
        else
            redirectexit($url);
    }

    /**
     * Simple interface function for SMFs allowedTo() function
     * @param string|array $perm
     * @return boolean
     */
    final protected function checkUserrights($perm)
    {
        if (!is_array($perm))
            $perm = array(
                $perm
            );

        return allowedTo($perm);
    }

    /**
     * Checks the controller access of the user.
     * This accesscheck works on serveral levels.
     * Level 0 - App: Tries to check access on possible app wide access function
     * Level 1 - Controller: Tries to check access by looking for access setting in the controller itself.
     * @param boolean $smf Use the SMF permission system. You should only deactivate this, if you have your own rightsmanagement
     * @param bool $force Set this to true if you want to force a brutal stop
     * @return boolean
     */
    final protected function checkControllerAccess($mode = 'smf', $force = false)
    {
        // Is there an global access method in the app main class to call?
        if (method_exists($this->app, 'appAccess') && $this->app->appAccess() === false)
            return false;

        // ACL set?
        if (isset($this->access))
        {
            $perm = array();

            // Global access for all actions?
            if (isset($this->access['*']))
            {
                if (!is_array($this->access['*']))
                    $perm[] = $this->access['*'];
                else
                    $perm += $this->access['*'];
            }

            // Actions access set?
            if (isset($this->access[$this->action]))
            {
                if (!is_array($this->access[$this->action]))
                    $perm[] = $this->access[$this->action];
                else
                    $perm += $this->access[$this->action];
            }

            // No perms until here means we can finish here and allow access by returning true
            if ($perm)
                return Security::checkAccess($perm, $mode, $force);
        }

        // Not set ACL or falling through here grants access by default
        return true;
    }

    /**
     * Set the name of the actiuon to rander.
     * By default this is the current controller action name and do not have to be set manually.
     * @param string $action
     */
    final protected function setRenderAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Publish a value to the view
     * @param string|array $arg1 Name of var or list of vars in an array
     * @param mixed $arg2 Optional value to be ste when $arg1 is the name of a var
     */
    final protected function setVar($arg1, $arg2 = null)
    {
        // On non existing view we do not have to set anything
        if (!isset($this->view) || !$this->view instanceof View)
            return;

            // Some vars are protected and not allowed to be used outside the framework
        $protected_var_names = array(
            'app',
            'controller',
            'action',
            'view',
            'model',
            'cfg'
        );

        // One argument has to be an assoc array
        if (!isset($arg2))
        {
            foreach ( $arg1 as $var => $value )
            {
                if (in_array($var, $protected_var_names))
                    Throw new Error('Varname is protected. Use other name for your var.', 5005, array(
                        $var,
                        $protected_var_names
                    ));

                $this->view->setVar($var, $value);
            }
        }
        elseif (isset($arg2))
        {
            if (in_array($arg1, $protected_var_names))
                Throw new Error('Varname is protected. Use other name for your var.', 5005, array(
                    func_get_arg(0),
                    $protected_var_names
                ));

            $this->view->setVar($arg1, $arg2);
        }
        else
            Throw new Error('The vars to set are not correct.', 1001, func_get_args());

        return $this;
    }

    /**
     * Set the meta title of the html output
     * @param string $title
     */
    final protected function setPageTitle($title)
    {
        Context::setPageTitle($title);
        return $this;
    }

    /**
     * Set the meta description of the html output
     * @param string $description
     */
    final protected function setPageDescription($description)
    {
        Context::setPageDescription($description);
        return $this;
    }

    /**
     * Shorthand method for adding a flash message.
     * @param string $message
     * @param string $type
     */
    final protected function addMessage($message, $type)
    {
        $this->message->{$type}($message);
        return $this;
    }

    /**
     * Adds an entry to the SMF linktree.
     * Label will be shown as text and URL
     * is the link url. Url parameter can be an instance of Url. The url will
     * be created automatic by using the url object getUrl() method.
     * @param string $name
     * @param string,Url $url
     */
    final protected function addLinktree($label, $url = null)
    {
        if ($url instanceof Url)
            $url = $url->getUrl();

        Context::addLinktree($label, $url);

        return $this;
    }

    /**
     * Shorthand method for a FormDesigner instance with auto attached model
     * @return FormDesigner
     */
    final protected function getFormDesigner()
    {
        $form = new FormDesigner();
        $form->attachModel($this->model);
        $form->setActionRoute($this->request->getCurrentRoute(), $this->param);
        return $form;
    }

    /**
     * Wrapper method for $this->app->getController()
     * @param string $controller_name
     * @return Controller
     */
    final protected function getController($controller_name)
    {
        return $this->app->getController($controller_name);
    }

    /**
     * Wrapper method for $this->app->getModel()
     * @param string $model_name
     * @return \Web\Framework\Lib\Model
     */
    final protected function getModel($model_name)
    {
        return $this->app->getModel($model_name);
    }

    /**
     * Adds a paramter to the controllers parameter collection.
     * Useful when redirecting to other controller action
     * which need additional parameters to function.
     * @param string $param Paramertername
     * @param mixed $value Parametervalue
     */
    final protected function addParam($param, $value)
    {
        $this->param[$param] = $value;
        return $this;
    }

    /**
     * Sets the selector name to where the result is ajaxed.
     * @param string $target
     * @return \Web\Framework\Lib\Controller
     */
    final protected function setAjaxTarget($target)
    {
        if ($this->is_ajax)
            $this->ajax->setSelector($target);

        return $this;
    }
}
?>
