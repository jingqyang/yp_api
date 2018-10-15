<?php
namespace app\admin\validate;
use think\Validate;
class Admin extends Validate{
    //规则设置
    protected $rule = [
        ['username','alphaNum|min:4|max:16', '用户名只能由英文或数字组成|用户名不能少于4个字符|用户名不能超过16个字符'],
        ['password','alphaNum|min:6|max:20','密码只能由英文和数字组成|密码不能少于6个字符|密码不能超过20个字符'],
        ['password_confirm','require|confirm:password','确认密码不能为空|两次输入的密码不一致'],
    ];


    //场景设置
    protected $scene = [
        'register'  =>  ['username','password','password_confirm']//注册

    ];


}
?>