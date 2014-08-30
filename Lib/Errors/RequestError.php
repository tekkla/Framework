<?php
namespace Web\Framework\Lib\Errors;

use Web\Framework\Lib\Abstracts\ErrorAbstract;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Request error handling object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib\Errors
 * @license BSD
 * @copyright 2014 by author
 */
final class RequestError extends ErrorAbstract
{
	protected $codes = array(
		6000 => 'General', 
		6001 => 'Missing', 
		6002 => 'AlreadyDeclared'
	);
	protected $fatal = true;
	protected $log = true;

	protected function processMissing()
	{
		$this->admin_message .= '<pre>' . print_r($this->params, true) . '</pre>';
		$this->user_message = '<h3 class="no-top-margin">' . Txt::get('error', 'Web') . ' 404</h3>' . Txt::get('error_404', 'Web');
	}

	protected function processAlreadyDeclared()
	{
		$this->admin_message = '
		<h1>' . $this->admin_message . '</h1>
		<h4>Description</h4>
		<p>An already declared route causes a full stop. You must use unique route names. Please check the corresponding app mainfile for this route and rename it to a unique name.</p>
		<h4>Parameter</h4>
		<pre>' . print_r($this->params, true) . '</pre>';
		
		// Flag not to be fatal, so this message runs through to die!
		$this->fatal = false;
		
		// Flag to cover message in red error div
		$this->box = true;
	}
}
?>

