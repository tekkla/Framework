<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\User;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: TypeMismatchError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class TypeMismatchError extends Error
{
    public function __construct($value, $type_expected)
    {
        parent::__construct($this->error($value, $type_expected));
    }

    private function error($value, $type_expected)
    {
        $message = 'The datatype of a value is not correct.';

        if (User::isAdmin())
            $message .= '<br>Valuetype: ' . gettype($value) . '<br>Expected: ' . $type_expected;

        return $message;
    }
}
?>
