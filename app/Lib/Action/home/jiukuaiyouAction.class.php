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
class jiukuaiyouAction extends frontendAction {
    public function _initialize() {
        parent::_initialize();
        $this->assign("new_cmt_list", D("jky_comment")->relation(true)->where("status=1 and (select count(i.id) from " .
            table('jky_item') . " as i where i.status=1 and i.id=" . table("jky_comment") .
            ".item_id )>0")->order("id desc")->limit(5)->select());
        $this->assign("recommend_list", D("jky_item")->where("is_recommend=1 and status=1")->
            order("ordid asc,id desc")->limit(5)->select());
    }
    public function index() {
        $this->_config_seo(C('pin_seo_config.jiukuaiyou'));     
        $type = $this->_get('type', 'trim', 'all');
        $sort=$this->_get('sort','trim','hot');
        $this->assign('c1', intval($_REQUEST['c1']));
        $this->assign('c2', intval($_REQUEST['c2']));
        $state = $this->_get("state", 'trim','all');
        $time = time();
        $where = "status=1 and collect_flag=1 ";
        if ($state == "underway") {
            $where .= " and `etime`>$time and `stime`<=$time ";
            $this->text = "进行中";
        } elseif ($state == "end") {
            $where .= " and `etime`<$time ";
            $this->text = "抢光了";
        } elseif ($state == "notstart") {
            $where .= " and stime>=" . (strtotime(date("y-m-d", time()))+3600*24);
            $this->css = "css";
        }
        for ($i = 1; $i < 3; $i++) {            
            $cid=intval($_REQUEST["c$i"]);            
            if ($cid == 0) continue;
            $where .= " and (select count(c.item_id) from " . table("jky_cate_re") .
                " as c where c.cate_id=$cid and c.item_id=" . table("jky_item") . ".id)>0 ";
        }
        switch($sort){
            case 'hot':
                $order='hits desc';
            case 'new':
            default:
                $order='id desc';
        }
        $this->_assign_common();
        $this->assign('type', $type);
        $this->assign('sort',$sort);
        $this->assign('state',$state);
        $this->_assign_list(D('jky_item'),$where,24,false,$order);
        $this->display();   
    }
    public function detail() {
        $where="1";
        $id=$this->_get('id','intval',0);
        if(!empty($id)){
            $where.=" and id=$id ";
        }
        $post_key=$this->_get('post_key','trim');
        if(!empty($post_key)){
            $where.=" and post_key='$post_key'";
        }
        $info = D("jky_item")->where($where)->relation(true)->find();
        $id=$info['id'];
        $info['state'] = get_jky_state($info);
        $info['join_number']=D('jky_anhao')->where("item_id=".$id)->count();
        $info['join_user_list']=D('jky_anhao')->where("item_id=".$id)->limit("5")->order("id desc")->select();
        $info['discount'] = round(($info['price'] / $info['mprice']) * 10);
        $info['icon_list']=D('jky_icon_type')
                ->where("status=1 and id in(select type_id from ".table('jky_icon')." as c where c.item_id=$id)")
                ->order("id desc,ordid asc")->select();
        $this->assign('info', $info);
        $this->assign('yqp_time',D('jky_anhao')->where("item_id=$id")->order("id desc")->getField("add_time"));
        $this->assign('yqp_users',D('jky_anhao')->where("item_id=".$id)->limit("5")->order("rand()")->select());
        $this->assign('like_list', D("jky_item")->where("id!=$id and status=1")->order("rand()")->limit(4)->select());
        $this->_config_seo(C('pin_seo_config.jiukuaiyou_info'),array(
            'jky_title'=>$info['title'],
            'seo_title'=>$info['seo_title'],
            'seo_keywords'=>$info['seo_keywords'],
            'seo_description'=>$info['seo_description'],
        ));  
        $this->_assign_common();
        $this->comment_list();
        $this->display();
    }
    public function go() {
        $id=$this->_get('id','intval',0);
        $id&&header("location:" . D("jky_item")->where("id=$id")->getfield("url"));        
    }
    public function comment() {
        foreach ($_post as $key => $val) {
            $_post[$key] = input::deletehtmltags($val);
        }
        $data = array();
        $data['item_id'] = $this->_post('id', 'intval');
        !$data['item_id'] && $this->ajaxreturn(0, l('invalid_item'));
        $data['info'] = $this->_post('content', 'trim');
        !$data['info'] && $this->ajaxreturn(0, l('please_input') . l('comment_content'));
        $check_result = D('badword')->check($data['info']);
        switch ($check_result['code']) {
            case 1: 
                $this->ajaxreturn(0, l('has_badword'));
                break;
            case 3: 
                $data['status'] = 0;
                break;
        }
        $data['info'] = $check_result['content'];
        $data['uid'] = $this->visitor->info['id'];
        $data['uname'] = $this->visitor->info['username'];
        $data['add_time'] = time();
        $data['pid'] = $this->_post('pid', 'intval');
        $item = D("jky_item")->field('id')->where(array('id' => $data['item_id'],
                'status' => '1'))->find();
        !$item && $this->ajaxreturn(0, l('invalid_item'));
        if (false === D("jky_comment")->create($data)) {
            $this->ajaxreturn(0, D("jky_comment")->geterror());
        }
        $comment_id = D("jky_comment")->add(filter_data($data));
        if ($comment_id) {
            $tag_arg = array(
                'uid' => $this->visitor->info['id'],
                'uname' => $this->visitor->info['username'],
                'action' => 'comment');
            tag('comment_end', $tag_arg);
            $to_id = $this->_post('to_id', 'intval');
            if ($to_id > 0) {
                D("message")->add(array(
                    'ftid' => $data['uid'],
                    'from_id' => $data['uid'],
                    'from_name' => $data['uname'],
                    'to_id' => $this->_post('to_id', 'intval'),
                    'to_name' => $this->_post('to_name', 'trim'),
                    'add_time' => time(),
                    'info' => $data['info'],
                    ));
            }
            $this->assign('cmt_list', array(array(
                    'id' => $comment_id,
                    'uid' => $data['uid'],
                    'uname' => $data['uname'],
                    'info' => $data['info'],
                    'add_time' => time(),
                    'digg' => 0,
                    'burn' => 0,
                    'quote' => D("jky_comment")->where(array('id' => $data['pid']))->find(),
                    'user' => D("user")->where(array('id' => $data['uid']))->find(),
                    )));
            $resp['html'] = $this->fetch('ajax_comment_list');
            $resp['total'] = D("jky_comment")->where(array('item_id' => $data['item_id']))->
                count('id');
            $this->ajaxReturn(1, L('comment_success'), $resp);
        } else {
            $this->ajaxReturn(0, L('comment_failed'));
        }
    }
    public function comment_list($id) {
        if (empty($id)) {
            $id = $this->_get('id', 'intval');
        }
        if(empty($id)){
            $id=D('jky_item')->where("post_key='".$this->_get('post_key','trim')."'")->getField('id');
        }
        !$id && $this->ajaxReturn(0, L('invalid_item'));
        $res = D("jky_item")->where(array('id' => $id, 'status' => '1'))->count('id');
        !$res && $this->ajaxReturn(0, L('invalid_item'));
        $pagesize = 8;
        $map = array('item_id' => $id);
        $count = D("jky_comment")->where($map)->count('id');
        $pager = $this->_pager($count, $pagesize, __ROOT__ . "/index.php?m=" .
            MODULE_NAME . "&a=" . ACTION_NAME . "&id=$id");
        $cmt_list = D("jky_comment")->relation(true)->where($map)->order('id DESC')->
            limit($pager->firstRow . ',' . $pager->listRows)->select();
        $floor = $count - $pager->firstRow;
        foreach ($cmt_list as $key => $val) {
            $cmt_list[$key]['quote'] = D("jky_comment")->where(array('id' => $val['pid']))->
                find();
            $cmt_list[$key]['floor'] = $floor;
            $floor--;
        }
        $this->assign('cmt_list', $cmt_list);
        $data = array();
        $data['list'] = $this->fetch('ajax_comment_list');
        $data['page'] = $pager->fshow();
        $data['total'] = $count;
        $this->assign('cmt_page', $data['page']);
        $this->assign('cmt_total', $data['total']);
        if (IS_AJAX) {
            $this->ajaxReturn(1, '', $data);
        }
    }
    public function digg_burn() {
        $id = $this->_get('id', 'intval');
        $type = $this->_get('type', 'trim');
        if (in_array($type, array('digg', 'burn'))) {
            D("post_comment")->where(array('id' => $id))->setInc($type);
            $this->ajaxReturn(1, '', D("post_comment")->where(array('id' => $id))->
                getField($type));
        }
    }
    protected function _parse_assign_list($list) {
        foreach ($list as $key => $val) {
            $list[$key]['buys'] = D('jky_anhao')->where("item_id=$val[id]")->count();
            $list[$key]['state'] = get_jky_state($val);
            $list[$key]['discount'] = round(($val['price'] / $val['mprice']) * 10);
            $list[$key]['icon_list']=D('jky_icon_type')
                ->where("status=1 and id in(select type_id from ".table('jky_icon')." as c where c.item_id=$val[id])")
                ->order("id desc,ordid asc")->select();
        }
        return $list;
    }
    public function anhao() {
        if (IS_AJAX) {
            $anhao_mod=D('jky_anhao');
            $id=$this->_post('id','intval');            
            !$id&&exit();            
            $info=$anhao_mod->where("item_id=$id and uid=".$this->visitor->info['id']." and add_time>".(time()-3600*24*3))->find();
            if(!$info){
                $info=array(
                    'item_id'=>$id,
                    'uid'=>$this->visitor->info['id'],
                    'uname'=>$this->visitor->info['username'],
                    'add_time'=>time(),
                    'key'=>time().rand(100,999),
                );
                $anhao_mod->add($info);
            }
            $this->assign('info',$info);
            echo $this->fetch();
        }
    }
    protected function _assign_common(){        
        parent::_assign_common();
        $this->assign('type_list', D("jky_cate")->where("pid=1 and status=1")->
            order("ordid desc")->select());
        $cate_list=D("jky_cate")->where("pid=2 and status=1")->
            order("ordid desc")->select();
        foreach($cate_list as $key=>$val){
            $cate_list[$key]['item_num']=D('jky_cate_re')->DISTINCT(true)->where("cate_id=$val[id] and (select count(i.id) from ".table('jky_item')." as i where i.status=1 and i.id=item_id)>0")->count();
        }
        $this->assign('cat_list',$cate_list);      
        $this->assign('total_jky_num',D('jky_item')->where("status=1")->count());  
    }
}
?>