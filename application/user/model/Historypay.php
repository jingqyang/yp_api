<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/27
 * Time: 18:50
 */

namespace app\user\model;

use think\Controller;
use think\Db;

class Historypay extends Controller
{
    //获取用户历史缴费信息接口
    // 条件 telephone  member_id
    //接口路径  https://yp.dxshuju.com/api/public/user/Historypay/historyInfo
    public function historyInfo($param)
    {

        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $where = "telephone = '$telephone' && token = '$token'";
        $nn = Db::table('yp_member')->field('telephone')->where($where)->select();

        foreach ($nn as $k => $v) {
            $nn[$k]['telephone'] = $v['telephone'];
        }
        if ($nn) {
            $telephone = $nn[$k]['telephone'];
//            $wher = "address.telephone = '$telephone' && current.telephone = '$telephone'";
            $whe = "history.telephone = '$telephone' && sf_pay = 0";

            $ree = Db::table('yp_member')->alias('member')
                ->field('member.truename,history.start_time,history.stop_time,community.area_name,history.floor,history.unit,history.room,history.area,history.assessment')
                ->join('yp_history_pay history', 'history.telephone = member.telephone', 'LEFT')
                ->join('yp_community community', 'history.community_id = community.id', 'LEFT')
                ->where($whe)
                ->select();

            foreach ($ree as $m=>$c)
            {
                $ree[$m]['truename'] = $c['truename'];
                $ree[$m]['start_time'] = substr($c['start_time'],0,10);
                $ree[$m]['stop_time'] = substr($c['stop_time'],0,10);
                $ree[$m]['area_name'] = $c['area_name'];
                $ree[$m]['floor'] = $c['floor'];
                $ree[$m]['unit'] = $c['unit'];
                $ree[$m]['room'] = $c['room'];
                $ree[$m]['area'] = $c['area'];
                $ree[$m]['assessment'] = $c['assessment'];

            }
            return $ree;

        } else {
            echo json_encode([
                'status' => 0,
                'message' => '用户信息查询失败'
            ]);
            exit();
        }
    }
}




