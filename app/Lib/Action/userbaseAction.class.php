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
class userbaseAction extends frontendAction {
    var $uid;
    public function _initialize(){
        parent::_initialize();
        if (!$this->visitor->is_login && !in_array(ACTION_NAME, array('login', 'register', 'binding', 'ajax_check'))) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'));
            $this->redirect('user/login');
        }        
        $this->_curr_menu(ACTION_NAME);
        $this->uid=$this->visitor->info['id'];
    }
    protected function _curr_menu($menu = 'index') {
        $menu_list = $this->_get_menu();
        $this->assign('user_menu_list', $menu_list);
        $this->assign('user_menu_curr', $menu);
    }
    private function _get_menu() {
        $menu = array();
        $menu = array(
            'setting' => array(
                'text' => '帐号设置',
                'submenu' => array(
                    'index' => array('text'=>'基本信息', 'url'=>U('user/index')),
                    'password' => array('text'=>'修改密码', 'url'=>U('user/password')),
                    'bind' => array('text'=>'帐号绑定', 'url'=>U('user/bind')),
                    'custom' => array('text'=>'个人封面', 'url'=>U('user/custom')),
                    'address' => array('text'=>'收货地址', 'url'=>U('user/address')),
                )
            ),
            'score' => array(
                'text' => '积分帐户',
                'submenu' => array(
                    'order' => array('text'=>'积分订单', 'url'=>U('score/index')),
                    'logs' => array('text'=>'积分记录', 'url'=>U('score/logs')),
                )
            ),
            'message' => array(
                'text' => '消息中心',
                'submenu' => array(
                    'message' => array('text'=>'我的私信', 'url'=>U('message/index')),
                    'system' => array('text'=>'系统通知', 'url'=>U('message/system')),
                )
            )
        );
        return $menu;
    }
}