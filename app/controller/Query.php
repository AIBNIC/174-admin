<?php

namespace app\controller;

use app\BaseController;
use app\model\User as UserModel;
use app\model\Students as StudentsModel;
use think\Request;

//用户查询控制器
class Query extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];

	public function index(Request $request, $xuehao = '')
	{
		if ($xuehao != '') {
			$xuehao = $xuehao;
		} elseif (!is_null($request->get('xuehao')) && !empty($request->get('xuehao'))) {
			$xuehao = $request->get('xuehao');
		} else {
			return view();
		}

		$student = new StudentsModel();
		$stus = $student->getStudents(trim($xuehao));
		$stu = $stus[0];
		if (!$stu) {
			return '搜索关键词错误';
		}

		//判断是否带有宿舍
		if (is_null($stu['room']) || empty($stu['room'])) {
			return redirect('Query/index');    //暂时重定向到这里！！！
			// return 'xxxxx';
		}
		$roomName = $stu['lh'] . '-' . $stu['fh'];
		$className = $student->getClass($stu['xuehao']);
		$className = $className['nowYear'] . '-' . $className['className'] . '班';
		// $stu['sex']=$stu['sex']==1?'电信':'移动';
		$abms=$student->getTime($xuehao);

		//安朗信息
		// $rs=$student->getAbms($xuehao);
		// dump($rs);

		if (session('role_id') != 0) {
			$stu['userid'] = substr($stu['userid'], -8);
		}
		$data = [
			'name' => $stu['username'],
			'xh' => $stu['xuehao'],
			'userid' => $stu['userid'],
			'class'=>$className,
			'room' => $roomName,
			'lianlu'=>'空',
			'start_time'=>$abms['start_time'],
			'old_time'=>$abms['old_time'],
			'ip'=>'空',
			'abmsKey'=>'空',
			'EnKey'=>$abms['EnKey']
		];
		return view('',$data);
	}
}
