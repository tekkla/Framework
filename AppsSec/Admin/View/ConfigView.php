<?php
namespace Web\Framework\AppsSec\Admin\View;

use Web\Framework\Lib\View;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Admin Config view
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage AppSec\Admin
 */
final class ConfigView extends View
{

	public function Config()
	{
		echo '<h2>' . $this->icon . '&nbsp;' . $this->app_name . '</h2>' . $this->form;
	}
}
?>
