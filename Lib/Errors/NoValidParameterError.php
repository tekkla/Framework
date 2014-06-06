<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: NoValidParameterError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class NoValidParameterError extends Error
{
    public function __construct($param, $paramlist)
    {
        parent::__construct($this->error($param, $paramlist));
    }

    private function error($param, $paramlist)
    {
        return array(
        	'Your parameter "' . $param . '" is not allowed. Please select from:' . implode(', ', $paramlist),
        	Txt::get('error_500', 'Web')
        );
    }
}
?>
