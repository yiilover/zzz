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
class item_cateModel extends Model
{
    public function get_spid($pid) {
        if (!$pid) {
            return 0; 
        }
        $pspid = $this->where(array('id'=>$pid))->getField('spid');
        if ($pspid) {
            $spid = $pspid . $pid . '|';
        } else {
            $spid = $pid . '|';
        }
        return $spid;
    }
    public function get_child_ids($id, $with_self=false) {
        $spid = $this->where(array('id'=>$id))->getField('spid');
        $spid = $spid ? $spid .= $id .'|' : $id .'|';
        $id_arr = $this->field('id')->where(array('spid'=>array('like', $spid.'%')))->select();
        $array = array();
        foreach ($id_arr as $val) {
            $array[] = $val['id'];
        }
        $with_self && $array[] = $id;
        return $array;
    }
    public function get_tag_ids($cate_id) {
        $res = M('item_cate_tag')->field('tag_id')->where(array('cate_id'=>$cate_id))->select();
        $ids = array();
        foreach($res as $tag) {
            $ids[] = $tag['tag_id'];
        }
        return $ids;
    }
    public function get_name($id) {
        if (false === $cate_data = F('cate_data')) {
            $cate_data = $this->cate_data_cache();
        }
        return $cate_data[$id]['name'];
    }
    public function get_pentity_id($id) {
        $pentity_id = 0;
        if (false === $cate_data = F('cate_data')) {
            $cate_data = $this->cate_data_cache();
        }
        $spid = array_reverse(explode('|', trim($cate_data[$id]['spid'], '|')));
        foreach ($spid as $val) {
            if ($cate_data[$val]['type'] == 0) {
                $pentity_id = $val;
                break;
            }
        }
        return $pentity_id;
    }
    public function cate_cache() {
        $cate_list = array();
        $cate_data = $this->field('id,pid,name,fcolor,type')->where('status=1')->order('ordid')->select();
        foreach ($cate_data as $val) {
            if ($val['pid'] == '0') {
                $cate_list['p'][$val['id']] = $val;
            } else {
                $cate_list['s'][$val['pid']][$val['id']] = $val;
            }
        }
        F('cate_list', $cate_list);
        return $cate_list;
    }
    public function cate_data_cache() {
        $cate_data = array();
        $result = $this->field('id,pid,spid,name,fcolor,type,seo_title,seo_keys,seo_desc')->where('status=1')->order('ordid')->select();
        foreach ($result as $val) {
            $cate_data[$val['id']] = $val;
        }
        F('cate_data', $cate_data);
        return $cate_data;
    }
    public function relate_cache() {
        $cate_relate = array();
        $cate_data = $this->field('id,pid,spid')->where('status=1')->order('ordid')->select();
        foreach ($cate_data as $val) {
            $cate_relate[$val['id']]['sids'] = $this->get_child_ids($val['id']); 
            if ($val['pid'] == '0') {
                $cate_relate[$val['id']]['tid'] = $val['id']; 
            } else {
                $cate_relate[$val['id']]['tid'] = array_shift(explode('|', $val['spid'])); 
            }
        }
        F('cate_relate', $cate_relate);
        return $cate_relate;
    }
    public function name_exists($name, $pid, $id=0) {
        $where = "name='" . $name . "' AND pid='" . $pid . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    protected function _before_write(&$data) {
        F('cate_data', NULL);
        F('cate_list', NULL);
        F('cate_relate', NULL);
        F('index_cate_list', NUll);
    }
    protected function _after_delete($data, $options) {
        F('cate_data', NULL);
        F('cate_list', NULL);
        F('cate_relate', NULL);
        F('index_cate_list', NUll);
    }
}