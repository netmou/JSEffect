/**
* a utility javascript library
* @since 2015-01-27
**/

/**
* fix ie8- not support bind-function
**/
if (!Function.prototype.bind) {
    Function.prototype.bind = function (oThis) {
        if (typeof this !== "function") {
            throw new TypeError("Function.prototype.bind – what is trying to be bound is not callable");
        }
        var aArgs = Array.prototype.slice.call(arguments, 1),
        fToBind = this,
        fNOP = function () {},
        fBound = function (){
            return fToBind.apply(this instanceof fNOP && oThis?this:oThis,
            aArgs.concat(Array.prototype.slice.call(arguments,0)));
        };
        fNOP.prototype = this.prototype;
        fBound.prototype = new fNOP();
        return fBound;
    };
} // end fix ie8- not support bind-function

/**
 * @desc	页面加载完成时触发的驱动器
 * @param   doc document-文档对象
 * @param   func 触发的事件处理器
 * @param   timer 循环检测毫秒数
 * @return  void
 *  */
var ready=function (func, timer, doc) {
    doc=doc||document;
    if (doc.addEventListener) {
        doc.addEventListener("DOMContentLoaded", func, false);
    } else {
        timer = timer || 30;
        var counter = setInterval(function() {
            if (/(complete)/.test(doc.readyState)) {
                clearInterval(counter);
                func();
            }
        }, timer);
    }
}; //end function ready

/**
*将对象或数组转换成JSON字符串
*/
function toJSON(obj) {
    var text = "";
    var p=null;
    var pos=-1;
    if (obj instanceof Array) {
        text = "[";
        for (p in obj) {
            switch (typeof obj[p]) {
                case  "string":
                    text = text + "\"" + toUnicode(obj[p]) + "\", ";
                    break;
                case  "number":
                    text = text + obj[p] + ",  ";
                    break;
                case  "object":
                    text = text + toJSON(obj[p]) + ", ";
                    break;
                case  "boolean":
                    text = text + obj[p] + ", ";
                    break;
                case  "undefined":
                    text = text + "undefined" + ", ";
                    break;
            }
        }
        pos = text.lastIndexOf(",");
        if(pos>0){
            text = text.substring(0, pos) + "]";
        }else{
            text+="]";
        }

    } else if (obj instanceof Object) {
        text = "{";
        for (p in obj) {
            switch (typeof obj[p]) {
                case  "string":
                    text = text + "\"" + p + "\":\"" + toUnicode(obj[p]) + "\", ";
                    break;
                case  "number":
                    text = text + "\"" + p + "\":" + obj[p] + ",  ";
                    break;
                case  "object":
                    text = text +"\"" + p + "\":"+ toJSON(obj[p]) + ", ";
                    break;
                case  "boolean":
                    text = text + "\"" + p + "\":" + obj[p] + ", ";
                    break;
                case  "undefined":
                    text = text + "\"" + p + "\":" + "undefined" + ", ";
                    break;
            }
        }
        pos = text.lastIndexOf(",");
        if(pos>0){
            text = text.substring(0, pos) + "}";
        }else{
            text+="}";
        }
    }
    return text;
}
/**
*将字符串中的汉字进行Unicode编码为json传输数据做准备
*/
function toUnicode(str) {
    var result = "";
    var cnReg = /^[\u4E00-\u9FA5]+$/;
    for (var i = 0; i < str.length; i++) {
        var char=str.charAt(i);
        if (cnReg.test(char)) {
            result += '\\u' + str.charCodeAt(i).toString(16);
        } else {
            result += char;
        }
    }
    return result;
}
/**
* @desc 解析一个JSON字符串
* @param str 要处理的JSON字符串
* @return Array or Object
*/
function parseJSON(json){
    return eval('(' + json + ')');
}

/**
* @desc 返回一个两端没有空格的字符串
* @param str 要处理的字符串
* @return string
*/
var trim = function (str) {
    var pattern = /^\s+|\s+$/;
    return str.toString().replace(pattern, "");
}; //end function trim;

//加载一个图片，完成后调用回调函数
var loadImage = function (url, callback, error) {
    error = error || function(){};
    var image = new Image();
    image.src = url;
    if (image.complete) {
         callback.call(image);
        return true;
    }
    image.onload = function () {
        image.onload = null;
        callback.call(image);
    };
    image.onerror=function(){
        error.call(image);
    };
}; //end loadImage


/**
* @desc 运行代码
* @param str 要运行的代码
* @return undifined
*/
var runCode=function (code) {
    var handler = window.open("about:blank", "_blank");
    handler.document.open('text/html', 'replace');
    handler.document.write(code);
    handler.document.close();
    handler.opener = null; // 防止代码攻击
}; //end function runCode

/**
* @desc 打印部分代码
* @param str 要打印的代码Id
* @return undifined
*/
function partPrint(divId){
    var elem = document.getElementById(divId);
	var handler = window.open('about:blank', "_blank");
	handler.document.open('text/html', 'replace');
	handler.document.write(elem.innerHTML);
	handler.document.close();
	handler.print();
    handler.close();
}

/**
* @desc 获取使用如下参数调整的整个queryString
* -实现在页面跳转的时候get方法参数列表不丢失
* @param name http-method-get参数名
* @param value http-method-get参数值
* @reurn queryString
* */
var setQueryString = function (name, value) {
    var param = [];
    if (location.search!=='') {
        var query = decodeURIComponent(location.search).replace("?", "");
        param = query.split("&");
        var regex = new RegExp("^" + name + "=(.*)$", "i");
        for (var i = 0; i < param.length; i++) {
            if (regex.test(param[i]))
                param.splice(i--, 1);
        }
    }
    param[param.length] = name + "=" + value;
    return  "?" + param.join("&");
}; //end function setQueryString

/**
* @desc 对一个日期的字符串进行加减，
* @param str 日期的字符串
* @param month 月份增量
* @param date 日期增量
* @param year 年份增量
* @return (date)object
**/
function newDate(str,month,date,year){
    var pattern=/(\d{4})[\s\-\/]?(\d{2})?[\s\-\/]?(\d{2})?/;
    var rst=pattern.exec(str);
    var parse=function(x){
        var y=parseInt(x);
        return isNaN(y) ? 0 : y;
    };
    year=parse(rst[1])+parse(year);
    month=parse(rst[2])+parse(month);
    date=parse(rst[3])+parse(date);
    return new Date(year,month,date);
} //end function newDate

//格式化一个日期
// yyyy-mm-dd hh:ii:ss
var dateFormat = function(format,seconds){
    seconds=seconds||0;
    var date=new Date();
    date.setTime(seconds*1000);
    var o = {
        "m+" : date.getMonth()+1,
        "d+" : date.getDate(),
        "h+" : date.getHours(),
        "i+" : date.getMinutes(),
        "s+" : date.getSeconds(),
    };
    //先解析（四位）年份
    if(/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, date.getFullYear().toString().substr(4 - RegExp.$1.length));
    }
    //再解析（两位）的信息
    for(var k in o) {
        if(new RegExp("("+ k +")").test(format)) {
            var v=o[k].toString();
            format = format.replace(RegExp.$1, RegExp.$1.length==1 ? v : ("00"+ v).substr(v.length));
        }
    }
    return format;
}; //end dateFormat

/**
* @desc 检验18位身份证号码
* @author wolfchen
* @param cid 18为的身份证号码
* @return Boolean 是否合法
**/
var isCnNewID = function (cid){
    var arrExp = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];//加权因子
    var arrValid = [1, 0, "X", 9, 8, 7, 6, 5, 4, 3, 2];//校验码
    if(/^\d{17}\d|x$/i.test(cid)){
        var sum = 0, idx;
        for(var i = 0; i < cid.length - 1; i++){
            // 对前17位数字与权值乘积求和
            sum += parseInt(cid.substr(i, 1), 10) * arrExp[i];
        }
        // 计算模（固定算法）
        idx = sum % 11;
        // 检验第18为是否与校验码相等
        return arrValid[idx] == cid.substr(17, 1).toUpperCase();
    }else{
        return false;
    }
}; // end isCnNewID function

/**
* @func 循环执行的函数体
* @args 参数
* @count 执行次数
* @delay 延时毫秒数
* @callback 执行完毕的回调函数
**/
var cycleQuery = function(func, args, count, delay, callback) {
    var caller = this;//调用函数的上下文
    delay = delay || 100;
    callback= callback || function(){};
    var counter = setInterval(function() {
        if (--count < 0) {
            clearInterval(counter);
            return callback.call(caller);
        }
        func.apply(caller, args);
    }, delay);
}; // end function cycleQuery


/** 数字金额大写转换(可以处理整数,小数,负数) */
var digitConvertCn = function(n) {
    var fraction = ['角', '分'];
    var digit = [
        '零', '壹', '贰', '叁', '肆',
        '伍', '陆', '柒', '捌', '玖'
    ];
    var unit = [
        ['元', '万', '亿'],
        ['', '拾', '佰', '仟']
    ];
    var head = n < 0 ? '欠' : '';
    n = Math.abs(n);
    var s = '';
    for (var i = 0; i < fraction.length; i++) {
        s += (digit[Math.floor(n * 10 * Math.pow(10, i)) % 10] + fraction[i]).replace(/零./, '');
    }
    s = s || '整';
    n = Math.floor(n);
    for (i = 0; i < unit[0].length && n > 0; i++) {
        var p = '';
        for (var j = 0; j < unit[1].length && n > 0; j++) {
            p = digit[n % 10] + unit[1][j] + p;
            n = Math.floor(n / 10);
        }
        s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
    }
    return head + s.replace(/(零.)*零元/, '元')
        .replace(/(零.)+/g, '零')
        .replace(/^整$/, '零元整');
};