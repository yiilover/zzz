/**
 * **********************后台操作JS************************
 * ajax 状态显示
 * confirmurl 操作询问
 * showdialog 弹窗表单
 * attachment_icon 附件预览效果
 * preview 预览图片大图
 * cate_select 多级菜单动态加载
 *
 * http://www.zhiphp.com
 * author: andery@foxmail.com
 */
;
$(function ($) {
    //AJAX请求效果
    $('#J_ajax_loading').ajaxStart(function () {
        $(this).show();
    }).ajaxSuccess(function () {
            $(this).hide();
        });

    //确认操作
    $('.J_confirmurl').live('click', function () {
        var self = $(this),
            uri = self.attr('data-uri'),
            acttype = self.attr('data-acttype'),
            title = (self.attr('data-title') != undefined) ? self.attr('data-title') : lang.confirm_title,
            msg = self.attr('data-msg'),
            callback = self.attr('data-callback');
        if (acttype == 'batch_action') {
            $('.J_checkitem').attr("checked", false);
            $('.J_checkitem[value="' + self.attr('data-row_id') + '"]').attr("checked", 'checked');
            $('input[data-tdtype="batch_action"]').trigger('click');
            $.dialog.get(self.attr('data-id')).title(self.attr('data-title'));
            return;
        }
        $.dialog({
            title: title,
            content: msg,
            padding: '10px 20px',
            lock: true,
            ok: function () {
                if (acttype == 'ajax') {
                    $.getJSON(uri, function (result) {
                        if (result.status == 1) {
                            $.zhiphp.tip({content: result.msg});
                            if (callback != undefined) {
                                eval(callback + '(self)');
                            } else {
                                window.location.reload();
                            }
                        } else {
                            $.zhiphp.tip({content: result.msg, icon: 'error'});
                        }
                    });
                } else {
                    location.href = uri;
                }
            },
            cancel: function () {
            }
        });
    });

    //弹窗表单
    $('.J_showdialog').live('click', function () {
        var self = $(this),
            dtitle = self.attr('data-title'),
            did = self.attr('data-id'),
            duri = self.attr('data-uri'),
            dwidth = parseInt(self.attr('data-width')),
            dheight = parseInt(self.attr('data-height')),
            dpadding = (self.attr('data-padding') != undefined) ? self.attr('data-padding') : '',
            dcallback = self.attr('data-callback');
        $.dialog({id: did}).close();
        $.dialog({
            id: did,
            zIndex:100,
            title: dtitle,
            width: dwidth ? dwidth : 'auto',
            height: dheight ? dheight : 'auto',
            padding: dpadding,
            lock: true,
            ok: function () {
                var info_form = this.dom.content.find('#info_form');
                if (info_form[0] != undefined) {
                    $(info_form).append('<input type="hidden" name="ajax" value="1"/>');
                    info_form.submit();
                    if (dcallback != undefined) {
                        eval(dcallback + '()');
                    }
                    return false;
                }
                if (dcallback != undefined) {
                    eval(dcallback + '()');
                }
            },
            cancel: function () {
            }
        });
        $.getJSON(duri, function (result) {
            if (result.status == 1) {
                var script="<script type='text/javascript'>\
                $(function(){\
                    var form_id= $('#d-content-"+did+" form').attr('id');\
                    $.formValidator.initConfig({formid:form_id,autotip:true});\
                    $('#'+form_id).ajaxForm({success:complate,dataType:'json'});\
                    function complate(result){\
                        if(result.status == 1){\
                            $.dialog.get(result.dialog).close();\
                            $.zhiphp.tip({content:result.msg});\
                            window.location.reload();\
                        } else {\
                            $.zhiphp.tip({content:result.msg, icon:'alert'});\
                        }\
                    };\
                });</script>";

                $.dialog.get(did).content(script+result.data);
                create_datepicker();
            }
        });
        return false;
    });

    //附件预览
    $('.J_attachment_icon').live('mouseover',function () {
        var ftype = $(this).attr('file-type');
        var rel = $(this).attr('file-rel');
        switch (ftype) {
            case 'image':
                if (!$(this).find('.attachment_tip')[0]) {
                    $('<img class="attachment_tip" width="160" height="80" src="' + rel + '" />').prependTo($(this)).fadeIn();
                } else {
                    $(this).find('.attachment_tip').fadeIn();
                }
                break;
        }
    }).live('mouseout', function () {
            $('.attachment_tip').hide();
        });
    //积分等级
    $('.J_user_level').live('click', function () {
        var $overlay = $('.overlay', this);
        if ($overlay.size()==0) {
            var html='<div class="overlay clearfix"><div class="title">选择图标</div><ul class="clearfix">';
            for(i=0;i<=21;i++){
                html+='<li><img src="static/images/user_level/'+i+'.gif" title="编号'+i+'"></li>';
            }
            html+='</ul></div>';
            $(this).append(html);
            $('img',this).click(function(){
                $('#J_img').val($(this).attr('src'));
            });
        }else{
            $overlay.remove();
        }
    });
});

//显示大图
;
(function ($) {
    $.fn.preview = function () {
        var w = $(window).width();
        var h = $(window).height();

        $(this).each(function () {
            $(this).hover(function (e) {
                if (/.png$|.gif$|.jpg$|.bmp$|.jpeg$/.test($(this).attr("data-bimg"))) {
                    $('#preview').remove();
                    $("body").append("<div id='preview'><img src='" + $(this).attr('data-bimg') + "' /></div>");
                }
                var show_x = $(this).offset().left + $(this).width();
                var show_y = $(this).offset().top;
                var scroll_y = $(window).scrollTop();
                $("#preview").css({
                    position: "absolute",
                    padding: "4px",
                    border: "1px solid #f3f3f3",
                    backgroundColor: "#eeeeee",
                    top: show_y + "px",
                    left: show_x + "px",
                    zIndex: 1000
                });
                
                $("#preview > div").css({
                    padding: "5px",
                    backgroundColor: "white",
                    border: "1px solid #cccccc"
                });
                if (show_y + 230 > h + scroll_y) {
                    $("#preview").css("bottom", h - show_y - $(this).height() + "px").css("top", "auto");
                } else {
                    $("#preview").css("top", show_y + "px").css("bottom", "auto");
                }
                $("#preview").fadeIn("fast");
                $("#preview img").css({
                    'maxWidth': '500px',
                    'maxHeight': '500px'
                });
            }, function () {
                $("#preview").remove();
            })
        });
    };
})(jQuery);

;
(function ($) {
    //联动菜单
    $.fn.cate_select = function (options) {
        var cate_sel = this.selector;
        //console.log(cate_sel);
        var settings = {
            field: 'J_cate_id',
            top_option: lang.please_select
        };
        if (options) {
            $.extend(settings, options);
        }

        var self = $(this),
            pid = self.attr('data-pid'),
            uri = self.attr('data-uri'),
            selected = self.attr('data-selected'),
            selected_arr = [];
        if (selected != undefined && selected != '0') {
            if (selected.indexOf('|')) {
                selected_arr = selected.split('|');
            } else {
                selected_arr = [selected];
            }
        }
        self.nextAll(cate_sel).remove();
        $('<option value="">--' + settings.top_option + '--</option>').appendTo(self);
        $.getJSON(uri, {id: pid}, function (result) {
            if (result.status == '1') {
                for (var i = 0; i < result.data.length; i++) {
                    $('<option value="' + result.data[i].id + '">' + result.data[i].name + '</option>').appendTo(self);
                }
            }
            if (selected_arr.length > 0) {
                //IE6 BUG
                setTimeout(function () {
                    self.find('option[value="' + selected_arr[0] + '"]').attr("selected", true);
                    self.trigger('change');
                }, 1);
            }
        });

        var j = 1;
        $(this.selector).die('change').live('change', function () {
            var _this = $(this),
                _pid = _this.val();
            _this.nextAll(cate_sel).remove();
            if (_pid != '') {
                $.getJSON(uri, {id: _pid}, function (result) {
                    if (result.status == '1') {
                        var _childs = $('<select class="' + cate_sel.substr(1) + ' mr10" data-pid="' + _pid + '"><option value="">--' + settings.top_option + '--</option></select>')
                        for (var i = 0; i < result.data.length; i++) {
                            $('<option value="' + result.data[i].id + '">' + result.data[i].name + '</option>').appendTo(_childs);
                        }
                        _childs.insertAfter(_this);
                        if (selected_arr[j] != undefined) {
                            //IE6 BUG
                            //setTimeout(function(){
                            _childs.find('option[value="' + selected_arr[j] + '"]').attr("selected", true);
                            _childs.trigger('change');
                            //}, 1);
                        }
                        j++;
                    }
                });
                $('#' + settings.field).val(_pid);
            } else {
                $('#' + settings.field).val(_this.attr('data-pid'));
            }
        });
    }
})(jQuery);
function add_cate($this) {
    $region = $("#cate_selected");
    var val = parseInt($this.prev().val()) || 0;
    var text = $this.prev().find("option:selected").text();
    if (val == 0) {
        val = parseInt($this.prev().prev().val()) || 0;
        text = $this.prev().prev().find("option:selected").text();
    }
    if (val > 0 && $("input[value='" + val + "']", $region).size() == 0) {
        var html = '<input type="checkbox" name="cate_id[]" value="' + val + '" checked="checked"/>'
            + text;
        $region.append(html);
    }
}
function checkbox(name, val) {
    for (var i = 0; i < val.length; i++) {
        $('input[name="' + name + '"][value="' + val[i] + '"]').attr('checked', true);
    }
}