<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Object error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
class ObjectError extends ErrorAbstract
{
    protected $codes = array(
        5000 => 'General',
        5001 => 'MethodMissing',
        5002 => 'PropertyMissing',
        5003 => 'PropertyNotSet',
        5004 => 'PropertyEmpty',
    );

    protected $fatal = true;
}
?>