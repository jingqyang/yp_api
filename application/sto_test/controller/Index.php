<?php
namespace app\index\controller;
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

    //获取商品详情(二维规格)
    public function getProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetail($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品详情(一维规格)
    public function getProDetailOneDimensional(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetailOneDimensional($pro_id);
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

    //获取小程序二维码
    public function getwxacode(){
    	//创建文件夹
    	$upload_file_path = 'wxcode/';
    	if(!is_dir($upload_file_path)) {
            mkdir($upload_file_path,0777,true);
        }
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
		$u_id = input('post.u_id');
        //获取access_token
        $access_token_json = $this->getAccessToken($appid,$secret);
        $arr = json_decode($access_token_json,true);
        $access_token = $arr['access_token'];
       	//设置路径及二维码大小
        $path="pages/index/index?id=".$u_id;
        $width=430;

        $post_data='{"path":"'.$path.'","width":'.$width.'}';
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        $result = $this->api_notice_increment($url,$post_data);
        $filepath = '../index/test.png';
        file_put_contents($upload_file_path.'test.png', $result);
        return $upload_file_path.'test.png';
//        echo $result;
        die;
    }

    //获取access_token
    public function getAccessToken($appid,$secret){

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
        die;
    }

    function api_notice_increment($url, $data){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }else{
            return $tmpInfo;
        }
    }
}
