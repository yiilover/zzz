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
class post_cateAction extends backendAction {

    public function _initialize() {
        parent::_initialize();
    }
    public function index() {
        $sort = $this->_request("sort", 'trim', 'ordid');
        $order = $this->_request("order", 'trim', 'ASC');
        $tree = new Tree();
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = $this->_mod->order($sort . ' ' . $order)->select();
        $array = array();
        foreach($result as $r) {
            $r['str_img'] = $r['img'] ? '<div class="img_border"><img src="'.attach($r['img'], 'post_cate').'" width="26" height="26" class="J_preview" data-bimg="'.attach($r['img'], 'article_cate').'"/></div>' : '';
            $r['str_status'] = '<img data-tdtype="toggle" data-id="'.$r['id'].'" data-field="status" data-value="'.$r['status'].'" src="__STATIC__/images/admin/toggle_' . ($r['status'] == 0 ? 'disabled' : 'enabled') . '.gif" />';
            $r['str_manage'] = '<a href="javascript:;" class="J_showdialog" data-uri="'.U('post_cate/add',array('pid'=>$r['id'])).'" data-title="'.L('add_article_cate').'" data-id="add" data-width="500" data-height="360">'.L('add_article_subcate').'</a> |
                                <a href="javascript:;" class="J_showdialog" data-uri="'.U('post_cate/edit',array('id'=>$r['id'])).'" data-title="'.L('edit').' - '. $r['name'] .'" data-id="edit" data-width="500" data-height="360">'.L('edit').'</a> |
                                <a href="javascript:;" data-acttype="ajax" class="J_confirmurl" data-uri="'.U('post_cate/delete',array('id'=>$r['id'])).'" data-msg="'.sprintf(L('confirm_delete_one'),$r['name']).'">'.L('delete').'</a>';
            $r['parentid_node'] = ($r['pid'])? ' class="child-of-node-'.$r['pid'].'"' : '';
            $r['cate_type'] = $r['type'] ? '<span class="blue">'.L('article_cate_type_'.$r['type']).'</span>' : L('article_cate_type_'.$r['type']);
            $array[] = $r;
        }
        $str  = "<tr id='node-\$id' \$parentid_node>
                <td align='center'><input type='checkbox' value='\$id' class='J_checkitem'></td>
                <td>\$spacer<span data-tdtype='edit' data-field='name' data-id='\$id' class='tdedit'>\$name</span></td>
                <td align='center'>\$id</td>
                <td align='center'>\$str_img</td>
                <td align='center'><span data-tdtype='edit' data-field='ordid' data-id='\$id' class='tdedit'>\$ordid</span></td>
                <td align='center'>\$str_status</td>
                <td align='center'>\$str_manage</td>
                </tr>";
        $tree->init($array);
        $list = $tree->get_tree(0, $str);
        $this->assign('list', $list);
        $big_menu = array(
            'title' => L('add_cate'),
            'iframe' => U('post_cate/add'),
            'id' => 'add',
            'width' => '500',
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
            $this->ajaxReturn(0, L('article_cate_already_exists'));
        }
        $data['spid'] = $this->_mod->get_spid($data['pid']);
        return $data;
    }
    protected function _before_update($data = '') {
        if ($this->_mod->name_exists($data['name'], $data['pid'], $data['id'])) {
            $this->ajaxReturn(0, L('article_cate_already_exists'));
        }
        $old_pid = $this->_mod->field('img,pid')->where(array('id'=>$data['id']))->find();
        if ($data['pid'] != $old_pid['pid']) {
            $wp_spid_arr = $this->_mod->get_child_ids($data['id'], true);
            if (in_array($data['pid'], $wp_spid_arr)) {
                $this->ajaxReturn(0, L('cannot_move_to_child'));
            }
            $data['spid'] = $this->_mod->get_spid($data['pid']);
        }
        return $data;
    }
    public function ajax_upload_img() {
        if (!empty($_FILES['img']['name'])) {
            $result = $this->_upload($_FILES['img'], 'post_cate', array('width'=>'80', 'height'=>'80'));
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
    protected function _before_delete(){
        $id=intval($_REQUEST['id']);
        $ids=get_child_ids($this->_mod,$id);        
        $this->_mod->where("id in($ids)")->delete();         
    }
    public function _before_upload() {

    }

    public function _before_upload_cate() {
        $mod = D($this->_name);
        $mod->create();
        require_once(APP_PATH .'Lib/Action/admin/Excel/reader.php');
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('UTF-8');
        $data->read($_FILES['file']['tmp_name']);
        $id = 1000;
        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            if($i==1) continue;
            for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
                $arr[$i]['id'] = $id;
                $arr[$i]['name'] = $data->sheets[0]['cells'][$i][5];
                $arr[$i]['pid'] = 1;
                $arr[$i]['status'] = 1;
            }
            $mod->add($arr[$i]);
            $id++;
        }
//        echo "<pre>";
//        print_r($arr);die;
    }

    public function _before_upload_cate2() {
        $mod = D($this->_name);
        $mod->create();
        require_once(APP_PATH .'Lib/Action/admin/Excel/reader.php');
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('UTF-8');
        $data->read($_FILES['file']['tmp_name']);
        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            if($i==1) continue;
            for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
                $arr[$i]['id'] = $data->sheets[0]['cells'][$i][5];
                $arr[$i]['name'] = $data->sheets[0]['cells'][$i][4];
                $arr[$i]['pid'] = $this->get_pid_by_pname($data->sheets[0]['cells'][$i][6]);
                $arr[$i]['status'] = 1;
            }
            $mod->add($arr[$i]);
        }
    }

    public function _before_upload_post() {
        $mod = D('post');
        $mod->create();
//        var_dump($mod);die;
        require_once(APP_PATH .'Lib/Action/admin/Excel/reader.php');
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('UTF-8');
        $data->read($_FILES['file']['tmp_name']);
        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            if($i==1) continue;
            $id = $data->sheets[0]['cells'][$i][4];
            $url = $data->sheets[0]['cells'][$i][6];
            $post_time = $data->sheets[0]['cells'][$i][9];
            $post_time = $this->get_format_time($post_time);
            $info = $data->sheets[0]['cells'][$i][17];
            for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
                $arr[$i]['id'] = $id;
                $arr[$i]['title'] = $data->sheets[0]['cells'][$i][5];
                $arr[$i]['url'] = base64_decode($url);
                $arr[$i]['img'] = $data->sheets[0]['cells'][$i][7];
                $arr[$i]['uname'] = $data->sheets[0]['cells'][$i][8];
                $arr[$i]['post_time'] = strtotime($post_time);
                $arr[$i]['mall_id'] = $data->sheets[0]['cells'][$i][10];
                $arr[$i]['rate_good'] = $data->sheets[0]['cells'][$i][13];
                $arr[$i]['rate_bad'] = $data->sheets[0]['cells'][$i][14];
                $arr[$i]['slogan'] = $data->sheets[0]['cells'][$i][15];
                $arr[$i]['prices'] = $data->sheets[0]['cells'][$i][16];
                $arr[$i]['info'] = trim($info);
                $arr[$i]['comments'] = $data->sheets[0]['cells'][$i][18];
                $arr[$i]['favs'] = $data->sheets[0]['cells'][$i][19];
                $arr[$i]['hits'] = $data->sheets[0]['cells'][$i][20];
                $arr[$i]['seo_keys'] = $data->sheets[0]['cells'][$i][21];
                $arr[$i]['seo_desc'] = $data->sheets[0]['cells'][$i][22];
                $arr[$i]['key_id'] = $id;
                $arr[$i]['add_time'] = time();
                $arr[$i]['status'] = 1;
                $arr[$i]['collect_flag'] = 1;
            }

            $mod->add($arr[$i]);
        }

        //$this->ajaxReturn(1, L('operation_success'));
//        echo "<pre>";
//        print_r($arr);die;
    }

    protected function get_pid_by_pname($pname){
        $r = D($this->_name)->where("name='$pname'")->find();
        return $r['id'];
    }

    protected function get_format_time($time){
        if(!preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}/',$time)){
            if(preg_match('/\d{2}-\d{2} \d{2}:\d{2}/',$time)){
                $time = '2014-'.$time;
            }elseif(preg_match('/昨天/',$time)){
                $time = preg_replace('/昨天([\s\S])/','2014-02-10 $1',$time);
            }elseif(preg_match('/前天/',$time)){
                $time = preg_replace('/前天([\s\S])/','2014-02-09 $1',$time);
            }else{
                $time = date('Y-m-d H:i');
            }
        }
        return $time;
    }
}