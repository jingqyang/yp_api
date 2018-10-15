<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/11
 * Time: 9:57
 */

namespace app\user\controller;


use think\Controller;
use think\Loader;

Loader::import('wxpay.WxPayApi',EXTEND_PATH);

class Pay extends Controller
{

    //传输给微信的参数要组装成xml格式发送,传如参数数组
    public function ToXml($data = [])
    {
        if(!is_array($data) || count($data) <= 0)
        {
            return '数组异常';
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    //生成随机字符串,微信所需参数
    function rand_code()
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str,0,32);
        return  $str;
    }
    //生成签名
    private function getSign($param)
    {
        ksort($param);
        foreach ($param as $key=>$item)
        {
            $newArr[] = $key.'='.$item;
        }
        $stringA = implode('&',$newArr);
        $stringSignTemp = $stringA."&key="."app2018zhibian201988888888888888";
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }

    public function wx_pay()
    {
        //调用随机字符串生成方法获取随机字符串
        $nonce_str = $this->rand_code();
        //appid
        $data['appid'] = 'wxb80a36968e113605';
        //商户号
        $data['mch_id'] = '1513889891';
        //内容
        $data['body'] = '支付测试';
        //ip地址
        $data['spbill_create_ip'] = $_SERVER['HTTP_HOST'];
        //金额
        $data['total_fee'] = 0.01;
        //商户订单号,不能重复
        $data['out_trade_no'] = time().mt_rand(10000,99999);
        //随机字符串
        $data['nonce_str'] = $nonce_str;
        //回调地址
        $data['notify_url'] = 'http://www.ypapi.com/user/pay/Notify';
        //支付方式
        $data['trade_type'] = 'APP';

        //将参与签名的数据保存到数组
        //获取签名
        $data['sign'] = $this->getSign($data);
        //数组转xml
        $xml = $this->ToXml($data);
        //curl 传递给微信方
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        if (stripos($url,'https://') != FALSE)
        {
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
//            curl_setopt($ch,CURLOPT_SSLVERSION,CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }else{
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
        }

        //设置header
        curl_setopt($ch,CURLOPT_SSLVERSION,1);
//        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
//        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //传输文件
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);

        return $data;



    }


}