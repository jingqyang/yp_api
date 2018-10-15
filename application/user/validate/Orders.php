<?php
namespace app\admin\validate;
use think\Validate;
class Orders extends Validate{
    protected $rule = [
        ['order_status','require', '订单状态必须传递'],
        ['post_mode','require','快递名称必须传递'],
        ['express_no','require','快递单号必须传递'],

    ];


    //场景设置
    protected $scene = [
        'status1'  =>  ['order_status'],//只更改状态
        'status2'  =>  ['order_status','post_mode','express_no'],//更改状态,快递名称,单号
        'status3'  =>  ['order_status'],//更改状态
    ];

}
?>