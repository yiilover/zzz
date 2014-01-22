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
class itemAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('item');
        $this->_cate_mod = D('item_cate');
    }
    public function _before_index() {
        $sm = $this->_get('sm', 'trim');
        $this->assign('sm', $sm);
        $res = $this->_cate_mod->field('id,name')->select();
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['name'];
        }
        $this->assign('cate_list', $cate_list);
        $this->sort = 'ordid ASC,';
        $this->order ='add_time DESC';
    }
    protected function _search() {
        $map = array();        
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        ($price_min = $this->_request('price_min', 'trim')) && $map['price'][] = array('egt', $price_min);
        ($price_max = $this->_request('price_max', 'trim')) && $map['price'][] = array('elt', $price_max);
        ($rates_min = $this->_request('rates_min', 'trim')) && $map['rates'][] = array('egt', $rates_min);
        ($rates_max = $this->_request('rates_max', 'trim')) && $map['rates'][] = array('elt', $rates_max);
        ($uname = $this->_request('uname', 'trim')) && $map['uname'] = array('like', '%'.$uname.'%');
        $cate_id = $this->_request('cate_id', 'intval');
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            if( $spid==0 ){
                $spid = $cate_id;
            }else{
                $spid .= $cate_id;
            }
        }
        if( $_GET['status']==null ){
            $status = -1;
        }else{
            $status = intval($_GET['status']);
        }
        $status>=0 && $map['status'] = array('eq',$status);
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'rates_min' => $rates_min,
            'rates_max' => $rates_max,
            'uname' => $uname,
            'status' =>$status,
            'selected_ids' => $spid,
            'cate_id' => $cate_id,
            'keyword' => $keyword,
        ));
        return $map;
    }
    public function add() {
        if (IS_POST) {
            if (false === $data = $this->_mod->create()) {
                $this->error($this->_mod->getError());
            }
            if( !$data['cate_id']||!trim($data['cate_id']) ){
                $this->error('请选择商品分类');
            }
            if (empty($_FILES['img']['name'])) {
                $this->error('请上传商品图片');
            }
            $auid = $this->_post('auid', 'intval');
            $auto_user_mod = M('auto_user');
            $user_mod = M('user');
            $unames = $auto_user_mod->where(array('id'=>$auid))->getField('users');
            $unamea = explode(',', $unames);
            $users = $user_mod->field('id,username')->where(array('username'=>array('in', $unamea)))->select();
            !$users && $this->error('请选择发布马甲');
            $user_rand = array_rand($users);
            $data['uid'] = $users[$user_rand]['id'];
            $data['uname'] = $users[$user_rand]['username'];
            $date_dir = date('ym/d/'); 
            $item_imgs = array(); 
            $result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
                'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'), 
                'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
                'suffix' => '_b,_m,_s',
            ));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $data['img'] = $date_dir . $result['info'][0]['savename'];
                $item_imgs[] = array(
                    'url'     => $data['img'],
                );
            }
            $file_imgs = array();
            foreach( $_FILES['imgs']['name'] as $key=>$val ){
                if( $val ){
                    $file_imgs['name'][] = $val;
                    $file_imgs['type'][] = $_FILES['imgs']['type'][$key];
                    $file_imgs['tmp_name'][] = $_FILES['imgs']['tmp_name'][$key];
                    $file_imgs['error'][] = $_FILES['imgs']['error'][$key];
                    $file_imgs['size'][] = $_FILES['imgs']['size'][$key];
                }
            }
            if( $file_imgs ){
                $result = $this->_upload($file_imgs, 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_simg.width'),
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    foreach( $result['info'] as $key=>$val ){
                        $item_imgs[] = array(
                            'url'    => $date_dir . $val['savename'],
                            'order'  => $key + 1,
                        );
                    }
                }
            }
            $data['imgs'] = $item_imgs;
            $item_id = $this->_mod->publish($data);
            !$item_id && $this->error(L('operation_failure'));
            $attr = $this->_post('attr', ',');
            if( $attr ){
                foreach( $attr['name'] as $key=>$val ){
                    if( $val&&$attr['value'][$key] ){
                        $atr['item_id'] = $item_id;
                        $atr['attr_name'] = $val;
                        $atr['attr_value'] = $attr['value'][$key];
                        M('item_attr')->add($atr);
                    }
                }
            }
            $this->success(L('operation_success'));
        } else {
            $orig_list = M('item_orig')->where(array('status'=>1))->select();
            $this->assign('orig_list',$orig_list);
            $auto_user = M('auto_user')->select();
            $this->assign('auto_user', $auto_user);
            $this->display();
        }
    }
    public function edit() {
        if (IS_POST) {
            if (false === $data = $this->_mod->create()) {
                $this->error($this->_mod->getError());
            }
            if( !$data['cate_id']||!trim($data['cate_id']) ){
                $this->error('请选择商品分类');
            }
            $item_id = $data['id'];
            $date_dir = date('ym/d/'); 
            $item_imgs = array(); 
            if (!empty($_FILES['img']['name'])) {
                $result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'), 
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_m,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    $data['img'] = $date_dir . $result['info'][0]['savename'];
                    $item_imgs[] = array(
                        'item_id' => $item_id,
                        'url'     => $data['img'],
                    );
                }
            }
            $file_imgs = array();
            foreach( $_FILES['imgs']['name'] as $key=>$val ){
                if( $val ){
                    $file_imgs['name'][] = $val;
                    $file_imgs['type'][] = $_FILES['imgs']['type'][$key];
                    $file_imgs['tmp_name'][] = $_FILES['imgs']['tmp_name'][$key];
                    $file_imgs['error'][] = $_FILES['imgs']['error'][$key];
                    $file_imgs['size'][] = $_FILES['imgs']['size'][$key];
                }
            }
            if( $file_imgs ){
                $result = $this->_upload($file_imgs, 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_simg.width'),
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    foreach( $result['info'] as $key=>$val ){
                        $item_imgs[] = array(
                            'item_id' => $item_id,
                            'url'    => $date_dir . $val['savename'],
                            'order'   => $key + 1,
                        );
                    }
                }
            }
            $tags = $this->_post('tags', 'trim');
            if (!isset($tags) || empty($tags)) {
                $tag_list = D('tag')->get_tags_by_title($data['intro']);
            } else {
                $tag_list = explode(' ', $tags);
            }
            if ($tag_list) {
                $item_tag_arr = $tag_cache = array();
                $tag_mod = M('tag');
                foreach ($tag_list as $_tag_name) {
                    $tag_id = $tag_mod->where(array('name'=>$_tag_name))->getField('id');
                    !$tag_id && $tag_id = $tag_mod->add(array('name' => $_tag_name)); 
                    $item_tag_arr[] = array('item_id'=>$item_id, 'tag_id'=>$tag_id);
                    $tag_cache[$tag_id] = $_tag_name;
                }
                if ($item_tag_arr) {
                    $item_tag = M('item_tag');
                    $item_tag->where(array('item_id'=>$item_id))->delete();
                    $item_tag->addAll($item_tag_arr);
                    $data['tag_cache'] = serialize($tag_cache);
                }
            }
            $this->_mod->where(array('id'=>$item_id))->save($data);
            $item_imgs && M('item_img')->addAll($item_imgs);
            $attr = $this->_post('attr', ',');
            if( $attr ){
                foreach( $attr['name'] as $key=>$val ){
                    if( $val&&$attr['value'][$key] ){
                        $atr['item_id'] = $item_id;
                        $atr['attr_name'] = $val;
                        $atr['attr_value'] = $attr['value'][$key];
                        M('item_attr')->add($atr);
                    }
                }
            }
            $this->success(L('operation_success'));
        } else {
            $id = $this->_get('id','intval');
            $item = $this->_mod->where(array('id'=>$id))->find();
            $spid = $this->_cate_mod->where(array('id'=>$item['cate_id']))->getField('spid');
            if( $spid==0 ){
                $spid = $item['cate_id'];
            }else{
                $spid .= $item['cate_id'];
            }
            $this->assign('selected_ids',$spid); 
            $tag_cache = unserialize($item['tag_cache']);
            $item['tags'] = implode(' ', $tag_cache);
            $this->assign('info', $item);
            $orig_list = M('item_orig')->select();
            $this->assign('orig_list', $orig_list);
            $img_list = M('item_img')->where(array('item_id'=>$id))->select();
            $this->assign('img_list', $img_list);
            $attr_list = M('item_attr')->where(array('item_id'=>$id))->select();
            $this->assign('attr_list', $attr_list);
            $this->display();
        }
    }
    function delete_album() {
        $album_mod = M('item_img');
        $album_id = $this->_get('album_id','intval');
        $album_img = $album_mod->where('id='.$album_id)->getField('url');
        if( $album_img ){
            $ext = array_pop(explode('.', $album_img));
            $album_min_img = C('pin_attach_path') . 'item/' . str_replace('.' . $ext, '_s.' . $ext, $album_img);
            is_file($album_min_img) && @unlink($album_min_img);
            $album_img = C('pin_attach_path') . 'item/' . $album_img;
            is_file($album_img) && @unlink($album_img);
            $album_mod->delete($album_id);
        }
        echo '1';
        exit;
    }
    function delete_attr() {
        $attr_mod = M('item_attr');
        $attr_id = $this->_get('attr_id','intval');
        $attr_mod->delete($attr_id);
        echo '1';
        exit;
    }
    public function check() {
        $res = $this->_cate_mod->field('id,name')->select();
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['name'];
        }
        $this->assign('cate_list', $cate_list);
        $map=array();
        $map['status']=0;
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $cate_id = $this->_request('cate_id', 'intval');
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            if( $spid==0 ){
                $spid = $cate_id;
            }else{
                $spid .= $cate_id;
            }
        }
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'selected_ids' => $spid,
            'cate_id' => $cate_id,
            'keyword' => $keyword,
        ));
        $count = $this->_mod->where($map)->count('id');
        $pager = new Page($count, 20);
        $select = $this->_mod->field('id,title,img,tag_cache,cate_id,uid,uname')->where($map)->order('id DESC');
        $select->limit($pager->firstRow.','.$pager->listRows);
        $page = $pager->show();
        $this->assign("page", $page);
        $list = $select->select();
        foreach ($list as $key=>$val) {
            $tag_list = unserialize($val['tag_cache']);
            $val['tags'] = implode(' ', $tag_list);
            $list[$key] = $val;
        }
        $this->assign('list', $list);
        $this->assign('list_table', true);
        $this->display();
    }
    public function do_check() {
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
        $datas['id']=array('in',$ids);
        $datas['status']=1;
        if ($datas) {
            if (false !== $mod->save($datas)) {
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    public function delete_search() {
        $items_mod = D('item');
        $items_cate_mod = D('item_cate');
        $items_likes_mod = D('item_like');
        $items_pics_mod = D('item_img');
        $items_tags_mod = D('item_tag');
        $items_comments_mod = D('item_comment');
        if (isset($_REQUEST['dosubmit'])) {
            if ($_REQUEST['isok'] == "1") {
                $where = '1=1';
                $keyword = trim($_POST['keyword']);
                $cate_id = trim($_POST['cate_id']);
                $cate_id = trim($_POST['cate_id']);
                $time_start = trim($_POST['time_start']);
                $time_end = trim($_POST['time_end']);
                $status = trim($_POST['status']);
                $min_price = trim($_POST['min_price']);
                $max_price = trim($_POST['max_price']);
                $min_rates = trim($_POST['min_rates']);
                $max_rates = trim($_POST['max_rates']);
                if ($keyword != '') {
                    $where .= " AND title LIKE '%" . $keyword . "%'";
                }
                if ($cate_id != ''&&$cate_id!=0) {
                    $where .= " AND cate_id=" . $cate_id;
                }
                if ($time_start != '') {
                    $time_start_int = strtotime($time_start);
                    $where .= " AND add_time>='" . $time_start_int . "'";
                }
                if ($time_end != '') {
                    $time_end_int = strtotime($time_end);
                    $where .= " AND add_time<='" . $time_end_int . "'";
                }
                if ($status != '') {
                    $where .= " AND status=" . $status;
                }
                if ($min_price != '') {
                    $where .= " AND price>=" . $min_price;
                }
                if ($max_price != '') {
                    $where .= " AND price<=" . $max_price;
                }
                if ($min_rates != '') {
                    $where .= " AND rates>=" . $min_rates;
                }
                if ($max_rates != '') {
                    $where .= " AND rates<=" . $max_rates;
                }
                $ids_list = $items_mod->where($where)->select();
                $ids = "";
                foreach ($ids_list as $val) {
                    $ids .= $val['id'] . ",";
                }
                if ($ids != "") {
                    $ids = substr($ids, 0, -1);
                    $items_likes_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_pics_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_tags_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_comments_mod->where("item_id in(" . $ids . ")")->delete();
                    M('album_item')->where("item_id in(" . $ids . ")")->delete();
                    M('item_attr')->where("item_id in(" . $ids . ")")->delete();
                }
                $items_mod->where($where)->delete();
                $items_nums = $items_mod->field('cate_id,count(id) as items')->group('cate_id')->select();
                foreach ($items_nums as $val) {
                    $items_cate_mod->save(array('id' => $val['cate_id'], 'items' => $val['items']));
                    M('album')->save(array('cate_id' => $val['cate_id'], 'items' => $val['items']));
                }
                $this->success('删除成功', U('item/delete_search'));
            } else {
                $this->success('确认是否要删除？', U('item/delete_search'));
            }
        } else {
            $res = $this->_cate_mod->field('id,name')->select();
            $cate_list = array();
            foreach ($res as $val) {
                $cate_list[$val['id']] = $val['name'];
            }
            $this->display();
        }
    }
}