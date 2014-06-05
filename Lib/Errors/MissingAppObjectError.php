<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: MissingAppObjectError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class MissingAppObjectError extends Error
{
    public function __construct()
    {
        parent::__construct($this->error());
    }

    private function error($source)
    {
        return get_called_class() . ' needs an app object.';
    }
}
?>
