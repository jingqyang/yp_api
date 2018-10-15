<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/12
 * Time: 14:36
 */

namespace app\user\controller;

use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('wxpay,WxPayApi',EXTEND_PATH);

class WxNotify extends  \WxPayNotify
{
    public function NotifyProcess($data, &$msg)
    {
        if ($data['result_code'] == 'SUCCESS')
        {
            //订单号
            $orderNo = $data['out_trade_no'];
            try{
                $out_order_info = Db::table('yp_main_order')->field('total_amount')->where('order_no ='.$orderNo)->find();
                if ($out_order_info['total_amount'] * 100 == $data['fee'])
                {
                    $res['pay_status'] = 1;
                    Db::table('yp_main_order')->where('order_no ='.$orderNo)->update($res);
                }
            }catch (Exception $ex){
                echo '支付失败';
                Log::error($ex);
                return false;
            }
        }else{
            return true;
        }
    }
}