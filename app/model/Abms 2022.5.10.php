<?php
namespace app\model;
use think\Model;
use app\model\Students as StudentModel;
use app\model\Net as NetModel;
class Abms extends Model
{
    private $wsdl='http://10.6.1.2/services/CardCharge?wsdl';


   /**
     * [offLineUser 销户]
     * @param  [type] $userid  [description]
     * @return [type]          array(3) { ["code"]=> string(6) "100004" ["msg"]=> string(24) "强制用户下线成功" ["data"]=> int(1) }
     */
    public function CardDelUser($userid)
    {
        try {
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));
            $result = $client->CardDelUser($userid);
            if($result=='1')
              return json_encode(['code'=>'100004','msg'=>'销户成功','data'=>$result]); 

            return json_encode(['code'=>'400004','msg'=>'销户失败','data'=>$result]); 
          
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }
    /**
     * [CardNewUser3 开户 完成]
     * @param [type] $userid   [账号]
     * @param [type] $groupid  [用户组](Group_Id,移动组 1 ，电信套餐 2 ，电信包月套餐 3，电信用户：5)
     * @param [type] $pwd      [密码]
     * @param [type] $username [用户名]
     * @param [type] $notes    [备注(开户费订单)]
     * @return  [Array]  array(3) { ["code"]=> string(6) "400006" ["msg"]=> string(27) "添加新用户信息成功" ["data"]=> string(24) "表示操作成功" }
     */
    public function CardNewUser3($userid,$groupid,$pwd,$username,$notes)
    {
        $limitdate_end=$groupid=='1'?date('Y-m-d H:i:s',strtotime("+3 years",time())):date('Y-m-d H:i:s',time());
        $opendate=date('Y-m-d H:i:s',time());
        $userstate='1';
        $teamid=0; $phone=''; $address='';
        try {
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));
            $result = $client->CardNewUser3($userid,$groupid,$teamid,$pwd,$username, $phone, $address,$limitdate_end,$userstate,$opendate,$notes);
            
            $Resultmsg=['1'=>'表示操作成功','0'=>'表示操作失败', '-1'=>'表示用户已经存在','11'=>'营业区不存在','12'=>'用户组不存在'];

            if($result=='1')
              return json_encode(['code'=>'100006','msg'=>'添加新用户信息成功','Resultmsg'=>$result,'data'=>$Resultmsg[$result]]); 

            return json_encode(['code'=>'400006','msg'=>'添加新用户信息失败','Resultmsg'=>$result,'data'=>$Resultmsg[$result]]); 
          
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }
    /**
     * [ModifyUserInfo3 修改用户组和失效时间  完成]
     * @param string $userid        [账号]
     * @param string $groupid       [用户组]
     * @return  Array   array(3) { ["code"]=> string(6) "100005" ["msg"]=> string(24) "修改用户信息成功" ["data"]=> int(1) }
     */
    public function ModifyUserInfo3($userid='', $groupid='')
    {
        $net=new NetModel();
        // $endTime=$net->getEndTime($userid)[0]['到期时间'];
        $endTime=$net->getEndTime($userid)['old_time'];
        $endTime=isset($endTime)?strtotime($endTime):strtotime("-1 days",time());

        $abmsInfo=json_decode($this->getAbmsInfo($userid),true)['data'];
        $alNextTime=strtotime($abmsInfo[6]);

       
        if($alNextTime>$endTime && $abmsInfo[1]!='1')
         {
            $endTime=$alNextTime;
         }   

        $endTime=date('Y-m-d H:i:s',$endTime); 
        
        
        //$limitdate_end=$groupid=='1'?date('Y-m-d H:i:s',strtotime("+3 years",time())):date('Y-m-d H:i:s',strtotime("-1 days",time()));
        $limitdate_end=$groupid=='1'?date('Y-m-d H:i:s',strtotime("+3 years",time())):$endTime;

   

        $userstate='1';
        $teamid=0; $pwd='';$username=''; $phone=''; $address='';$opendate='';$notes='';$float='';$remain_fee=0.0;$certNum='';
        try {
            //$limitdate_end='2018-09-05';

             
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));

        //   $type=['1'=>'移动用户','2'=>'电信用户'];
        // echo "我之前是：".$type[$abmsInfo[1]];
        // echo "<br>";
        // echo "我要修改为：".$type[$groupid];
        // echo "<br>";
        // echo "我之前的失效时间为：".date('Y-m-d H:i:s',$alNextTime);
        // echo "<br>";
        // echo "我要修改时间为：".$limitdate_end;
        // echo "<br>";
        // if($groupid=='2')
        //     echo "你将来要改成".$type['2']."，应该同步为".$limitdate_end;
        // exit;

            $result = $client->ModifyUserInfo3($userid,$groupid,$teamid,$pwd,$username,$phone,$address,$limitdate_end,$userstate,$opendate,$notes,$remain_fee,$certNum);
            if($result=='1')
             {
                $students = new StudentModel();
                 if($groupid!='1')
                     $students->updateRuijieTime($limitdate_end,$groupid,$userid);
                $students->updateRuijieType($groupid,$userid);
                return json_encode(['code'=>'100005','msg'=>'修改用户信息成功','data'=>$result]); 
             } 

            return json_encode(['code'=>'400005','msg'=>'修改用户信息失败','data'=>$result]); 
          
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }

    /**
     * [offLineUser 强制用户下线]
     * @param  string $userip  [description]
     * @param  string $usermac [description]
     * @param  [type] $userid  [description]
     * @return [type]          array(3) { ["code"]=> string(6) "100004" ["msg"]=> string(24) "强制用户下线成功" ["data"]=> int(1) }
     */
    function offLineUser($userip='',$usermac='',$userid)
    {
        try {
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));
            $result = $client->offLineUser($userip,$usermac,$userid);
            if($result=='1')
              return json_encode(['code'=>'100004','msg'=>'强制用户下线成功','data'=>$result]); 

            return json_encode(['code'=>'400004','msg'=>'强制用户下线失败','data'=>$result]); 
          
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }
     /**
     * [ModifyUserInfo 修改安朗用户失效时间  完成]
     * @param [type]  $userid        [description]
     * @param integer $userstatus    [description]
     * @param [type]  $limitdatedate [description]
     * @return [Array] array(3) { ["code"]=> string(6) "100003" ["msg"]=> string(36) "修改安朗用户失效时间成功" ["data"]=> int(1) }
     */
    public function ModifyUserInfo($userid, $userstatus=1, $limitdatedate)
    {
        try {
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));
            $result = $client->ModifyUserInfo1($userid,$userstatus,$limitdatedate);
            if($result=='1')
              return json_encode(['code'=>'100003','msg'=>'修改安朗用户失效时间成功','data'=>$result]); 

            return json_encode(['code'=>'400003','msg'=>'修改安朗用户失效时间失败','data'=>$result]); 
          
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }

    /**
     * [getAbmsInfo 根据学号获取安郎用户信息    完成]
     * @param  [type] $userid [description]
     * @return [Array]     Array ( [code] => 100001 [msg] => 获取安朗用户信息成功 [data] => Array ( [0] => 20165533145 [1] => 5 [2] => 许钊纹 [3] => 25031X [4] => 北区26栋5006宿舍 [5] => 020-32962566 [6] => 2019-09-05 23:59:59 [7] => 2018-09-01 19:08:10.484 [8] => 18126746830 [9] => 0.0 [10] => 1 [11] => 0 [12] => 电信用户 [13] => 44528119970724571X [14] => 哈哈哈 [15] => 单位 [16] => 2018-11-05 [17] => ) )
     */
    public function getAbmsInfo($userid)
     {  
        try {
            $client = new \SoapClient($this->wsdl,array("connection_timeout" => 180));
            $param = array('userid' => $userid);
            $result = $client->getUserInfo($userid);
            if($result)
            {
                $anlang_infos=explode('##',$result);
                // if(strtotime($anlang_infos[6])<strtotime($anlang_infos[7]))
                //     return json_encode(['code'=>'400007','msg'=>'此用户未缴开户费','data'=>$anlang_infos]); //获取到用户的安朗信息
                $students = new StudentModel();
                if(count($students->getrruijie($userid))>0)
                {
                    if(!isset($students->getrruijie($userid)['EnKey']) || empty(trim($students->getrruijie($userid)['EnKey'])))
                    {
                        return json_encode(['code'=>'400007','msg'=>'此用户未缴开户费','data'=>$anlang_infos]); //
                    }
                }    
                else
                    return json_encode(['code'=>'400007','msg'=>'此用户未缴开户费','data'=>$anlang_infos]); //

                return json_encode(['code'=>'100001','msg'=>'获取安朗用户信息成功','data'=>$anlang_infos]); //获取到用户的安朗信息
            }
            return json_encode(['code'=>'400002','msg'=>'获取安朗用户失败，无此用户','data'=>$result]);//长度为0，代表安朗无此用户
        }catch(\SOAPFault $e)
        {
           return json_encode(['code'=>'400000','msg'=>'服务器错误']);
        }
    }
   
    /**
     * [getUserLimitEndDate 根据学号获取失效时间和下次续费时间  完成]
     * @param  [type] $userid [description]
     * @return [Array]  array(3) { ["code"]=> string(6) "100002" ["msg"]=> string(39) "获取失效时间和续费时间成功" ["data"]=> array(2) { ["NextLimitEndDate"]=> string(19) "2018-10-20 23:59:59" ["LimitEndDate"]=> string(19) "2018-09-20 23:59:59" } }
     */
    public function getUserLimitEndDate($userid)
    {
        $res=json_decode($this->getAbmsInfo($userid),true);
        if($res['code']!='100001')
        return json_encode($res);
        if($res['data'][1]=='1')
        return json_encode(['code'=>'400000','msg'=>'此用户为移动用户,不需要续费','data'=>$res['data'][1]]);
        $infos=$res['data'];
        //echo "失效时间：". date('Ymd',strtotime($infos[6]));

        $student=new StudentModel();
        $RuiJieInfo = $student->getrruijie($userid);

        //当前安朗的失效时间
        $data['LimitEndDate']=$infos[6];
        //欲续费的最大到期时间
        $maxNextTime="";
        //当月到期时间
        $MonthEndTime=mktime(23,59,59,date('m'),date('t'),date('Y'));
        //锐捷数据库到期时间
        $day=date('Y-m-01',strtotime($RuiJieInfo['old_time']));
        $NextTime=strtotime("$day +2 month -1 day");
        $RuijieNextTime=$NextTime;
        //安朗下次到期时间
        $day=date('Y-m-01',strtotime($infos[6]));
        $alNextTime=strtotime("$day +2 month -1 day");

        $maxNextTime=$MonthEndTime;//首先，设续费的日期为当月底
        if($RuijieNextTime>$maxNextTime)
            $maxNextTime=$RuijieNextTime;//如果，锐捷数据库的下次续费时间比目前欲续费的时间长，则选择锐捷数据库的续费时间

        if($alNextTime>$maxNextTime)
            $maxNextTime=$alNextTime;//如果，安朗的下次续费时间比目前欲续费的时间长，则选择安朗的续费时间


        $maxNextTime=mktime(23,59,59,date('m',$maxNextTime),date('t',$maxNextTime),date('Y',$maxNextTime)); //将欲续费时间变完那个月最后小时分秒
        //$maxNextTime=date("Y-m-d H:i:s",$maxNextTime);
        $data['NextLimitEndDate']=date("Y-m-d H:i:s",$maxNextTime);




        
        //寻找最长有效时间
        /*if(strtotime($RuiJieInfo[0]['到期时间'])>=mktime(23,00,00,date('m'),date('t'),date('Y')))
        {
        $data['NextLimitEndDate'] = mktime(23,00,00,date('m',$NextTime),date('t',$NextTime),date('Y',$NextTime));
        }
        else
        {
        $data['NextLimitEndDate'] = mktime(23,59,59,date('m'),date('t'),date('Y'));
        }


        if($alNextTime>=$data['NextLimitEndDate'])
        {
        $data['NextLimitEndDate']=$alNextTime;
        }

        $NextTime=$data['NextLimitEndDate'];

        $NextTimeEnd=mktime(23,59,59,date('m',$NextTime),date('t',$NextTime),date('Y',$NextTime));

        $data['NextLimitEndDate']=date("Y-m-d H:i:s",$NextTimeEnd);


        

        //jia yi xia shujuku panduan 
        if(strtotime($data['LimitEndDate'])<mktime(23,59,59,date('m'),date('t'),date('Y')))
            $data['NextLimitEndDate']=date('Y-m-d H:i:s',mktime(23,59,59,date('m'),date('t'),date('Y')));

        if($RuijieNextTime>strtotime($data['NextLimitEndDate']))
            $data['NextLimitEndDate']=date('Y-m-d H:i:s',$RuijieNextTime);*/


        return json_encode(['code'=>'100002','msg'=>'获取失效时间和续费时间成功','data'=>$data]);
        }
}
