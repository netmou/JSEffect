/**************************************************
 Drag.js
 作者：橡树小屋 07.17.2010
 修改：泪眼佛 leiyanfo@sina.com
 用法：Drag.initDrag(id); id是标签的id名称
 **************************************************/

var Drag={
	dragObject:null,
	mouseOffset:null,
	initDrag:function(item){
		this.item=document.getElementById(item);
		this.item.onmousedown=function(evnt){
			document.onmousemove=Drag.mouseMove;
			document.onmouseup=Drag.mouseUp;
			Drag.dragObject=this;
			Drag.mouseOffset=Drag.getMouseOffset(this,evnt);
            return false;
		}
	},
	mousePoint:function(x,y){
		this.x=x;
		this.y=y;
	},
	mousePosition:function (evnt){
		evnt=evnt||window.event;
		var x=parseInt(evnt.clientX);
		var y=parseInt(evnt.clientY);
		return new Drag.mousePoint(x,y);
	},
	getMouseOffset:function(target,evnt){
		var mousePos=Drag.mousePosition(evnt);
		var x=mousePos.x-target.offsetLeft;
		var y=mousePos.y-target.offsetTop;
		return new Drag.mousePoint(x,y);
	},
	mouseUp:function (evnt){
		Drag.dragObject=null;
		document.onmousemove = null;
		document.onmouseup   = null;
	},
	mouseMove:function(evnt){
		var mousePos=Drag.mousePosition(evnt);
		Drag.dragObject.style.position="absolute";
		Drag.dragObject.style.top=mousePos.y-Drag.mouseOffset.y+"px";
		Drag.dragObject.style.left=mousePos.x-Drag.mouseOffset.x+"px";
		return false;
	}
}
