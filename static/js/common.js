function init_input() {
	$('.text').each(function() {
		$this = $(this);
		var tag = $this[0].tagName;
		if (tag == 'INPUT') {
			$this.val($this.attr('data-default'));
		} else if (tag == 'TEXTAREA') {
			$this.html($this.attr('data-default'));
		}
	}).click(function() {
		if ($(this).attr('data-default') == $(this).val()) {
			$(this).val('');
		}
	}).blur(function() {
		$this = $(this);
		if ($this.attr('data-default') == $this.val() || $.trim($this.val()) == '') {
			$this.val($this.attr('data-default'));
		}
	});
}

function checkbox(name, val) {
	for (var i = 0; i < val.length; i++) {
		$('input[name="' + name + '"][value="' + val[i] + '"]').attr('checked', true);
	}
}

function check_login(content) {
	if (!def.is_login) {
		$.zhiphp._tip({
			content: content || '请登录!',
			status: false,
			url: [{
				url: 'index.php?m=user&a=register',
				title: '注册'
			},
			{
				url: 'index.php?m=user&a=login',
				title: '登录'
			}]
		});
	}
	return def.is_login;
}

function cookie_exist(name, v) {
	var val = $.trim($.cookie(name));
	var ids = val.split(",");
	for (i in ids) {
		if (v == parseInt(ids[i])) {
			return true;
		}
	}
	val += "," + v;
	$.cookie(name, val);
	return;
}

function toggle_content($this) {
	var is_short = $this.attr("class") == 'short_content';
	$showtext_body = $('.showtext_body', $this.parent().parent());
	var old_height, new_height;
	if (is_short) {
		$this.removeClass('short_content').addClass('long_content').text('向上收起');
		$old_height = $showtext_body.height();
		$showtext_body.css({
			'overflow': 'visible',
			'height': 'auto'
		});
		$new_height = $showtext_body.height();
		$('img', $showtext_body).show();
		$showtext_body.removeClass('showcont_l');
		$showtext_body.removeClass('fl');
	} else {
		$this.removeClass('long_content').addClass('short_content').text('展开全文');
		$showtext_body.css({
			'overflow': 'hidden',
			'height': '169px'
		});
		$('img', $showtext_body).hide();
		$showtext_body.addClass('showcont_l');
		$showtext_body.addClass('fl');
	}
}

function MarqueeNews() {
	$('#news').find("ul").animate({
		marginTop: "-20px"
	}, 1000, function() {
		$(this).css({
			marginTop: "0px"
		}).find("li:first").appendTo(this)
	})
}
var MarNews = setInterval(MarqueeNews, 3000);

function gstop() {
	clearInterval(MarNews);
}

function gstart() {
	MarNews = setInterval(MarqueeNews, 3000);
}

function goup() {
	$('#news').find("ul li").last().insertBefore($('#news').find("ul li").first());
	$('#news').find("ul").css({
		marginTop: '-20px'
	});
	$('#news').find("ul").animate({
		marginTop: "0px"
	}, 500)
}

function godown() {
	$('#news').find("ul").animate({
		marginTop: "-20px"
	}, 500, function() {
		$(this).css({
			marginTop: "0px"
		}).find("li:first").appendTo(this)
	})
} /*头部公告E*/

function AddFavorite(sURL, sTitle) {
	try {
		window.external.addFavorite(sURL, sTitle);
	} catch (e) {
		try {
			window.sidebar.addPanel(sTitle, sURL, "");
		} catch (e) {
			alert("您的浏览器不支持点击添加，请按下 Ctrl + D 添加到收藏夹");
		}
	}
}
/*
返回值：类似".jpg"
*/
function get_file_extension(path){
    return $.trim(path.substr(path.lastIndexOf(".")));
}

function create_datepicker() {
    if ($.fn.datepicker) {
        $(".J_date_picker").each(function () {
            var opt = {
                showWeek: true,
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                timeFormat: 'HH:mm:ss',
                yearRange:'-100:+50',
                stepHour: 1,
                stepMinute: 1,
                stepSecond: 1,
                timeOnlyTitle: '1',
                timeText: '时间',
                hourText: '小时',
                minuteText: '分钟',
                secondText: '秒',
                currentText: '现在',
                closeText: '关闭'
            };
            if ($(this).attr("data-minDate")) {
                opt.onClose = function (selectedDate) {
                    $($(this).attr("data-minDate")).datetimepicker("option", "minDate", selectedDate);
                };
            }
            if ($(this).attr("data-maxDate")) {
                opt.onClose = function (selectedDate) {
                    $($(this).attr("data-maxDate")).datetimepicker("option", "maxDate", selectedDate);
                };
            }
            $(this).datetimepicker(opt);
        });
    }
};
function parse_form(){
    $('select').each(function(){
        $('option[value="'+$(this).attr('data-selected')+'"]').attr('selected','selected');
    });
}
function subtext(str,length){
    str=$.trim(str);
    if(parseInt(str.length)>parseInt(length)){
        str=str.substr(0,length/2)+'...'+str.substr(str.length-length/2,str.length-1);
    }
    return str;
}
function htmlspecialchars(str){
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/"/g, '&quot;');
    str = str.replace(/'/g, '&#039;');
    return str;
}
function htmlspecialchars_decode (str) {
    str = str.replace(/&amp;/g, '&');
    str = str.replace(/&quot;/g, '"');
    str = str.replace(/&#039;/g, '\'');
    return str;
}
function create_share_widget(){
    $('.J_share_widget .item').live('click',function(){
        var type=$(this).attr('type');
    });
}