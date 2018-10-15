<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Product extends Controller{

    //获取商品配置信息
    public function getProConfigInfo(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProConfigInfo($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级名称&商品id获取二级配置信息
    public function getConfig2InfoById(){
        //获取参数
        $pro_id = input('post.pro_id');
        $con_info = input('post.con_info');
        $res = model('Products')->getConfig2InfoById($pro_id,$con_info);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

}



