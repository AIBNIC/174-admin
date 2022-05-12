<?php

namespace app\model;

use SoapFault;
use think\Model;

class Abms
{
	protected $wsdl = 'http://10.6.1.2/services/CardCharge?wsdl';

	/**
	 * [getUserInfo 获取安朗用户信息  [基础，还需完善]]
	 * @param string $userid        [账号]
	 * @return  Array
	 */
	public function getUserInfo($userid)
	{
		$client = new \SoapClient($this->wsdl);
		$result = $client->getUserInfo($userid);
		$result = explode("##", $result);
		return $result;
	}

	/**
	 * [ModifyUserInfo2 修改用户组、到期时间、密码  [基础，还需完善]]
	 * @param string $userid        [账号]
	 * @param int $groupid        [用户组id，电信2，移动1]
	 * @param string $limitdate_end    [到期时间]
	 * @param string $pwd    [密码]
	 * @return  Array
	 */
	public function ModifyUserInfo2($userid, $groupid = -1, $limitdate_end = '', $pwd = '')
	{
		try {
			$client = new \SoapClient($this->wsdl);
			//一堆安朗接口参数
			$userstate = '1';
			$teamid = 0;
			$username = '';
			$phone = '';
			$address = '';
			$opendate = '';
			$notes = '';
			// $float = '';
			$remain_fee = 0.0;
			$certNum = '';

			$result = $client->ModifyUserInfo3($userid, $groupid, $teamid, $pwd, $username, $phone, $address, $limitdate_end, $userstate, $opendate, $notes, $remain_fee, $certNum);
			return $result;
		} catch (SoapFault $e) {
			return 0;
		}
	}

	public function offLineUser($userid)
	{
		$client = new \SoapClient($this->wsdl);
		$result = $client->offLineUser($userip = '', $usermac = '', $userid);
		return $result;
	}
}
