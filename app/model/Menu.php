<?php

namespace app\model;

use think\Model;

class Menu extends Model
{

    /*
    *[getMenuList 获取获得权限、栏目列表  [完成]]
    * @param int $option       [选择：1=>左侧菜单栏,2=>系统控制中添加和修改时的权限树，3=>获取指定的权限数组（一维），4=>获取全部权限（一维）]
    * @param Array id
    */
    public function getMenuList($option=1,$id=[])
    {
        if($option==1){
            $menuList = $this
            ->field('id,pid,title,icon,href,target')
            ->where('status', 1)
            ->order('sort', 'desc')
            ->select()->toArray();
        }
        elseif($option==2){
            $menuList = $this
            ->field('id,pid,title')
            ->select()->toArray();
        }
        elseif($option==3){
            $menuList = $this
            ->field('id,pid,title')
            ->select($id)->toArray();
            foreach($menuList as &$vo){
                $vo['checked']='false';
            }
            return $menuList;   
        }
        if($option==4){
            $menuList = $this
            ->field('id,pid,title')
            ->select()->toArray();
            return $menuList;  
        }
        $menuList = $this->buildMenuChild(0, $menuList);
        return $menuList;
        
    }

    //递归获取子菜单
    public function buildMenuChild($pid, $menuList)
    {
        $treeList = [];
        foreach ($menuList as $v) {
            if ($pid == $v['pid']) {
                $node = $v;
                $child = $this->buildMenuChild($v['id'], $menuList);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                // todo 后续此处加上用户的权限判断
                $treeList[] = $node;
            }
        }
        return $treeList;
    }

    //获取栏目地址
    public function getHref($id)
    {
        $data=[];
        foreach($id as $key=>$va){
            $data[]=$this->where('id',$va)->value('href');
        }
        return $data;
    }
}
