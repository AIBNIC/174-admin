<?php
namespace app\controller;

use app\BaseController;
//故障控制器
class Fault extends BaseController
{
	protected $middleware = [\app\middleware\Check::class];
	public function index()
	{
		return view();
	}
}

