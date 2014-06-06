<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: NoValidDbQueryTypeError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class NoValidDbQueryTypeError extends Error
{
    public function __construct($query_type)
    {
        parent::__construct($this->error($query_type));
    }

    private function error($query_type)
    {
        return array(
        	'Your DB query type "' . $query_type . '") does not exist.',
        	Txt::get('error_500', 'Web')
        );
    }
}
?>
