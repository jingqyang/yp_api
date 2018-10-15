<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/27
 * Time: 18:50
 */

namespace app\user\controller;


use think\Controller;

class Historypay extends Controller
{
    //获取用历史户缴费信息接口
    // 条件 telephone  member_id
    //接口路径  https://yp.dxshuju.com/user/Historypay/historyInfo
    public function historyInfo()
    {
        $param =  input('post.');
        $res = model('Historypay')->historyInfo($param);
            echo json_encode([
                'status'=>1,
                'message'=>'历史欠费信息以查询成功',
                'res'=>$res
            ]);

    }
}