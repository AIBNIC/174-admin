<?php
namespace app\model;

use PDO;
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

	public function getAllRole($xz=1){
		if($xz==1){
			$data=$this->field(['id','role_name'])->select();
		}
		elseif($xz==2){
			$data=$this->field(['id','role_name','static','auth'])->order('id')->select();
		}
		return $data;
	}

	public function getRoleName($id){
		$data=$this->where('id',$id)->value('role_name');
		return $data;
	}


	public function addRole($data){
		$data=$this->insert($data);
		return $data;
	}

	public function delRole($id){
		$data=$this->where('id',$id)->delete();
		return $data;
	}

	public function editRole($data){
		$data=$this->where('id',$data['id'])->update($data);
		return $data;
	}
}
