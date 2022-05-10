<?php

namespace app\model;

use think\Model;

class Abms
{
	protected $wsdl = 'http://10.6.1.2/services/CardCharge?wsdl';

	public function getUserInfo($userid)
	{
		$client = new \SoapClient($this->wsdl);
		$result = $client->getUserInfo($userid);
		$result=explode("##",$result);
		return $result;
		
	}
}
