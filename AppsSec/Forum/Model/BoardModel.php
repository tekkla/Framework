<?php
namespace Web\Framework\AppsSec\Forum\Model;

use Web\Framework\Lib\Model;

class BoardModel extends Model
{
	public $tbl = 'boards';
	public $alias = 'board';
	public $pk = 'id_board';

	public function getBoardlist()
	{
		$this->setField(array(
			'id_board', 
			'name'
		));
		$this->setOrder('`name`');
		return $this->read('2col');
	}
}
?>