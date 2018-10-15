<?php
namespace app\ka\model;
use think\Model;
use think\Db;

class Products extends Model{
    //获取推荐商品列表
    public function getRecommendProInfo($bis_id){
        $res = Db::table('ka_card')
                ->where('bis_id = '.$bis_id)
                ->order('id desc')
                ->limit(6)
                ->select();
        return $res;
    }

    //获取新品列表
    public function getNewProInfo($bis_id){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price')
                ->join('store_pro_images i','pro.id = i.p_id','LEFT')
                ->where('pro.bis_id = '.$bis_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
                ->order('pro.create_time desc')
                ->limit(8)
                ->select();

        $count = Db::table('store_products')->alias('pro')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.bis_id = '.$bis_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
            ->count();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }
        $new_count = ceil($count / 2);
        $new_count = $new_count > 4 ? 4 : $new_count;
        $new_res = array();

        for($i = 0; $i < $new_count; $i ++){
            for($j=0;$j<2;$j++){
                $new_num = $i*2+$j;
                if($new_num < $count){
                    $new_res[$i][] = $res[$new_num];
                }else{
                    break;
                }

            }
        }

        return $new_res;
    }

    //获取商品详情
    public function getProDetail($pro_id){
        $res = Db::table('ka_card')->alias('pro')->field('pro.id as pro_id,pro.card_name,pro.image,pro.config_image1,pro.config_image2,pro.config_image3,pro.config_image4,pro.config_image5,pro.money')
                ->where('id = '.$pro_id)
                ->find();


        //设置详情页轮播图
        $images_info = array();
        array_push($images_info,$res['image']);
        if($res['config_image1'] && $res['config_image1'] != ''){
            array_push($images_info,$res['config_image1']);
        }
        if($res['config_image2'] && $res['config_image2'] != ''){
            array_push($images_info,$res['config_image2']);
        }
        if($res['config_image3'] && $res['config_image3'] != ''){
            array_push($images_info,$res['config_image3']);
        }
        if($res['config_image4'] && $res['config_image4'] != ''){
            array_push($images_info,$res['config_image4']);
        }
        if($res['config_image5'] && $res['config_image5'] != ''){
            array_push($images_info,$res['config_image5']);
        }
       


        $result = array();
        $result = [
            'pro_id' => $res['pro_id'],
            'card_name' => $res['card_name'],
            'images'  => $images_info,
            'ka_money'  => $this->getMoney($res['money']),
        ];

        return $result;
    }

    public function getMoney($money){
        $res = Db::table('ka_money')
                ->where('id','in',$money)
                ->select();

        $index = 0;
        foreach ($res as $val) {
            $res[$index]['count'] = 0;
            $res[$index]['status'] = "disabled";
            $index ++;
        }

        return $res;
    }


    //根据一级分类id获取商品信息
    public function getProInfoByFirstId($param){
        //获取参数
        $cat1_id = $param['cat_id'];
        $page = !empty($param['page']) ? $param['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $pricestatus = !empty($param['pricestatus']) ? $param['pricestatus'] : '';
        $soldcount = !empty($param['soldcount']) ? $param['soldcount'] : '';
        $order = "pro.update_time desc";
        if($pricestatus && $pricestatus != ''){
            if($pricestatus == 'up'){
                $order = "pro.associator_price asc, pro.update_time desc";
            }else{
                $order = "pro.associator_price desc, pro.update_time desc";
            }

        }
        if($soldcount && $soldcount != ''){
            if($soldcount == 'up'){
                $order = "pro.sold asc, pro.update_time desc";
            }else{
                $order = "pro.sold desc, pro.update_time desc";
            }

        }
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.defined_cat1_id = '.$cat1_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
            ->order($order)
//            ->limit($offset,$limit)
            ->select();
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }
        return $res;
    }

    //根据二级分类id获取商品信息
    public function getProInfoBySecondId($param){
        //获取参数
        $cat_id = !empty($param['cat_id']) ? $param['cat_id'] : '';
        $cat2_id = !empty($param['cat2_id']) ? $param['cat2_id'] : '';
        if($cat_id){
            $con = "pro.defined_cat1_id = ".$cat_id;
        }
        if($cat2_id){
            $con = "pro.defined_cat2_id = ".$cat2_id;
        }
        $order = "pro.update_time desc";
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where($con.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
            ->order($order)
//            ->limit(10)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }
        return $res;
    }

    //获取商品配置信息
    public function getProConfigInfo($pro_id){
        $pro_res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,pro.brand,i.thumb,pro.original_price,pro.associator_price')
                ->join('store_pro_images i','pro.id = i.p_id','LEFT')
                ->where('pro.id = '.$pro_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
                ->find();


        //查询规格信息
        $org_guige_info = DB::table('store_pro_config')
            ->field('id as con_id,content1_name,con_content1,content2_name,con_content2')
            ->where('pro_id = '.$pro_res['pro_id'].' and status = 1')
            ->select();

        //设置规格数组内容
        $config1_info_array = array();
        $temp_config1_info_array = array();
        $config2_info_array = array();
        $temp_config2_info_array = array();
        $config1_info_array['content1_name'] = $org_guige_info[0]['content1_name'];
        $config2_info_array['content2_name'] = $org_guige_info[0]['content2_name'];
        foreach($org_guige_info as $val){
            if(!in_array($val['con_content1'],$temp_config1_info_array)){
                $temp_config1_info_array[] = $val['con_content1'];
            }
            if(!in_array($val['con_content2'],$temp_config2_info_array)){
                $temp_config2_info_array[] = $val['con_content2'];
            }
        }
        $config1_info_count = count($temp_config1_info_array);
        $config2_info_count = count($temp_config2_info_array);

        $new_count1 = ceil($config1_info_count / 5);
        $new_count2 = ceil($config2_info_count / 5);

        $new_res1 = array();
        $new_res2 = array();
        for($i = 0; $i < $new_count1; $i ++){
            for($j=0;$j<5;$j++){
                $new_num = $i*5+$j;
                if($new_num < $config1_info_count){
                    $new_res1[$i][] = $temp_config1_info_array[$new_num];
                }else{
                    break;
                }

            }
        }

        for($i = 0; $i < $new_count2; $i ++){
            for($j=0;$j<5;$j++){
                $new_num = $i*5+$j;
                if($new_num < $config2_info_count){
                    $new_res2[$i][] = $temp_config2_info_array[$new_num];
                }else{
                    break;
                }

            }
        }

        $config1_info_array['con_content1'] = $new_res1;
        $config2_info_array['con_content2'] = $new_res2;

        $result = [
            'pro_id' => $pro_res['pro_id'],
            'p_name' => $pro_res['p_name'],
            'brand' => $pro_res['brand'],
            'thumb' => $pro_res['thumb'],
            'original_price' => $pro_res['original_price'],
            'associator_price' => $pro_res['associator_price'],
            'config1_info' => $config1_info_array
        ];
        return $result;
    }

    //根据一级名称&商品id获取二级配置信息
    public function getConfig2InfoById($pro_id,$con_info){
        $where = "pro_id = ".$pro_id." and con_content1 = '$con_info' and status = 1";
        $res = DB::table('store_pro_config')
            ->field('id as con_id,content2_name,con_content2')
            ->where($where)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }

        $temp_res = array();
        $temp_res['content_name'] = $res[0]['content2_name'];
        $config_info_count = count($res);

        $new_count = ceil($config_info_count / 5);

        $new_res = array();
        for($i = 0; $i < $new_count; $i ++){
            for($j=0;$j<5;$j++){
                $new_num = $i*5+$j;
                if($new_num < $config_info_count){
                    $new_res[$i][$j]['con_id'] = $res[$new_num]['con_id'];
                    $new_res[$i][$j]['con_content'] = $res[$new_num]['con_content2'];
                }else{
                    break;
                }

            }
        }
        $temp_res['con_content'] = $new_res;
        return $temp_res;
    }
}
