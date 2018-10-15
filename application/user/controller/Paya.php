<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/27
 * Time: 14:46
 */

namespace app\user\controller;

use app\catering\controller\WxNotify;
use think\Controller;
use think\Db;
use think\Loader;

Loader::import('CyWxPay.WxPayApi',EXTEND_PATH);

class Paya extends Controller
{

    public function pay()
    {
        $param = input('post.');
        $res = $this->makeWxPreOrder($param);
        echo json_encode([
            'status'=>1,
            'success'=>'支付成功',
            'result'=>$res
        ]);
        exit();
    }

    //生成微信预订单
    public function makeWxPreOrder($param)
    {
      $WxPayConfig = new \WxPayConfig();
      //获取参数
      $trade_no = $this->getOutTradeInfoById($param['pay_id'])['pay_no'];
      $body = $param['body'];
//      $total_fee = $this->getOutTradeInfoById($param['pay_id'])['total_amount'];
      $notify_url = $WxPayConfig::NOTIFY_URL;
//      $openid = $param['openid'];
      $wxOrderData = new \WxPayUnifiedOrder();
      $wxOrderData->SetOut_trade_no($trade_no);
      $wxOrderData->SetBody($body);
//      $wxOrderData->SetTotal_fee($total_fee * 100);
      $wxOrderData->SetTrade_type('JSAPI');
      $wxOrderData->SetNotify_url($notify_url);
//      $wxOrderData->SetOpenid($openid);
      return $this->getPaySignature($param['order_id'],$wxOrderData);
    }
    //该方法内部调用微信预订单接口
    private function getPaySignature($order_id,$wxOrderData)
    {
        //$wxOrder是微信返回的结果
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        //判断代码
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS')
        {
            //存入日志，不管
        }
        $this->recordPreOrder($order_id,$wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;

    }

    //处理签名
    private function sign($wxOrder){
        $jsApiPayData = new \WxPayApi();
        $WxPayConfig = new \WxPayConfig();
        $jsApiPayData->SetAppid($WxPayConfig::APPID);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNoceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSgnType('md5');

        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['sign']=$sign;

        unset($rawValues['appId']);
        return $rawValues;
    }

    //处理 prepay_id,把 prepay_id存入数据库
    private function recordPreOrder($pay_id,$wxOrder)
    {
        $data = ['prepay_id'] = $wxOrder['parepay_id'];
        $res = Db::table('yp_current_pay')->where('id ='.$pay_id)->update($data);
        return $res;
    }

    //根据外部订单id获取相关信息
    public function getOutTradeInfoById($pay_id)
    {
        $res = Db::table('yp_current_pay')->where('id ='.$pay_id)->find();
        return $res;
    }
    //支付回调
    public function receiveNotify()
    {
        $notify = new WxNotify();
        $notify->Handle();
    }





}