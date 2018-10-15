<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/10/11
 * Time: 17:21
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Banners extends Model
{
    public function getBannerInfo($bis_id)
    {

        $res = Db::table('yp_banners')->field('bis_id,image,rout_ios,rout_android')->where(['bis_id'=>$bis_id])->order('create_time desc')->limit(4)->select();
        foreach ($res as $k => $v)
        {
            $res[$k]['bis_id'] = $v['bis_id'];
            $res[$k]['image'] = 'http://39.105.68.130/img/'.str_replace('\\','/',$v['image']);
            $res[$k]['rout_ios'] = $v['rout_ios'];
            $res[$k]['rout_android'] = $v['rout_android'];
        }
        return $res;
    }
}