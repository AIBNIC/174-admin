<?php

namespace app\controller;

use app\BaseController;
use app\model\menu as  MenuModel;
use app\model\Fault as FaultModel;
use app\model\Notice as NoticeModel;
use app\model\Role as RoleModel;

use think\facade\View;

class Index extends BaseController
{
    protected $middleware = [\app\middleware\Check::class];

    public function index()
    {
        $id = session('id');
        $name = session('name');
        View::assign([
            'id' => $id,
            'name' => $name,
        ]);
        return View();
    }

    public function welcome()
    {

        $fault = new FaultModel();
        $notice = new NoticeModel();

        $text = $notice->getText();
        $data = $fault->tongji(); //数据统计
        $baobiao = $this->dataStatic(); //报表
        View::assign([
            'total'  => $data['total'],
            'Untreated' => $data['Untreated'],
            'benbu' => $baobiao['b'],
            'zc' => $baobiao['z'],
            'time' => $baobiao['time'],
            'notice' => $text
        ]);
        // dump($text); 
        return View();
    }

    public function getSystemInit()
    {
        $homeInfo = [
            'title' => '首页',
            'href'  => 'index/welcome',
        ];
        $logoInfo = [
            'title' => '网管后台',
            'image' => 'http://127.0.0.1:8000/static/images/logo.jpg',
            'href' => 'index'
        ];
        $menu = new MenuModel();
        $role = new RoleModel();
        //获取权限id
        $ault = $role->getRoleAuth(session('role_id'));
        // $ault=explode(' ',$ault);
        // halt($ault);

        $menuInfo = $menu->getMenuList();
        if (session('role_id') != 1) {
            foreach ($menuInfo as $key => $va) {
                if (!in_array($va['id'], $ault)) {    //判断返回的栏目id是否包含用户权限
                    unset($menuInfo[$key]);
                }
            }
        }


        //判断完后从新排列数组(layuimini菜单栏数组key必须是0开始)
        $newMenuInfo = [];
        foreach ($menuInfo as $va) {
            $newMenuInfo[] = $va;
        }
        $menuInfo = $newMenuInfo;

        $systemInit = [
            'homeInfo' => $homeInfo,
            'logoInfo' => $logoInfo,
            'menuInfo' => $menuInfo,
        ];
        // dump($newMenuInfo);
        return json($systemInit);
    }

    //报表统计
    public function dataStatic()
    {
        $fault = new FaultModel();
        $data['b'] = $fault->chart(false);   //本部最近七天故障
        $data['z'] = $fault->chart(); //增城最近七天故障
        $data['time'] = $fault->getData($query = 7, $mwdy = 'days', $format = 'm-d');
        return $data;
        // dump($data);
    }
}
