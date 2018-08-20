$(function() {
    // 清除a和button标签的焦点
    $(document).on('focus', 'a, button', function() {
        this.blur();
    });

    // 禁止回车提交表单
    $(document).on('keydown', '.select-group input', function(event) {
        if (event.keyCode == 13) {
            return false;
        }
    });
});

// trim方法
String.prototype.trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, '');
}

String.prototype.ltrim = function() {
    return this.replace(/(^\s*)/g, '');
}
String.prototype.rtrim = function() {
    return this.replace(/(\s*$)/g, '');
}

// 回车转br
String.prototype.nl2br = function() {
    return this.replace(/(\r\n?|\n)/g, '<br>');
}

String.prototype.replaceAll = function(s1,s2) {
    return this.replace(new RegExp(s1,"gm"),s2);
}

// 简单模板引擎
String.prototype.assign = function(replace, exclude, nl2br) {

    var placeHolder;
    var value = this;
    var list = value.match(/(\{|(%7B))\$.+?(\}|(%7D))/ig);
    var nl2br = nl2br || true;
    var isIE = /*@cc_on!@*/false;

    for (var i = 0; placeHolder = list[i]; i++)
    {
        var key = placeHolder.replace('{', '')
            .replace('}', '')
            .replace('$', '')
            .replace('%7B', '', 'i')
            .replace('%7D', '', 'i');

        if (replace[key] === false ||
            replace[key] === null  ||
            replace[key] === undefined) {

            value = value.replace(placeHolder, '');
            continue;
        }

        if (exclude && $.inArray(key, exclude) != -1) {
            // no escape
            value = value.replace(placeHolder, replace[key], 'g');
            continue;
        }

        if (nl2br) {
            // lineFeed replacing for IE
            if (isIE) {
                value = value.replace(placeHolder,
                            $('<div />').text(String(replace[key]).replace(/(\r\n?|\n)/g, '###lineFeed###'))
                                        .html().replace(/###lineFeed###/g, '<br>'), 'g');
            } else {
                value = value.replace(placeHolder,
                            $('<div />').text(replace[key]).html().nl2br(), 'g');
            }
            continue;
        }

        value = value.replace(placeHolder, $('<div/>').text(replace[key]).html(), 'g');
    }

    return value;
}

function isset(variable) {
    return typeof variable != 'undefined' ? true : false;
}

function isMobile() {
    var flag = false;
    if (/android/i.test(navigator.userAgent)){
        flag = true;
    }

    if (/ipad|iphone/i.test(navigator.userAgent)){
        flag = true;
    }
    return flag;
}

function hasScroll() {
    var flg = $(document).scrollTop() > 0 ? true : false;

    if (!flg) {
        $(document).scrollTop(1);
        flg = $(document).scrollTop() > 0 ? true : false;
        $(document).scrollTop(0);
    }

    return flg;
}

// 改变当前页数
function change_page(page)
{
    var newUrl = change_url_arg(location.href, 'page', page);
    location.href = newUrl;
}

// 改变url里query的某个值
function change_url_arg(url, arg, arg_val)
{
    var pattern = arg + '=([^&]*)';
    var replaceText = arg + '=' + arg_val;

    if (url.match(pattern)) {
        var tmp = '/(' + arg + '=[^&]*)/gi';
        tmp = url.replace(eval(tmp),replaceText);
        return tmp;
    } else {
        if (url.match('[\?]')) {
            return url + '&' + replaceText;
        }else{
            return url + '?' + replaceText;
        }
    }
}

function auth(url) {
    if ($('#auth-check').val() == 'true') {
        window.location.href = url;
    } else {
        $.toaster({ priority : 'danger', title : '需要登录', message : '正在跳往登录页面' });

        setTimeout(function() {
            window.location.href = $('#login-url').val() + '?return_url=' + encodeURIComponent(url);
        }, 3000);
    }
}

var Base = function() {
    this.waiting = false;
}

Base.prototype.initForm = function(uri) {
    var self = this;
    var uri = uri || false;

    // ajax提交
    $(document).on('submit', '.main-form', function() {
        var self = $(this);
        self.find('button[type="submit"]').attr('disabled', 'disabled');

        $(this).ajaxSubmit({
            success: function(response) {
                if (response.result !== true) {
                    self.find('button[type="submit"]').removeAttr('disabled');
                    $.toaster({ priority : 'danger', title : '失败', message : response.message });
                    return;
                }

                $.toaster({ priority : 'success', title : '成功', message : response.message });

                if (response.go_url) {
                    setTimeout(function() {
                        window.location.href = response.go_url;
                    }, 1500);
                } else if (uri) {
                    if (uri == 'edit') {
                        uri += '/' + response.new_id;
                    }

                    setTimeout(function() {
                        window.location.href = uri;
                    }, 1500);
                } else {
                    self.find('button[type="submit"]').removeAttr('disabled');
                }
            },
            error: function () {
                self.find('button[type="submit"]').removeAttr('disabled');
                $.toaster({ priority : 'danger', title : '失败', message : response.message });
            }
        });

        return false;
    });
}

Base.prototype.disableCaptcha = function(target, second) {
    this.waiting = true;
    target.attr('disabled', 'disabled');

    // 每秒递减等待时间
    var func = function() {
        target.text(second-- + '秒后可重发');
    }

    func();
    var timer = setInterval(func, 1000);

    // 60秒后可再次使用
    setTimeout(function() {
        self.waiting = false;
        target.removeAttr('disabled');
        target.text('往邮箱发送验证码');
        clearInterval(timer);
    }, second * 1000);
}

Base.prototype.sendCaptcha = function(target) {
    var self = this;

    var data = {
        email  : $('input[name="email"]').val(),
        _token  : $('input[name="_token"]').val()
    };

    if (self.waiting === true) {
        alert('请稍候...验证码每60秒只能发送一次');
        return false;
    }
    self.waiting = true;
    target.attr('disabled', 'disabled');
    target.text('正在努力发送...');

    $.post('/common/captcha/email', data, function(response) {
        if (response.result !== true) {
            $.toaster({ priority : 'danger', title : '失败', message : response.message });
            self.waiting = false;
            target.removeAttr('disabled');
            target.text('往邮箱发送验证码');
            return false;
        }

        $.toaster({ priority : 'success', title : '成功', message : response.message });

        // 每秒递减等待时间
        var second = 60;
        var func = function() {
            target.text(second-- + '秒后可重发');
        }

        func();
        var timer = setInterval(func, 1000);

        // 60秒后可再次使用
        setTimeout(function() {
            self.waiting = false;
            target.removeAttr('disabled');
            target.text('往邮箱发送验证码');
            clearInterval(timer);
        }, 60000);
    }).complete(function(){
        //
    }).error(function(){
        $.toaster({ priority : 'danger', title : '失败', message : '网络错误' });
        self.waiting = false;
        target.removeAttr('disabled');
        target.text('往邮箱发送验证码');
    });
}
