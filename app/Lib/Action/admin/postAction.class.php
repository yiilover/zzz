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
class postAction extends backendAction {
    var $list_relation = true;
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('post');
        $this->_cate_mod = D('post_cate');
        $this->assign('img_dir', './data/upload/post/');
        $this->py = new cls_pinyin();
    }
    public function _before_index() {
        $res = D("mall")->field('id,title')->select();
        $mall_list = array();
        foreach ($res as $val) {
            $mall_list[$val['id']] = $val['title'];
        }
        $this->assign('mall_list', $mall_list);
        $this->sort = 'id';
        $this->order = 'desc';
    }
    protected function _search() {
        $map = array();
        $collect_flag = $this->_request('collect_flag', 'intval', 1);
        $map['collect_flag'] = $collect_flag;
        ($time_start = $this->_request('time_start', 'trim')) && $map['post_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['post_time'][] = array('elt', strtotime($time_end) + (24 * 60 * 60 - 1));
        $status = $this->_request('status');
        if ($status != null) {
            $map['status'] = $status;
        }
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%' . $keyword . '%');
        $cate_id = $this->_request('cate_id', 'intval');
        $selected_ids = '';
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $res = D("post_cate_re")->where("cate_id in(" . implode(',', $id_arr) . ")")->select();
            $ids = "0";
            foreach ($res as $val) {
                $ids .= "," . $val['post_id'];
            }
            $map['id'] = array('IN', $ids);
            $spid = $this->_cate_mod->where(array('id' => $cate_id))->getField('spid');
            $selected_ids = $spid ? $spid . $cate_id : $cate_id;
        }
        $mall_id = $this->_request('mall_id', 'intval');
        if ($mall_id > 0) {
            $map['mall_id'] = $mall_id;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'cate_id' => $cate_id,
            'selected_ids' => $selected_ids,
            'status' => $status,
            'keyword' => $keyword,
            'mall_id' => $mall_id,
            'collect_flag' => $collect_flag,
        ));
        return $map;
    }
    protected function _get_cate_tree($list, $checked_ids = array()) {
        $html = "";
        foreach ($list as $key => $val) {
            $margin_left = $val['depth'] * 20;
            $html .= "<div style='margin-left:" . $margin_left . "px;'>
                <input type='checkbox'";
            if (in_array($val['id'], $checked_ids)) {
                $html .= " checked='checked' ";
            }
            $html .= " name='cate_id[]' value='$val[id]'/>&nbsp;&nbsp;$val[name]</div>";
            $html .= $this->_get_cate_tree($val['child'], $checked_ids);
        }
        return $html;
    }
    public function _before_add() {
        $info['author'] = $_SESSION['admin']['username'];
        $this->assign('info', $info);
        $cate_tree = $this->_get_cate_tree(get_cate_tree(M("post_cate")));
        $this->assign('cate_tree', $cate_tree);
    }
    protected function _before_insert($data) {
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d');
            $result = $this->_upload($_FILES['img'], 'post/' . $art_add_time);
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $data['img'] = $art_add_time . '/' . $result['info'][0]['savename'];
            }
        }
        $data['post_time'] = strtotime($this->_request('post_time', 'trim'));
        $data['post_key'] =$this->get_post_key($data['title']);
        return $data;
    }
    protected function _after_insert($id) {
        $cids = $_REQUEST['cate_id'];
        foreach ($cids as $key => $val) {
            M("post_cate_re")->add(array(
                'post_id' => $id,
                'cate_id' => $val,
            ));
        }
        $where = array('post_id' => $id);
        $tags = $this->update_tag(M("post_tag"), $where, $data['title']);
        D("post_tag")->where($where)->delete();
        foreach ($tags as $key => $val) {
            D("post_tag")->add(array(
                'post_id' => $id,
                'tag_id' => $key,
            ));
        }
    }
    public function _after_edit($data) {
        $where = array('post_id' => $data['id']);
        $ids = array();
        $list = M("post_cate_re")->where($where)->select();
        foreach ($list as $key => $val) {
            $ids[] = $val['cate_id'];
        }
        $cate_tree = $this->_get_cate_tree(get_cate_tree(M("post_cate")), $ids);
        $this->assign('cate_tree', $cate_tree);
        $this->assign("mall_index", D("mall")->where(array('id' => $data['mall_id']))->getField("index"));
        $tag_list = D("post_tag")->relation(true)->where($where)->select();
        foreach ($tag_list as $key => $val) {
            $tags .= " " . $val['tag']['name'] . " ";
        }
        $this->assign("tags", $tags);
    }
    protected function _after_update(){        
        if($this->_post('edit_collect','intval')==1){
            IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'edit');
            $this->success(L('operation_success'), U(MODULE_NAME . '/index',array('collect_flag'=>0)));   exit();
        }
    }
    protected function _before_update($data) {
        M("post_cate_re")->where(array('post_id' => $data['id']))->delete();
        $cids = $_REQUEST['cate_id'];
        foreach ($cids as $key => $val) {
            M("post_cate_re")->add(array(
                'post_id' => $data['id'],
                'cate_id' => $val,
            ));
        }
        $where = array('post_id' => $data['id']);
        $tags = $this->update_tag(M("post_tag"), $where, $data['title']);
        D("post_tag")->where($where)->delete();
        foreach ($tags as $key => $val) {
            D("post_tag")->add(array(
                'post_id' => $data['id'],
                'tag_id' => $key,
            ));
        }
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d');
            $old_img = $this->_mod->where(array('id' => $data['id']))->getField('img');
            $old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            $result = $this->_upload($_FILES['img'], 'post/' . $art_add_time);
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $data['img'] = $art_add_time . '/' . $result['info'][0]['savename'];
            }
        } else {
            unset($data['img']);
        }
        $data['post_time'] = strtotime($this->_request('post_time', 'trim'));        
        $data['post_key'] =$this->get_post_key($data['title']);
        return $data;
    }
    public function _before_drop($ids) {
        foreach ($ids as $val) {
            if ($info = M(MODULE_NAME)->where(array('id' => $val))->find()) {
                @unlink(attach($info['img'], MODULE_NAME, true));
            }
        }
    }
    private function _get_imgdir() {
        static $dir = null;
        if ($dir === null) {
            $dir = './data/upload/post/';
        }
        return $dir;
    }
    public function ajax_mall_list() {
        $index = $this->_post('index', 'trim');
        $res = D("mall")->where(array('index' => $index))->select();
        $data = "";
        foreach ($res as $key => $val) {
            $data .= "<option value='$val[id]'>$val[title]</option>";
        }
        $this->ajaxReturn(1, '', $data);
    }
    public function ajax_post_key() {        
        echo $this->py->tourl($this->_post('title'));
    }
    function get_post_key($title){
        $data['post_key']=$this->py->tourl($title);
        if ($this->_mod->where(array('post_key' => trim($data['post_key'])))->count() > 0) {
            $data['post_key'] .= '_' . time();
        }
        $data['post_key'] = str_replace($this->spec_chars, '', $data['post_key']);
        return $data['post_key'];
    }
    function  _before_batch_edit(){
        $cate_tree = $this->_get_cate_tree(get_cate_tree(M("post_cate")));
        $this->assign('cate_tree', $cate_tree);
        $this->assign('uname',$_SESSION['admin']['username']);
    }
    function _before_batch_edit_update($data){
        if(!empty($_REQUEST['cate_id'])){
            $ids=explode(',',$this->_request('id','trim'));
            foreach($ids as $post_id){
                $post_id=intval($post_id);
                D('post_cate_re')->where("post_id=$post_id")->delete();
                foreach($_REQUEST['cate_id'] as $val){
                    D('post_cate_re')->add(array(
                        'post_id' => $post_id,
                        'cate_id' => $val
                    ));
                }
            }    
        }
        $data['post_time']>0&&$data['post_time']=strtotime($data['post_time']);
        $data=D('post')->parse_data($data);        
        return $data;
    }
    function collect() {
        $this->assign('list_table', true);
        $act = $this->_request("act", 'trim');
        if ($this->_request("act", 'trim') == 'search') {
            $p = $this->_request('p', 'intval', 1);
            $keyword = $this->_request("keyword", 'trim');
            empty($keyword) && $this->error("请填写关键词");
            $params = array('a' => 'post', 'keyword' => $keyword, 'p' => $p);
            $res = json_decode($this->api_collect($params));
            $data = unserialize($res->data);
            foreach ($data['items'] as $key => $val) {
                $data['items'][$key]['is_collect'] = D('post')->where(array('key_id' => 'zhiapi_' . $val['id']))->count() > 0;
            }
            $this->assign('list', $data['items']);
            $pager = new Page($data['total'], $data['page_size']);
            $this->assign('page', $pager->show());
            $this->assign('p', $p);
            $this->assign('api_params', urlencode(serialize($params)));
        } elseif ($act == 'batch_collect_form') {
            $this->_before_batch_edit();
            $this->ajaxReturn(1, '', $this->fetch("batch_collect_form"));
        } elseif ($act == 'add') {
            $data=D('post')->create();
            unset($data['id']);
            $ids = explode(',', $this->_request('id', 'trim'));
            $res = json_decode($this->api_collect(unserialize(urldecode($this->_request('api_params')))));
            $resdata = unserialize($res->data);            
            foreach ($resdata['items'] as $key => $val) {
                if (in_array($key, $ids)) {
                    if (D('post')->where(array('key_id' => 'zhiapi_' . $val['id']))->count() > 0) continue;
                    $data['title'] = $data['seo_title'] = $val['title'];
                    $data['prices'] = $val['price'];
                    $data['key_id'] = 'zhiapi_' . $val['id'];
                    $data['info'] = $val['info'];
                    $data['add_time'] =  time();
                    $data['img'] =$val['img'];
                    if(!empty($_REQUEST['post_time'])){
                        $data['post_time'] = strtotime($this->_request('post_time', 'trim', time()));    
                    }             
                    $data['collect_flag']=intval($data['collect_flag']);   
                    $data['post_key']=$this->get_post_key($data['title']);
                    $data=D('post')->parse_data($data);     
                    D('post')->add($data);
                    $post_id = D('post')->getLastInsID();
                    if(!empty($_REQUEST['cate_id'])){
                        foreach($_REQUEST['cate_id'] as $val){
                            D('post_cate_re')->add(array(
                                'post_id' => $post_id,
                                'cate_id' => $val
                            ));
                        }    
                    }
                }
            }
            $this->ajaxReturn(1, L('operation_success'));
        }
        $this->display();
    }
}