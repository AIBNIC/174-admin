<?php

namespace app\controller;

use app\BaseController;
use app\model\Role as RoleModel;
use app\model\User as UserModel;
use app\model\Menu as MenuModel;
use think\Request;

class System extends BaseController
{
	public function index()
	{
		return view();
	}
	public function userList()
	{
		$role = new RoleModel();
		$user = new UserModel();
		$userList = $user->getAllAdmin();
		foreach ($userList as $vo) {
			$vo['roleName'] = $role->getRoleName($vo['role_id']);
		}
		return JSON(['code' => 0, 'msg' => '获取成功', 'count' => count($userList), 'data' => $userList]);
	}
	//修改用户信息
	public function editUser($data = '')
	{
		if (empty($data)) {
			$role = new RoleModel();
			$roleList = $role->getAllRole();
			return view('', ['list' => $roleList]);
		}
		$user = new UserModel();
		// dump($data);
		$data = $user->updateUserInfo($data);
		return $this->returnJson(1, '修改成功');
	}

	public function delUser($id)
	{
		$user = new UserModel();
		if (is_array($id)) {
			foreach ($id as $vo) {
				$result = $user->delUser($vo);
			}
		} else {
			$result = $user->delUser($id);
		}
		return $this->returnJson(1, '删除成功');
	}

	public function addUser($data = '')
	{
		if (empty($data)) {
			$role = new RoleModel();
			$roleList = $role->getAllRole();
			return view('', ['list' => $roleList]);
		}
		$user = new UserModel();
		$data['updata_time'] = date('Y-m-d G:i:s');
		$result = $user->addUser($data);
		return $this->returnJson(1, '添加成功');
	}

	public function addUsers($data = '')
	{
		return '没编写';
	}

	public function role()
	{
		return view();
	}
	public function roleList()
	{
		$role = new RoleModel();
		$roleList = $role->getAllRole(2);
		// dump($roleList);
		return $this->LayuiTbaleJson(0, '获取成功', count($roleList), $roleList);
	}

	public function addRole(Request $request, $option = '')
	{
		if ($option == '') {
			return view();
		}
		//获取权限树并将键名‘child’改为'children'
		if ($option == 1) {
			$menu = new MenuModel();
			$menuList = $menu->getMenuList(2);
			foreach ($menuList as $key => &$vo) {
				if (!empty($vo['child'])) {
					$arr[] = $vo['child'];
					unset($vo['child']);
					foreach ($arr as $keyArr => $voArr) {
						$vo['children'] = $voArr;
					}
				} else {
					$vo['children'] = [];
				}
			}
			return $this->returnJson(1, '获取成功', $menuList);
			// return $menuList;
		}
		//添加用户
		else {
			$reData = $request->post();
			$roleName = $reData['name'];
			$roleInfo = $reData['role'];
			//取出树中的id并排序
			$id = $this->getid($roleInfo);
			$arr = [];
			foreach ($id as $key => $vo) {
				if (is_array($vo)) {
					foreach ($vo as $sonvo) {
						$arr[] = $sonvo;
					}
					unset($id[$key]);
				}
			}
			$newid = array_merge($id, $arr);
			sort($newid);

			$auth = implode(' ', $newid);
			$addData = ['role_name' => $roleName, 'static' => 1, 'auth' => $auth];
			$role = new RoleModel();
			$result = $role->addRole($addData);
			if ($result == 1) {
				return $this->returnJson(1, '添加成功');
			} else {
				return $this->returnJson(0, '添加失败');
			}
		}
	}

	public function delRole($id)
	{
		$role = new RoleModel();
		$result = $role->delRole($id);
		if ($result == 1) {
			return $this->returnJson(1, '删除成功');
		} else {
			return $this->returnJson(0, '删除失败');
		}
	}

	public function editRole(Request $request, $option = '')
	{
		if ($option == '') {
			return view();
		}
		$role = new RoleModel();
		$data = $request->post();

		if ($option == 1) {
			/***
			 *      ┌─┐       ┌─┐
			 *   ┌──┘ ┴───────┘ ┴──┐
			 *   │                 │
			 *   │       ───       │
			 *   │  ─┬┘       └┬─  │
			 *   │                 │
			 *   │       ─┴─       │
			 *   │                 │
			 *   └───┐         ┌───┘
			 *       │         │
			 *       │         │
			 *       │         │
			 *       │         └──────────────┐
			 *       │                        │
			 *       │                        ├─┐
			 *       │                        ┌─┘
			 *       │                        │
			 *       └─┐  ┐  ┌───────┬──┐  ┌──┘
			 *         │ ─┤ ─┤       │ ─┤ ─┤
			 *         └──┴──┘       └──┴──┘
			 *        此时此刻我的心情犹如此神兽
			 *        以下代码均可无视,是我脑抽写的
			 * 		  这部分可以简单很多,甚至可以不需要后端
			 * 		  直接输出该角色的auth使用layui的tree默认勾选即可
			 * 		  注:layui的tree有回显bug,解决方案:https://blog.csdn.net/weixin_42232296/article/details/110786038
			 * 		  本项目中的layui.js已改
			 * 		  友情提示:改为layui.js记得ctrl+F5强制刷新页面,有缓存(泪崩^)
			 * 		  解决方案中最后一部我注释掉了，不知为何加上后还是会有bug
			 */
			$auth = $data['auth'];
			$auth = explode(' ', trim($auth));
			if (count($auth) == 1) {
				return $this->returnJson(1, '此用户现无权限');
			}
			$menu = new MenuModel();
			$menuList = $menu->getMenuList(4);
			$authList = $menu->getMenuList(3, $auth);
			// //比较两个多维数组，返回重复值
			$alike = array_udiff(
				$authList,
				$menuList,
				function ($a, $b) {
					return strcmp(implode($a), implode($b));
				}
			);
			//为有权限的栏目加默认勾选
			for ($i = 0; $i < count($alike); $i++) { //此处与下面使用for是因为foreach有bug,会导致最后一项被前一项覆盖.暂没找到原因
				// array_push($alike[$i],);
				$alike[$i]['checked'] = 'true';
			}
			// //取出有权限的栏目的id
			$id = [];
			foreach ($alike as $vo) {
				$id[] = $vo['id'];
			}
			// //删除掉$menuList中有与$alike中重复的栏目
			foreach ($menuList as $key => &$vo) {
				if (in_array($vo['id'], $id)) {
					unset($menuList[$key]);
				}
			}
			// //合并数组
			$result = array_merge($menuList, $alike);
			// //对数组进行排序
			$idArr = array();
			for ($i = 0; $i < count($result); $i++) {
				$idArr[] = $result[$i]['id'];
			}
			$sortResult = array_multisort($idArr, SORT_ASC, $result);
			// //递归获取子栏目
			$resultTree = $menu->buildMenuChild(0, $result);

			foreach ($resultTree as $key => &$vo) {
				if (!empty($vo['child'])) {
					$arr[] = $vo['child'];
					unset($vo['child']);
					foreach ($arr as $keyArr => $voArr) {
						$vo['children'] = $voArr;
					}
				} else {
					$vo['children'] = [];
				}
			}
			return JSON($resultTree);
		}

		if ($option == 2) {
			$id = $data['id_name']['RoleID'];
			$name = $data['id_name']['roleName'];
			$auth = $data['auth'];
			//取出$auth中所有id，注：此处应用递归，我偷懒了，三维以上数组就要修改
			foreach($auth as $vo){
				$authId[]=$vo['id'];
				if(!empty($vo['children'])){
					foreach($vo['children'] as $sonvo){
						$authId[]=$sonvo['id'];
					}
				}
			}
			sort($authId);
			$authId_str=implode(' ',$authId);
			$RoleArr=['id'=>$id,'role_name'=>$name,'auth'=>$authId_str];
			$role=new  RoleModel();
			$result=$role->editRole($RoleArr);
			if($result==1){
				return $this->returnJson(1,'修改成功');
			}
			else{
				return $this->returnJson(0,'修改失败');
			}
			
		}
	}


	public function getid($arr)
	{
		$id = [];
		foreach ($arr as $vo) {
			$id[] = $vo['id'];
			if (!empty($vo['children'])) {
				$id[] = $this->getid($vo['children']);
			}
		}
		return $id;
	}
	// 	function arrayUdiffMore($a, $b)
	// 	{
	// 		return strcmp(implode("", $a), implode("", $b));
	// 	}
}
