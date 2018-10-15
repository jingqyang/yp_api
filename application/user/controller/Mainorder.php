<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/3
 * Time: 14:46
 */

namespace app\user\controller;


use think\Controller;

class Mainorder extends Controller
{

    //获取订单信息
    public function getNormalOrderInfo()
    {
        $param = input('post.');
        $res = model('Mainorder')->getNormalOrderInfo($param);

        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'订单获取成功',
                'result'=>$res
            ]);
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'订单信息获取失败'
            ]);
        }
    }
}