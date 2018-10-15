<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/3
 * Time: 14:09
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Mainorder extends Model
{
//    public $table = 'yp_main_order';
    //添加订单信息接口
    public function addMemberOrderInfo($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';

    }

    //查询订单信息
    //查询条件  telephone   token(最新的token)  mem_id
    //地址    https://yp.dxshuju.com/api/public/user/mainorder/getNormalOrderInfo
    public function getNormalOrderInfo($param)
    {
        //获取参数
        $wx_id = !empty($param['openid']) ? $param['openid'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $community_id = !empty($param['community_id']) ? $param['community_id'] : '';
        $tab = Db::table('yp_main_order')->field('community_id')->select();
        $where = "member.telephone = '$telephone' and member.token = '$token' and member.mem_id = '$mem_id'";
        $res = Db::table('yp_main_order')->alias('order')
            ->field('order.id as order_id,order.order_no,member.truename,community.area_name,address.floor,address.unit,address.room,address.area,current.assessment,order.create_time,order.update_time')
            ->join('yp_community community','community.id = order.community_id','LEFT')
            ->join('yp_current_pay current','community.id = current.community_id','LEFT')
            ->join('yp_address address','address.community_id = community.id','LEFT')
            ->join('yp_member member','address.member_id = member.mem_id','LEFT')
            ->where($where)
            ->select();
        return $res;
    }
}