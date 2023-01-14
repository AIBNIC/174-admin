<?php

namespace app\model;

use think\Model;
use app\model\Abms  as AbmsModel;

class Students extends Model
{
	protected $table = 'xyw_user';
	protected $connection = 'db_config_165';
	// protected $connection = 'db_config_174';

	public function getStudents($xuehao)
	{
		$data = $this->where("xuehao", $xuehao)->whereor("username", $xuehao)->find();
		if ($data != '') {
			$data = $data->toArray();
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

	public function getUserName($xuehao)
	{
		$data = $this->where('xuehao', $xuehao)->field('username')->select()->toArray();
		return $data;
	}

	//根据宿舍id获取宿舍楼号和房号
	public function getRoom($id)
	{
		$data = $this->table('xyw_room')->where('id', $id)->find()->toArray();
		return $data;
	}

	//根据学号查询宿舍楼号和房号
	public function getRoomName($xuehao)
	{
		$room = $this->where('xuehao', $xuehao)->value('room');
		$data = $this->table('xyw_room')->where('id', $room)->field('lh,fh')->find()->toArray();
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
	public function editTime($xuehao, $old_time, $enkey)
	{
		$data = $this->table('xyw_abms_user')->where('xuehao', $xuehao)->update(['old_time' => $old_time, 'EnKey' => $enkey]);
		return $data;
	}

	//根据学号获取安朗信息
	public function getAbms($xuehao)
	{
		$abms = new AbmsModel();
		$data = $abms->getUserInfo($xuehao);
		return $data;
	}
	//修改安朗后台用户组、到期时间、密码
	public function ModifyUserInfo($userid, $groupid = -1, $limitdate_end = '', $pwd = '')
	{
		$abms = new AbmsModel();
		$data = $abms->ModifyUserInfo2($userid, $groupid, $limitdate_end, $pwd);
		return $data;
	}

	public function offLineUser($userid)
	{
		$abms = new AbmsModel();
		$data = $abms->offLineUser($userid);
		return $data;
	}

	//获取缴费清单数量
	public function getStuOrderListCount($startTime, $endTime, $search, $pageArr = '', $state = "")
	{
		$startTime = date('YmdHis', strtotime($startTime));
        $endTime = date('YmdHis', strtotime($endTime));


        $f['total_fee'] = ['>=', '50'];
        $g['total_fee'] = ['in', '15,65'];
        if ($search != null && $search != "") {

            $a['xuehao'] = ['like', '%' . $search . '%'];
            $b['attach'] = ['like', '%' . $search . '%'];
            $c['out_trade_no'] = ['like', '%' . $search . '%'];
            $d['transaction_id'] = ['like', '%' . $search . '%'];


            if ($state != 0) {
                if ($state == 1) {
                    $data = $this->table('xyw_order')->whereor($a)->whereor($b)->whereor($c)->whereor($d)->where($f)->select();
                }
                if ($state == 2) {
                    $data = $this->table('xyw_order')->whereor($a)->whereor($b)->whereor($c)->whereor($d)->where($g)->select();
                }
            } else {
                $data = $this->table('xyw_order')->whereor($a)->whereor($b)->whereor($c)->whereor($d)->select();
            }
        } else {
            $e['time_end'] = ['between', [$startTime, $endTime]];
            if ($state != 0) {
                if ($state == 1) {
                    $data = $this->table('xyw_order')->where($e)->where($f)->select();
                }
                if ($state == 2) {
                    $data = $this->table('xyw_order')->where($e)->where($g)->select();
                }
            } else {
                $data = $this->table('xyw_order')->where('time_end','between', [$startTime, $endTime])->select();
            }
        }

        return count($data);
	}

	//获取缴费清单
	public function getStuOrderList($startTime, $endTime, $search, $pageArr = '', $state = "")
	{
		$startTime = date('YmdHis', strtotime($startTime));
		$endTime = date('YmdHis', strtotime($endTime));
		if ($pageArr) {
			$page = $pageArr['page'];
			$pageSize = $pageArr['pageSize'];
		} else {
			$page = '';
			$pageSize = '';
		}

		// $db = Db::connect("db_config_xyw");
		$f[] = ['total_fee','>=', '50'];
		$g[] = ['total_fee','in', '15,65'];
		//如果有查询关键字
		if ($search != null && $search != "") {
			$a[] = ['xuehao','like', '%' . $search . '%'];
			$b[] = ['attach','like', '%' . $search . '%'];
			$c[] = ['out_trade_no','like', '%' . $search . '%'];
			$d[] = ['transaction_id','like', '%' . $search . '%'];

			if ($state != 0) {
				if ($state == 1) {
					$data = $this->table('xyw_order')
					->whereor($a)
					->whereor($b)
					->whereor($c)
					->whereor($d)
					->where($f)
					->order('time_end desc')->select()->toArray();
				}
				if ($state == 2) {
					$data = $this->table('xyw_order')
					->whereor($a)
					->whereor($b)
					->whereor($c)
					->whereor($d)
					->where($g)
					->order('time_end desc')->select()->toArray();
				}
			} else {
				$data = $this->table('xyw_order')
				->whereor($a)->whereor($b)->whereor($c)->whereor($d)
				->order('time_end desc')->select()->toArray();
			}
		} else {
			$e[]=['time_end','between',[$startTime, $endTime]];
			
			if ($state != 0) {
				if ($state == 1) {
					if ($pageSize == '') {
						$data = $this->table('xyw_order')
						->where($e)
						->where($f)
						->order('time_end desc')->select()->toArray();
					} else {
						$data = $this->table('xyw_order')
						->where($e)
						->where($f)
						->order('time_end desc')
						->paginate($pageSize)->toArray();
					}
				}
				if ($state == 2) {
					if ($pageSize == '') {
						$data = $this->table('xyw_order')
						->where($e)
						->where($g)
						->order('time_end desc')->select()->toArray();
					} else {
						$data = $this->table('xyw_order')
						->where($e)
						->where($g)
						->order('time_end desc')->paginate($pageSize)->toArray();
					}
				}
			} else {
				$data = $this->table('xyw_order')->where($e)->order('time_end desc')->paginate($pageSize)->toArray();
			}
		}
		return $data;
	}

	//根据学号获取安朗订单信息
	public function getrAllEnTime($xuehao)
    {
        $data = $this->table('xyw_abms_user')->where("xuehao",'in',$xuehao)->select();
        return $data;
    }
    //根据ID获取订单信息
    public function getrAllOrder($id)
    {
        $data = $this->table('xyw_order')->where("id",'in',$id)->order('time asc')->select();
        return $data;
    }
}
