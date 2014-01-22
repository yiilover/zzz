<?php 
/**
* ZhiPHP 值得买模式的海淘网站程序
* ====================================================================
* 版权所有 杭州言商网络有限公司，并保留所有权利。
* 网站地址: http://www.zhiphp.com
* 交流论坛: http://bbs.pinphp.com
* --------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ====================================================================
* Author: brivio <brivio@qq.com>
* 授权技术支持: 1142503300@qq.com
*/
return array(
    'code'      => 'taobao',
    'name'      => '淘宝/天猫',
    'desc'      => '通过淘宝开放平台获取商品数据，可到 http://open.taobao.com/ 查看详细',
    'author'    => 'ZhiPHP TEAM',
    'domain'   => 'http://www.taobao.com,http://www.tianmao.com',
    'version'   => '1.0',
    'config'    => array(
        'app_key'   => array(        
            'text'  => 'App Key',
            'desc'  => '淘宝开放平台申请应用获取',
            'type'  => 'text',
        ),
        'app_secret'       => array(        
            'text'  => 'App Secret',
            'desc'  => '淘宝开放平台申请应用获取',
            'type'  => 'text',
        )
    )
);