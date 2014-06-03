<?php
namespace Web\Framework\Lib\Errors;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

// Used classes
use Web\Framework\Lib\Error;
use Web\Framework\Lib\User;

/**
 * Error Class: NeededPropertyNotSetError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class NeededPropertyNotSetError extends Error
{
    public function __construct($property)
    {
        parent::__construct($this->error($property));
    }

    private function error($property)
    {
        return User::isAdmin() ? 'Needed property "' . $property . '" not set.' : '';
    }
}
?>
