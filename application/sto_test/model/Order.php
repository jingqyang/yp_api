<?php
namespace app\sto_test\model;
use think\Model;
use think\Db;

class Order extends Model{

    //获取订单信息
    public function getOrderInfo($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.bis_id = ".$bis_id." and main.mem_id = '$wx_id' and main.status = 1 ";

        if($type == 2){
            $con = "and main.order_status = 1";
        }elseif($type == 3){
            $con = "and main.order_status = 2";
        }elseif($type == 4){
            $con = "and main.order_status = 3";
        }elseif($type == 5){
            $con = "and main.order_status = 4";
        }elseif($type == 6){
            $con = "and main.order_status = 5";
        }else{
            $con = "";
        }
        $where .= $con;

        $res = Db::table('store_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status')
            ->where($where)
            ->order('main.create_time desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['order_id'] = $val['order_id'];
            $result[$index]['order_no'] = $val['order_no'];
            $result[$index]['amount'] = $val['total_amount'];
            $result[$index]['status'] = $val['order_status'];

            switch($val['order_status']){
                case 1:
                    $status_text =  '未确认';
                    break;
                case 2:
                    $status_text =  '待付款';
                    break;
                case 3:
                    $status_text =  '待发货';
                    break;
                case 4:
                    $status_text =  '待收货';
                    break;
                default:
                    $status_text =  '已完成';
                    break;
            }
            $result[$index]['status_text'] = $status_text;
            $result[$index]['pro_info'] = $this->getSubOrderInfo($val['order_id']);
            $index ++;
        }

        return $result;
    }

    //生成订单
    public function makeOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $address_id = !empty($param['address_id']) ? $param['address_id'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $pro_info = !empty($param['pro_info']) ? $param['pro_info'] : '';
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');

        //补全店铺id格式
        if($bis_id < 10){
            $new_bis_id = '000'.$bis_id;
        }elseif($bis_id < 100 and $bis_id >=10){
            $new_bis_id = '00'.$bis_id;
        }elseif($bis_id < 1000 and $bis_id >=100){
            $new_bis_id = '0'.$bis_id;
        }else{
            $new_bis_id = $bis_id;
        }
        //设置主订单表字段
        $main_data = [
            'bis_id'  => $bis_id,
            'mem_id'  => $mem_id,
            'address_id'  => $address_id,
            'payment'  => 1,
            'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
            'total_amount'  => $total_amount,
            'create_time'  => $create_time,
            'update_time'  => $update_time,
            'order_status'  => 1,
            'remark'  => $remark
        ];

        //向主表添加数据
        $main_res = Db::table('store_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        $sub_data = array();
        $cart_ids = '';
        foreach($pro_info as $val){
            //设置副订单表字段
            $temp_sub_data = [
                'main_id'  => $main_res,
                'pro_id'  => $val['pro_id'],
                'count'  => $val['count'],
                'unit_price'  => $val['associator_price'],
                'amount'  => $val['count'] * $val['associator_price']
            ];
            array_push($sub_data,$temp_sub_data);

            //设置接收的购物车表信息
            $cart_ids .= $val['cart_id'].',';
        }

        //向副表添加数据
        $sub_res = Db::table('store_sub_orders')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }
        //格式化购物车表信息
        $cart_ids = substr($cart_ids,0,-1);
        //更改对应购物车信息状态
        $cart_data['status'] = 0;
        $update_cart_res = Db::table('store_shopping_carts')->where("id in ($cart_ids)")->update($cart_data);
        if(!$update_cart_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '更改购物车状态失败'
            ));
            exit;
        }

        return $main_res;
    }

    //获取订单副表信息
    public function getSubOrderInfo($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('store_sub_orders')->alias('sub')->field('pro.p_name,img.thumb,con.con_content1,con.con_content2')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }
}