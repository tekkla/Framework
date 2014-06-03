<?php
namespace Web\Framework\AppsSec\Admin\View;

use Web\Framework\Lib\View;

final class ConfigView extends View
{
	public function Config()
	{
		$html = '
		<h2>' . $this->icon . '&nbsp;' . $this->app_name . '</h2>
		' . $this->form;

		return $html;
	}
}

?>