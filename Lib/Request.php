<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\SingletonAbstract;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Request class which handles routes and request like post or get.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 *
 * ------------------------------------------------------------------------
 * Routing based on AltoRouter
 * https://github.com/dannyvankooten/AltoRouter
 *
 * Copyright 2012-2013 Danny van Kooten hi@dannyvankooten.com
 * License MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 *
 * Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * ------------------------------------------------------------------------
 */
class Request extends SingletonAbstract
{

    /**
     * Status flag web
     * @var bool
     */
    private $is_web = false;

    /**
     * Status flag ajax
     * @var bool
     */
    private $is_ajax = false;

    /**
     * Status flag smf
     * @var bool
     */
    private $is_smf = false;

    /**
     * Requested app
     * @var string
     */
    private $app = '';

    /**
     * Requested conroller
     * @var string
     */
    private $ctrl = '';

    /**
     * Requestet action
     * @var string
     */
    private $action = '';

    /**
     * Target parameter used in AJAX requestshandling
     * @var string
     */
    private $target = '';

    /**
     * storage for GET parameters
     * @var array
     */
    private $params = array();

    /**
     * Name of current route
     * @var unknown
     */
    private $name = '';

    /**
     * Storage for POST values
     * @var Data
     */
    private $post = false;


    /**
     * Storage for unprocessed POST values
     * @var Data
     */
    private $post_raw = false;

    /**
     * Route storage
     * @var array
     */
    private $routes = array();

    /**
     * Named routes storage
     * @var array
     */
    private $named_routes = array();

    /*+
     * Basepath for routing
     * @var string
     */
    private $base_path = '/web';

    // PCRE matchtypes
    private $match_types = array(
        'i' => '[0-9]++',
        'a' => '[0-9A-Za-z]++',
        'h' => '[0-9A-Fa-f]++',
        '*' => '.+?',
        '**' => '.++',
        '' => '[^/\.]++'
    );

    private $match = false;

    // ---------------------------------------------------------------------------
    // ROUTE HANDLING
    // ---------------------------------------------------------------------------

    /**
     * Add multiple routes at once from array in the following format:
     *
     * $routes = array(
     * array($method, $route, $target, $name)
     * );
     *
     * @param array $routes
     * @return void
     * @author Koen Punt
     */
    public function addRoutes($routes)
    {
        if (!is_array($routes) && !$routes instanceof \Traversable)
            Throw new Error('Routes should be an array or an instance of Traversable');

        foreach ( $routes as $route )
            call_user_func_array(array(
                $this,
                'mapRoute'
            ), $route);
    }

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function setBasePath($base_path)
    {
        $this->base_path = $base_path;
    }

    /**
     * Add named match types.
     * It uses array_merge so keys can be overwritten.
     * @param array $match_types The key is the name and the value is the regex.
     */
    public function addMatchTypes($match_types)
    {
        $this->match_types = array_merge($this->match_types, $match_types);
    }

    /**
     * Map a route to a target
     * @param string $method One of 4 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PUT|DELETE)
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @param array $access Optional array with names of SMF access rights.
     */
    public function mapRoute($route)
    {
        if (!isset($route['target']))
            Throw new Error('A route needs a target', 6004, $route);

        // Is this a named route?
        if (isset($route['name']))
        {
            if (array_key_exists($route['name'], $this->named_routes))
            {
                throw new Error('Route "' . $route['name'] .'" has already been declared.', 6002, $route);
            }
            else
            {
                $named_route = array(
                    'route' => $route['route']
                );

                if (isset($route['access']))
                    $named_route['access'] = $route['access'];

                $this->named_routes[$route['name']] = $named_route;
            }
        }

        // Prepare route definition
        $route_definition = array();

        // Check for set route method
        $route_definition[0] = isset($route['method']) ? $route['method'] : 'GET';

        // Extend route with basepath
        $route_definition[1] = $this->base_path . $route['route'];

        // Set target
        $route_definition[2] = $route['target'];

        // Name set?
        $route_definition[3] = isset($route['name']) ? $route['name'] : '';

        // Access set?
        $route_definition[4] = isset($route['access']) ? $route['access'] : array();

        // Stor new route
        $this->routes[] = $route_definition;

        return $this;
    }


    /**
     * Reversed routing
     * Generate the URL for a named route.
     * Replace regexes with supplied parameters
     * @param string $route_name The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     * @return string The URL of the route with named parameters in place.
     */
    public function getRouteUrl($route_name, $params=array())
    {
        // Check if named route exists
        if (!isset($this->named_routes[$route_name]))
            Throw new Error(
                'Route "' . $route_name . '" does not exist.',
                6000,
                array(
                    'route_name' => $route_name,
                    'params' => $params,
                )
            );

        // Replace named parameters
        $route = $this->named_routes[$route_name];

        // Accesscheck set?
        if (isset($route['access']) && !allowedTo($route['access']))
            return;

        $url = $route['route'];

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route['route'], $matches, PREG_SET_ORDER))
        {
            foreach ( $matches as $match )
            {
                list($block, $pre, $type, $param, $optional) = $match;

                if ($pre)
                    $block = substr($block, 1);

                if (isset($params[$param]))
                    $url = str_replace($block, $params[$param], $url);
                elseif ($optional)
                    $url = str_replace($pre . $block, '', $url);
                else
                    Throw new Error(
                        'Parameter missing.',
                        6001,
                        array(
                            'route_name' => $route_name,
                            'params' => $params,
                            'block' => $block,
                            'param' =>  $param
                        )
                    );
            }
        }

        return BOARDURL . $this->base_path . $url;
    }

    /**
     * Match a given request url against stored routes
     * @param string $request_url
     * @param string $request_method
     * @return array boolean with route information on success, false on failure (no match).
     */
    public function processRequest($request_url=null, $request_method=null)
    {
        if (empty($_REQUEST['action']) && Cfg::exists('Web', 'default_action'))
        	$_REQUEST['action'] = Cfg::get('Web', 'default_action');

        // Is this a web request?
        $this->is_web = isset($_REQUEST['action']) && $_REQUEST['action'] == 'web';

        // Is it a Smf request?
        if ((isset($_REQUEST['action']) && $_REQUEST['action'] != 'web') || isset($_REQUEST['board']) || isset($_REQUEST['topic']))
        {
            // Set flag ...
            $this->is_smf = true;

            // ... and stop request processing
            return;
        }

        $params = array();
        $match = false;

        // set Request Url if it isn't passed as parameter
        if ($request_url === null)
            $request_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

            // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($request_url, '?')) !== false)
            $request_url = substr($request_url, 0, $strpos);

            // Framework ajax.js adds automatically an /ajax flag @ the end of the requested URI.
            // Here we check for this flag, remembers if it's present and then remove the flag
            // so the following URI processing runs without flaw.
        if (substr($request_url, -5) == '/ajax')
        {
            $this->addParam('ajax', true);
            $request_url = str_replace('/ajax', '', $request_url);
            $this->is_ajax = true;
        }

        // set Request Method if it isn't passed as a parameter
        if ($request_method === null)
            $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        foreach ( $this->routes as $handler )
        {

            list($method, $_route, $target, $name, $access) = $handler;

            // Method seems to match. First to do is a possible access check
            // If check fails, the rest of our routes will be checked
            if (isset($access) && !allowedTo($access))
                continue;

            $methods = explode('|', $method);
            $method_match = false;

            // Check if request method matches. If not, abandon early. (CHEAP)
            foreach ( $methods as $method )
            {
                if (strcasecmp($request_method, $method) === 0)
                {
                    $method_match = true;
                    break;
                }
            }

            // Method did not match, continue to next route.
            if (!$method_match)
                continue;

                // Check for a wildcard (matches all)
            if ($_route === '*')
                $match = true;
            elseif (isset($_route[0]) && $_route[0] === '@')
                $match = preg_match('`' . substr($_route, 1) . '`u', $request_url, $params);
            else
            {
                $route = null;
                $regex = false;
                $j = 0;
                $n = isset($_route[0]) ? $_route[0] : null;
                $i = 0;

                // Find the longest non-regex substring and match it against the URI
                while ( true )
                {
                    if (!isset($_route[$i]))
                        break;
                    elseif (false === $regex)
                    {
                        $c = $n;
                        $regex = $c === '[' || $c === '(' || $c === '.';

                        if (false === $regex && false !== isset($_route[$i + 1]))
                        {
                            $n = $_route[$i + 1];
                            $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                        }

                        if (false === $regex && $c !== '/' && (!isset($request_url[$j]) || $c !== $request_url[$j]))
                            continue 2;

                        $j++;
                    }
                    $route .= $_route[$i++];
                }

                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $request_url, $params);
            }

            if ($match == true || $match > 0)
            {
                if ($params)
                {
                    foreach ( $params as $key => $value )
                        if ($key == '0'.$key)
                            unset($params[$key]);
                }

                $this->match = array(
                    'target' => $target,
                    'params' => $params,
                    'name' => $name
                );

                $this->name = $name;

                // Map target results to request properties
                foreach ( $target as $key => $val )
                {
                    if (property_exists($this, $key))
                        $this->{$key} = String::camelize($val);
                }

                // When no target controller defined in route but provided by parameter
                // we use the parameter as requested controller
                if (!$this->ctrl && isset($params['controller']))
                    $this->ctrl = String::camelize($params['controller']);

                // Same for action as for controller
                if (!$this->action && isset($params['action']))
                	$this->action = String::camelize($params['action']);

                $this->params = $params;

                // Finally try to process possible posted data
                if (isset($_POST) && isset($_POST['web']))
                {
                    $this->post = new Data($_POST['web']);
                    $this->post_raw = $_POST['web'];
                }

                return $this;
            }
        }

        $this->match = false;

        if ($this->isWeb())
            Throw new Error('No matching route found.', 6001, array($request_url, $request_method));

        return $this;
    }

    /**
     * Compile the regex for a given route (EXPENSIVE)
     */
    private function compileRoute($route)
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER))
        {
            $match_types = $this->match_types;

            foreach ( $matches as $match )
            {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($match_types[$type]))
                    $type = $match_types[$type];

                if ($pre === '.')
                    $pre = '\.';

                    // Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:' . ($pre !== '' ? $pre : null) . '(' . ($param !== '' ? "?P<$param>" : null) . $type . '))' . ($optional !== '' ? '?' : null);

                $route = str_replace($block, $pattern, $route);
            }
        }

        return "`^$route$`u";
    }

    /**
     * Checks for a route with the name of the parameter
     * @param string $route
     * @return boolean
     */
    public function checkRoute($route)
    {
        return isset($this->routes[$route]);
    }

    /**
     * Returns the name of the current active route
     */
    public function getCurrentRoute()
    {
        return $this->name;
    }

    /**
     * Returns smf related infos
     * todo Why this? Is this still required?
     * @return string
     */
    public function getSmfAction()
    {
        if (isset($_REQUEST['action']))
            return $_REQUEST['action'];

        if (isset($_REQUEST['board']))
            return 'board';

        if (isset($_REQUEST['topic']))
            return 'topic';
    }

    /**
     * Checks if the request is a call on smf
     * @return boolean
     */
    public function isSmf($bool = null)
    {
        if (isset($bool))
        {
            $this->is_smf = true;
            return $this;
        }

        return $this->is_smf;
    }

    /**
     * Checks for web framework call.
     * @return boolean
     */
    public function isWeb($bool = null)
    {
        if (isset($bool))
        {
            $this->is_web = true;
            return $this;
        }

        return $this->is_web;
    }

    /**
     * Checks for an ajax request and returns boolean true or false
     * @return boolean
     */
    public function isAjax($bool = null)
    {
        if (isset($bool))
        {
            $this->is_ajax = true;
            return $this;
        }

        return $this->is_ajax;
    }

    /**
     * Checks if the request is a (A)pp(C)ontroller(A)ction call
     * @return boolean
     */
    public function isCall()
    {
        return $this->checkApp() && $this->checkCtrl() && $this->checkAction();
    }

    /**
     * Check set of app property
     */
    public function checkApp()
    {
        return isset($this->app);
    }

    /**
     * Returns a camelized app name
     * @return string
     */
    public function getApp()
    {
        return isset($this->app) ? $this->app : false;
    }

    /**
     * Set appname manually
     * @param string $val
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Checks if a controller is set and returns true/false
     * @return boolean
     */
    public function checkCtrl()
    {
        return !empty($this->ctrl);
    }

    /**
     * Returns a camelized controller name
     * @return string
     */
    public function getCtrl()
    {
        return isset($this->ctrl) ? $this->ctrl : false;
    }

    /**
     * Sets the requested controller manually
     * @param string $ctrl
     */
    public function setCtrl($ctrl)
    {
        $this->ctrl = $ctrl;
        return $this;
    }

    /**
     * Checks if the function name is set
     */
    public function checkAction()
    {
        return !empty($this->action);
    }

    /**
     * Returns either the requested or 'Index' (default) as action name
     */
    public function getAction()
    {
        if (!isset($this->action))
            $this->action = 'Index';

        return $this->action;
    }

    /**
     * Set the requested func manually
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Returns requested app, controller and action as array
     * @return multitype:string
     */
    public function getACA()
    {
        return array(
        	'app' => $this->app,
            'ctrl' => $this->ctrl,
            'action' => $this->action
        );
    }

    /**
     * Boolean check if parameter exists
     * @param string parametername
     * @return boolean
     */
    public function checkParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Sets one or mor request parameter. Set $arg1 as assoc array to add multiple parameter.
     * Both args ($arg1 and $arg2) set means to add a parameter by key ($arg1) and value ($arg2)
     * @param string|array $arg1
     * @param string $arg2
     * @return \Web\Framework\Lib\Request
     */
    public function addParam($arg1, $arg2=null)
    {
        if ($arg2===null && is_array($arg1))
        {
            $arg1 = Lib::fromObjectToArray($arg1);

            foreach ( $arg1 as $key => $val )
                $this->params[$key] = $val;
        }

        if ($arg2 !== null)
            $this->params[$arg1] = Lib::fromObjectToArray($arg2);

        return $this;
    }

    /**
     * Returns the value of a paramter as long as it exists.
     * @param string $key
     */
    public function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : false;
    }

    /**
     * Returns the complete paramslist
     * @return array
     */
    public function getAllParams()
    {
        return $this->params;
    }

    public function isPost()
    {
        return isset($_POST) && isset($_POST['web']);
    }

    /**
     * Returns the value of $_POST[web][appname][controllername][key]
     * @param string $key
     */
    public function getPost($app_name='', $model_name='')
    {
    	// Use values provided by request for missing app and model name
        if (!$app_name || !$model_name)
        {
            $app_name = $this->getApp();
            $model_name = $this->getCtrl();
        }

        $app_name = String::uncamelize($app_name);
        $model_name = String::uncamelize($model_name);

        if (isset($this->post->{$app_name}->{$model_name}))
            return $this->post->{$app_name}->{$model_name};
        else
            return false;
    }

    /**
     * Returns the complete raw post array
     * @return array
     */
    public function getRawPost()
    {
    	return $this->post_raw;
    }

    /**
     * Returns the complete processed post object
     * @return \Web\Framework\Lib\Data
     */
    public function getCompletePost()
    {
        return $this->post;
    }

    /**
     * Returns true if $_POST[web][appname][modelname] is in the processed post data
     * @param string $app
     */
    public function checkPost(&$app_name = null, &$model_name = null)
    {
        if (!isset($app_name) || !isset($model_name))
        {
            $app_name = $this->getApp();
            $model_name = $this->getCtrl();
        }

        $app_name = String::uncamelize($app_name);
        $model_name = String::uncamelize($model_name);

        return isset($this->post->{$app_name}) && isset($this->post->{$app_name}->{$model_name});
    }

    public function clearPost()
    {
        $this->data = new \stdClass();
    }

    public function getStatus()
    {
        return array(
            'is_web' => $this->is_web,
            'is_smf' => $this->is_smf,
            'is_ajax' => $this->is_ajax,
            'app' => $this->getApp(),
            'ctrl' => $this->getController(),
            'action' => $this->getAction(),
            'params' => $this->getAllParams()
        );
    }
}
?>
