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
class item_commentModel extends Model
{
    protected $_auto = array (array('add_time','time',1,'function'));
    protected function _after_insert($data,$options) {
        $item_mod = D('item');
        $item_mod->where(array('id'=>$data['item_id']))->setInc('comments');
        $item_mod->update_comments($data['item_id'], array(
            'id' => $data['id'],
            'uid' => $data['uid'],
            'uname' => $data['uname'],
            'info' => $data['info']
        ));
    }
}