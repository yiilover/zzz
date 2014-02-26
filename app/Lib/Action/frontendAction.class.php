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
class frontendAction extends baseAction {
    protected $visitor = null;
    protected $uid;
    public function _initialize() {
        parent::_initialize();
        if (!C('pin_site_status')) {
            header('Content-Type:text/html; charset=utf-8');
            exit(C('pin_closed_reason'));
        }
        $homepage = D("nav")->where("homepage=1")->find();
        if (!empty($homepage)) {
            if ($_SERVER['REQUEST_URI'] == __ROOT__ . '/' && ltrim($homepage['link'], '.') != '/') {
                header("Location:" . U($homepage['link']));
            }
            $homepage['link'] = U($homepage['link']);
        } else {
            $homepage['link'] = U('index/index');
        }
        $this->assign('homepage', $homepage);
        $this->_init_visitor();
        $this->_assign_oauth();
        $this->assign('nav_curr', '');
        $cate1 = D("post_cate")->where("pid=1 and status=1")->select();
        foreach($cate1 as $r){
            $id = $r['id'];
            $cate[] = array(
                'id' => $id,
                'name' => $r['name'],
                'url' => 'forum/'.$r['alias'],
                'son' => $this->get_cate2($id, $r['alias'])
            );
        }




        $this->assign('recommend_cate', $cate);
        $this->assign('tese_cate', D("post_cate")->where("pid=2 and status=1")->select());
        $this->assign('main_nav_list', D("nav")->where("type='main' and status=1 and homepage=0")->order('ordid')->select());
        $this->assign('bottom_nav_list', D("nav")->where("type='bottom' and status=1")->order('ordid')->select());
        $this->assign('new_post_list', D("post")->where("status=1 and collect_flag=1 and post_time<=" . time())->limit("9")->order("id desc")->select());
        $this->assign('flink_list', D("flink")->where("status=1")->order("ordid desc")->select());
        $help_list = D("article_cate")->where("pid=1 and status=1")->select();
        foreach ($help_list as $key => $val) {
            $help_list[$key]['articles'] = D("article")->where("cate_id=$val[id]")->select();
        }
        $this->assign('help_list', $help_list);
        $this->assign('gonggao_list', D("article")->where('cate_id=13 and status=1')->order("ordid desc")->select());
        $this->uid = intval($this->visitor->info['id']);
        $this->assign('req', $_REQUEST);
        $this->assign('server', $_SERVER);
        $this->_assign_common();
        $def = array(
            'is_login' => $this->uid > 0,
            'm' => MODULE_NAME,
            'a' => ACTION_NAME,
            'url_prefix' => __ROOT__ . '/',
            'site_name' => C('pin_site_name'),
            'cps_alimama_pid' => C('pin_cps_alimama_pid'),
        );
        $this->assign('def', $def);
        if ($_REQUEST['act'] == 'loadjs') {
            header("content-type:text/javascript");
            echo "var def=" . json_encode($def) . ';';
            exit();
        }
    }

    protected function get_cate2($id, $alias){
        $arr = D("post_cate")->where("pid={$id} and status=1")->select();
        foreach($arr as $r){
            $data[] = array(
                'id' => $r['id'],
                'name' => $r['name'],
                'url' => 'forum/'.$alias.'?type='.$r['id']
            );
        }
        return $data;
    }
    protected function _assign_common() {
        $this->assign("quick_mall_list", D("mall")->where("status=1")->order("ordid desc")->limit("10")->select());
        $this->assign("all_post_cate_list", D("post_cate")->where("status=1 and pid<2")->order("ordid desc")->select());
        $about_content = msubstr(strip_tags(D("article_page")->where("cate_id=2")->getField("info")), 350);
        $this->assign("about_content", $about_content);
    }
    private function _init_visitor() {
        $this->visitor = new user_visitor();
        $this->assign('visitor', $this->visitor->info);
    }
    private function _assign_oauth() {
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('oauth')->oauth_cache();
        }
        $this->assign('oauth_list', $oauth_list);
    }
    protected function _config_seo($seo_info = array(), $data = array()) {
        $page_seo = array(
            'title' => C('pin_site_title'),
            'keywords' => C('pin_site_keyword'),
            'description' => C('pin_site_description')
        );
        $page_seo = array_merge($page_seo, $seo_info);
        $searchs = array('{site_name}', '{site_title}', '{site_keywords}', '{site_description}');
        $replaces = array(C('pin_site_name'), C('pin_site_title'), C('pin_site_keyword'), C('pin_site_description'));
        preg_match_all("/\{([a-z0-9_-]+?)\}/", implode(' ', array_values($page_seo)), $pageparams);
        if ($pageparams) {
            foreach ($pageparams[1] as $var) {
                $searchs[] = '{' . $var . '}';
                $replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
            }
            $searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
            $replacespace = array('-', ',', '|', ' ', '_');
            foreach ($page_seo as $key => $val) {
                $page_seo[$key] = trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $val)), ' ,-|_');
            }
        }
        if ($page_seo['title'] != C('pin_site_title')) {
        }
        $this->assign('page_seo', $page_seo);
    }
    protected function _user_server() {
        $passport = new passport(C('pin_integrate_code'));
        return $passport;
    }
    protected function _pager($count, $pagesize, $url = "") {
        $pager = new Page($count, $pagesize, $parameter, $url);
        $pager->rollPage = 5;
        $pager->url = $url;
        $pager->setConfig('theme', '%upPage% %first% %linkPage% %end% %downPage%');
        return $pager;
    }
    protected function _parse_post($list) {
        foreach ($list as $key => $val) {
            $list[$key]['cate_list'] = D("post_cate_re")->relation(true)->where(array('post_id' => $val['id']))->select();
            $list[$key]['tag_list'] = D("tag")->where("id in(select tag_id from " . table("post_tag") . " where post_id=$val[id])")->select();
        }
        return $list;
    }
    protected function _waterfall($mod, $where = '', $order = "", $pagesize = 5, $s_list_rows = '') {
        import("ORG.Util.Page");
        $p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
        $sp = !empty($_GET['sp']) ? intval($_GET['sp']) : 1;
        $sp > C('pin_wall_spage_max') && exit;
        !$s_list_rows && $s_list_rows = C('pin_wall_spage_size');
        $list_rows = C('pin_wall_spage_max') * $s_list_rows;
        $count = $mod->where($where)->count();
        $pager = new Page($count, $list_rows);
        $pager->setConfig('theme', '%upPage% %first% %linkPage% %end% %downPage%');
        if (MODULE_NAME == 'index'&&C('pin_index_wall')==0) {        
            $first_row = $pager->firstRow ;
            $items_list = $mod->relation(true)->where($where)
                ->limit($first_row . ',' . $list_rows)->order($order)
                ->select();
        } else {
            $show_sp = 0;
            $count > $s_list_rows && $show_sp = 1;
            $first_row = $pager->firstRow + $s_list_rows * ($sp - 1);
            $items_list = $mod->relation(true)->where($where)
                ->limit($first_row . ',' . $s_list_rows)->order($order)
                ->select();
            $this->assign('show_load', 1);
            $this->assign('show_sp', $show_sp);
            $this->assign('sp', $sp);
        }
        $this->assign('p', $p);
        $_parse = '_parse_' . $mod->getModelName();
        if (method_exists($this, $_parse)) {
            eval('$items_list=$this->' . $_parse . '($items_list);');
        }
        $this->assign('page_bar', $pager->fshow());
        $this->assign($mod->getModelName() . '_list', $items_list);
        if (IS_AJAX && $sp >= 2) {
            $resp = $this->fetch('public:ajax_' . $mod->getModelName() . '_list');
            $data = array(
                'isfull' => 1,
                'html' => $resp
            );
            $this->ajaxReturn(1, '', $data);
        } else {
            $this->display();
        }
    }
    protected function _assign_hot_list() {
        $hot_list = D("post")->where("is_hot=1 and status=1 and post_time<=" . time())->order("ordid")->limit("0,8")->select();
        $this->assign('hot_list', $hot_list);
    }
    protected function _assign_recommend_list() {
        $recommend_list = D("post")->where("is_recommend=1 and status=1 and post_time<=" . time())->order("ordid")->limit("0,8")->select();
        $this->assign('recommend_list', $recommend_list);
    }
    protected function _assign_list($mod, $where, $page_size = 15, $relation = false, $order = "zhi_post.id desc", $callback = "_parse_assign_list") {
        import("ORG.Util.Page");
        $count = $mod->where($where)->count();
        $pager = $this->_pager($count, $page_size);
        $join = 'zhi_mall on zhi_post.mall_id = zhi_mall.id ';
        $relation = true;
        $field = 'zhi_post.*,zhi_mall.title as mall_title';
        $select = $mod->field($field)->where($where)->join($join)->order($order)->limit($pager->firstRow . ',' .
        $pager->listRows);
        if ($relation) {
            $select = $select->relation($relation);
        }
        $list = $select->select();
        if (method_exists($this, $callback)) {
            $list = $this->$callback($list);
        }
        $this->assign('list', $list);

        $this->assign('page', $pager->fshow());
        return $list;
    }
}