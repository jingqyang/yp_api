<?php
namespace app\ka\controller;
use think\Controller;
use think\Db;
use think\Loader;
class Index extends Controller{

    //获取首页bannger
    public function getBannersInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Recommed')->getBanners($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取推荐商品列表
    public function getRecommendProInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Products')->getRecommendProInfo($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取新品列表
    public function getNewProInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Products')->getNewProInfo($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品详情
    public function getProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetail($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取微信openid
    public function getOpenId(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $code = input('post.code');

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        echo $r;
        die;
    }


    public function getAppId(){
        $bis = 1;
        $WxPayConfig = new \WxPayConfig();
        $res = $WxPayConfig->getAppId($bis);
        return $res;
    }
}
