<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/4
 * Time: 16:46
 */

namespace app\user\controller;


use think\Controller;

class Finance extends Controller
{
    //获取分期信息
    public function selectFinanceInfo()
    {
        $param = input('post.');
        $res = model('Finance')->selectFinanceInfo($param);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'分期信息查询成功',
                'res'=>$res
            ]);
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'分期信息获取失败'
            ]);
        }
    }
}