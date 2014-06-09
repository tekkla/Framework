<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * RouteMissing error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
final class GeneralError extends ErrorAbstract
{
    /**
     * (non-PHPdoc)
     * @see \Web\Framework\Lib\Abstracts\ErrorAbstract::process()
     */
    public function process()
    {
        $this->fatal = true;
    }
}
?>

