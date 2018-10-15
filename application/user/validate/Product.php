<?php
namespace app\admin\validate;
use think\Validate;
class Product extends Validate{

    //定义规则
    protected $rule = [
        ['cat1_id','require|number', '平台一级分类必须设置|平台一级分类id必须为数字'],
        ['cat2_id','require|number', '平台二级分类必须设置|平台二级分类id必须为数字'],
        ['cat3_id','number', '总站子类id必须为数字'],
        ['d_cat1_id','require|number', '自定义一级分类必须设置|自定义一级分类id必须为数字'],
        ['d_cat2_id','require|number', '自定义二级分类必须设置|自定义二级分类id必须为数字'],
        ['p_name','require','商品名称必须填写'],
        ['brand','require','商品品牌必须填写'],
        ['unit','require','商品单位必须填写'],
        ['producing_area','require','商品产地必须填写'],
        ['original_price','require','零售价必须填写'],
        ['sold','require','已销售数量必须填写'],
        ['jifen','require','积分必须填写'],
        ['weight','require','商品重量必须填写'],
        ['huohao','require','商品货号必须填写'],
        ['rate','require','税率必须填写'],
        ['keywords','require','关键字必须填写'],
        ['description','require','商品描述必须填写'],
        ['nature','require','商品性质必须填写'],
        ['status','number|in:-1,0,1','状态必须是数字|状态范围不合法'],
        ['listorder','number']

    ];


    //场景设置
    protected $scene = [
//        'add'  =>  ['cat1_id','cat2_id','cat3_id','d_cat1_id','d_cat2_id'],//添加
        'add'  =>  ['cat1_id','cat2_id','d_cat1_id','d_cat2_id'],//添加
        'listorder'  =>  ['id','listorder'],//排序
        'status'  => ['id','status'],
        'update'   => ['id','cat1_id','cat2_id','d_cat1_id','d_cat2_id','cat_name'],//修改
    ];

}
?>