<?php

namespace app\model;

use think\Model;

class Fault extends Model
{
	// protected $connection = 'db_config_174';
	protected $connection = 'db_config_165';

	//数据统计
	public function tongji()
	{
		$data['Untreated'] = $this->where('state', 0)->count('id');   //未处理故障
		$data['total'] = $this->whereMonth('create_time')->count('id');
		return $data;
	}

	/**
     * 首页报表统计
     * @param float $iszc 校区，默认增城
     */
	public function chart($iszc = true)
	{

		//获取过去7天时间
		$time=$this->getData();
		// return $time;

		//增城校区最近七天故障统计
		if ($iszc == true) {
			for($a=0;$a<7;$a++){
				$data[$a]=$this->where('lh','like','北区%')->whereDay('create_time', $time[$a])->count('id');
			}
			return $data;
		}
		if ($iszc == false) {
			for($a=0;$a<7;$a++){
				$data[$a]=$this->where('lh','like','%楼')->whereDay('create_time', $time[$a])->count('id');
			}
			return $data;
		}
	}

	/**
     * 查询过去时间
     * @param int $query 查询时间，默认7
	 *  @param string $mwdy  查询单位，默认日
	 * *  @param string $format  输出格式，默认Y-m-d
     */
	public function getData($query=7,$mwdy='days',$format='Y-m-d'){
		$time = [];
		for ($a = 0; $a < $query; $a++) {
			if ($a == 0) {
				$time[0] = date($format);
			}
			$time[] = date($format, strtotime('-' . $a . 'days'));
		}

		//将时间反过来
		$newTime=[];
		for($i=count($time)-1;$i>=0;$i--){     
			$newTime[]=$time[$i];
		}
		return $newTime;
	}

	//获取全部楼号
	public function getlh(){
		$data=$this->table('xyw_room')->group("lh")->column('lh');
		return $data;
	}

	
	public function addFault($faultArr){
		$data=$this->insert($faultArr);
		return $data;
	}

	public function delFault($id){
		$data=$this->where('id',$id)->update(['state'=>'1','finish_time'=>date('Y-m-d G:i:s'),'finish_man'=>session('username')]);
		return $data;
	}
}
