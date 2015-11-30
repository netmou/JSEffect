(function($) {
    $.fn.myscroll = function(opts) {
        var defaults = {
            interval: 40,
            step: 1,
            dir: "left"
        };
        opts = $.extend(defaults, opts);
		var move=null,player=null;
        this.each(function() {
            var cell = $("ul>li", this);
            $(this).css({overflow: "hidden", position: "relative"}); //div
            cell.parent().css({"margin": "0", "padding": "0", "overflow": "hidden", "position": "relative", "list-style": "none"}); //ul
            cell.css({"position": "relative", "overflow": "hidden"}); //li
            cell.css({"display":"inline","float": "left","list-style": "none"});
            
            //初始大小
            var size = 0;
            for (var i = 0; i < cell.size(); i++){
                size +=  cell.eq(i).outerWidth(true);
            }
            if(size < parseInt($(this).css("width"))){//如果宽度不够就不滚动
                return null;
            }
            //循环所需要的元素
            cell.parent().css({"width": (size * 3) + "px"});
            cell.parent().append(cell.clone());
			cell.parent().append(cell.clone());
            var tcell=$("ul>li",this);//更新li集合，包含克隆li
			tcell.parent().css({"left": -size});
            //滚动
			//return ;
            var offset = 0;
            var player=function() {
				if(opts.dir=="left"){
					offset += opts.step;
				}else{
					offset -= opts.step;
				}
                if (Math.abs(offset)>= size) {
                    offset = 0;
                    tcell.parent().css({"left": -size});
                }
                tcell.parent().css({"left": -size-offset});
            }
            //开始
            var move = setInterval(function() {
                player.call(this);
            }, opts.interval);
            tcell.parent().on("mouseover",function() {
				if(move){
					clearInterval(move);
				}
            });
			tcell.parent().on("mouseout",function() {
				if(move){
					clearInterval(move);
				}
                move = setInterval(function() {
                    player.call(this);
                }, opts.interval);
            });
        });
		this.setOpts=function(newOpts){
			opts=newOpts;
			$('ul',this).trigger("mouseout");
		}
		return this;
    };
})(jQuery);
