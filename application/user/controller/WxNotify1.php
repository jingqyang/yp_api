<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/9/10
 * Time: 13:29
 */

namespace app\user\controller;

use think\Controller;
use think\Loader;

require_once '../../../extend/wxpay/WxPay.Notify.php';
require_once '../../../extend/wxpay/WxPayApi.php';

Loader::import('wxpay,WxPayApi'.EXTEND_PATH);
require_once '../../../extend/wxpay/WxPay.Notify.php';
class WxNotify1
{

}