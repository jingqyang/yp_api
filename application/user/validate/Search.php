<?php
namespace app\admin\validate;
use think\Validate;
class Search extends Validate{
    protected $rule = [
        ['word','require|max:8', '热门词汇必须传递|热门词汇不能超过8个字符'],
        ['id','number','id必须是数字'],
        ['status','number|in:-1,0,1','状态必须是数字|状态范围不合法'],

    ];


    //场景设置
    protected $scene = [
        'add'  =>  ['brand_name'],//添加
        'status'  => ['id','status'],//修改状态
        'update'   => ['id','brand_name'],//修改
    ];

}
?>