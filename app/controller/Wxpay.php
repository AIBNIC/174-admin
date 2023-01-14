<?php

namespace app\controller;

use app\BaseController;
use app\model\Wxpay as WxpayMode;
use app\model\Students as studentsModel;
use app\model\Abms;
use think\Request;
use think\facade\Db;
use think\facade\Request as RequestStatic;

class Wxpay extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];
	
	public function downTable(Request $request, $option = 0)
	{
		if ($option == 0) {
			return view();
		}
		if ($option == 1) {
			$data = $request->param();
			// var_dump($data);

			$start_date = date('Y-m-d 00:00:00', strtotime('-1 day', time()));
			$end_date = date('Y-m-d 23:59:59', strtotime('-1 day', time()));

			$search = "";

			//这里接收需查询的日期范围
			if (isset($data['day']) && $data['day'] != null && $data['day'] != "") {
				// print_r($data['day']);
				$time = explode('~', $data['day']);
				$start_date = trim($time[0]);
				$end_date = trim($time[1]);
			}
			//这里接收需查询的商品订单，微信订单，学号，商品名等等
			if (isset($data['search']) && $data['search'] != null && $data['search'] != "") {
				if ($data['search'] != null && $data['search'] != "")
					$search = $data['search'];
			}

			$wxpay = new WxpayMode();
			//调用模型中的查询方法

			$page = isset($data['page']) ? $data['page'] : '1';
			$pageSize = isset($data['pageSize']) ? $data['pageSize'] : '20';

			$allCount = $wxpay->getDownOrderListCount($start_date, $end_date, $search);
			// dump($allCount);
			// exit;

			if ($page <= 1) {
				$pageNum = 0; //第一页
			} else if ($page >= (ceil($allCount / $pageSize) - 1)) {
				$pageNum = ceil($allCount / $pageSize) - 1; //最后一页
			} else {
				$pageNum = $page - 1; //第一页
			}
			//最大的页码为 2277/100=23
			$pageArr = [
				'pageSize' => $pageSize,
				'page' => $pageNum,
			];
			$result = $wxpay->getDownOrderList($start_date, $end_date, $search, $page, $pageSize);
			// foreach ($result as $vo) {
			// 	$vo['xuehao'] = substr($vo['goods_name'], 0, 11);
			// }
			return $this->LayuiTbaleJson(0, '获取成功', $allCount, $result);
			// dump($result);
		}
	}


	public function table(Request $request, $option = 0)
	{
		if ($option == 0) {
			return view();
		}
		if ($option == 1) {
			$data = $request->param();
			//1开户 2 网费
			if (isset($data['r3'])) {
				$state = $data['r3'];
			} else {
				$state = 0;
			}
			Session('r3', $state);

			$startTime = date('Y-m-d 00:00:00', time());
			$endTime = date('Y-m-d 23:59:59', time());
			$search = "";
			$reTime = $startTime . '~' . $endTime;

			if (isset($data['orderDate']) && $data['orderDate'] != null && $data['orderDate'] != "") {
				$time = explode('~', $data['orderDate']);
				$startTime = trim($time[0]);
				$endTime = trim($time[1]);
				$reTime = $data['orderDate'];
			}
			if (isset($data['search']) && $data['search'] != null && $data['search'] != "") {
				if ($data['search'] != null && $data['search'] != "")
					$search = $data['search'];
			}

			$student = new studentsModel();

			$page = isset($data['page']) ? $data['page'] : '1';
			$pageSize = isset($data['pageSize']) ? $data['pageSize'] : '100';

			$allCount = $student->getStuOrderListCount($startTime, $endTime, $search, $state);

			if ($page <= 1) {
				$pageNum = 0; //第一页
			} else if ($page >= (ceil($allCount / $pageSize) - 1)) {
				$pageNum = ceil($allCount / $pageSize) - 1; //最后一页
			} else {
				$pageNum = $page - 1; //第一页
			}
			//最大的页码为 2277/100=23
			$pageArr = [
				'pageSize' => $pageSize,
				'page' => $pageNum,
			];
			$list = $student->getStuOrderList($startTime, $endTime, $search, $pageArr, $state);
			// dump($list);
			// exit;

			//得出网费数量
			$wf = $student->getStuOrderList($startTime, $endTime, $search, '', 2);
			$wf = count($wf);
			//开户数量
			$kw = $student->getStuOrderList($startTime, $endTime, $search, '', 1);
			$kw = count($kw);
			if (empty($list['data'])) {
				$data = ['code' => 0, 'msg' => '获取成功', 'count' => $allCount, 'data' => $list, 'kw' => $kw, 'wf' => $wf, 'time' => $reTime];
			} else {
				$data = ['code' => 0, 'msg' => '获取成功', 'count' => $allCount, 'data' => $list['data'], 'kw' => $kw, 'wf' => $wf, 'time' => $reTime];
			}

			// return $this->LayuiTbaleJson(0,'获取成功',$allCount,$list['data']);
			return JSON($data);
		}
	}

	public function search_order_endtime(Request $request)
	{
		if (RequestStatic::instance()->isPost()) {
			if (RequestStatic::instance()->param()) {

				$data = $request->param();
				$data = $data['data'];

				$arrId = array_column($data, 'id');
				$xuehao = array_column($data, 'xuehao');

				//批量查询这批学号的到期时间
				$student = new StudentsModel();
				$EnTimelist = $student->getrAllEnTime($xuehao);

				//批量查询这批订单ID的订单信息
				$Orderlist = $student->getrAllOrder($arrId);
				$OrderCount = count($Orderlist);
				$list = [];
				foreach ($EnTimelist as $k => $v) {
					$list[$k] = [
						'id' => $k + 1,
						'xuehao' => $v['xuehao'],
						'username' => $v['username'],
						'old_time' => $v['old_time'],
						'Enkey' => $v['EnKey']
					];
					foreach ($Orderlist as $key => $vo) {
						if ($list[$k]['xuehao'] == $vo['xuehao']) {
							$list[$k]['attach'] = $vo['attach'];
							$list[$k]['total_fee'] = $vo['total_fee'];
							$list[$k]['out_trade_no'] = $vo['out_trade_no'];
							$list[$k]['time'] = $vo['time'];
							$list[$k]['alike'] = $list[$k]['Enkey'] == $vo['out_trade_no'] ? '是' : '否';
						}
					}
				}
				$total_fee = array_column($list, 'total_fee');
				$money = array_sum($total_fee);
				$alike = array_column($list, 'alike');
				$yes = 0;
				$no = 0;
				foreach ($alike as $vo) {
					if ($vo == '是') {
						$yes += 1;
					} else {
						$no += 1;
					}
				}
				$number = count($list);
				$data = [
					'code' => 0,
					'msg' => '获取成功',
					'count' => $OrderCount,
					'data' => $list,
					'number' => $number,
					'money' => $money,
					'yes' => $yes,
					'no' => $no
				];
				return JSON($data);
			}
		}

		return "0";
	}

	//微信订单查询
	public function wxquery(Request $request, $option = 0)
	{
		if ($option == 0) {
			return view();
		}
		if ($option == 1) {
			if (RequestStatic::instance()->isGet()) {
				//1、接收订单号
				// $data = $request->param();
				// $type = 'out_trade_no';

				// if (isset($data['out_trade_no']) && !empty($data['out_trade_no'])) {
				// 	$out_trade_no = $data['out_trade_no'];
				// 	$type = 'out_trade_no';
				// } else {
				// 	$out_trade_no = $data['transaction_id'];
				// 	$type = 'transaction_id';
				// }
				// $order = \wxpay\Query::exec($out_trade_no, $type);

				// echo "<pre>";
				// halt($order);
			}
		}
	}

	/*
	*收款记录
	*@ $option 选项 1为近14天统计 2为数据
	*/
	public function orderSummary(Request $request, $option = 0)
	{
		if ($option == 0) {
			return view();
		}
		$wxpay = new WxpayMode();
		if ($option == 1) {
			$result_list14 = $wxpay->getOrderSummary(0, 14);
			// return $result_list14;
			if ($result_list14 != '' and $result_list14 != null) {
				$date14 = array_column($result_list14, 'date');
				$date14 = str_replace(' 00:00:00', '', $date14);
				$turnover14 = array_column($result_list14, 'turnover');
				return $this->returnJson(1, '获取成功', ['date' => $date14, 'turnover' => $turnover14]);
			} else {
				return $this->returnJson(1, '查询失败');
			}
		}
		if ($option == 2) {
			$data = $request->param();
			$page = $data['page'];
			$limit = $data['limit'];
			$result_list = $wxpay->getOrderSummary($page, $limit);
			foreach ($result_list as $key => $vo) {
				$result_list[$key]['id'] = ($page - 1) * $limit + ($key + 1);
			}
			$count = $wxpay->getOrderSummaryCount();
			// halt($count);
			return $this->LayuiTbaleJson(0, '获取成功', $count, $result_list);
		}
	}

	//查询订单
	public function searchorder(Request $request,$example=0)
	{
		$data = $request->param();
		if (isset($data['userid'])) {    //判断是否查询
			$userid = $data['userid'];
			
		} else {
			return view();
		}
		$Abms = new Abms();
		if ($userid !== "") {
			$db = Db::connect("db_config_165");
			if ($example == 1) {       //简约订单
				$attach = $db->table('xyw_attach')->where('xuehao', $userid)->whereor('out_trade_no', $userid)->order('time_end desc')->select();
				return $this->LayuiTbaleJson(0, '获取成功', count($attach), $attach);
			}
			if ($example == 2) {      //详细订单
				$order = $db->table('xyw_order')->where('xuehao', $userid)->whereor('out_trade_no', $userid)->order('time_end desc')->select();
				return $this->LayuiTbaleJson(0, '获取成功', count($order), $order);
			}
			if ($example == 3) {      //操作记录
				$logorder = $db->table('xyw_log_order')->where('xuehao', $userid)->whereor('out_trade_no', $userid)->order('nowTime desc')->select();
				return $this->LayuiTbaleJson(0, '获取成功', count($logorder), $logorder);
			} 
			// else {
			// 	$local = $db->table('xyw_abms_user')->where('xuehao', $userid)->find();
			// 	// halt($local);
			// 	if (strlen($userid) == 11) {
			// 		$abms_res = $Abms->getUserInfo($userid);
			// 		// halt($abms_res);
			// 		if($abms_res==0){
			// 			return $this->returnJson(0, $abms_res);
			// 		}
			// 		// if (!$abms_res) {
						
			// 		// }
			// 		$user_old_time = $abms_res[6];
			// 	}
			// 	return $this->returnJson(1, '获取成功', ['local' => $local, 'user_old_time' => $user_old_time]);
			// }
		}
	}

	public function orderQuick(Request $request){
		$data=$request->param();
		if(empty($data)){
			return view();
		}
		$data['state'] = 3;
            $res = Db::connect('db_config_174')->table('xyw_attach')->strict(false)->insert($data);
            if($res){
                return $this->returnJson(1,'快捷补单成功');
            }
            return $this->returnJson(0,'快捷补单失败');
	}
}
