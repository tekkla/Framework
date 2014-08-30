<?php

namespace Framework\AppsSec\Web\View;

use Web\Framework\Lib\View;

/**
 *
 * @author
 *		 Michael
 *
 */
class Web_View_Default extends View
{
	public function Index()
	{
		return '
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h1 class="panel-title">Default View</h1>
			</div>
			<div class="panel-body">You see this, beacuse there is no viev defined for the requested action.</div>
		</div>';
	}

}

?>