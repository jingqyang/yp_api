<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/21
 * Time: 13:39
 */

namespace app\user\controller;

header("Content-Type: text/html;charset=utf-8");
use think\Controller;
use think\Db;
use think\Model;

class Member extends Controller
{
    public $status = [
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    ];

    //注册手机号
    public function faMember()
    {
        $param = input('post.');
        $res = \model('Member')->faMember($param);
        if ($res){
            echo json_encode([
                'status' => 1,
                'message'=>'第一次注册成功',
                'result'=>$res
            ]);
            exit();
        }else{
            echo json_encode([
                'status' => 0,
                'message' => '注册失败'
            ]);
        }
    }

    public function login()
    {
        $par = input('post.');
        $res = \model('Member')->login($par);
        if($res)
        {
            echo json_encode([
                'status' => 1,
                'message'=>'登录成功',
                'result'=>$res
            ]);
            exit();
        }
    }


    //获取个人信息接口
    public function selectMember()
    {
        $telephone = input('post.telephone');
        $token = input('post.token');
        $res = \model('Member')->selectInfo($telephone,$token);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'用户信息查询成功',
                'res'=>$res
            ]);
            exit();
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'用户信息查询失败'
            ]);
        }
    }


    
    //修改数据接口
    public function updateMember()
    {
        $param = input('post.');
        $res = \model('Member')->updateMember($param);
        echo json_encode([
            'status'=>1,
            'message'=>'数据修改成功',
            'result'=>$res
        ]);
    }
    
    
    //图片上传
    public function fileTop()
    {

        $param = input('post.');
        $res = \model('Member')->fileTop($param);

        if ($res){
            echo  json_encode([
                'status'=>1,
                'message'=>'图片上传成功',
                'result'=>$res
            ]);
        }else{
            echo  json_encode([
                'status'=>0,
                'message'=>'图片上传失败'
            ]);
        }
    }

    //banner获取
    public function getBannerInfo()
    {

        $bis_id = input('get.bis_id');
        $res = \model('Banners')->getBannerInfo($bis_id);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'banner获取成功',
                'result'=>$res
            ]);
            exit();
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'banner获取失败'
            ]);
        }
    }
    
    //分期信息统计接口
    public function addStage()
    {
        $param = input('post.');
        $res = \model('Member')->addStage($param);
        if ($res)
        {

            echo json_encode([
                'status'=>1,
                'message'=>'添加分期信息成功',
                'result'=>$res
            ]);
            exit();
        }
    }

    //获取分期记录接口
    public function stageInfo()
    {

        $param = input('post.');

        $res = \model('Member')->stageInfo($param);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'获取分期记录成功',
                'result'=>$res
            ]);
            exit();
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'获取分期记录失败'
            ]);
            exit();
        }

    }

    //省信息获取
    public function getProvince()
    {
        $res = \model('Member')->getProvince();
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'省信息获取成功',
                'res'=>$res
            ]);
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'省信息获取失败'
            ]);
        }
    }
    //市与区信息获取
    public function getCityArea()
    {
        $param = input('post.');
        $res = \model('Member')->getCityArea($param);
        if ($res)
        {
            echo json_encode([
                'status'=>1,
                'message'=>'市区信息查询成功',
                'res'=>$res
            ]);
        }else{
            echo json_encode([
                'status'=>0,
                'message'=>'市与区信息查询失败'
            ]);
        }
    }

}