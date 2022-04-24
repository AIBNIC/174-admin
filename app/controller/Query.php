<?php
namespace app\controller;

use app\BaseController;
//查询控制器
class Query extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];
	public function index()
	{
		return view();
	}
}

