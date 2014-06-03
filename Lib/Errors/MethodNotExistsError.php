<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: MethodNotExistsError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class MethodNotExistsError extends Error
{
    public function __construct($method_name, $object_name)
    {
        parent::__construct($this->error($method_name, $object_name));
    }

    private function error($method_name, $object_name)
    {
        return 'The method "' . $method_name . '()" does not exists in "' . $object_name . '".';
    }
}
?>
