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
final class RouteMissingError extends ErrorAbstract
{
    /**
     * (non-PHPdoc)
     * @see \Web\Framework\Lib\Abstracts\ErrorAbstract::process()
     */
    public function process()
    {
        $this->admin_message = 'Message Admin';
        $this->user_message = 'Message User';
        $this->redirect = '';
        $this->code = '666';
        $this->fatal = true;
    }
}
?>

