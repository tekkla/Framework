<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Error Class: MissingRouteError
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Errors
 */
class MissingRouteError extends Error
{
    public function __construct($request_method, $request_url)
    {
        parent::__construct(array(
        	'Route not found. Method: ' . $request_method . ' | Url: ' . $request_url,
        	Txt::get('error_403', 'Web')
        ));
    }
}
?>
