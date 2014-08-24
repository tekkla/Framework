<?php

namespace Web\Framework\AppsSec\Admin\View;

use Web\Framework\Lib\View;

/**
 *
 * @author
 *         Michael
 *
 */
class AdminView extends View
{

	public function Index()
	{
		echo '<h1>WebExt Framework Config</h1>';

		echo '
		<div class="row">
			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-body">
						<a class="btn btn-default" href="', $this->web_config, '">Framework Config</a>
						<h3><strong>Applications:</strong></h3>
						<ul class="list-group">';

						foreach ($this->loaded_apps as $app_name => $app)
							echo '
							<li class="list-group-item clearfix">', $app_name, ($app->config_link ? '<a href="' . $app->config_link . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cog"></i></a></li>' : '');

						echo '
						</ul>
					</div>
				</div>
			</div>
		</div>';
	}
}
?>
