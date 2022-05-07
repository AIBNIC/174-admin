<?php

namespace app\model;
use think\Db;
use think\Model;

class Net extends Model
{
    //获取相同宿舍的开网情况
    public function getnet($xuehao){
        $data = $this->query("SELECT TOP 10 a.学号, a.姓名, b.EnKey, c.mid FROM 学生信息表 a INNER JOIN 网络维护ip地址 b ON a.学号 = b.学号 LEFT OUTER JOIN MOBILE c ON b.学号=c.帐号 WHERE (a.宿舍代码 =(SELECT 宿舍代码 FROM 学生信息表 WHERE 学号 = '$xuehao'))");
        return $data;
    }

     //获取学生网络的到期时间
    public function getEndTime($xuehao){

        // $data = Db::query("SELECT  * FROM 网络维护ip地址  WHERE 学号 = '$xuehao' ");
        // return $this -> array_iconv($data);
        $db  = Db::connect("db_config_174");
        $data = $db ->table('xyw_abms_user')->where('xuehao',$xuehao)->find();
        return $data;
    }


    //转码
    function array_iconv($arr, $in_charset="gbk", $out_charset="utf-8//IGNORE"){
	 	$ret = eval('return '.iconv($in_charset,$out_charset,var_export($arr,true).';'));
	 	return $ret;
	 // 这里转码之后可以输出json
	 // return json_encode($ret);
	}
}