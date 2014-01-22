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
class TagLibList extends TagLib {
    public function __call($method, $args) {    
        $tag = substr($method, 1);
        $_tag = parent::parseXmlAttr($args[0], $tag);
        $_tag['id']=isset($_tag['id'])?$_tag['id']:"val";
        $parse_str = "<?php \$mod=D('$tag');";
        $parse_str .= "\$res=\$mod";
        foreach($_tag as $key=>$val){
            if(in_array($key,array('where','limit','order','field','relation'))){
                $parse_str.="->$key('$val')";    
            }
        }
        $parse_str .= "->select();";
        $parse_str .= "foreach(\$res as \$$_tag[id]){ ?>";
        $parse_str .= $this->tpl->parse($args[1]);
        $parse_str .= '<?php } ?>';
        return $parse_str;
    }
    function getTags() {
        if(S('tagliblist_tags')==false){
            $mod = new Model();
            $table_list=$mod->db->getTables();            
            foreach ($table_list as $val) {
                $name = substr($val, strlen(C('DB_PREFIX')));
                $tags[$name] = array(
                    'attr' => 'where,limit,order,fiedld',
                    'close' => 1,
                    'level' => 3);
            }            
            S('tagliblist_tags',$tags);
        }        
        return S('tagliblist_tags');
    }
}
?>