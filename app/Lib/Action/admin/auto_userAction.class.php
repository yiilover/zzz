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
class auto_userAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = M('auto_user');
    }
    public function _before_index() {
        $big_menu = array(
            'title' => L('添加马甲'),
            'iframe' => U('auto_user/add'),
            'id' => 'add',
            'width' => '400',
            'height' => '170'
        );
        $this->assign('big_menu', $big_menu);
    }
    protected function _search() {
        $map = array();
        ($keyword = $this->_request('keyword', 'trim')) && $map['name'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'keyword' => $keyword,
        ));
        return $map;
    }
    public function ajax_check_name() {
        $name = $this->_get('name', 'trim');
        $id = $this->_get('id', 'intval');
        if (D('auto_user')->name_exists($name, $id)) {
            $this->ajaxReturn(0, L('该名称已存在'));
        } else {
            $this->ajaxReturn(1);
        }
    }
}