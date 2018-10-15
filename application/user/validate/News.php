<?php
namespace app\admin\validate;
use think\Validate;
class News extends Validate{

    //定义规则
    protected $rule = [
        ['news_id','number', '新闻序号必须为数字'],
        ['theme','require', '新闻主题必须填写'],
        ['content','require', '新闻内容必须填写'],

    ];


    //场景设置
    protected $scene = [
        'add'  =>  ['theme','content'],//添加
        'update'   => ['news_id','theme','content'],//修改
    ];

}
?>