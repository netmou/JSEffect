$(function(){
    $("tr td").hover(function(){
        $(this).addClass("over");
    },function(){
        $(this).removeClass("over");
    });
    $("tr:odd td").addClass("odd");
    $("tr:even td").addClass("even");
});

/**
* 检验18位身份证号码
* @author wolfchen
* @param cid 18为的身份证号码
* @return Boolean 是否合法
**/
function isCnNewID(cid){
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
}
