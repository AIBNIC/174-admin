<?php

namespace app\controller;

use app\BaseController;
use app\model\Fault as FaultModel;
use think\facade\Db;

//故障控制器
class Fault extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];

	public function index()
	{
		$fault = new FaultModel();
		$lh = $fault->getlh();
		array_unshift($lh, '');
		return view('', ['lh' => $lh]);
	}

	public function faultList($lh = '')
	{
		$db = Db::connect('db_config_165');
		if ($lh != '') {
			$faultList = $db->table('vfault')->where('state', 0)->where('lh', $lh)->field('id,create_time,faultcontent,userid,lh,fh,username')->order("lh asc,create_time desc")->select();
		} else {
			$faultList = $db->table('vfault')->where('state', 0)->field('id,create_time,faultcontent,userid,lh,fh,username')->order("lh asc,create_time desc")->order("lh asc,create_time desc")->select();
		}
		if ($faultList->isEmpty()) {
			$data = ['code' => 1, 'msg' => '故障为空', 'count' => 0, 'data' => $faultList];
		} else {

			$data = ['code' => 0, 'msg' => '获取成功', 'count' => count($faultList), 'data' => $faultList];
		}
		return JSON($data);
	}

	/* [delFault 删除故障  [权限判断未作]]
	* @param array $id       [故障id]
	* @return  JSON
	*/
	public function delFault($id,$lh)
	{
		$fault = new FaultModel();
		$result = [];
		foreach ($id as $vo) {
			$result[] = $fault->delFault($vo);
		}
		return $this->returnJson(1, '删除成功');
	}
}
