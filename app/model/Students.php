<?php

namespace app\model;

use think\Model;
use app\model\Abms  as AbmsModel;

class Students extends Model
{
	protected $table = 'xyw_user';
	// protected $pk = 'id';
	protected $connection = 'db_config_174';

	public function getStudents($xuehao)
	{
		$data = $this->where("xuehao", $xuehao)->whereor("username", $xuehao)->find();
		if($data!=''){
			$data=$data->toArray();
		}
		return $data;
	}

	public function getClass($xuehao)
	{
		$data = $this->table('xyw_user_class')->where('xuehao', $xuehao)->find()->toArray();
		if ($data) {
			return $data;
		} else {
			$data = $this->query("SELECT 学号,当前所在级,班级名称 FROM v学生信息表 WHERE 学号=? ", [$xuehao]);
			$insert = $this->array_iconv($data);
			$insert['xuehao'] = $insert[0]['学号'];
			$insert['nowYear'] = $insert[0]['当前所在级'];
			$insert['className'] = $insert[0]['班级名称'];
			$this->table('xyw_user_class')->strict(false)->insert($insert);

			$Newdata = $this->table('xyw_user_class')->where('xuehao', $xuehao)->find()->toArray();
			return $Newdata;
		}
	}

	//根据宿舍id获取宿舍楼号和房号
	public function getRoom($id)
	{
		$data = $this->table('xyw_room')->where('id', $id)->find()->toArray();
		return $data;
	}

	//根据学号查询宿舍楼号和房号
	public function getRoomName($xuehao){
		$room=$this->where('xuehao',$xuehao)->value('room');
		$data=$this->table('xyw_room')->where('id',$room)->field('lh,fh')->find()->toArray();
		// if(empty($data)){
		// 	$data=$data;
		// }
		return $data;

	}

	//获取宿舍列表
	public function getAllRoom()
	{
		$data = $this->table('xyw_room')->select()->toArray();
		$lh = $this->table('xyw_room')->group("lh")->column('lh');
		return $lh;
	}
	public function getAllfh($lh)
	{
		$fh = $this->table('xyw_room')->where('lh', $lh)->order('fh')->field('id,lh,fh')->select()->toArray();
		return $fh;
	}
	//更新宿舍
	public function editRoom($xuehao, $room, $lh = '', $fh = '')
	{
		$data = $this->where('xuehao', $xuehao)->update(['room' => $room, 'lh' => $lh, 'fh' => $fh]);
		// ['room'=>$updata['room'],'lh'=>$updata['lh'],'fh'=>$updata['fh']];
		return $data;
	}

	//获取安朗表的用户信息
	public function getTime($xuehao)
	{
		$data = $this->table('xyw_abms_user')->where('xuehao', $xuehao)->whereor("username", $xuehao)->find()->toArray();
		return $data;
	}
	//修改安朗表用户到期时间
	public function editTime($xuehao,$old_time,$enkey){
		$data=$this->table('xyw_abms_user')->where('xuehao', $xuehao)->update(['old_time'=>$old_time,'EnKey'=>$enkey]);
		return $data;
	}

	//根据学号获取安朗信息
	public function getAbms($xuehao)
	{
		$abms = new AbmsModel();
		$data = $abms->getUserInfo($xuehao);
		return $data;
	}
	//修改安朗用户组、到期时间、密码
	public function ModifyUserInfo($userid, $groupid=-1, $limitdate_end = '', $pwd = ''){
		$abms = new AbmsModel();
		$data = $abms->ModifyUserInfo2($userid, $groupid, $limitdate_end, $pwd);
		return $data;
	}

	public function offLineUser($userid){
		$abms = new AbmsModel();
		$data=$abms->offLineUser($userid);
		return $data;
	}
}
