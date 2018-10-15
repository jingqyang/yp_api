<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Exception;
class Base extends Controller{

    public function _initialize(){
        $check_code = 'ba9c43391dc62a8fd9ea3e59690c37801';
        $host = $_SERVER['HTTP_HOST'];
        $maked_url = md5(md5($host.'-'.'zhibian.net'));
        if($check_code != $maked_url){
            throw new Exception("Domain name is incorrect");
        }else{
            return true;
        }
    }

}
