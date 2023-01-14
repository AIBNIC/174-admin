<?php
//管理员账号模型
namespace app\model;
use think\Model;
class User extends Model
{
	// protected $table = 'table_name';
	// protected $pk = 'id';
	
	public function login($username){
		$data=$this->where('username',$username)->find();
		// $pwd=$data['pwd'];
		return $data;
	}
	public function getAllAdmin(){
		$data=$this->where('static',1)->select();
		return $data;
	}

	public function updateUserInfo($arr){
		$data=$this->where('id',$arr['id'])->update(['username'=>$arr['userName'],'pwd'=>$arr['userPwd'],'name'=>$arr['name'],'email'=>$arr['email'],'role_id'=>intval($arr['roleName'])]);
		return $data;
	}

	public function delUser($id){
		$data=$this->where('id',$id)->update(['static'=>0]);
		return $data;
	}

	public function addUser($data){
		try{
			$data=$this->insert($data);
		}
		catch(\Exception $e){
			return 0;
		}
		
		return $data;
	}
}

