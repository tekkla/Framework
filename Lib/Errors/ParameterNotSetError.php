<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: ParameterNotSetError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class ParameterNotSetError extends Error
{
    public function __construct($method, $parameter)
    {
        parent::__construct($this->error($method, $parameter));
    }

    private function error($method, $parameter)
    {
        return 'Needed parameter "' . $parameter . '" for method "' . $method . '" not set.';
    }
}
?>
