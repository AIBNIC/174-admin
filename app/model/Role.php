<?php
namespace app\model;
use think\Model;
class Role extends Model
{
	// protected $table = 'table_name';
	// protected $pk = 'id';
	
	//获取到权限id
	public function getRoleAuth($id){
		$data=$this->where('id',$id)->value('auth');
		$data=trim($data);
		return explode(' ',$data);
	}
}
