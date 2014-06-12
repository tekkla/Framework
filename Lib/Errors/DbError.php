<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * General error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
final class DbError extends ErrorAbstract
{
    protected $codes = array(
    	3000 => 'General',
        3001 => 'WrongQueryType',
    );

    protected $fatal = true;

    protected function processWrongQueryType()
    {
        $this->admin_message .= '<pre>' . print_r($this->params, true) . '</pre>';
    }

}
?>

