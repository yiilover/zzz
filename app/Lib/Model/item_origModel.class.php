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
class item_origModel extends Model{
    public function get_id_by_url($url) {
        $rs = preg_match("/^(http:\/\/|https:\/\/)/", $url, $match);
        if (intval($rs) == 0) {
            $url = "http://" . $url;
        }
        $rs = parse_url($url);
        $scheme = isset($rs['scheme']) ? $rs['scheme'] . "://" : "http://";
        $host = isset($rs['host']) ? $rs['host'] : "none";
        $host = explode('.', $host);
        $host = array_slice($host, -2, 2);
        $domain = implode('.', $host);
        $orig_id = $this->where(array('url' => array('like', '%'.$domain.'%')))->getField('id');
        return $orig_id;
    }
    public function name_exists($name, $id=0)
    {
        $pk = $this->getPk();
        $where = "name='" . $name . "'  AND ". $pk ."<>'" . $id . "'";
        $result = $this->where($where)->count($pk);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}