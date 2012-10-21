if ($ == null) var $ = function(id){return document.getElementById(id);};
// window事件对内容编辑工具的影响------------------------------------------------------------------------------------------------
// 加载
window.onload = function()
{
	fSetEditable('htmleditor');
	$('switch').checked = false;
	set_editor_wh();
}

// 可以编辑
function fSetEditable(frameName)
{
	var f = window.frames[frameName];
	f.document.designMode = 'on';
	if (!document.all) f.document.execCommand('useCSS', false, true);
}

// 设置编辑窗口的高度
function set_editor_wh()
{
	// 由editor的高度来自动设置html编辑窗口的高度
	var h = parseInt(window.parent.document.getElementById('editor').style.height);
	var w = parseInt(window.parent.document.getElementById('editor').style.width);
	$('htmleditor').style.height = (h-27) + 'px';
	$('htmleditor').style.width = (w-2) + 'px';
	$('sourceeditor').style.height = (h-27) + 'px';
	$('sourceeditor').style.width = w + 'px';
}

// 获取浏览器信息
var gIEVer = function fGetIEVer()
{
	var iVerNo = 0;
	var sVer = navigator.userAgent;
	if(sVer.indexOf("MSIE")>-1)
	{
		var sVerNo = sVer.split(";")[1];
		sVerNo = sVerNo.replace("MSIE","");
		iVerNo = parseFloat(sVerNo);
	}
	return iVerNo;
}

// 内容编辑工具使用方法------------------------------------------------------------------------------------------------
// 字体
function fontface(obj)
{
	if (obj!=null && obj.value != -1)
		format('htmleditor', 'FontName', obj.value);
}

// 字号大小
function fontsize(obj)
{	
	if (obj!=null && obj.value != -1)
		format('htmleditor', 'FontSize', obj.value);
}

// 字体颜色
function forecolor(obj)
{
	if (obj!=null && obj.value != -1)
		format('htmleditor', 'ForeColor', obj.value);
}

// 加重
function bold(obj)
{ format('htmleditor', 'Bold'); }

// 斜体
function italic(obj)
{ format('htmleditor', 'Italic'); }

// 下划线
function underline(obj)
{ format('htmleditor', 'Underline'); }

// 删除线
function strikethrough(obj)
{ format('htmleditor', 'StrikeThrough'); }

// 上标
function superscript(obj)
{ format('htmleditor', 'SuperScript'); }

// 下标
function subscript(obj)
{ format('htmleditor', 'SubScript'); }

// 有序排列--数字序号
function insertorderedlist(obj)
{ format('htmleditor', 'InsertOrderedList'); }

// 无序排列--圆点序号
function insertunorderedlist(obj)
{ format('htmleditor', 'InsertUnorderedList'); }

// 向前缩进
function outdent(obj)
{ format('htmleditor', 'Outdent'); }

// 向后缩进
function indent(obj)
{ format('htmleditor', 'Indent'); }

// 居左
function justifyleft(obj)
{ format('htmleditor', 'JustifyLeft'); }

// 居右
function justifyright(obj)
{ format('htmleditor', 'JustifyRight'); }

// 居中
function justifycenter(obj)
{ format('htmleditor', 'JustifyCenter'); }

// 添加连接
function createlink(obj)
{ 
	var surl=window.prompt("Enter link location (e.g. http://www.baidu.com/):", "http://");
	if ((surl!=null) && (surl!="http://"))
		format('htmleditor', 'CreateLink', surl);
}

// 查看源代码  鼠标至上的事件
function set_mode_tip(obj)
{
	var x = getX(obj);
	var y = getY(obj);
	var div_mode_tip = $('div_mode_tip');
	if (!div_mode_tip)
	{
		var div = document.createElement('div');
		div.style.position = 'absolute';
		div.style.top = (y+20) + 'px';
		div.style.left = (x-40) + 'px';
		div.style.zIndex = '999';
		div.style.fontSize = '12px';
		div.id = 'div_mode_tip';
		div.style.padding = '2px 2px 0px 2px';
		div.style.border = '1px solid #000000';
		div.style.backgroundColor = '#ffffcc';
		div.style.height = '12px';
		div.innerHTML = '编辑源码';
		document.body.appendChild(div);
	}
	else div_mode_tip.style.display = '';
}

// 查看源代码 鼠标移走的事件
function hide_tip()
{
	var div_mode_tip = $('div_mode_tip');
	if (div_mode_tip) div_mode_tip.style.display = "none";
}

// 设置模板 自动调用该方法
function set_mode(status)
{
	var sourceeditor = $('sourceeditor');
	var htmleditor = $('htmleditor');
	var diveditor = $('diveditor');
	var f = window.frames['htmleditor'];
	var body = f.document.getElementsByTagName('body')[0];
	if (status)
	{
		sourceeditor.style.display = '';
		htmleditor.style.display = 'none';
		sourceeditor.value = body.innerHTML;
	}
	else
	{
		sourceeditor.style.display = 'none';
		htmleditor.style.display = '';
		body.innerHTML = sourceeditor.value;
	}
	set_editor_wh();
}

// 公用方法------------------------------------------------------------------------------------------------
// mouse out
function mouse_out(obj)
{
	obj.style.border = 'none';
}

// mouse over
function mouse_over(obj)
{
	obj.style.borderLeft = obj.style.borderRight = '1px #c5c5c5 solid';
}

// mouse down
function mouse_down(obj)
{
	
}

// 格式化命令
function format(frameName, type, para)
{
	try
	{
		var f = window.frames[frameName];
		if(!para)
			if(document.all) f.document.execCommand(type);
			else f.document.execCommand(type, false, false);
		else f.document.execCommand(type, false, para);
	}
	catch (ex){alert(ex.message);}	
}

// 获取相对位置X
function getX(e)
{
	var x = e.offsetLeft;
	while(e = e.offsetParent){				
		x += e.offsetLeft;
	}
	return x;
}

// 获取相对位置Y
function getY(e)
{
	var y = e.offsetTop;
	while(e = e.offsetParent){
		y += e.offsetTop;
	}
	return y;
}

// 字体颜色列表
function drawCube()
{
	var s = "<select id='fontcolor' onchange='forecolor(this)'>";
	s += "<option value='-1'>- 颜色 -</option>";
	var hex = new Array('FF', 'CC', '99', '66', '33', '00');
	for (var i = 0; i < 2; ++i) 
	{
		for (var j = 0; j < 6; ++j) 
		{
			for (var k = 0; k < 6; ++k) 
			{
				var red = hex[j];
				var green = hex[k];
				var blue = hex[i];
				var color = '#' + red + green + blue;
				if(color == "#000066") color = "#000000";
				s += "<option value='"+color+"' style='background-color:"+color+";'>&nbsp;</option>";
			}
		}
	}
	for (var i = 2; i < 4; ++i)
	{
		for (var j = 0; j < 6; ++j) 
		{
			for (var k = 0; k < 6; ++k) 
			{
				var red = hex[j];
				var green = hex[k];
				var blue = hex[i];
				var color = '#' + red + green + blue;
				if(color == "#000066") color = "#000000";
				s += "<option value='"+color+"' style='background-color:"+color+";'>&nbsp;</option>";
			}
		}
	}
	s += "</select>";
	return s;
}