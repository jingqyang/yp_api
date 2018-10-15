<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/2
 * Time: 15:02
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Recommed extends Model
{

    //获取首页banner图
    //提交方式  get
    //条件    bis_id      telephone
    //接口路径  https://yp.dxshuju.com/api/public/user/member/getBannerInfo?bis_id=1
    public function getBannerInfo($bis_id)
    {
        $n = Db::table('yp_member')->alias('member')
            ->field('member.truename,recommend.bis_id,recommend.image,recommend.rout_ios,recommend.rout_android')
            ->join('yp_recommend recommend', 'member.bis_id = recommend.bis_id', 'LEFT')
            ->where(['member.bis_id' => $bis_id])
            ->order('recommend.listorder desc,recommend.create_time desc')
            ->limit(4)
            ->select();
        if ($n)
        {
            foreach ($n as $k=>$v)
            {
                $n[$k]['truename']=$v['truename'];
                $n[$k]['bis_id']=$v['bis_id'];
                $n[$k]['image']='http://39.105.68.130:81/api/public/img/'.str_replace('\\','/',$v['image']);
                $n[$k]['rout_ios']=$v['rout_ios'];
                $n[$k]['rout_android']=$v['rout_android'];
            }
            return $n;
        }else{
            echo json_encode([
                'status' => 0,
                'message' => 'banner获取失败'
            ]);
            exit();
        }
    }
}