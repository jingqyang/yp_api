<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/20
 * Time: 11:31
 */

namespace app\user\model;


use think\Db;
use think\Model;

class Member extends Model
{
    //
    public function getMmmberInfo($telephone)
    {
        $tab = Db::table('member')->where('telephone ='.$telephone)->select();
        var_dump($tab);
    }
}