<?php
namespace Web\Framework\Html\Form;

use Web\Framework\Lib\Interfaces\HtmlInterface;
use Web\Framework\Html\Elements\FormElement;

class Label extends FormElement implements HtmlInterface
{
	public static function factory($for, $inner=null)
	{
		$obj = new Label();
		$obj->setFor($for);

		if (isset($inner))
			$obj->setInner($inner);
		else
			$obj->setInner($for);

		return $obj;
	}

	function __construct()
	{
		$this->setElement('label');
	}


	public function setFor($for)
	{
		$this->addAttribute('for', $for);
		return $this;
	}
}
?>