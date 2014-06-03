<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\User;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: PathError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class PathError extends Error
{
    public function __construct($path)
    {
        parent::__construct($this->error($path));
    }

    private function error($path)
    {
        $message = 'Error on accessing path.';

        if (User::isAdmin())
            $message .= '<br>Path: ' . $path;

        return $message;
    }
}
?>
