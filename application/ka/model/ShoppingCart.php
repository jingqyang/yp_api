<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ShoppingCart extends Model{
    //添加商品到购物车
    public function addProIntoCart($param){
        //获取参数
        $pro_id = !empty($param['pro_id']) ? $param['pro_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $count = !empty($param['count']) ? $param['count'] : '';
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';

        $data = [
            'bis_id'  => $bis_id,
            'pro_id'  => $pro_id,
            'wx_id'  => $wx_id,
            'count'  => $count,
            'create_time'  => date('Y-m-d H:i:s'),
            'update_time'  => date('Y-m-d H:i:s'),
        ];

        $res = Db::table('store_shopping_carts')->insert($data);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加失败'
            ));
            exit;
        }

        return $res;
    }

    //获取购物车信息
    public function getShoppingCartInfo($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $where = "carts.bis_id = ".$bis_id." and carts.wx_id = '$wx_id' and carts.status = 1 and con.status = 1 and img.status = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,pro.p_name,con.con_content1,con_content2,pro.associator_price,carts.count,carts.selected,img.thumb')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        foreach($res as $val){
            $res[$index]['selected'] = $val['selected']  == 1 ? true : false;
            $index ++;
        }
        return $res;
    }

    //更改单条信息选中状态
    public function updateSelectedStatus($param){
        //获取参数
        $cart_id = !empty($param['cart_id']) ? $param['cart_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $data['selected'] = $selected_status;
        $res = Db::table('store_shopping_carts')->where('id = '.$cart_id)->update($data);

        return $res;
    }

    //获取选中的价格信息
    public function getSelectedTotalPrice($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $where = "carts.bis_id = ".$bis_id." and carts.wx_id = '$wx_id' and carts.status = 1 and carts.selected = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where)
            ->SUM('pro.associator_price * carts.count');
        if(!$res){
            $res = 0;
        }

        return $res;
    }

    //更改全部选中状态
    public function updateAllSelectedStatus($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $data['selected'] = $selected_status;
        $where = "bis_id = ".$bis_id." and wx_id = '$wx_id'";
        $res = Db::table('store_shopping_carts')->where($where)->update($data);
        return $res;
    }

    //更改单个商品选中数量
    public function updateSelectedCount($param){
        //获取参数
        $cart_id = !empty($param['cart_id']) ? $param['cart_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $selectedcount = !empty($param['selectedcount']) ? $param['selectedcount'] : '';
        $type = !empty($param['type']) ? $param['type'] : '';

        //设置修改内容
        $con = '';
        if($selected_status && $selected_status == 1){
            $con = ",selected = 1";
        }

        if($type == 'sub'){
            $newselectedcount = $selectedcount - 1;
        }else{
            $newselectedcount = $selectedcount + 1;
        }

        $sql = "UPDATE store_shopping_carts SET count = ".$newselectedcount.$con." WHERE id = ".$cart_id;
        $res = Db::execute($sql);

        return $res;
    }

    //点击"去结算"返回选中的购物车内信息
    public function getSelectedCartInfo($wx_id){
        $where = "carts.wx_id = '$wx_id' and carts.status = 1 and con.status = 1 and img.status = 1 and carts.selected = 1";
        $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,con.con_content1,con_content2,pro.associator_price,carts.count,carts.selected,img.thumb')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();

        $total_amount = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,pro.p_name,con.con_content1,con_content2,pro.associator_price,carts.count,carts.selected,img.thumb')
                    ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
                    ->join('store_products pro','con.pro_id = pro.id','LEFT')
                    ->join('store_pro_images img','img.p_id = pro.id','LEFT')
                    ->where($where)
                    ->SUM('pro.associator_price * carts.count');

        $address_res = Db::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address')
                    ->where("mem_id = '$wx_id' and status = 1 and is_default = 1")
                    ->find();


        if(!$address_res){
            $address_array = array();
        }else{
            $address_array = [
                'address_id'  => $address_res['a_id'],
                'rec_name'  => $address_res['rec_name'],
                'mobile'  => $address_res['mobile'],
                'address'  => $address_res['province'].$address_res['city'].$address_res['area'].$address_res['address']
            ];
        }

        return array(
            'address_info' => $address_array,
            'pro_info' => $pro_res,
            'total_amount' => $total_amount
        );

    }

    //判断购物车内是否存在勾选的产品
    public function checkSelectedPro($wx_id){
        $where = "wx_id = '$wx_id' and selected = 1 and status = 1";
        $res = Db::table('store_shopping_carts')->where($where)->count();

        if($res > 0){
            return 1;
        }else{
            return 0;
        }
    }

}