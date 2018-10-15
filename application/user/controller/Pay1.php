<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/28
 * Time: 9:35
 */

namespace app\user\controller;
//use \app\user\controller\WxNotify1;
//use \app\catering\controller\WxNotify1;
use think\Controller;
use think\Db;
use think\Loader;

Loader::import('wxpay.WxPayApi',EXTEND_PATH);

class Pay extends Controller
{
    public function pay()
    {
        $param = input('post.');
        $res = $this->makeWxPreOrder($param);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'数据查询成功',
                'result'=>$res
            ]);
            exit;
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'数据查询失败'
            ]);
        }
    }

    //生成微信预订单

    public function makeWxPreOrder($param)
    {
        $WxPayConfig = new \WxPayConfig();
        //获取参数
        $trade_no = $this->getOutTradeInfoById($param['order_id'])['order_no'];
        //商品描述
        $body = $param['body'];
        //总金额
        $total_fee = $this->getOutTradeInfoById($param['order_id'])['total_amount'];
        //通知回调地址
        $notify_url = $WxPayConfig::NOTIFY_URL;
        $openid = $param['openid'];
        //统一下单
        $wxOrderData = new \WxPayUnifiedOrder();
        //设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $wxOrderData->SetOut_trade_no($trade_no);
        //设置商品或支付单简要描述
        $wxOrderData->SetBody($body);
        //设置订单总金额，只能为整数，详见支付金额
        $wxOrderData->SetTotal_fee($total_fee * 100);
        //交易类型
        $wxOrderData->SetTrade_type('JSAPI');
        //交易开始时间
        $wxOrderData->SetTime_start(date('YmdHis'));
        //交易结束时间
        $wxOrderData->SetTime_expire(date('YmdHis',time()+ 600));
        //通知地址
        $wxOrderData->SetNotify_url($notify_url);


        //设置trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid。
        $wxOrderData->SetOpenid($openid);
        return $this->getPaySignature($param['order_id'],$wxOrderData);

    }

    //该方法内部调用微信预订单接口
    private function getPaySignature($order_id,$wxOrderData)
    {
        //$wxOrder是微信返回的结果
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);

//        $a = [
//            'package'=>'prepay_id ='.$wxOrder['prepay_id']
//        ];
//        var_dump($a);
//        exit();

        //判断代码
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS')
        {
            //存入日志(不管)
        }
        $this->recordPreOrder($order_id,$wxOrder);
        //签名
        $signature = $this->sign($wxOrder);
        return $signature;

    }
    //处理签名
    private function sign($wxOrder)
    {

        $jsApiPayData = new \WxPayJsApiPay();
        $WxPayConfig = new \WxPayConfig();
        $jsApiPayData->SetAppid($WxPayConfig::APPID);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
//        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
//        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['sign'] = $sign;

        unset($rawValues['appId']);
        return $rawValues;
    }


    //处理 prepay_id,把 prepay_id存入数据库
    private function recordPreOrder($order_id,$wxOrder)
    {
//        $data = Db::table('yp_main_order')->field('prepay_id')->select();
//        foreach ($data as $k=>$v)
//        {
//            $data[$k]['prepay_id'] = $v['prepay_id'];
//        }
////        var_dump($data[$k]['prepay_id']);
////        exit();
//        $data[$k]['prepay_id'] = $wxOrder['prepay_id']


//        $data['prepay_id'] = $wxOrder['prepay_id'];
//        $res = Db::table('yp_main_order')->where('id = '.$order_id)->update($data);
    }




    //根据外部订单id获取相关信息
    public function getOutTradeInfoById($order_id)
    {
        $res = Db::table('yp_main_order')->field('order_no,total_amount')->where('id = '.$order_id)->find();
        return $res;
    }
//
////    //支付回调
//    public function receiveNotify()
//    {
////        $notify = new \app\user\controller\WxNotify1();
//        $notify = new \app\user\controller\WxNotify1();
//        $notify->Handle();
////        $notify->Handle();
//    }

    //支付回调
    public function receiveNotify()
    {

        $notify = new WxNotify1();
        $notify->Handle();

    }







}