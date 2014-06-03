<?php

namespace Web\Framework\AppsSec\Admin\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Lib\Url;

/**
 *
 * @author
 *         Michael
 *
 */
class AdminController extends Controller
{


	public function Index()
	{
		$this->setVar('web_config', Url::factory('admin_app_config', array('app_name' => 'web'))->getUrl());
		
		
		$this->addLinktree('WebExt Framework Center');
		
		$this->setVar( 'loaded_apps', $this->model->getApplist() );
	}

}

?>