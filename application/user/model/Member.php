<?php
/**
 * Created by PhpStorm.
 * User: ls19980819
 * Date: 2018/8/21
 * Time: 13:37
 */

namespace app\user\model;

header("Content-Type: text/html;charset=utf-8");
use think\Db;
use think\Model;
use think\Validate;

class Member extends Model
{

    function rand_code()
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str, 0, 16);
        return $str;
    }

    public function faMember($param)
    {
        //获取参数
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        //查询会员表中是否存在此会员
        $where = "telephone = '$telephone'" ;
        $mem_res = Db::table('yp_member')->where($where)->select();
        //验证手机号是否合法
        $rules = [
            'telephone'=>'require|number|length:11',
        ];
        $data = [
            'telephone'=>$telephone
        ];
        $validate = new Validate($rules);
        $re = $validate->check($data);
        if (!$re){
            echo json_encode([
                'status' => 0,
                'message' => '注册失败'
            ]);
        }else{
            if ($mem_res){
                //调用第三方接口
                $smsapi = "https://api.smsbao.com/";
                //短信平台帐号
                $user = "dalaa";
                //短信平台密码
                $pass = md5('123456');
                //随机生成的验证码
                $code=rand(111111,999999);
                //获取手机号
                $phone = $telephone;
                //要发送的短信内容
                $content="尊敬的客户您好，您的验证码为".$code.'【一盆】';
                //发送短信
                $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
                file_get_contents($sendurl);
                $yan = [
                    'newcode'=>$code
                ];
                Db::table('yp_member')->where(['telephone'=>$phone])->update($yan);
                $res = Db::table('yp_member')->where(['telephone'=>$phone])->field(['status,create_time'])->select();
                foreach ($res as $k => $val){
                    $res[$k]['status'] = 1;
                    $res[$k]['create_time'] = $val['create_time'];
                }
                return $res;
            }else{
                //设置数据
                $data = [
                    'telephone' => $telephone,
                    'status'=>0,
                    'create_time' => date('Y-m-d H:i:s',time()),
                    'last_login_time' => date('Y-m-d H:i:s',time())
                ];
                //添加数据
                Db::table('yp_register')->insert($data);
                $aa = [];
                $a []= Db::table('yp_register')->where(['telephone'=>$telephone])->find();
                foreach ($a as $v)
                {
                    $aa = [
                        'telephone'=> $v['telephone'],
                        'status'=> $v['status'],
                        'bis_id'=>1,
                        'create_time'=> $v['create_time'],
                        'last_login_time'=> $v['last_login_time'],
                    ];
                    Db::table('yp_member')->insert($aa);
                    $res = Db::table('yp_member')->where(['telephone'=>$telephone])->field(['status,create_time'])->select();
                    Db::table('yp_register')->where(['telephone'=>$telephone])->delete($a);
                    //调用第三方接口
                    $smsapi = "https://api.smsbao.com/";
                    //短信平台帐号
                    $user = "dalaa";
                    //短信平台密码
                    $pass = md5('123456');
                    //随机生成的验证码
                    $code=rand(111111,999999);
                    //获取手机号
                    $phone = $telephone;
                    //要发送的短信内容
                    $content="尊敬的客户您好，您的验证码为".$code.'【一盆】';
                    //发送短信
                    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
                    file_get_contents($sendurl);
                    $yan = [
                        'newcode'=>$code,
                    ];
                    $wher = "telephone='$phone' && 'status'=0";
                    $rs = Db::table('yp_member')->where($wher)->update($yan);
                    foreach ($res as $k => $val){
                        $res[$k]['status'] = 1;
                        $res[$k]['create_time'] = $val['create_time'];
                    }
                    return $res;
                }
                exit();
            }
        }
    }


    //登录
    public function login($par)
    {
        //获取参数
        $telephone = !empty($par['telephone']) ? $par['telephone'] : '';
        $newcode = !empty($par['newcode']) ? $par['newcode'] : '';
        $where = [
            'telephone'=>$telephone,
            'newcode'=>$newcode
        ];

        $tab = Db::table('yp_member')->where($where)->select();
        if (!$tab)
        {
            echo json_encode([
                'status'=>0,
                'message'=>'登录失败,您的验证码输入错误'
            ]);
            exit();
        }
        $ta = [];
        foreach ($tab as $v)
        {
            $ta = $v['code_url'];
        }
        if (!empty($ta))
        {
                $res = Db::table('yp_member')->where($where)->select();
                $index = 0;
                $result= [];
                foreach($res as $val){
                    $result[$index]['bis_id'] = $val['bis_id'];
                    $result[$index]['truename'] = $val['truename'];
                    $result[$index]['code_url'] = $val['code_url'];
                    $result[$index]['telephone'] = $val['telephone'];
                    $result[$index]['create_time'] = $val['create_time'];
                    $result[$index]['token'] = md5($val['bis_id'].$val['truename'].
                        rand(1111,9999).$val['telephone'].
                        date('Y-m-d H:i:s',time()));
                    $index ++;
            }
                $status = [
                    'status'=>1,
                    'bis_id'=>1,
                    'province_id'=>'北京',
                    'city_id'=>'海淀区',
                    'address'=>'八家嘉园',
                    'token'=>$result[0]['token'],
                    'last_login_time'=>date('Y-m-d H:i:s',time())
                ];
                $token = $status['token'];
                        $user = Db::table('yp_member')->where($where)->field('token')->find();
                        if ($token == $user)
                        {
                            echo json_encode([
                                'status'=>-1,
                                'message'=>'您以在别处登录'
                            ]);
                            exit();
                        }else{
                            $where = "telephone = '$telephone' && newcode ='$newcode'";
                            Db::table('yp_member')->where($where)->update($status);
                            $ta = Db::table('yp_member')->where($where)->field('bis_id,truename,code_url,telephone,code_url,create_time,token')->select();
                            return $ta;
                        }
        }else{
            //修改用户头像
            $a = [
                'code_url'=>'https://yp.dxshuju.com/api/public/static/img/touxiang.png'
            ];
            Db::table('yp_member')->where(['telephone'=>$telephone])->update($a);
            $res = Db::table('yp_member')->where($where)->select();
            $index = 0;
            $result= [];
            foreach($res as $val){
                $result[$index]['bis_id'] = $val['bis_id'];
                $result[$index]['username'] = $val['username'];
                $result[$index]['code_url'] = $val['code_url'];
                $result[$index]['telephone'] = $val['telephone'];
                $result[$index]['create_time'] = $val['create_time'];
                $result[$index]['token'] = md5($val['bis_id'].$val['username'].
                    rand(1111,9999).$val['telephone'].
                    date('Y-m-d H:i:s',time()));
                $index ++;

            }
            $status = [
                'status'=>1,
                'token'=>$result[0]['token'],
                'last_login_time'=>date('Y-m-d H:i:s',time())
            ];
            $token = $status['token'];
            $user = Db::table('yp_member')->where($where)->field('token')->find();
            if ($token == $user)
            {
                echo json_encode([
                    'status'=>-1,
                    'message'=>'您以在别处登录'
                ]);
                exit();
            }else{
                $where = "telephone = '$telephone' && newcode ='$newcode'";
                Db::table('yp_member')->where($where)->update($status);
                $ta = Db::table('yp_member')->where($where)->field('bis_id,username,code_url,telephone,code_url,create_time,token')->select();
                return $ta;
            }
        }
    }


    //获取个人信息接口
    //条件    telephone token
    //接口地址     https://yp.dxshuju.com/user/api/public/member/selectMember
    public function selectInfo($telephone,$token)
    {
        $where = "telephone = '$telephone' and token ='$token'";
        $res = Db::table('yp_member')
            ->where($where)
            ->field('username,nickname,truename,type,email,id_number,sex,age,qq,jifen,code_url')
            ->select();
        return $res;
    }


    //修改数据
    //条件    telephone
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/updateMember
    public function updateMember($param)
    {
        //获取参数
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '';
        $token = !empty($param['token']) ? $param['token'] : '';
        $where = "telephone = '$telephone' && token = '$token'";
        //查询用户信息
        $shu = Db::table('yp_member')->where($where)->select();
        if (!$shu)
        {
            echo json_encode([
                'status'=>-1,
                'message'=>'获取数据信息失败'
            ]);
            exit();
        }else{
            $data = [];
            //修改图片信息
            //上传文件
            $file = request()->file('code_url');
            if (empty($file)){
                unset($data['code_url']);
            }else{
                //// 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH.'public'.DS.'img');

                //判断是否上传
                if ($info){
                    // 成功上传后 获取上传信息
                    $data['code_url'] = $info->getSaveName();
                    $tu = 'http://39.105.68.130:81/api/public/img/'.str_replace('\\','/',$data['code_url']);

                }else{
                    //上传失败获取错误信息
                    echo $info->getError();
                }
                $tu = 'http://39.105.68.130:81/api/public/img/'.str_replace('\\','/',$data['code_url']);
                $data = [
                    'code_url'=>$tu
                ];

            }
            //验证所修改的数据
            $rules = [
                //            'age'=>'require|number',
                //            'email'=>'require|email'
            ];
            $validate = new Validate($rules);
            $re = $validate->check($data);
            Db::table('yp_member')->where($where)->update($_POST);
            Db::table('yp_member')->where($where)->update($data);
            if ($re){
                $res = Db::table('yp_member')->where(['telephone'=>$telephone])->select();
                $index = 0;
                $result= [];
                foreach($res as $val){
                    $result[$index]['username'] = $val['username'];
                    $result[$index]['nickname'] = $val['nickname'];
                    $result[$index]['truename'] = $val['truename'];
                    $result[$index]['sex'] = $val['sex'] == 1 ? '男' : '女' ;
                    $result[$index]['type'] = $val['type'] == 0 ? '普通会员' : 'VIP会员';
                    $result[$index]['email'] = $val['email'];
                    $result[$index]['age'] = $val['age'];
                    $result[$index]['team_status'] = $val['team_status'] == 1 ? '加入组织' : '未加入组织';
                    $result[$index]['address'] = $val['province_id'].$val['city_id'];
                    $result[$index]['create_time'] = $val['create_time'];
                    $result[$index]['last_login_time'] = date('Y-m-d H:i:s',time());
                    $result[$index]['is_fenxiao'] = $val['is_fenxiao'] == 1 ? '是分销会员' : '不是分销会员';
                    $result[$index]['code_url'] = $val['code_url'];
                    $result[$index]['token'] = $val['token'];
                    $index ++;
                }
                return $result;
            }else{
                echo json_encode([
                    'status'=>0,
                    'message'=>'修改数据失败'
                ]);
                exit();
            }
        }
    }

    //上传图片
    //条件    code_url
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/fileTop
    public function fileTop($param)
    {
        //根据手机号查询数据
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '' ;
        $token = !empty($param['token']) ? $param['token'] : '' ;
        $where = "telephone = '$telephone' && token = '$token'";
        $n = Db::table('yp_member')->where($where)->select();
       if ($n){
           //上传文件
           $file = request()->file('code_url');
           //移动到框架应用根目录/public/uploads/ 目录下
           $info = $file->move(ROOT_PATH.'public'.DS.'img');

           //判断是否上传
           if ($info){
               // 成功上传后 获取上传信息
               $data['code_url'] = $info->getSaveName();

           }else{
               //上传失败获取错误信息
               echo $info->getError();
           }
           $tu = str_replace('\\','/',$data['code_url']);
           $data = [
               'code_url'=>$tu
           ];

           //将上传文件的路径添加到数据库中
            Db::table('yp_member')->where($where)->update($data);
           //返回路径
           $result = 'http://39.105.68.130:81/api/public/img/'.$tu;
           return $result;
       }else{
           echo  json_encode([
               'status'=>0,
               'message'=>'图片上传失败'
           ]);
           exit();
       }
    }


    //省信息获取
    //方式    $_GET
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/getProvince
    public function getProvince()
    {
        //$province,$cite,$area
//        $por = !empty($province['provinceid']) ? $province['provinceid'] : '';
//        $por = !empty($province['provinceid']) ? $province['provinceid'] : '';
//        $ci = !empty($province['cityid']) ? $province['cityid'] : '';
//        $por = !empty($province['areaid']) ? $province['areaid'] : '';
        $shen = Db::table('yp_provinces')->field('provinceid,province')->select();
        foreach ($shen as $k=>$v)
        {
            $shen[$k]['provinceid']=$v['provinceid'];
            $shen[$k]['province']=$v['province'];
        }
        return $shen;

    }

    //市区信息获取
    //方式    $_POST
    //条件    provinceid
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/getCityArea
    public function getCityArea($param)
    {
        $pro = !empty($param['provinceid']) ? $param['provinceid'] : '';
        $where = "citie.provinceid = '$pro'";
        $tab = Db::table('yp_provinces')->alias('provin')
            ->field('provin.provinceid,provin.province,citie.cityid,citie.city,are.areaid,are.area')
            ->join('yp_cities citie','citie.provinceid = provin.provinceid','LEFT')
            ->join('yp_areas are','are.cityid = citie.cityid')
            ->where($where)
            ->select();
        foreach ($tab as $k=>$v)
        {
            $tab[$k]['provinceid']=$v['provinceid'];
            $tab[$k]['province']=$v['province'];
            $tab[$k]['cityid']=$v['cityid'];
            $tab[$k]['city']=$v['city'];
            $tab[$k]['areaid']=$v['areaid'];
            $tab[$k]['area']=$v['area'];
        }
        return $tab;
    }

    //添加分期信息接口
    //条件    telephone （用户手机号） token     phone   申请手机号
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/addStage
    public function addStage($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '' ;
        $token = !empty($param['token']) ? $param['token'] : '' ;
        $phone =$param['phone'];

        $where = [
            'telephone'=>$telephone,
            'token'=>$token
        ];
        $fen = Db::table('yp_member')->where($where)->select();
        $data = [];
        foreach($fen as $v)
        {
            $fen['telephone'] = $v['telephone'];
            $fen['truename'] = $v['truename'];
        }
        array_push($data,$fen['telephone'],$fen['truename']);
        $re = [
            'telephone'=>$data[0],
            'turename'=>$data[1],
            'phone'=>$phone,
            'start_time'=>date('Y-m-d H:i:s',time())
        ];

        if ($re['phone'] == '')
        {
            echo json_encode([
                'status'=>0,
                'message'=>'添加分期信息失败'
            ]);
            exit();
        }else{
            $res = Db::table('yp_stage')->insert($re);
            return $res;
        }

    }

    //获取分期记录接口
    //条件    telephone （用户手机号） token     phone   申请手机号
    // 接口地址     https://yp.dxshuju.com/api/public/user/member/stageInfo

    public function stageInfo($param)
    {
        $telephone = !empty($param['telephone']) ? $param['telephone'] : '' ;
        $token = !empty($param['token']) ? $param['token'] : '' ;
        $where = [
            'mem.telephone'=>$telephone,
            'mem.token'=>$token
        ];
        $res = Db::table('yp_member')->alias('mem')->field('sta.phone,sta.turename,sta.start_time')
            ->join('yp_stage sta','sta.telephone = mem.telephone')
            ->where($where)
            ->select();
        return $res;
    }

}