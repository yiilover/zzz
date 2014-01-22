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
class albumModel extends Model {
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    public function default_album($user, $cate_id = 0) {
        $album_id = $this->where(array('uid' => $user['id'], 'status' => 1))->order('add_time DESC')->getField('id');
        if (!$album_id) {
            !$cate_id && $cate_id = M('album_cate')->getField('id'); 
            $this->create(array(
                'uid' => $user['id'],
                'uname' => $user['name'],
                'cate_id' => $cate_id,
                'title' => C('pin_album_default_title'),
            ));
            $album_id = $this->add();
            M('user')->where(array('id' => $user['id']))->setInc('albums');
        }
        return $album_id;
    }
    public function add_item($item_id, $album_id, $intro) {
        $result = M('album_item')->add(array(
            'album_id' => $album_id,
            'item_id' => $item_id,
            'intro' => $intro,
            'add_time' => time(),
        ));
        $this->where(array('id' => $album_id))->setInc('items');
        return $result ? $this->update_cover($album_id, $item_id) : false;
    }
    public function del_item($item_id, $album_id = '') {
        $map = array('item_id' => $item_id);
        $album_id && $map['album_id'] = $album_id;
        M('album_item')->where($map)->delete();
        if ($album_id) {
            $this->where(array('id' => $album_id))->setDec('items');
            $album_id && $this->update_cover($album_id);
        }
    }
    public function update_cover($id, $item_id = '') {
        $max_num = C('pin_album_cover_items');
        if ($item_id) {
            $item = M('item')->field('img,intro')->find($item_id);
            $cover_cache = $this->where(array('id' => $id))->getField('cover_cache');
            if ($cover_cache) {
                $cover_cache = unserialize($cover_cache);
                array_unshift($cover_cache, $item);
                $cover_cache = array_slice($cover_cache, 0, $max_num);
            } else {
                $cover_cache = array($item);
            }
        } else {
            $db_pre = C('DB_PREFIX');
            $ai_table = $db_pre . 'album_item';
            $where = array($ai_table . '.album_id' => $id);
            $cover_cache = M('album_item')->field('i.img,i.intro')->join($db_pre . 'item i ON i.id=' . $ai_table . '.item_id')->where($where)->order($ai_table . '.add_time DESC')->limit($max_num)->select();
        }
        $cover_cache = $cover_cache ? serialize($cover_cache) : '';
        return $this->where(array('id' => $id))->setField('cover_cache', $cover_cache);
    }
    protected function _after_delete($data, $options) {
        M('album_item')->where(array('album_id' => $data['id']))->delete();
        M('album_follow')->where(array('album_id' => $data['id']))->delete();
        M('album_comment')->where(array('album_id' => $data['id']))->delete();
    }
}