<?php
//登录控制器
namespace app\controller;

use app\BaseController;

use app\model\User as UserModel;

use app\validate\Login as LoginCheck;
use think\exception\ValidateException;

class Login extends BaseController
{
	public function index()
	{
		return view();
	}

	public function checkLogin()
	{
		if (!$this->request->isPost()) {
			return JSON(['code' => 0, 'msg' => '请求方式错误']);
		}

		$data = $this->request->param();
		$username = $data['username'];
		$pwd = $data['password'];
		$captcha = $data['captcha'];

		try {
			validate(LoginCheck::class)->check([
				'username'  => $username,
				'pwd' => $pwd,
				'captcha' => $captcha
			]);
		} catch (ValidateException  $e) {
			// 验证失败 输出错误信息
			return JSON(['code'=>0,'msg'=>$e->getError()]);
		}

		$user=new UserModel();
		$info=$user->login($username);
		if($username!=$info['username']){
			return $this->returnJson(0,'账号错误');
		}
		if($pwd!=$info['pwd']){
			return $this->returnJson(0,'密码错误');
		}
		if($info['static']!=1){
			return $this->returnJson(0,'账号已禁用');
		}
		else{
			session('id',$info['id']);
			session('name',$info['name']);
			session('role_id',$info['role_id']);
			session('username',$info['username']);
			return $this->returnJson(1,'登录成功');
		}
	}


	//退出登录
	public function outLogin(){
		session('id',null);
		session('name',null);
		session('role_id',null);
		session('username',null);
		return $this->returnJson(1,'退出成功');
	}
}
