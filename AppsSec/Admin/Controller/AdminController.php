<?php
namespace Web\Framework\AppsSec\Admin\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Lib\Url;

/**
 * Admin Controller
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 */
final class AdminController extends Controller
{

	public function Index()
	{
		$this->setVar(array(
			'web_config' => Url::factory('admin_app_config', array('app_name' => 'web'))->getUrl(),
			'loaded_apps' => $this->model->getApplist()
		));

		$this->addLinktree('WebExt Framework Center');
	}
}
?>
