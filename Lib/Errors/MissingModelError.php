<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: MissingModelError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class MissingModelError extends Error
{
    public function __construct($source)
    {
        parent::__construct($this->error($source));
    }

    private function error($source)
    {
        return $source . ' has found no model.';
    }
}
?>
