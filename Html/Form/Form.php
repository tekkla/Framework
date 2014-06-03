<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Errors\NoValidParameterError;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a form html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Form extends HtmlAbstract
{
    /**
     * Factory method
     * @return Form
     */
    public static function factory()
    {
        return new Form();
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $this->setElement('form');
        $this->addAttribute('role', 'form');

        // Forms method is POST by default
        $this->setMethod('post');
    }

    /**
     * Set the name of a route to compile as action url
     * @param string $route Name of route to compile
     * @param array|stdClass $params Parameter to use in route to compile
     * @return \Web\Framework\Html\Elements\Form
     */
    public function setActionRoute($route, $params = null)
    {
        // Store routename for later use
        $this->route = $route;

        // Compile route and set url as action url
        $this->addAttribute('action', Url::factory($route, $params)->getUrl());

        return $this;
    }


    /**
     * Set the form method attribute.
     * Use 'post' or 'get'.
     * Form elements are using post by default.
     * @param string $method Value for the method attribute of from
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
            Throw new NoValidParameterError($method, $methods);

        $this->addAttribute('method', $method);
        return $this;
    }

    /**
     * Set form accept charset attribute
     * @param string $accept_charset
     * @return \Web\Framework\Html\Elements\Form
     */
    public function setAcceptCharset($accept_charset)
    {
        $this->addAttribute('accept_charset', $accept_charset);
        return $this;
    }

    /**
     * Set form target attribute
     * @param string $target
     * @return \Web\Framework\Html\Elements\Form
     */
    public function setTarget($target)
    {
        $this->addAttribute('target', $target);
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
            Throw new NoValidParameterError($state, $states);

        $this->addAttribute('autocomplete', $state);
        return $this;
    }

    /**
     * Deactivates form validation by setting "novalidate" attribute
     * @return \Web\Framework\Html\Elements\Form
     */
    public function noValidate()
    {
        $this->addAttribute('novalidate');
        return $this;
    }
}
?>
