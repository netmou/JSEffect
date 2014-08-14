// JavaScript Document
$(function() {
	var focus=$("#bigfocus");
	var width = focus.width(); //获取焦点图的宽度（显示面积）
	var num = $("ul li",focus).length; //获取焦点图个数
	var index = 0;
	var picTimer;
	$(".btnWrap",focus).css("opacity",0.4);

	//为小圆按钮添加鼠标滑入事件，以显示相应的内容
	$(".btn",focus).css("opacity",0.4).mouseenter(function() {
		index = $(".btn",focus).index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");

	//上一页、下一页按钮透明度处理
	$(".next,.prev",focus).css("opacity",0.2).hover(function() {
		$(this).stop(true,false).animate({"opacity":"0.5"},300);
	},function() {
		$(this).stop(true,false).animate({"opacity":"0.2"},300);
	});

	//上一页按钮
	$(".prev",focus).click(function() {
		index -= 1;
		if(index == -1) {index = num - 1;}
		showPics(index);
	});

	//下一页按钮
	$(".next",focus).click(function() {
		index += 1;
		if(index == num) {index = 0;}
		showPics(index);
	});

	//本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
	$("ul",focus).css("width",width * num);

	//鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
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
	//显示图片函数，根据接收的index值显示相应的内容
	function showPics(index) {
		//根据index值计算ul元素的left值
		var nowLeft = -index*width;
		//通过animate()调整ul元素滚动到计算出的position
		$("ul",focus).stop(true,false).animate({"left":nowLeft},300);
		//调整小圆形按钮的外观样式
		$(".btn",focus).stop(true,false).animate({"opacity":"0.5"},300).eq(index).stop(true,false).animate({"opacity":"1"},300);
	}
});
