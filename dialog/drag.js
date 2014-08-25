/**************************************************
 Drag.js
 作者：泪眼佛 leiyanfo@sina.com
 **************************************************/

(function($){
    $.fn.drag=function(aim){
        return $(this).each(function(){
            var drag=this;
            var startX=0;
            var startY=0;
            var ondrag=false;
            $(drag).css({
                position:"absolute",
                display:"block"
            });
            $(document).on( "mousemove", function(event) {
                if(ondrag  && 1  == event.which){
                    $(drag).offset({
                        left:event.pageX - startX,
                        top:event.pageY - startY
                    });
                }
            });
            $(document).on( "mouseup", function(event) {
                ondrag = false;
                $(aim || drag).css("cursor","auto");
            });
            $(aim || drag).on( "mousedown", function(event) {
                ondrag = true;
                $(aim || drag).css("cursor","move");
                var offset = $(drag).offset();
                startX = event.pageX - offset.left;
                startY = event.pageY - offset.top;
            });
        });
    }
})(jQuery)
