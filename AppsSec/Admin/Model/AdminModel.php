<?php

namespace Web\Framework\AppsSec\Admin\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\App;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\String;

/**
 *
 * @author Michael "Tekkla" Zorn
 *
 */
class AdminModel extends Model
{

	public function getApplist()
	{
		$applist =  App::getLoadedApps();

		sort($applist);
		
		$out = new \stdClass();
		
		foreach ($applist as $app_name)
		{
			$app = App::create($app_name);
			
			$app_data = new \stdClass();
			
			$app_data->config_link = isset($app->config) ? Url::factory('admin_app_config')->setParameter('app_name', String::uncamelize($app_name))->getUrl() : false;
			
			
			$out->{$app_name} = $app_data;

		}

		return $out;
	}

}

?>