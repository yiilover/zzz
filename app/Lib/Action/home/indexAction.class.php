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
class indexAction extends frontendAction {     
    public function index() {
        $this->_assign_hot_list();
        $this->_assign_recommend_list();
        $where=array();
        ($keyword=$this->_get('keyword','trim'))&&$where['title']=array('like',"%$keyword%");
        if(empty($keyword)){
            $this->_config_seo(C('pin_seo_config.home'));
        }else{
            $this->_config_seo(C('pin_seo_config.search'),array('keyword'=>$keyword));
        }
        $this->assign('search',array(
            'keyword'=>$keyword
        ));
        $where['zhi_post.post_time'] = array('elt',time());
        $where['zhi_post.status'] = 1;
//        $this->_waterfall(D("post"),$where,'post_time desc');
        $this->_assign_list(D("post"),$where,9);
        $this->display();
    }
    public function go(){        
        $id=$this->_get('id','intval');
        $url=trim(D("post")->where("id=$id")->getField("url"));        
        if(!empty($url)){
            header("Location:$url");
        }else{
            $this->error("为提供商品直达链接",U("index/index"));
        }
    }        
}