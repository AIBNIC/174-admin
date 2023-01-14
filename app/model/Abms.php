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
		ini_set('default_socket_timeout', 5); //定义响应超时
		try {
			// $options = array(
			// 	'cache_wsdl' => 0,
			// 	'connection_timeout' => 5, //定义连接超时为5秒
			// );
			$client = new \SoapClient($this->wsdl, array('connection_timeout' => 5));
			$result = $client->getUserInfo($userid);
			$result = explode("##", $result);
		} catch (\SoapFault $e) {
			// return $e->getMessage();
			return 0;
		}
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
		} catch (\SoapFault $e) {
			return 0;
		}
	}

	/**
	 * [offLineUser 下线安朗账号]
	 * @param string $userid        [账号]
	 * @return  1，0
	 */
	public function offLineUser($userid)
	{
		try {
			$client = new \SoapClient($this->wsdl);
			$result = $client->offLineUser($userip = '', $usermac = '', $userid);
			return $result;
		} catch (\SoapFault $e) {
			return 0;
		}
	}

	/**
	 * [CardDelUser 销户]
	 * @param string $userid        [账号]
	 * @return  1，0
	 */
	public function CardDelUser($userid){
		try {
			$client = new \SoapClient($this->wsdl);
			$result = $client->CardDelUser($userid);
			return $result;
		} catch (\SoapFault $e) {
			return 0;
		}
	}
}
