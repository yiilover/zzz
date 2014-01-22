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
class mobileAction extends baseAction {
    var $pageIndex=0;
    var $pageSize=20;
    var $client_info;
    protected function _initialize() {
        $this->pageIndex=$this->_request('pageIndex','intval',0);
        $pageSize=$this->_request('pageSize','intval',20);
        parent::_initialize();
        $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
        $this->client_info=array(
            'app_version'=>$user_agent[1],
            'platform'=>strtolower($user_agent[2]),
            'platform_version'=>$user_agent[3],
        );
    }       
    protected function response($data){
        header("Content-Type: text/html; charset=utf-8");
        exit(json_encode($data));
    }
    protected function error($msg){
        $this->response(array('error_code'=>0,'error_message'=>$msg));
    }
    protected function success($msg){
        $this->response(array('error_code'=>1,'error_message'=>$msg));
    }
}