<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\User;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: ProtectedVarError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class ProtectedVarError extends Error
{
    public function __construct($var, $varlist)
    {
        parent::__construct($this->error($var, $varlist));
    }

    private function error($var, $varlist)
    {
        return User::isAdmin() ? 'The var "' . $var . '" you are using is protected like the following ones:' . implode(', ', $varlist) : '';
    }
}
?>
