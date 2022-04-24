<?php
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
}

