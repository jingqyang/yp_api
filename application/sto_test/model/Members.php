<?php
namespace app\sto_test\model;
use think\Model;
use think\Db;

class Members extends Model{
    //添加会员信息
    public function addMembers($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $sex = !empty($param['sex']) ? $param['sex'] : '';

        //查询会员表中是否存在此会员
        $where = "bis_id = ".$bis_id." and mem_id = '$mem_id'";
        $mem_res = Db::table('store_members')->where($where)->select();
        if($mem_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '该会员已存在!'
            ));
            exit;
        }

        //设置数据
        $data = [
            'bis_id'  => $bis_id,
            'mem_id'  => $mem_id,
            'username'  => $mem_id,
            'nickname'  => $mem_id,
            'truename'  => $mem_id,
            'sex'  => $sex,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        //添加数据
        $res = Db::table('store_members')->insert($data);
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加会员失败!'
            ));
            exit;
        }

        return $res;
    }


}