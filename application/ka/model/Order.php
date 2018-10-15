<?php
namespace app\ka\model;
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
        $total_price = !empty($param['total_price']) ? $param['total_price'] : '';
        $ka_dingdan = !empty($param['ka_dingdan']) ? $param['ka_dingdan'] : '';
        $dingdan = substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').rand(1000,9999);

        $sub_data = array();

        foreach($ka_dingdan as $val){
            $temp_sub_data = [
                'dingdan'  => $dingdan,
                'money_name'  => $val['money_name'],
                'count'  => $val['count'],
                'image'  => $val['image'],
                'value'  => $val['value']
            ];
            array_push($sub_data,$temp_sub_data);
         
        }

        //向表添加数据
        $sub_res = Db::table('ka_dingdan')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加订单失败'
            ));
            exit;
        }


        return $sub_res;
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