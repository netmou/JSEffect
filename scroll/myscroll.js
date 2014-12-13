(function($) {
    $.fn.myscroll = function(opts) {
        var defaults = {
            interval: 40,
            step: 1,
            dir: "left"
        };
        opts = $.extend(defaults, opts);
        return this.each(function() {
            var cell = $("li", this);
            $(this).css({overflow: "hidden", position: "relative"}); //div
            cell.parent().css({"margin": "0", "padding": "0", "overflow": "hidden", "position": "relative", "list-style": "none"}); //ul
            cell.css({"position": "relative", "overflow": "hidden"}); //li
            if (opts.dir == "left"){
                 cell.css({"display":"inline","float": "left","list-style": "none"});
            }
            //初始大小
            var size = 0;
            for (var i = 0; i < cell.size(); i++){
                size += opts.dir == "left" ? cell.eq(i).outerWidth(true) : cell.eq(i).outerHeight(true);
            }
            if(size < parseInt($(this).css("width"))){//如果宽度不够就不滚动
                return null;
            }
            //循环所需要的元素
            if (opts.dir == "left")
                cell.parent().css({"width": (size * 2) + "px"});
            cell.parent().append(cell.clone());
            var tcell=$("li",this);//更新li集合，包含克隆li
            //滚动
            var offset = 0;
            function player() {
                offset += opts.step;
                if (offset >= size) {
                    offset = 0;
                    tcell.parent().css(opts.dir == "left" ? {"left": 0} : {"top": 0});
                }
                tcell.parent().css(opts.dir == "left" ? {"left": -offset} : {"top": -offset});
            }
            //开始
            var move = setInterval(function() {
                player();
            }, opts.interval);
            tcell.parent().hover(function() {
                clearInterval(move);
            }, function() {
                clearInterval(move);
                move = setInterval(function() {
                    player();
                }, opts.interval);
            });
        });
    };
})(jQuery);
