<?php

namespace app\middleware;

use think\exception\HttpResponseException;
use think\facade\Config;


use app\model\Role;
use app\model\Menu;

class Check
{
	public function handle($request, \Closure $next)
	{
		//验证是否登录
		if (!session('?id')) {
			return redirect('login');
		}

		//获取到当前类和方法名进行权限判断
		$controller=Request()->controller();
		$action=Request()->action();
		$url = $controller . '/' . $action;
		$role = new Role();
		$ault = $role->getRoleAuth(session('role_id'));

		$menu = new Menu();
		$aultHref = $menu->getHref($ault);
		if ($controller != 'Index' && $url != 'Login/login') {
			if (!in_array($url, $aultHref)) {
				$this->error();
				// echo'ceshi';
			}
		}
		return $next($request);
	}

	public function error(){
        
        $response=view(Config::get('app.exception_error_tmpl'));
        throw new HttpResponseException($response);
    }
}
