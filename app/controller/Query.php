<?php

namespace app\controller;

use app\BaseController;
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

	public function userInfo(Request $request){
		$xuehao = $request->get('xuehao');
		if(is_null($xuehao)){
			return $this->returnJson(0,'关键词未输入');
		}
		$student = new StudentsModel();
		$stu = $student->getStudents(trim($xuehao));
		// halt(trim($xuehao));
		// $stu = $stus[0];
		
		if (!$stu) {
			return '搜索关键词错误';
		}

		//判断是否带有宿舍
		if (empty($stu['room'])) {
			return redirect('editRoom');    
		}

		if(!empty($stu['lh']) and !empty($stu['fh'])){
			$roomName = $stu['lh'] . '-' . $stu['fh'];
		}
		else{
			$room=$student->getRoom($stu['room']);
			$roomName = $room['lh'] . '-' . $room['fh'];
		}	
		
		
		$className = $student->getClass($stu['xuehao']);
		$className = $className['nowYear'] . '-' . $className['className'] . '班';
		// $stu['sex']=$stu['sex']==1?'电信':'移动';
		$abms=$student->getTime($xuehao);

		//安朗信息
		$rs=$this->abmsUserInfo($stu['xuehao']);
		// halt($rs);

		if (session('role_id') != 0) {
			$stu['userid'] = substr($stu['userid'], -8);
		}
		$data = [
			'name' => $stu['username'],
			'xh' => $stu['xuehao'],
			'userid' => $stu['userid'],
			'class'=>$className,
			'roomid'=>$stu['room'],
			'room' => $roomName,
			'lianlu'=>$rs[1]==1?'移动':'电信',
			'start_time'=>$abms['start_time'],
			'old_time'=>$abms['old_time'],
			'ip'=>'空',
			'abmsKey'=>$rs[14],
			'EnKey'=>$abms['EnKey']
		];
		session('xh',$stu['xuehao']);
		return $this->returnJson(1,'获取成功',$data);
	}

	public function editRoom(Request $request){
		$data = $request->param();
		$student=new StudentsModel();
		$lhAll=$student->getAllRoom();
		$xh=session('xh');
		if(!empty($data['fhid'])){
			// return $data['fh'];
			$updata=[
				'room'=>$data['fhid'],
				'lh'=>$data['lh'],
				'fh'=>$student->getRoom($data['fhid'])
			];
			// $fh=$student->getRoom($data['fhid']);
			$data=$student->editRoom($xh,$updata['room']);
			if($data!=0){
				return $this->returnJson(1,'修改成功');
			}
			else{
				return $this->returnJson(0,'修改失败');
			}
		}
		if(empty($data['lh'])){
			// dump($lhAll);
			$stu=$student->getStudents($xh);
			view::assign('name',$stu['username']);
			view::assign('lh',$lhAll);
			return view();
		}
		
		else{
			$fh=$student->getAllfh($data['lh']);
			$opt      = '<option></option>';
            foreach($fh as $key=>$val){
                // dump($val);
                $opt .= "<option value='{$val['id']}'>{$val['fh']}</option>";
             }
			 return JSON($opt);
			
		}
	}

	//通过学号查询安朗信息
	public function abmsUserInfo($xuehao){
		$student=new StudentsModel();
		$rs=$student->getAbms($xuehao);
		return $rs;
	}
	public function abmsIp($xuehao){
		$rules = [    //采集规则
            'user'=>['.center>td','value'] 
        ];
		$range='.odd';
		$data = QueryList::get("http://net1.gdngs.cn/admin/dxhtml/searchUserOnline",[
            'userid' => $xuehao
        ])->rules($rules)->range($range)->queryData();
		return dump($data);
	}

}
