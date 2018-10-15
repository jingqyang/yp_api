<?php
namespace app\admin\validate;
use think\Validate;
class Recommend extends Validate{

    //定义规则
    protected $rule = [
        ['bis_id','number|gt:0','必须选择店铺|必须选择店铺'],
        ['redirect_url','require', '跳转url必须填写'],
        ['type','require', '推荐位类型必须选择'],
        ['res_id','number', '推荐位id必须存在'],

    ];


    //场景设置
    protected $scene = [
        'add'  =>  ['bis_id','redirect_url','type'],//商家添加
        'add1'  =>  ['redirect_url'],//平台添加
        'update'   => ['res_id','redirect_url','type'],//商家修改
        'update1'   => ['res_id','redirect_url'],//平台修改
    ];

}
?>