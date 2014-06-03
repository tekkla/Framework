<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Error Class: CfgNotExistsError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class CfgNotExistsError extends Error
{
    public function __construct($app, $key)
    {
        parent::__construct($this->error($app, $key));
    }

    private function error($app, $key)
    {
        return 'The requested config "' . $key . '" does not exist for app "' . $app . '"';
    }
}
?>
