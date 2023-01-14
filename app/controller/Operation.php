<?php

namespace app\controller;
// header( "content-type:text/html;charset=utf-8" );

use app\BaseController;
use QL\QueryList;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use LDAP\Result;

class operation extends BaseController
{
	protected $cookie;

	public function __construct()
	{
		$jar = new CookieJar();

		$ql = QueryList::get('http://10.6.1.3/master/Logon.do', [], [
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'cookies' => $jar
		]);
		$cookieArr2 = file_get_contents('./xx_cookie.txt');
		$cookieArr2 = json_decode($cookieArr2, true);

		$ql2 = QueryList::post('http://10.6.1.3/master/logonAction.do', [
			'manager_id' => '20205539142',
			'passwd' => '123456',
			'x' => '79',
			'y' => '11'
		], [
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Referer' => 'http://10.6.1.3/master/Logon.do',
			'cookies' => $jar
		]);
		$this->cookie = $jar;
	}

	public function userQueryView(){
		return view();
	}
	public function userQuery($userid)
	{
		if ($userid == '') {
			return $this->returnJson(0, '学号为空');
		}
		$ql = QueryList::post('http://10.6.1.3/master/userQueryAction.do', [
			'org.apache.struts.taglib.html.TOKEN' => '60efb21aef420e8ea8fb95da78a4f542',
			'act' => 'query',
			'like' => 'true',
			'user_id' => $userid
		], 
		[
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Referer' => 'http://10.6.1.3/master/userQueryAction.do',
			'cookies' => $this->cookie
		]);
		// $html = $ql->removeHead()->getHtml();
		$html=$ql->encoding('UTF-8','gbk')->find('tr:last')->remove();
		$html=$ql->find('.active')->html();
	
		return $html;
	}

	public function userTicketView(){
		return view();
	}
	public function userTicket($userid)
	{
		if ($userid == '') {
			return $this->returnJson(0, '学号为空');
		}
		$ql = QueryList::post('http://10.6.1.3/master/userQueryAction.do', [
			'act' => 'ticket',
			'user_id' => $userid
		], [
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Referer' => 'http://10.6.1.3/master/userQueryAction.do',
			'cookies' => $this->cookie
		]);
		// $html = $ql->removeHead()->getHtml();
		$html=$ql->find('.active')->html();
		return $html;
	}

	public function mainStatic(){
		$ql = QueryList::get('http://10.6.1.3/master/main_static.do', [], [
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Referer' => 'http://10.6.1.3/master/userQueryAction.do',
			'cookies' => $this->cookie
		]);
	}

	//查询在线用户 ip userid
    public function searchUserOnline($userid){
        $data=array(
            "user_id" => $userid,
        );

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1/Operation/searchUserOnline"); 
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); 
        curl_setopt($ch, CURLOPT_POSTFIELDS , http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch); 

        
        if(!$output){

            return ["status"=>false,"msg"=>"Server Error"];
        }
      	
      	return $output;
        
    }

    public function onlineAction($userid)
	{
		if ($userid == '') {
			return $this->returnJson(0, '学号为空');
		}
		$jar=$this->cookie;
		$client =new Client();
		$response= $client->request('POST', 'http://10.6.1.3/master/onlineAction.do', [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
				'Referer' => 'http://10.6.1.3/master/userQueryAction.do',
				'Content-Type'=>'application/x-www-form-urlencoded;charset=UTF-8',
	    		'cookies' => $jar,
	    		'Accept-Language'=> 'zh-CN,zh;q=0.9',
	    		'decode_content' => 'GBK',
	    		'form_params' => [
	    			'userid' => $userid,
	    			'act' => 'query',
	    		]
			]);
		$type = $response->getHeader('content-type');
		$text=$response->getBody();
		// $body=mb_convert_encoding($text, 'GBK');
		// halt($body);
		// $code = $response->getBody();
		return $text;
	}	
}
