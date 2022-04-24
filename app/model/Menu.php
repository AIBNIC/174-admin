<?php

namespace app\model;

use think\Model;

class Menu extends Model
{
    public function getMenuList()
    {
        $menuList = $this
            ->field('id,pid,title,icon,href,target')
            ->where('status', 1)
            ->order('sort', 'desc')
            ->select()->toArray();
        $menuList = $this->buildMenuChild(0, $menuList);
        return $menuList;
    }

    //递归获取子菜单
    private function buildMenuChild($pid, $menuList)
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
