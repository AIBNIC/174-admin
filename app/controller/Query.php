<?php

namespace app\controller;

use app\BaseController;
use app\model\Fault as FaultModel;
use app\model\User as UserModel;
use app\model\Students as StudentsModel;
use think\Request;
use QL\QueryList;
// use think\View;
use think\facade\View;

//用户查询控制器
class Query extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];

	public function index(Request $request)
	{
		return view();
	}

	/*
* [getUserInfo 获取安朗用户信息  [完成]]
* @param string $xuehao       [学号或姓名]
* @return  JSON
*/
	public function userInfo(Request $request)
	{
		$xuehao = $request->get('xuehao');
		if ($xuehao == '') {
			return $this->returnJson(0, '关键词未输入');
		}
		$student = new StudentsModel();
		$stu = $student->getStudents(trim($xuehao));
		// halt(trim($xuehao));
		// // $stu = $stus[0];
		// dump($stu);
		// exit;
		if (empty($stu)) {
			return $this->returnJson(0, '搜索关键词错误');
		}

		//判断是否带有宿舍
		if (empty($stu['room'])) {
			return $this->returnJson(3, '没有宿舍', $stu['xuehao']);
		}

		if (!empty($stu['lh']) and !empty($stu['fh'])) {
			$roomName = $stu['lh'] . '-' . $stu['fh'];
		} else {
			$room = $student->getRoom($stu['room']);
			$roomName = $room['lh'] . '-' . $room['fh'];
		}


		$className = $student->getClass($stu['xuehao']);
		$className = $className['nowYear'] . '-' . $className['className'] . '班';
		$abms = $student->getTime($xuehao);

		//安朗信息
		$rs = $this->abmsUserInfo($stu['xuehao']);
		if($rs==0){
			return $this->returnJson(0,'安朗接口卡了,请再按一下搜索');
		}
		$rt = $this->abmsIp($stu['xuehao']);
		// dump($rs);
		// exit;
		$old_time=$rs['6']==$abms['old_time']?$abms['old_time']:$rs['6'];


		if (session('role_id') != 0) {
			$stu['userid'] = substr($stu['userid'], -8);
		}
		$data = [
			'name' => $stu['username'],
			'xh' => $stu['xuehao'],
			'userid' => $stu['userid'],
			'class' => $className,
			'roomid' => $stu['room'],
			'room' => $roomName,
			'lianlu' => $rs[1] == 1 ? '移动' : '电信',
			'start_time' => $abms['start_time'],
			'old_time' => $old_time,
			'ip' => $rt['ip'],
			'abms_time' => $rt['start_time'],
			'abmsKey' => $rs[14],
			'EnKey' => $abms['EnKey']
		];
		session('xh', $stu['xuehao']);
		// dump($data);
		return $this->returnJson(1, '获取成功', $data);
	}

	public function editRoom(Request $request)
	{
		$data = $request->param();
		$student = new StudentsModel();
		$lhAll = $student->getAllRoom();
		$xh = session('xh');
		if (!empty($data['fhid'])) {
			// return $data['fh'];
			$updata = [
				'room' => $data['fhid'],
				'lh' => $data['lh'],
				'fh' => $student->getRoom($data['fhid'])
			];
			// $fh=$student->getRoom($data['fhid']);
			$data = $student->editRoom($xh, $updata['room']);
			if ($data != 0) {
				return $this->returnJson(1, '修改成功');
			} else {
				return $this->returnJson(0, '修改失败');
			}
		}
		if (empty($data['lh'])) {
			// dump($lhAll);
			$stu = $student->getStudents($xh);
			if ($stu != null) {
				view::assign('name', $stu['username']);
				view::assign('lh', $lhAll);
				return view();
			}
		} else {
			$fh = $student->getAllfh($data['lh']);
			$opt      = '<option></option>';
			foreach ($fh as $key => $val) {
				// dump($val);
				$opt .= "<option value='{$val['id']}'>{$val['fh']}</option>";
			}
			return JSON($opt);
		}
	}

	//通过学号查询安朗信息
	protected function abmsUserInfo($xuehao)
	{
		$student = new StudentsModel();
		$rs = $student->getAbms($xuehao);
		return $rs;
	}
	protected function abmsIp($xuehao)
	{
		$rules = [    //采集规则
			'user' => ['', 'text']
		];
		$range = '.odd>td';
		$rt  = QueryList::get("http://net1.gdngs.cn/admin/dxhtml/searchUserOnline", [
			'userid' => $xuehao
		])->rules($rules)->range($range)->queryData();
		if ($rt == null) {
			$data = ['xuehao' => $xuehao, 'ip' => '不在线', 'start_time' => '不在线'];
			return $data;
		}
		$data = ['xuehao' => $rt[1]['user'], 'ip' => $rt[2]['user'], 'start_time' => $rt[7]['user']];
		return $data;
		// dump($rt);
	}

	//修改安朗用户链路
	public function ModifyGroupid(Request $request)
	{
		$reData = $request->post();
		$userid = $reData['xh'];
		$groupid = $reData['groupid'];

		if (empty($userid)) {
			return $this->returnJson(0, '学号传参错误');
		}
		if ($groupid == 1 or $groupid == 2) {

			$student = new StudentsModel();
			$data = $student->ModifyUserInfo($userid, $groupid);
			if ($data == 1) {
				return $this->returnJson(1, '修改成功');
			} else {
				return $this->returnJson(0, '修改失败');
			}
		} else {
			return $this->returnJson(0, '链路id传参错误');
		}
	}

	//修改到期时间
	public function ModifyLimitdate(Request $request)
	{
		$reData = $request->post();
		// $userid = $reData['xh'];
		// $limitdate_end = $reData['old_time'];
		if (empty($reData)) {
			return view();
		}
		if (!isset($reData['xh']) or !isset($reData['old_time']) or !isset($reData['enkey'])) {
			return $this->returnJson(0, '传参错误');

		}
		$userid = $reData['xh'];
		$limitdate_end = $reData['old_time']." 23:59:59";
		$enkey = $reData['enkey'];
		$groupid=-1;   //用户组，-1为不做修改

		// echo $userid, $limitdate_end, $enkey;

		$student=new StudentsModel();
		$data=$student->ModifyUserInfo($userid,$groupid,$limitdate_end);
		if($data==0){
			return $this->returnJson(0, '安朗修改错误');
		}
		$stu=$student->editTime($userid,$limitdate_end,$enkey);
		if($stu==0){
			return $this->returnJson(0, '数据表更新错误');
		}
		return $this->returnJson(1, '修改成功');
	}
	//下线安朗用户
	public function offLineUser($userid){
		if(empty($userid)){
			return $this->returnJson(0,'传参错误');
		}
		$student=new StudentsModel();
		$data=$student->offLineUser($userid);
		$msg=$data==1?'下线成功':'下线失败';
		return $this->returnJson($data,$msg);
	}

	//添加故障
	public function addFault(Request $request){
		$reData=$request->post();
		if(empty($reData)){
			return view();
		}
		$userid=$reData['xh'];
		$faultname=$reData['faultType'];
		$faultcontent=$reData['faultText'];

		if(!isset($userid) or !isset($faultname)){
			return $this->returnJson(0,'传参出错，学号与类型为必填');
		}

		$stu=new StudentsModel();
		$room=$stu->getRoomName($userid);
		
		$fault=new FaultModel();
		$faultArr=[
			'faultname'=>$faultname,
			'create_time'=>date('Y-m-d G:i:s'),
			'faultcontent'=>$faultcontent,
			'userid'=>$userid,
			'lh'=>$room['lh'],
			'fh'=>$room['fh']
		];

		$data=$fault->addFault($faultArr);
		$msg=$data==1?'添加故障成功':'添加故障失败';
		return $this->returnJson($data,$msg);
	}

	//删除用户
	public function CardDelUser($userid){

	}

}
