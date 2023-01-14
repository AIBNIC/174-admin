<?php

namespace app\controller;

use app\BaseController;
use app\model\Fault as FaultModel;
use app\model\Role as RoleModel;
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

	/* [delFault 删除故障  [完成]]
	* @param array $id       [故障id]
	* @return  JSON
	*/
	public function delFault($id, $lh)
	{
		$fault = new FaultModel();
		$role = new roleModel();
		$auth = $role->getRoleAuth(session('role_id'));
		$lhId = [];
		foreach ($lh as $vo) {
			$lhId[] = $role->lhId($vo);
		}
		if (session('role_id') != 1) {
			foreach ($lhId as $vo) {
				if (!in_array($vo, $auth)) {
					return $this->returnJson(0, '你没有该栋的删除权限');
				}
			}
		}
		$result = [];
		foreach ($id as $vo) {
			$result[] = $fault->delFault($vo);
		}
		return $this->returnJson(1, '删除成功');
	}

	public function bbtj($option = 0)
	{
		if ($option == 0) {
			return view();
		}
		$fault = new FaultModel();
		if ($option == 1) {
			$thisMonthBb = $fault->getBbTimeFault();
			$lastMonthBb = $fault->getBbTimeFault('lastMonthBb');
			$thisYear = $fault->getBbTimeFault('thisYear');
			$lastYear = $fault->getBbTimeFault('lastYear');
			$data = ['thisMonthBb' => $thisMonthBb, 'lastMonthBb' => $lastMonthBb, 'thisYear' => $thisYear, 'lastYear' => $lastYear];
			$data['all'] = [array_sum($thisMonthBb), array_sum($lastMonthBb), array_sum($thisYear), array_sum($lastYear)];
			return $this->returnJson(1, '获取成功', $data);
		}
	}

	public function zctj($option = 0)
	{
		if ($option == 0) {
			return view();
		}
		$fault = new FaultModel();
		if ($option == 1) {
			$thisMonthBb = $fault->getZcTimeFault();
			// halt($thisMonthBb);
			$lastMonthBb = $fault->getZcTimeFault('lastMonth');
			$thisYear = $fault->getZcTimeFault('thisYear');
			$lastYear = $fault->getZcTimeFault('lastYear');
			$data = ['thisMonthBb' => $thisMonthBb, 'lastMonthBb' => $lastMonthBb, 'thisYear' => $thisYear, 'lastYear' => $lastYear];
			$data['all'] = [array_sum($thisMonthBb), array_sum($lastMonthBb), array_sum($thisYear), array_sum($lastYear)];
			return $this->returnJson(1, '获取成功', $data);
		}
	}
}
