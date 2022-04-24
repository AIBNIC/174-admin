<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'username'=>'require',
        'pwd'=>'require',
        'captcha'=>'require|captcha'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'username.require'=>'账号不得为空',
        'pwd.require'=>'密码不得为空',
        'captcha.require'=>'验证码不得为空',
        'captcha.captcha'=>'验证码错误'
    ];
}
