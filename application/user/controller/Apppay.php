<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/28
 * Time: 9:35
 */

namespace app\user\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Model;

Loader::import('wxpay.WxPayApi',EXTEND_PATH);

class Apppay extends Controller
{
    //传输给微信的参数组装成xml格式发送
    public function ToXml($data = array())
    {
        if (!is_array($data) || count($data) <= 0) {
            return '数组异常';
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //随机字符串
    function rand_code()
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str, 0, 32);
        return $str;
    }

    //获取签名
    private function getSign($params)
    {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            };
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA . "&key=" . "app2018zhibian201988888888888888";        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }
    //生成微信支付订单接口
    //接口地址   https://yp.dxshuju.com/user/apppay/wx
    //接口条件    telephone  token

    public function wx()
    {

        $telephone = input('post.telephone');
        $token = input('post.token');
        $where = "mem.telephone = '$telephone' && mem.token = '$token' ";
       $pay = Db::table('yp_current_pay')->alias('pay')->field('pay.assessment,mem.bis_id,mem.telephone')
            ->join('yp_member mem', 'mem.telephone=pay.telephone', 'LEFT')
            ->where($where)
            ->select();
        $pay2 = Db::table('yp_history_pay')->alias('his')->field('his.assessment')
            ->join('yp_member mem', 'mem.telephone=his.telephone', 'LEFT')
            ->where($where)
            ->select();
        $sum1 = $sum2 =$sum = 0;
       
        if ($pay) {
            foreach ($pay as $k => $v) {
                $sum1 += $v['assessment'];
                $pay[$k]['bis_id'] = $v['bis_id'];
                $pay[$k]['telephone'] = $v['telephone'];
            }
             foreach ($pay2 as $k => $v)
            {
                $sum2 += $v['assessment'];
            }
           $sum = $sum1+$sum2;

            $id[] = Db::table('yp_member')->field('id')->where(['telephone'=>$telephone,'token'=>$token])->select();
            $i = [];
            foreach ($id as $k=>$v)
            {
                $i = $v[$k]['id'];
            }

            $data = [
                'bis_id' => $pay[0]['bis_id'],
                'telephone' => $pay[0]['telephone'],
                'order_no' => substr(date('YmdHis',time()),2,14).$i.rand(111,999),
                'total_amount' => $sum,
                'pay_status' => 0,
                'create_time' => date('Y-m_d H:i:s', time()),
            ];

           $where = [
                'telephone'=>$pay[0]['telephone']
            ];
            Db::table('yp_main_order')->insert($data);
            Db::table('yp_history_pay')->where($where)->update(['order_no'=>$data['order_no']]);
            Db::table('yp_current_pay')->where($where)->update(['order_no'=>$data['order_no']]);
            return json([
                'status' => 1,
                'message' => '订单添加成功',
                'result' => $data
            ]);
        } else {
            return json([
                'status' => 0,
                'message' => '订单添加失败'
            ]);
        }


    }



      public function wxpay()
    {
        $order_no = input('post.order_no');
        $tab = Db::table('yp_main_order')->where(['order_no' => $order_no, 'pay_status' => 0])->select();

        if ($tab) {
            foreach ($tab as $k => $v) {
                $tab[$k]['total_amount'] = $v['total_amount'];
            }
            $WxPayConfig = new \WxPayConfig();
            //调用随机字符串生成方法获取随机字符串
            $nonce_str = $this->rand_code();
            //appid
            $data['appid'] = $WxPayConfig::APPID;
            //商户号
            $data['mch_id'] = $WxPayConfig::MCHID;
            //商品描述
            $body = $tab[$k]['total_amount'];
            $data['body'] = $body;
            //ip地址
            $data['spbill_create_ip'] = '116.255.206.241';
            //金额
            $total_fee = $tab[$k]['total_amount'];
            $data['total_fee'] = $total_fee * 100;
            //商户订单号,不能重复
            $data['out_trade_no'] = $order_no;
            //随机字符串
            $data['nonce_str'] = $nonce_str;
            //        $data['notify_url'] = 'https://yp.dxshuju.com/user/Pay/receiveNotify';   //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
            //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
            $data['notify_url'] = $WxPayConfig::NOTIFY_URL;
            //支付方式
            $data['trade_type'] = 'APP';
            //将参与签名的数据保存到数组  注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
            //获取签名
            $data['sign'] = $this->getSign($data);
            $xml = $this->ToXml($data);
            //数组转xml
            //curl 传递给微信方
            $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                //严格校验
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }

            //设置header
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //设置超时
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //传输文件
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            //运行curl
            $data = curl_exec($ch);
            //返回结果
           

            if ($data) {
                curl_close($ch);
                //返回成功,将xml数据转换为数组.
                $re = $this->FromXml($data);
                //修改表中的预支付id
                Db::table('yp_main_order')->where(['order_no' => $order_no, 'pay_status' => 0])->update(['prepay_id' => $re['prepay_id']]);

                if ($re['return_code'] != 'SUCCESS') {
                    return json([
                        'status' => 0,
                        'message' => '微信支付统一下单失败'
                    ]);
                } else {
                    //接收微信返回的数据,传给APP!
                    $arr = array(
                        //预支付交易会话标识
                        'prepayid' => $re['prepay_id'],
                        //应用APPID
                        'appid' => $WxPayConfig::APPID,
                        //商户号
                        'partnerid' => $WxPayConfig::MCHID,
                        //随机字符串
                        'noncestr' => $nonce_str,
                        //时间
                        'timestamp' => time(),
                        //扩展字段
                        'package' => 'Sign=WXPay'
                    );
                    //第二次生成签名
                    $sign = $this->getSign($arr);
                    $arr['sign'] = $sign;
                    return json([
                        'status' => 1,
                        'message' => '微信支付统一下单成功',
                        'result' => $arr
                    ]);
                }
            } else {
                $error = curl_errno($ch);
                curl_close($ch);
                return json([
                    'status' => 0,
                    'message' => '微信支付统一下单失败'
                ]);
            }
        } else {
            return json([
                'status' => 0,
                'message' => '微信支付统一下单失败'
            ]);
        }
    }

    public function FromXml($xml)
    {
        if(!$xml){
            echo "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
    public function Notify(){
        //接收微信返回的数据数据,返回的xml格式
        $xmlData = file_get_contents('php://input');
        //将xml格式转换为数组
        $data = $this->FromXml($xmlData);
        //用日志记录检查数据是否接受成功，验证成功一次之后，可删除。
        $file = fopen('./log.txt', 'a+');
        fwrite($file,var_export($data,true));
        //为了防止假数据，验证签名是否和返回的一样。
        //记录一下，返回回来的签名，生成签名的时候，必须剔除sign字段。
        $sign = $data['sign'];
        unset($data['sign']);
        if($sign == $this->getSign($data)){
            //签名验证成功后，判断返回微信返回的
            if ($data['result_code'] == 'SUCCESS') {
                //根据返回的订单号做业务逻辑
                $arr = array(
                    'pay_status' => 1,
                    'transaction_id' => $data['transaction_id'],
                    'update_time' => date('Y-m-d H:i:s',time())
                );


                $re = Db::table('yp_main_order')->where(['order_no'=>$data['out_trade_no']])->update($arr);
                $ta = Db::table('yp_main_order')->where(['order_no'=>$data['out_trade_no'],'pay_status'=>1])->select();
                $tas = Db::table('yp_main_order')->field('pay_status')->where(['order_no'=>$data['out_trade_no'],'pay_status'=>1])->select();
                if ($tas)
                {
                    Db::table('yp_current_pay')->where(['order_no'=>$data['out_trade_no']])->update(['sf_pay'=>1,'pay_data'=>'已缴']);
                    Db::table('yp_history_pay')->where(['order_no'=>$data['out_trade_no']])->update(['sf_pay'=>1,'pay_data'=>'已缴']);
                    Db::table('yp_current_pay')->where(['order_no'=>$data['out_trade_no'],'sf_pay'=>1])->update(['assessment'=>0]);
                    Db::table('yp_history_pay')->where(['order_no'=>$data['out_trade_no'],'sf_pay'=>1])->update(['assessment'=>0]);
                }
                $sum = 0;
                $tel = '';
                $a = Db::table('yp_member')->alias('mem')
                    ->field('mem.jifen,mem.telephone')
                    ->join('yp_main_order order','order.telephone = mem.telephone','LEFT')
                    ->where('order.telephone = mem.telephone')
                    ->select();
                foreach ($a as $v)
                {
                    $sum = $v['jifen'];
                    $tel = $v['telephone'];
                }
                $re = Db::table('yp_main_order')->where(['order_no'=>$data['out_trade_no']])->update($arr);
                Db::table('yp_main_order')->where(['order_no'=>$data['out_trade_no']])->update($arr);
                $rs =  Db::table('yp_member')->alias('mem')
                    ->field('mem.jifen')
                    ->join('yp_main_order main','mem.telephone = main.telephone','LEFT')
                    ->where(['main.pay_status'=>1,'main.telephone'=>$tel])
                    ->update(['mem.jifen'=> $sum+$data["total_fee"]/100]);
                //处理完成之后，告诉微信成功结果！
                if($re){
                    echo '<xml>
              <return_code><![CDATA[SUCCESS]]></return_code>
              <return_msg><![CDATA[OK]]></return_msg>
              </xml>';exit();
                }
            }
            //支付失败，输出错误信息
            else{
                $file = fopen('./log.txt', 'a+');
                fwrite($file,"错误信息：".$data['return_msg'].date("Y-m-d H:i:s"),time()."\r\n");
            }
        }
        else{
            $file = fopen('./log.txt', 'a+');
            fwrite($file,"错误信息：签名验证失败".date("Y-m-d H:i:s"),time()."\r\n");
        }

    }




}