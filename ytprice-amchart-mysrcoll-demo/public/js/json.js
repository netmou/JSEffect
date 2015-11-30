/**将对象或数组转换成json*/
function toJSON(obj) {
    var text = "";
    if (obj instanceof Array) {
        text = "[";
        for (var p in obj) {
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
        var pos = text.lastIndexOf(",");
        text = text.substring(0, pos) + "]";
    } else if (obj instanceof Object) {
        text = "{";
        for (var p in obj) {
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
        var pos = text.lastIndexOf(",");
        text = text.substring(0, pos) + "}";
    }
    return text;
}
/**将字符串中的汉字进行Unicode编码为json传输数据做准备*/
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
