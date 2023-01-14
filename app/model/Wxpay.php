<?php

namespace app\model;

use think\Model;

class Wxpay extends Model
{
	protected $table = 'xyw_order';
	protected $connection = 'db_config_165';

	/**
     * 微信订单数据列表
     * @param string $start_date 搜索开始时间
     * @param string $end_date 搜索结束时间
     * @param string $search 搜索关键字
     * @param int $page 搜索关键字
     * @param int $pageSize 搜索关键字
     * @throws Exception
     */
	public function getDownOrderList($start_date = '', $end_date = '', $search = '', $page = '', $pageSize = '')
	{

		$today=date('Y-m-d 00:00:00', strtotime('-1 day', time()));
		if ($search != null && $search != "" ) {
			//2019.10.28 locate("wlzx",order_sn_sh)!=0
			$result = $this
			->where('  locate("wlzx",out_trade_no)!=0 ')
			->where(" xuehao like '%" . $search . "%' or out_trade_no like '%" . $search . "%' ")
			->field('id,xuehao,attach,cash_fee,out_trade_no,time_end,return_code,transaction_id,time')
			->select();
		}
		if($search != null && $search != "" && $start_date!=$today){
			$result = $this
			->where('  locate("wlzx",out_trade_no)!=0 ')
			->where(" xuehao like '%" . $search . "%' or out_trade_no like '%" . $search . "%' ")
			->whereTime('time','between',[$start_date, $end_date])
			->field('id,xuehao,attach,cash_fee,out_trade_no,time_end,return_code,transaction_id,time')
			->select();
		}
		else {
			$result = $this->where('  locate("wlzx",out_trade_no)!=0 ')
			->where(" time >= '" . $start_date . "' and time <= '" . $end_date . "'")
			->limit(($page - 1) * $pageSize, $pageSize)
			->field('id,xuehao,attach,cash_fee,out_trade_no,time_end,return_code,transaction_id,time')
			->select();
			// $count = count($result);
			// $db ->table('down_order')->paginate(1, $count);
		}
		return $result;
	}

	/**
     * 微信订单数据总数
     * @param string $start_date 搜索开始时间
     * @param string $end_date 搜索结束时间
     * @param string $search 搜索关键字
     * @throws Exception
     */
	public function getDownOrderListCount($start_date = '', $end_date = '', $search = '')
	{

		if ($search != null && $search != "") {
			$result = $this->where('  locate("wlzx",out_trade_no)!=0 ')
			->where(" out_trade_no like '%" . $search . "%' or out_trade_no like '%" . $search . "%'  '%" . $search . "%' ")
			->whereTime('time','between',[$start_date, $end_date])
			->field('id')
			->select();
			$result = count($result);
		} else {
			$result = $this->where('  locate("wlzx",out_trade_no)!=0 ')
			->where(" time >= '" . $start_date . "' and time <= '" . $end_date . "'")
			->field('id')
			->select();
			$result = count($result);
		}
		return $result;
	}


	public function getOrderSummary($pageStart,$pageEnd)
    {

        // $result=$this ->table('down_order_summary')->field('order_num,turnover,date')->order('date','desc')->page($pageStart,$pageEnd)->select()->toArray();
        // return $result;
        $result=$this->fieldRaw('DATE( time ) as date,COUNT( id ) as order_num ,SUM(cash_fee) as turnover')->group('date')->page($pageStart,$pageEnd)->order('date', 'desc')->select()->toArray();
        return $result;

    }
    public function getOrderSummaryCount()
    {

        $result=$this->fieldRaw('DATE( time ) as date,COUNT( id ) as order_num')->group('date')->count();
        return $result;

    }
}
