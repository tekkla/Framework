<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * ParameterValue error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
final class ParameterValueError extends ErrorAbstract
{
    protected $codes = array(
        1000 => 'General',
        1001 => 'WrongParameter',
        1001 => 'MissingParameter',
    );

    protected $fatal = true;

    protected function processMissingParameter()
    {
        $this->admin_message .= '<pre>' . print_r($this->params, true) . '</pre>';
    }
}
?>
