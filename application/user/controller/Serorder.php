<?php
namespace app\user\controller;

use think\Controller;
use think\Db;

class Serorder extends controller
{
    //生成订单
    public function makeOrder()
    {
        //获取参数
        $mobile = input('post.mobile');
        $token = input('post.token');
        $address = input('post.address');
        $type = input('post.type');
        $quantity = input('post.quantity');
        $cleaning_agent_count = input('post.cleaning_agent_count', 0);
        $remark = input('post.remark', '');
        $ser_time = input('post.ser_time');
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');

        //校验用户
        $userInfo = Db::table('yp_member')->where("telephone = '$mobile' and token = '$token'")->find();
        if (!$userInfo) {
            echo json_encode(array(
                'statuscode' => 0,
                'message' => '手机号未对应或token失效,请重新登录!'
            ));
            exit;
        }

        $userName = $userInfo['truename'];

        //计算订单价格
        if ($type == 1) {
            $amount = $quantity * 40 + $cleaning_agent_count * 5;
        } else {
            $amount = ceil($quantity) * 3 + $cleaning_agent_count * 5;
        }

        //设置数据
        $data = [
            'user_name' => $userName,
            'mobile' => $mobile,
            'address' => $address,
            'ser_type' => $type,
            'quantity' => $quantity,
            'cleaning_agent_count' => $cleaning_agent_count,
            'amount' => $amount,
            'remark' => $remark,
            'ser_time' => $ser_time,
            'create_time' => $create_time,
            'update_time' => $update_time
        ];
        $res = Db::table('yp_ser_orders')->insert($data);

        if ($res) {
            echo json_encode(array(
                'statuscode' => 1,
                'message' => '下单成功'
            ));
            exit;
        } else {
            echo json_encode(array(
                'statuscode' => 0,
                'message' => '下单失败'
            ));
            exit;
        }
    }

    //查看订单
    public function getOrder()
    {
        //获取参数
        $order_id = input('post.order_id');
        $res = Db::table('yp_ser_orders')->where("id = $order_id")->find();
        if(!$res){
            echo json_encode(array(
                'statuscode' => 0,
                'message' => '获取订单失败'
            ));
            exit;
        }
        $returnList = [
            'user_name' => $res['user_name'],
            'mobile' => $res['mobile'],
            'address' => $res['address'],
            'type_text' => $res['ser_type'] == 1 ? '按时计费' : '按面试计费',
            'quantity_text' => $res['ser_type'] == 1 ? intval($res['quantity']) . '小时' : intval($res['quantity']) . '㎡',
            'cleaning_agent_count' => $res['cleaning_agent_count'],
            'amount' => $res['amount'],
            'order_status' => $this->getOrderStatus($res['order_status']),
            'remark' => $res['remark'],
            'ser_time' => $res['ser_time'],
            'create_time' => $res['create_time']
        ];

        echo json_encode(array(
            'statuscode' => 1,
            'result' => $returnList
        ));
        exit;
    }

    //获取订单状态
    public function getOrderStatus($status)
    {
        switch ($status) {
            case 1:
                return '待付款';
                break;
            case 2:
                return '已付款';
                break;
            case 3:
                return '已结束';
                break;
            case 4:
                return '已取消';
                break;
            default:
                return '未知状态';
        }
    }
}