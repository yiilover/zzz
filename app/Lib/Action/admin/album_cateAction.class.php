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
class album_cateAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_cate_mod = D('album_cate');
    }
    public function _before_index() {
        $big_menu = array(
            'title' => L('添加分类'),
            'iframe' => U('album_cate/add'),
            'id' => 'add',
            'width' => '550',
            'height' => '300'
        );
        $this->assign('big_menu', $big_menu);
        $this->sort = 'ordid';
        $this->order = 'ASC';
    }
    public function ajax_upload_img() {
        if (!empty($_FILES['img']['name'])) {
            $result = $this->_upload($_FILES['img'], 'album_cate', array('width'=>'80', 'height'=>'80'));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $data['img']);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    public function _before_delete() {
        $ids = trim($this->_request('id'), ',');
        $ids_arr = explode(',', $ids);
        foreach ($ids_arr as $val) {
            if (M('album')->where(array('cate_id'=>$val))->count()) {
                IS_AJAX && $this->ajaxReturn(0, '分类下面存在数据，不能删除！');
                $this->error('分类下面存在数据，不能删除！');
            }
        }
    }
    public function ajax_check_name() {
        $name = $this->_get('name', 'trim');
        $id = $this->_get('id', 'intval');
        if (D('album_cate')->name_exists($name, $id)) {
            $this->ajaxReturn(0,'此分类已存在！');
        } else {
            $this->ajaxReturn(1);
        }
    }
}