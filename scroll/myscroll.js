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
            cell.parent().parent().css({overflow: "hidden", position: "relative"}); //div
            cell.parent().css({margin: "0", padding: "0", overflow: "hidden", position: "relative", "list-style": "none"}); //ul
            cell.css({position: "relative", overflow: "hidden"}); //li
            if (opts.dir == "left"){
                 cell.css({float: "left"});
            }
            //初始大小
            var size = 0;
            for (var i = 0; i < cell.size(); i++){
                size += opts.dir == "left" ? cell.eq(i).outerWidth(true) : cell.eq(i).outerHeight(true);
            }
            //循环所需要的元素
            if (opts.dir == "left")
                cell.parent().css({width: (size * 3) + "px"});
            cell.parent().empty().append(cell.clone()).append(cell.clone()).append(cell.clone());
            cell = $("li", this);
            //滚动
            var offset = 0;
            function player() {
                offset += opts.step;
                if (offset >= size) {
                    offset = 0;
                    cell.parent().css(opts.dir == "left" ? {left: 0} : {top: 0});
                }
                cell.parent().css(opts.dir == "left" ? {left: -offset} : {top: -offset});
            }
            //开始
            var move = setInterval(function() {
                player();
            }, opts.interval);
            cell.parent().hover(function() {
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
