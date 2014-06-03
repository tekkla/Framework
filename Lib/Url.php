<?php
namespace Web\Framework\Lib;

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
final class Url
{

    private $ajax;

    private $page;

    private $app;

    private $ctrl;

    private $func;

    private $part;

    private $action;

    private $topic;

    private $board;

    private $target;

    private $anchor;

    private $params;

    private $named_route;

    /**
     * Returns an URL object
     *
     * @return Url
     */
    public static function factory($named_route = null, $params = null)
    {
        $url = new Url();

        if (isset($named_route))
        {
            $url->setNamedRoute($named_route);

            if (isset($params))
                $url->addParameter($params);
        }

        return $url;
    }

    private function __construct()
    {
        $this->params = new \stdClass();
    }

    private function reset()
    {
        foreach ( $this as &$property )
        {
            if (isset($property))
                unset($property);
        }
        return $this;
    }

    public function setNamedRoute($named_route)
    {
        $this->named_route = $named_route;
        return $this;
    }

    public function setAjax($bool)
    {
        $this->ajax = $bool;
        $this->addParameter('is_ajax', 1);
        return $this;
    }

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    function setCtrl($ctrl)
    {
        $this->ctrl = $ctrl;
        return $this;
    }

    function setFunc($func)
    {
        $this->func = $func;
        return $this;
    }

    function setAction($action)
    {
        $this->action = $action;

        $this->unsetData('board');
        $this->unsetData('topic');

        return $this;
    }

    function setTopic($topic, $msg = null, $anchor = null)
    {
        if (isset($msg))
            $topic .= '.msg' . $msg;

        if (isset($anchor))
            $this->setAnchor($anchor);

        $this->topic = $topic;

        $this->ctrl = 'smf';

        $this->unsetData('board');
        $this->unsetData('action');

        return $this;
    }

    function setBoard($board)
    {
        $this->board = $board;

        $this->ctrl = 'smf';

        $this->unsetData('topic');
        $this->unsetData('action');
        return $this;
    }

    function setPart($part)
    {
        $this->part = $part;
        return $this;
    }

    function setTarget($target)
    {
        $this->addParameter('target', $target);
        return $this;
    }

    function setSubaction($sa)
    {
        $this->addParameter('sa', $sa);
        return $this;
    }

    public function setArea($area)
    {
        $this->addParameter('area', $area);
        return $this;
    }

    /**
     * Add one parameter in form of key and value or a list of parameters as assoc array
     * @throws Error
     * @return \Web\Framework\Lib\Url
     */
    function addParameter()
    {
        if (func_num_args() == 1 && is_array(func_get_arg(0)) && !empty(func_get_arg(0)))
        {
            // if argument is an assoc array all is ok
            if (Arrays::isAssoc(func_get_arg(0)))
            {
                $params = func_get_arg(0);

                foreach ( $params as $key => $val )
                    $this->params->{$key} = $val;
            }
            else
            {
                // No assoc array => exception!
                throw new Error('The array you are trying to add as url parameter is not an associative array!');
            }
        }

        if (func_num_args() == 2)
        {
            if (is_string(func_get_arg(0)))
                $this->params->{func_get_arg(0)} = func_get_arg(1);
            else
                throw new Error('The key of the url parameter to add is not of type sting!');
        }

        return $this;
    }

    function setParameter()
    {
        $this->params = new \stdClass();

        if (func_num_args() == 1 && is_array(func_get_arg(0)) && !empty(func_get_arg(0)))
        {

            // if argument is an assoc array all is ok
            if (Arrays::isAssoc(func_get_arg(0)))
            {
                $params = func_get_arg(0);

                foreach ( $params as $key => $val )
                    $this->params->{$key} = $val;
            }
            else
            {
                // no assoc array => exception!
                throw new Error('The array you are trying to add as url parameter is not an associative array!');
            }
        }

        if (func_num_args() == 2)
        {
            if (is_string(func_get_arg(0)))
                $this->params->{func_get_arg(0)} = func_get_arg(1);
            else
                throw new Error('The key of the url parameter to add is not of type sting!');
        }

        return $this;
    }

    function setAnchor($anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }

    function getUrl()
    {
        $request = Request::getInstance();

        // Page requests are simple an will be redirected as as
        if (isset($this->page))
            return $request->getRouteUrl('web_page', $this->params);

            // if action isset, we have a smf url to build
        if (isset($this->action) || isset($this->board) || isset($this->topic))
            return $this->getSmfURL();

        if (isset($this->named_route))
            return $request->getRouteUrl($this->named_route, $this->params);

        return '#';
    }

    private function getSmfUrl()
    {
        // build parameterlist
        $params = array();

        foreach ( $this->params as $key => $val )
        {
            if ($key == 'area' || $key == 'sa')
                continue;

            $params[] = empty($val) ? $key : $key . '=' . $val;
        }

        $anchor = isset($this->anchor) ? '#' . $this->anchor : '';

        $params = count($params) > 0 ? '?' . implode(';', $params) : '';

        if (isset($this->topic))
            $url_base = '/topic/' . $this->topic . '.html';
        elseif (isset($this->board))
            $url_base = '/board/' . $this->board . '.html';
        else
        {
            $url_parts = array(
                $this->action
            );

            if (isset($this->params->area))
                $url_parts[] = 'area_' . $this->params->area;

            if (isset($this->params->sa))
                $url_parts[] = 'sa_' . $this->params->sa;

            $url_base = '/' . implode('/', $url_parts) . '/';
        }
        return BOARDURL . $url_base . $params . $anchor;
    }

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

        // Empty params oder no 'action' set or not 'action' first query part? Return url unchnged
        if (empty($parsed['params']) || !isset($parsed['params']['action']) || key($parsed['params']) != 'action')
            return $raw_url[0];

            // All checks done. Lets rewrite the url
        $url = self::factory();

        foreach ( $parsed['params'] as $key => $val )
        {
            $method = 'set' . String::camelize($key);

            //
            if ($key != 'board' && $key != 'topic' && method_exists($url, $method))
                $url->{$method}($val);
            else
                $url->addParameter($key, $val);
        }

        // And finally return the rewritten url
        return $url->getUrl();
    }
}
?>
