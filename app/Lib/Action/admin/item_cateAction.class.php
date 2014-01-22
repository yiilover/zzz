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
class item_cateAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('item_cate');
    }
    public function index() {
        $sort = $this->_get("sort", 'trim', 'ordid');
        $order = $this->_get("order", 'trim', 'ASC');
        $tree = new Tree();
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = $this->_mod->order($sort . ' ' . $order)->select();
        $array = array();
        foreach($result as $r) {
            $r['str_img'] = $r['img'] ? '<span class="img_border"><img src="'.attach($r['img'], 'item_cate').'" style="width:26px; height:26px;" class="J_preview" data-bimg="'.attach($r['img'], 'item_cate').'" /></span>' : '';
            $r['str_status'] = '<img data-tdtype="toggle" data-id="'.$r['id'].'" data-field="status" data-value="'.$r['status'].'" src="__STATIC__/images/admin/toggle_' . ($r['status'] == 0 ? 'disabled' : 'enabled') . '.gif" />';
            $r['str_index'] = '<img data-tdtype="toggle" data-id="'.$r['id'].'" data-field="is_index" data-value="'.$r['is_index'].'" src="__STATIC__/images/admin/toggle_' . ($r['is_index'] == 0 ? 'disabled' : 'enabled') . '.gif" />';
            $r['str_type'] = $r['type'] ? '<span class="gray">'.L('item_cate_type_tag').'</span>' : L('item_cate_type_cat');
            $r['str_manage'] = '<a href="javascript:;" class="J_showdialog" data-uri="'.U('item_cate/add',array('pid'=>$r['id'])).'" data-title="'.L('add_item_cate').'" data-id="add" data-width="520" data-height="360">'.L('add_item_subcate').'</a> |
                                <a href="'.U('item_cate/tag_list',array('cate_id'=>$r['id'])).'">'.L('tag').'</a> |
                                <a href="javascript:;" class="J_showdialog" data-uri="'.U('item_cate/edit',array('id'=>$r['id'])).'" data-title="'.L('edit').' - '. $r['name'] .'" data-id="edit" data-width="520" data-height="360">'.L('edit').'</a> |
                                <a href="javascript:;" class="J_confirmurl" data-acttype="ajax" data-uri="'.U('item_cate/delete',array('id'=>$r['id'])).'" data-msg="'.sprintf(L('confirm_delete_one'),$r['name']).'">'.L('delete').'</a>';
            $r['parentid_node'] = ($r['pid'])? ' class="child-of-node-'.$r['pid'].'"' : '';
            $array[] = $r;
        }
        $str  = "<tr id='node-\$id' \$parentid_node>
                <td align='center'><input type='checkbox' value='\$id' class='J_checkitem'></td>
                <td align='center'>\$id</td>
                <td>\$spacer<span data-tdtype='edit' data-field='name' data-id='\$id' class='tdedit'  style='color:\$fcolor'>\$name</span></td>
                <td align='center'>\$str_img</td>
                <td align='center'>\$str_type</td>
                <td align='center'><span data-tdtype='edit' data-field='ordid' data-id='\$id' class='tdedit'>\$ordid</span></td>
                <td align='center'>\$str_index</td>
                <td align='center'>\$str_status</td>
                <td align='center'>\$str_manage</td>
                </tr>";
        $tree->init($array);
        $list = $tree->get_tree(0, $str);
        $this->assign('list', $list);
        $big_menu = array(
            'title' => L('add_item_cate'),
            'iframe' => U('item_cate/add'),
            'id' => 'add',
            'width' => '520',
            'height' => '360'
        );
        $this->assign('big_menu', $big_menu);
        $this->assign('list_table', true);
        $this->display();
    }
    public function _before_add() {
        $pid = $this->_get('pid', 'intval', 0);
        if ($pid) {
            $spid = $this->_mod->where(array('id'=>$pid))->getField('spid');
            $spid = $spid ? $spid.$pid : $pid;
            $this->assign('spid', $spid);
        }
    }
    protected function _before_insert($data = '') {
        if($this->_mod->name_exists($data['name'], $data['pid'])){
            $this->ajaxReturn(0, L('item_cate_already_exists'));
        }
        $data['spid'] = $this->_mod->get_spid($data['pid']);
        return $data;
    }
    protected function _before_update($data = '') {
        if ($this->_mod->name_exists($data['name'], $data['pid'], $data['id'])) {
            $this->ajaxReturn(0, L('item_cate_already_exists'));
        }
        $item_cate = $this->_mod->field('img,pid')->where(array('id'=>$data['id']))->find();
        if ($data['pid'] != $item_cate['pid']) {
            $wp_spid_arr = $this->_mod->get_child_ids($data['id'], true);
            if (in_array($data['pid'], $wp_spid_arr)) {
                $this->ajaxReturn(0, L('cannot_move_to_child'));
            }
            $data['spid'] = $this->_mod->get_spid($data['pid']);
        }
        return $data;
    }
    public function move() {
        if (IS_POST) {
            $data['pid'] = $this->_post('pid', 'intval');
            $ids = $this->_post('ids');
            $target_spid = $this->_mod->where(array('id'=>$data['pid']))->getField('spid');
            $ids_arr = explode(',', $ids);
            foreach ($ids_arr as $id) {
                if (false !== strpos($target_spid . $data['pid'].'|', $id.'|')) {
                    $this->ajaxReturn(0, L('cannot_move_to_child'));
                }
            }
            $data['spid'] = $this->_mod->get_spid($data['pid']);
            $this->_mod->where(array('id' => array('in', $ids)))->save($data);
            $this->ajaxReturn(1, L('operation_success'), '', 'move');
        } else {
            $ids = trim($this->_request('id'), ',');
            $this->assign('ids', $ids);
            $resp = $this->fetch();
            $this->ajaxReturn(1, '', $resp);
        }
    }
    public function tag_list() {
        $cate_id = $this->_get('cate_id', 'intval');
        $keyword = $this->_get('keyword', 'trim');
        $cate_tag_mod = M('item_cate_tag');
        $db_pre = C('DB_PREFIX');
        $table = $db_pre.'item_cate_tag';
        $pagesize = 20;
        $map = array($table.'.cate_id'=>$cate_id);
        $keyword && $map['t.name'] = array('like', '%'.$keyword.'%');
        $join = $db_pre.'tag t ON t.id = '.$table.'.tag_id';
        $count = $cate_tag_mod->where($map)->join($join)->count();
        $pager = new Page($count, $pagesize);
        $list = $cate_tag_mod->field('t.id,t.name,weight')->where($map)->join($join)->limit($pager->firstRow.','.$pager->listRows)->select();
        $cate_name = $this->_mod->get_name($cate_id); 
        $this->assign('list', $list);
        $this->assign('page', $pager->show());
        $this->assign('cate_id', $cate_id);
        $this->assign('cate_name', $cate_name);
        $this->assign('list_table', true);
        $this->display();
    }
    public function ajax_tag_edit() {
        $tag_id = $this->_get('id', 'intval');
        $cate_id = $this->_get('cate_id', 'intval');
        if (!$cate_id && !$tag_id) {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
        $weight = $this->_get('val', 'intval', 0);
        M('item_cate_tag')->where(array('cate_id'=>$cate_id, 'tag_id'=>$tag_id))->save(array('weight'=>$weight));
        $this->ajaxReturn(1);
    }
    public function tag_search() {
        $tag_mod = D('tag');
        $keywords = $this->_get('keywords', 'trim');
        $cate_id = $this->_get('cate_id', 'intval');
        $map = array();
        $keywords && $map['name'] = array('like', '%'.$keywords.'%');
        if ($cate_id) {
            $noids = $this->_mod->get_tag_ids($cate_id);
            $noids && $map['id'] = array('not in', $noids);
        }
        $data = $tag_mod->where($map)->limit('0,60')->select();
        $this->ajaxReturn(1, '', $data);
    }
    public function tag_add() {
        if (IS_POST) {
            $cate_id = $this->_post('cate_id', 'intval');
            !$cate_id && $this->ajaxReturn(0, L('illegal_parameters'));
            $tag_ids = $this->_post('tag_ids', 'trim');
            $custom_tags = $this->_post('custom_tags', 'trim');
            $tag_ids_arr = array();
            if ($tag_ids) {
                $tag_ids = substr($tag_ids, 1);
                $tag_ids_arr = explode('|', $tag_ids);
            }
            if ($custom_tags) {
                $tag_mod = M('tag');
                $custom_tags_arr = explode(',', $custom_tags);
                foreach ($custom_tags_arr as $val) {
                    $tag_id = $tag_mod->where("name='".$val."'")->getField('id');
                    if (!$tag_id) {
                        $tag_id = $tag_mod->add(array('name' => $val,));
                    }
                    if ($tag_id) {
                        $tag_ids_arr[] = $tag_id;
                    }
                }
            }
            $cate_tag_mod = M('item_cate_tag');
            $cate_tag_mod->where(array('cate_id'=>$cate_id))->delete();
            foreach ($tag_ids_arr as $val) {
                $cate_tag_mod->add(array(
                    'cate_id' => $cate_id,
                    'tag_id' =>$val
                ));
            }
            $this->ajaxReturn(1, L('operation_success'), '', 'tag_add');
        } else {
            $cate_id = $this->_get('cate_id', 'intval');
            $this->assign('cate_id', $cate_id);
            $resp = $this->fetch();
            $this->ajaxReturn(1, '', $resp);
        }
    }
    public function tag_delete() {
        $cate_tag_mod = M('item_cate_tag');
        $cate_id = $this->_get('cate_id', 'intval');
        $ids = trim($this->_get('id'), ',');
        if ($ids) {
            $map = array('cate_id'=>$cate_id, 'tag_id'=>array('in', $ids));
            $cate_tag_mod->where($map)->delete();
            $this->ajaxReturn(1, L('operation_success'));
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    public function ajax_getchilds() {
        $id = $this->_get('id', 'intval');
        $type = $this->_get('type', 'intval', null);
        $map = array('pid'=>$id);
        if (!is_null($type)) {
            $map['type'] = $type;
        }
        $return = $this->_mod->field('id,name')->where($map)->select();
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $return);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
    public function ajax_upload_img() {
        if (!empty($_FILES['img']['name'])) {
            $result = $this->_upload($_FILES['img'], 'item_cate', array(
                    'width' => C('pin_itemcate_img.width'),
                    'height' => C('pin_itemcate_img.height'),
                    'remove_origin' => true,
                )
            );
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
}