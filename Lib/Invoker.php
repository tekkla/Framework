<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Errors\ParameterNotSetError;
use Web\Framework\Lib\Errors\MethodNotExistsError;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Invoker class for running object methods
 * This class checks for optional and needed method parameters by using reflection.
 * Missing but needed parameters produces an error
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Invoker
{
    /**
     * Object to run method from
     * @var object
     */
    private $obj;

    /**
     * Name of method to run
     * @var string
     */
    private $method;

    /**
     * Parameters as method arguments
     * @var \stdClass
     */
    private $params;

    /**
     * Factory method
     * @param string|object $obj Name of an object or the object itself
     * @param string $method Name of method to call
     * @param \stdClass $params Optinal parameterlist
     * @return mixed
     */
    public static function run(&$obj, $method, $params = null)
    {
        $invoker = new Invoker($obj, $method, $params);
        return $invoker->executeInvoker();
    }

    /**
     * Constructor
     * @param string|object $obj Name of an object or the object itself
     * @param string $method Name of method to call
     * @param \stdClass $params Optinal parameterlist
     */
    private function __construct($obj, $method, $params = null)
    {
        $this->obj = $obj;
        $this->method = $method;

        if (isset($params))
            $this->params = $params;
    }

    /**
     * Executes object method by using Reflection
     * @throws MethodNotExistsError
     * @throws ParameterNotSetError
     * @return mixed
     */
    public function executeInvoker()
    {
        // Look for the method in object. Throw error when missing.
        if (!method_exists($this->obj, $this->method))
            Throw new MethodNotExistsError($this->method, get_called_class());

        // Get reflection method
        $method = new \ReflectionMethod($this->obj, $this->method);

        // Init empty arguments array
        $args = array();

        // Get list of paremters from refelction method object
        $param_list = $method->getParameters();

        // Let's see what arguments are needed and which are optional
        foreach ( $param_list as $param )
        {
            // Get current paramobject name
            $param_name = $param->getName();

            // Parameter is not optional and not set => throw error
            if (!$param->isOptional() && !isset($this->params->{$param_name}))
                Throw new ParameterNotSetError($method->getName(), $param);

            // If parameter is optional and not set, set argument to null
            $args[] = $param->isOptional() && !isset($this->params->{$param_name}) ? null : $this->params->{$param_name};
        }

        // Return result executed method
        return $method->invokeArgs($this->obj, $args);
    }
}
?>
