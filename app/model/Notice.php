<?php
namespace app\model;
use think\Model;
class Notice extends Model
{
	// protected $table = 'table_name';
	// protected $pk = 'id';

	public function getText(){
		$data=$this->limit(3)->order('creat_time','dasc')->select()->toArray();
		return $data;
	}
	
}
