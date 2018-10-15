<?php
namespace app\admin\validate;
use think\Validate;
class DefinedCategory extends Validate{
    protected $rule = [
        ['bis_id','number|gt:0','必须选择店铺|必须选择店铺'],
        ['cat_name','require|max:10', '分类名必须传递|分类名不能超过10个字符'],
        ['parent_id','number'],
        ['id','number'],
        ['status','number|in:-1,0,1','状态必须是数字|状态范围不合法'],
        ['listorder','number']

    ];


    //场景设置
    protected $scene = [
        'add'  =>  ['bis_id','cat_name','parent_id'],//添加
        'listorder'  =>  ['id','listorder'],//排序
        'status'  => ['id','status'],
        'update'   => ['id','cat_name','parent_id'],//修改
    ];

}
?>