// JavaScript Document
$(function() {
    var focus=$("#myfocus");
    var width = focus.width();
    var num = $("ul li",focus).length;
    var index = 0;
    var picTimer;
    $("ul",focus).css("width",width * num);
    $(".btn",focus).mouseenter(function() {
        index = $(".btn",focus).index(this);
        showPics(index);
    }).eq(0).trigger("mouseenter");
    focus.hover(function() {
        clearInterval(picTimer);
    },function() {
        picTimer = setInterval(function(){
            showPics(index);
            index++;
            if(index == num) {
                index = 0;
            }
        },4000);
    }).trigger("mouseleave");
    function showPics(index) {
        var offset = -index*width;
        $("ul",focus).stop(true,false).animate({"left":offset},300);
        $(".btn",focus).stop(true,false).css({"background":"black"},300).eq(index).stop(true,false).css({"background":"red"},300);
    }
});
