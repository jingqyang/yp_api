<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/27
 * Time: 9:26
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Currentpay extends Model
{
    //获取用户共需缴费的信息接口
    // 条件 telephone
    //接口路径  https://yp.dxshuju.com/api/public/user/currentpay/sumPay
    public function sumPay($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        //查询条件pay.assessment,pay2.assessment
        $where = "telephone = '$telephone' && token = '$token'";
        $nn = Db::table('yp_member')->field('telephone')->where($where)->select();

        foreach ($nn as $k=>$v)
        {
            $nn[$k]['telephone'] = $v['telephone'];
        }
        if ($nn)
        {
            $id = $nn[$k]['telephone'];
            $wher = "member.telephone = '$id' && current.telephone = '$id'";
            $whe = "member.telephone = '$id' && history.telephone = '$id'";
            //查询出当前应缴的费用
            $re = Db::table('yp_member')->alias('member')->field('current.assessment')
                ->join('yp_current_pay current','current.telephone = member.telephone','LEFT')
                ->where($wher)
                ->select();



            //查询出历史欠费的费用
            $ree = Db::table('yp_member')->alias('member')->field('history.assessment')
                ->join('yp_history_pay history','history.telephone = member.telephone','LEFT')
                ->where($whe)
                ->select();

            $sum = $a = $b = 0;
            foreach ($re as $n=>$v)
            {
                $a += $re[$n]['assment'] = $v['assessment'];
            }
            foreach ($ree as $m=>$c)
            {
                $b += $ree[$m]['assessment'] = $c['assessment'];
            }
            //共需交的费用
            $sum = $a + $b;
            return $sum;
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'用户信息查询失败'
            ]);
            exit();
        }
    }

    //获取用户缴费信息接口
    // 条件 telephone  member_id
    //接口路径  https://yp.dxshuju.com/user/currentpay/selectInfo
    public function current($param)
    {

        //获取参数
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        //查询条件
        $where = "telephone = '$telephone' && token = '$token'";
        $nn = Db::table('yp_member')->field('telephone')->where($where)->find();
//        foreach ($nn as $v=>$k)
//        {
//            $nn[$v]['telephone'] = $k['telephone'];
//        }
        if ($nn){
//            $telephone = $nn[$v]['telephone'];
            $tab = Db::table('yp_member')->alias('member')
                ->field('member.truename,current.assessment,current.floor,current.unit,current.room,current.area,current.start_time,current.stop_time,community.area_name')
                ->join('yp_current_pay current','current.telephone = member.telephone','LEFT')
                ->join('yp_community community', 'current.community_id = community.id', 'LEFT')
                ->where(['current.telephone'=>$telephone])
                ->select();
            foreach ($tab as $k=>$v)
            {
                $tab[$k]['truename']=$v['truename'];
                $tab[$k]['area_name']=$v['area_name'];
                $tab[$k]['start_time']=substr($v['start_time'],0,10);
                $tab[$k]['stop_time']=substr($v['stop_time'],0,10);
                $tab[$k]['floor']=$v['floor'];
                $tab[$k]['unit']=$v['unit'];
                $tab[$k]['room']=$v['room'];
                $tab[$k]['area']=$v['area'];
                $tab[$k]['assessment']=$v['assessment'];
            }
            if ($tab == null)
            {
                return $tab = [];
            }else{
                return $tab;
            }
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'用户信息查询失败',
                'res'=>[]
            ]);
            exit();
        }

    }
    
    //获取用户已缴费信息接口
    //https://yp.dxshuju.com/api/public/user/currentpay/payRecordInfo
    //接口地址      
    //1.Key=>telephone		value=>$_POST
    //3.Key=>token		value=>token值
    public function payRecordInfo($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $tab = Db::table('yp_main_order')
            ->alias('order')
            ->join('yp_current_pay pay','order.telephone = pay.telephone','LEFT')
            ->join('yp_member mem','mem.telephone = order.telephone','LEFT')
            ->join('yp_community com','com.id = pay.community_id',"LEFT")
            ->field('order.total_amount,mem.truename,mem.telephone,com.area_name,pay.floor,pay.unit,pay.room,pay.area,order.order_no,order.create_time,order.update_time')
            ->where(['order.pay_status'=>1,'mem.telephone'=>$telephone,'token'=>$token])
            ->order('order.create_time desc')
            ->select();



        $res = [];
        foreach ($tab as $v) {
            $res[$v['order_no']][] = $v;
            if ($res)
            {
                return $res;
            }else{
                echo json_encode([
                    'status'=>0,
                    'message'=>'您还未交钱'
                ]);
                exit();
            }

        }
        

    }
    
    
}