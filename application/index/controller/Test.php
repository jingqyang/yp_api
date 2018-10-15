<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;
class Test extends Controller{

    //测试接口
    public function test(){
        $bis_id = 1;
        $res = Db::table('store_bis')->where('id = '.$bis_id)->find();
        dump($res);
        die;
    }


}
