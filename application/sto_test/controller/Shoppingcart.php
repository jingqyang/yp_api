<?php
namespace app\sto_test\controller;
use think\Controller;
use think\Db;

class Shoppingcart extends Controller{

    //添加商品到购物车
    public function addProIntoCart(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->addProIntoCart($param);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }

    //获取购物车信息
    public function getShoppingCartInfo(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->getShoppingCartInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res

        ));
        exit;
    }

    //更改单条信息选中状态
    public function updateSelectedStatus(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateSelectedStatus($param);
        echo json_encode(array(
            'statuscode'  => 1

        ));
        exit;
    }

    //获取选中的价格信息
    public function getSelectedTotalPrice(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->getSelectedTotalPrice($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res

        ));
        exit;
    }

    //更改全部选中状态
    public function updateAllSelectedStatus(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateAllSelectedStatus($param);
        echo json_encode(array(
            'statuscode'  => 1

        ));
        exit;
    }

    //更改单个商品选中数量
    public function updateSelectedCount(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateSelectedCount($param);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }

    //点击"去结算"返回选中的购物车内信息
    public function getSelectedCartInfo(){
        $wx_id = input('post.openid');
        $res = model('ShoppingCart')->getSelectedCartInfo($wx_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //判断购物车内是否存在勾选的产品
    public function checkSelectedPro(){
        $wx_id = input('post.openid');
        $res = model('ShoppingCart')->checkSelectedPro($wx_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据id删除购物车
    public function deleteCartById(){
        $cart_id = input('post.cart_id');
        $data['status'] = -1;
        $res = Db::table('store_shopping_carts')->where('id = '.$cart_id)->update($data);
        if($res){
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }
    }
}



