<?php
namespace app\sto_test\controller;
use think\Controller;
use think\Db;

class Order extends Controller{

    //获取订单信息
    public function getOrderInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成订单
    public function makeOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据id查询订单号和总金额
    public function getOrderInfoByOrderId(){
        $order_id = input('post.order_id');
        $res = Db::table('store_main_orders')->field('order_no,total_amount')->where('id = '.$order_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //取消订单
    public function cancelOrder(){
        $order_id = input('post.order_id');
        $data['status'] = -1;
        $res = Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }
}



