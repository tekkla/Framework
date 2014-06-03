<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Error Class: FileAlreadyExistsError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class FileAlreadyExistsError extends Error
{
    public function __construct($file)
    {
        parent::__construct($this->error($file));
    }

    private function error($file)
    {
        return 'The file already exists.<br>File: ' . $file;
    }
}
?>
