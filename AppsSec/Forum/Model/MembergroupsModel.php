<?php
namespace Web\Framework\AppsSec\Forum\Model;

use Web\Framework\Lib\Model;

class MembergroupsModel extends Model
{
	public $tbl = 'membergroups';
	public $pk = 'id_group';

	public function getMembergroups()
	{
		$this->setField(array(
			'id_group', 
			'group_name'
		));
		$this->setOrder('group_name');
		return $this->read('2col');
	}
}
?>