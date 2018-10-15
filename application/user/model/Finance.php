<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/4
 * Time: 16:50
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Finance extends Model
{
    //获取分期信息接口
    // 条件 telephone  mem_id  token
    //接口路径  https://yp.dxshuju.com/api/public/user/finance/selectFinanceInfo
    public function selectFinanceInfo($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $where = "member.telephone = '$telephone' && token = '$token'";
        $tab = Db::table('yp_member')->alias('member')
            ->field('finance.general_finance,finance.apply_number,finance.interest_rate,finance.telephone')
            ->join('yp_finance finance','finance.telephone = member.telephone','LEFT')
            ->where($where)
            ->select();
        foreach ($tab as $k=>$v)
        {
            $tab[$k]['general_finance']=$v['general_finance'];
            $tab[$k]['apply_number']=$v['apply_number'];
            $tab[$k]['interest_rate']=$v['interest_rate'];
            $tab[$k]['telephone']=$v['telephone'];
        }
        return $tab;


    }
}