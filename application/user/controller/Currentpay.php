<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/27
 * Time: 9:25
 */

namespace app\user\controller;
use think\Controller;
use think\Db;

class Currentpay extends Controller
{
    //获取用户共需缴费的信息接口
    // 条件 telephone
    //接口路径  https://yp.dxshuju.com/user/currentpay/sumPay
    public function sumPay()
    {
        $param = input('post.');
        $res = model('Currentpay')->sumPay($param);
            echo json_encode([
                'status'=>1,
                'message'=>'用户信息查询成功',
                'result'=>$res
            ]);
    }

    //获取用户缴费信息接口
    // 条件 telephone  member_id
    //接口路径  https://yp.dxshuju.com/public/user/user/currentpay/selectInfo
    public function selectInfo()
    {
        $param = input('post.');
        $res = \model('Currentpay')->current($param);
        if ($res){
            echo json_encode([
                'status'=>1,
                'message'=>'用户信息查询成功',
                'result'=>$res
            ]);
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'用户信息查询失败',
                'res'=>[]
            ]);
        }
    }

    //获取用户已缴费信息接口
    public function payRecordInfo()
    {

        $param = input('post.');
        $res = model('Currentpay')->payRecordInfo($param);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'用户已缴费信息查询成功',
                'result'=>$res
            ]);
        }
    }

}