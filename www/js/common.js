/**************************************************************************
 * GKE3.3管理端 common.js (北京点击科技有限公司)
 * 
 * 作者:章启涛
 * 制作日期: 2008-03-26
 * 修改时间: 2008-03-26
 ************************************************************************/
var $ = function(id){
    return document.getElementById(id) || jQuery(id);
}
var is_ie = typeof document.all === "undefined" ? false : true;

function isset(v) {
	return typeof v == 'undefined' ? false: true;
}

function empty(v) {
	return !isset(v) || !v; 
}

function func_htmlspecialchars(string, quote_style) 
{
   string = string.toString();
   string = string.replace(/&/g, '&amp;');
   string = string.replace(/</g, '&lt;');
   string = string.replace(/>/g, '&gt;');
   if (quote_style == 'ENT_QUOTES')
   {
       string = string.replace(/"/g, '&quot;');
       // string = string.replace(/\'/g, '&#039;');
       string = string.replace(/\'/g, '&apos;');
   }
   else if (quote_style != 'ENT_NOQUOTES')
   {
       string = string.replace(/"/g, '&quot;');
   }
   return string;
}

function func_htmlspecialchars_decode(string, quote_style)
{
   string = string.toString();
   string = string.replace(/&amp;/g, '&');
   string = string.replace(/&lt;/g, '<');
   string = string.replace(/&gt;/g, '>');
   if (quote_style == 'ENT_QUOTES')
   {
       string = string.replace(/&quot;/g, '"');
      // string = string.replace(/&#039;/g, '\'');
       string = string.replace(/&apos;/g, '\'');
   }
   else if (quote_style != 'ENT_NOQUOTES')
   {
       string = string.replace(/&quot;/g, '"');
   }
   return string;
}

function preg_replace(search, replace, str) {
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], "ig");
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function strpos(haystack, needle, offset) {
	if(isUndefined(offset)) {
		offset = 0;
	}
	index = haystack.toLowerCase().indexOf(needle.toLowerCase(), offset);
	return index == -1 ? false : index;
}

function addslashes(str) {
	return preg_replace(['\\\\', '\\\'', '\\\/', '\\\(', '\\\)', '\\\[', '\\\]', '\\\{', '\\\}', '\\\^', '\\\$', '\\\?', '\\\.', '\\\*', '\\\+', '\\\|'], ['\\\\', '\\\'', '\\/', '\\(', '\\)', '\\[', '\\]', '\\{', '\\}', '\\^', '\\$', '\\?', '\\.', '\\*', '\\+', '\\|'], str);
}

function alter_display(id) {
	var o = document.getElementById(id);
	if (!o) {
		alert('alter id does not exist!');
		return;
	}
	if (o.style.display == 'none') {
		o.style.display = '';
	} else {
		o.style.display = 'none';
	}
}

function is_empty(id) {
	var obj = document.getElementById(id);
	obj.value = obj.value.replace(/\s*/,'');
	if(obj.value == '')
		return false;
	return true;	
}

function checkAll(frmobj) {
	var checkall = checkall ? checkall : 'checkall';
	for (var i=0;i<frmobj.elements.length;i++) {
	var e = frmobj.elements[i];
	if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled))
	  e.checked = frmobj.elements[checkall].checked;
	}
}

function show_modal_win(url, title, w, h, top, left) 
{
//	if (is_ie) {
//		w=w+23;
//		h=h+50;
//		window.showModalDialog(url,title,'dialogWidth:'+w+'px;dialogHeight:'+h+'px;dialogTop:'+top+'px;dialogLeft:'+left+'px;help:no; resizable:no;status:yes;scrollbars:yes;titlebar:no;toolbar:no;menubar:no;location:no');
//	} else {
		window.open(url,title,'width='+w+'px,height='+h+'px,top='+top+',left='+left+',dialog=yes,modal=yes,scrollbars=yes,resizable=no,titlebar=no,status=yes,toolbar=no,menubar=no,location=no');
//	}
}
/**************************************************************************
 * msweb3.2-select.js
 * 
 *
 * 制作日期: 2007-10-17
 * 修改时间: 2007-11-6
 * 作者:章启涛
 * 
 *
 **************************************************************************/
var sid1 = 'select1';
var sid2 = 'select2';
/**
 * 移动 select multiple，从select1 移动选中 到 select2中，在select1中移除选中，在select2中移除重复
 *
 * @param string id1 原始
 * @param string id2
 * @author zhangqitao 2007-10-17
 */
function move_selected(id1, id2, limit, msg)
{
	var o1 = document.getElementById(id1);
	var o2 = document.getElementById(id2); 
	
	
	var len1 = o1.length;
	var len2 = o2.length;
	
	if (typeof limit != 'undefined')
	{
		if (len2 == limit )
		{
			alert(msg);
			return;
		}	
	}
	
	var flag = false;
	for (var i=len1-1; i>=0; i--)
	{
		if (o1.options[i].selected == true && !o1.options[i].disabled)
		{
			for (var j=0; j<len2; j++)
			{
				if (o2.options[j].value == o1.options[i].value)
					flag = true;
			}
			if (!flag)
			{
				o2.options[o2.length] = new Option(o1.options[i].text, o1.options[i].value);
				o1.removeChild(o1.options[i]);
			}
		}
	}
}
/**
 * 移动 select multiple，从select1 移动全部 到 select2中，在select1中移除全部，在select2中移除重复
 *
 * @param string id1 原始
 * @param string id2
 * @author zhangqitao 2007-10-17
 */
function move_all(id1, id2)
{
	var o1 = document.getElementById(id1);
	var o2 = document.getElementById(id2); 
	var len1 = o1.length;
	var len2 = o2.length;
	var flag = false;
	for (var i=len1-1; i>=0; i--)
	{
		if (!o1.options[i].disabled)
		{
			for (var j=0; j<len2; j++)
			{
				if (o2.options[j].value == o1.options[i].value)
					flag = true;
			}
			if (!flag)
			{
				o2.options[o2.length] = new Option(o1.options[i].text, o1.options[i].value);
				o1.removeChild(o1.options[i]);
			}
		}
	}
}





//移除select的所有option
function clear_options(id)
{
	var o = document.getElementById(id);
	var len = o.length;
	if (len == 0) return;
	for (i=len-1; i>=0; i--)
	{
		o.removeChild(o.options[i]);
	}
}

/**
 * 获得某个mutiselect的所有option值，并将值连成字符串后将值设给某个元素
 * 
 * @param string sid select的id
 * @param string vid 将select的value连成字符串后设给某个元素(input hidden)的id
 */
function get_mutiselect_values(sid, vid)
{
	var o = document.getElementById(sid);
	var len = o.length;
	var s = '';
	for (i=0; i<len; i++)
	{
		s += o.options[i].value+':';
	}
	//set value to element 'vid'
	if (document.getElementById(vid)) document.getElementById(vid).value = s;
	else return s;
}	

/**
 * 获得某个mutiselect的所有option text
 * 
 * @param string sid select的id
 */
function get_mutiselect_texts(sid)
{
	var o = document.getElementById(sid);
	var len = o.length;
	var s = '';
	for (i=0; i<len; i++)
	{
		s += o.options[i].text+'\r\n';
	}
	return s;
}	

/**
 * 设置select的option
 *
 * @param string sid1 select1的id 将response中的数组值，append到sid1中。当response增加到select1时,不增加和select1相同的项
 * @param string sid2 select2的id 如果select2的option不为空，当response增加到select1时。不增加和select2相同的项
 * @param string response ajax请求后的response
 * 
 * response格式(ajax请求php输出):
	var arr = new Array();
	arr[0] = ['111111111', '8ff80327ca9d884f4ba7410a50c9c143'];
	arr[1] = ['222222222', '902feef38bb4148ddb51c190be17c291'];
	……
 */
function set_select_option(response)
{
	clear_options(sid1);//移除select1中的option,不进行叠加,如需要叠加，则注掉此行
	var str = response.responseText;
	eval(str);
	if (arr.length == 0) return;
	var len1 = document.getElementById(sid1).length;
	var len2 = document.getElementById(sid2).length;
	for (var i=0; i<arr.length; i++)
	{
		var o = arr[i];
		var option_text = o[0];
		var option_value = o[1];
		var flag1 = false;
		var flag2 = false;
		for (var j=0; j<len1; j++)
		{
			if (document.getElementById(sid1).options[j].value == option_value)
			flag1 = true;
		}
		for (var k=0; k<len2; k++)
		{
			if (document.getElementById(sid2).options[k].value == option_value)
			flag2 = true;
		}		
		if (!flag1 && !flag2)
		{
			document.getElementById(sid1).options[document.getElementById(sid1).length] = new Option(o[0], o[1]);
		}
	}
}
/**************************************************************************
 * GKE3.3管理端 ajax.js (北京点击科技有限公司)
 *
 * @author zhangqitao
 * @create_time 2007-12-28
 * @mender_time 2008-03-13
 * 说明:
 * 1.异步调用
 * 2.支持多个图片loading
 * 3.支持同一url防上一个请求未返回的重复请求（未返回的双击）
 * 4.支持防止浏览器缓存
 * 5.支持一个页面内同时并发多个请求
 * 6.使用append来安全替代eval()方法
 * 7.支持请求完成后，不做其他任何处理，将返回内容作为参数传到指定函数中，并执行该函数
 * 8.支持form表单(id)的数据发送(强制为post)
  参数说明：
   {method:'', url:'', id:'', loadingid:'', jsfunc:'', params:'', evalscripts:'', frmid:'', responsetype:'', charset:''}
	responsetype:返回类型(不指定默认取responseText)
	charset:返回字符的编码(不指定默认为utf-8)
  
  	method:如不指定，则默认为get
	url:请求的连接，必须指定
	id:返回后进行innerHTML的元素id
	loadingid:使用显示进度图片的id
		
	params:使用POST时传送的数据,a=b&c=d...
 	frmid:指定的form的id,将强制为post方法，发送该表单内的数据,此时如指定params则指定params无效。
 	evalscripts:值为true和false，对返回内容中的script标签进行等价于eval的处理。
 	
 	jsfunc:如指定了jsfunc,指定的参数id（不进行innerHTML处理）和evalscripts无效，强制使用eval方法执行指定jsfunc
  注意:
  	返回的内容中有 <script src="link"></script>  "link"要是双引号
  用法简单示例：
  	Ajax.Updater({url:'outdata.php', id:'outer', loadingid:'loading', method:'POST'});
 */

var AjaxStacks = new Array();
var Ajax = {
	//ajax对象池
	Pool:[],
	//从对象池获得一个实例
	getInstance:function()
	{
		for (var i=0; i<this.Pool.length; i++) {
            if (this.Pool[i].readyState == 0 || this.Pool[i].readyState == 4)
                return this.Pool[i];
		}
		this.Pool[this.Pool.length] = this.createRequest();
		return this.Pool[this.Pool.length-1]; 
	},
	//创建一个新的请求
	createRequest:function() 
	{
		var oRequest = null;
		if (window.XMLHttpRequest)
		{
			try 
			{ 
				oRequest = new XMLHttpRequest();  
				if (oRequest) return oRequest;
			} catch (e) {}		
		}
		else if (window.ActiveXObject)
		{ 
			var ver = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 
						'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 
						'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for (var i=0; i<ver.length ;i++ )
			{
				try
				{ 
					oRequest = new ActiveXObject(ver[i]);
					if (oRequest) return oRequest;
				} catch (e) {}
			}
		}
		return false;
	},
	//发送数据
	Updater:function(args)
	{
		// ------ 参数初始化
		var method = (args.method == '' || typeof args.method == 'undefined') ? 'get' : args.method;
		var ReponseType = (args.responsetype == '' || typeof args.responsetype == 'undefined') ? 'responseText' : args.responsetype;
		var CharSet = (args.charset == '' || typeof args.charset == 'undefined') ? 'utf-8' : args.charset;
		
		var TargetId = (args.id == '' || typeof args.id == 'undefined') ? '' : args.id;
		var TargetUrl = (args.url == '' || typeof args.url == 'undefined') ? '' : args.url;
		var LoadingId = (args.loadingid == '' || typeof args.loadingid == 'undefined') ? '' : args.loadingid;
		var JsFunc = (args.jsfunc == '' || typeof args.jsfunc == 'undefined' ) ? '' : args.jsfunc;
		var EvalScripts = (args.evalscripts == '' || typeof args.evalscripts == 'undefined' ) ? 'false' : args.evalscripts;
		var AppendId = (args.appendid == '' || typeof args.appendid == 'undefined' ) ? '' : args.appendid;		
		var FrmId = (args.frmid == '' || typeof args.frmid == 'undefined') ? null : args.frmid;
		var params = (args.params == '' || typeof args.params == 'undefined') ? null : args.params;
		// ----- 创建一个当前请求的url的ajax请求，并防止同一url还没返回就重复请求	
		if (typeof AjaxStacks[TargetUrl] == 'undefined' || AjaxStacks[TargetUrl] == null) 
		{
			AjaxStacks[TargetUrl] = '';
			var oRequest = this.getInstance();
			if (!oRequest) AjaxStacks[TargetUrl] = null;
		} else return;
		if (oRequest && LoadingId!='')
		{
			try 
			{
				document.getElementById(LoadingId).style.display = '';
			} 
			catch (e) 
			{
				var msg = LoadingId+' does not exist!';
				alert(msg);
			}
		}
		// ------ send data	for post
		if (FrmId != null) 
		{
			var Frm = document.getElementById(FrmId);
			if (Frm != null) params = getFormQueryString(FrmId);
			else
			{
				//form不存在恢复池状态
				document.getElementById(loadingid).style.display = 'none';
				AjaxStacks[TargetUrl] = null;
				alert('form ['+Frmid+'] does not exists!');
				return;
			}
		}
		if (params != null) method = 'post';//form提交强制为post	
		var sTargetUrl = TargetUrl;
		//防浏览器缓存
		if (TargetUrl.indexOf("?") > 0) sTargetUrl += "&randnum=" + Math.random();
		else sTargetUrl += "?randnum=" + Math.random();
		// ------- send	
		with (oRequest)
		{
			//send
			open(method, sTargetUrl, true);
			setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset='+CharSet);
			send(params);
			onreadystatechange = function() 
			{
				try 
				{
	                if (oRequest.readyState == 4 && (oRequest.status == 200 || oRequest.status == 304)) 
	                {
	                	AjaxStacks[TargetUrl] = null;//请求完毕，释放
						if (LoadingId!='') document.getElementById(LoadingId).style.display = 'none';
						var s = 'var response = oRequest.'+ReponseType;
						eval(s);
						if (JsFunc)
						{
							var obj = new Object();
							obj.responseText = response;
							eval(JsFunc+"(obj);");
							evalScripts(response, AppendId);							
//							var s = '<script>'+JsFunc+'("'+response+'")'+'</script>';
//	                        evalScripts(s);
	                    }
	                    else
	                    {
						    if (EvalScripts == 'true') evalScripts(response, AppendId);
						    if (TargetId) document.getElementById(TargetId).innerHTML = response;
						}
	                }
				} catch(e){};
			}
		}
	}
}

//reload=1，强制append覆盖相同的src或text；reload=0，如果有存在的src和text则不匹配出来，放弃存在的append
function evalScripts(s, AppendId) {
	if (!s || s.indexOf('<script') == -1) return s;
	var arr = new Array();
	//append src (<script src="">|<script type="text/javascript" src="">|<script language="javascript" type="text/javascript" src="">)<script>
	p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
	while(arr = p.exec(s)) appendScripts(arr[1], '', AppendId);
	//append text (<script src="">|<script type="text/javascript" src="">|<script language="javascript" type="text/javascript" src="">)alert(1);function abc(){}</script>
	s = s.replace(p, '');
	p = /<script.*?>([^\x00]+?)<\/script>/ig;
	while(arr = p.exec(s)) appendScripts('', arr[1], AppendId);
	return s;
}

function appendScripts(src, text, AppendId) 
{
    var id = (src+text).substring(0, 7);
    id = id.replace(/[\?\s\/\\:\.]/ig, '_');
    //移除以前append
    if (document.getElementById(id)) {
        document.getElementById(id).parentNode.removeChild(document.getElementById(id));
    }
	if (AppendId == '' || AppendId == 'undefined') {
    	var oHead = document.getElementsByTagName('head').item(0);
	} else {
		var oHead = document.getElementById(AppendId);	
	}
	var oScript = document.createElement("script");  
	oScript.language = "javascript";
	oScript.type = "text/javascript";
	oScript.id = id;
	oScript.defer = false;
	try {
		if (src) oScript.src = src;
		else if (text) oScript.text = text;
		else return;
		oHead.appendChild(oScript);
	} catch (e) {}
}

function checkScriptsId(needle, haystack) {
	if (typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) return true;
		}
	}
	return false;
}

function getFormQueryString(frmID) { 
	var frm = document.getElementById(frmID);
	var i,queryString = "", and = "";
	var item;
	var itemValue;
	for( i=0;i<frm.length;i++ ) {
		item = frm[i];
		if (item.name=='') continue;
		if (item.type == 'select-one') {
		 	itemValue = item.options[item.selectedIndex].value;
		} else if (item.type=='checkbox' || item.type=='radio') {
		    if (item.checked == false) continue;
		    itemValue = item.value;
		} else if (item.type == 'button' || item.type == 'submit' || item.type == 'reset' || item.type == 'image') {
		    continue;
		} else {
		    itemValue = item.value;
		}
		itemValue = encodeURIComponent(itemValue);
		queryString += and + item.name + '=' + itemValue;
	    and="&";
   }
   return queryString;
}
// ---------- base64
var BASE64={
    enKey: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
    deKey: new Array(
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
        52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
        -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
        15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
        -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
    ),
    encode: function(src){
        var str=new Array();
        var ch1, ch2, ch3;
        var pos=0;
        while(pos+3<=src.length){
            ch1=src.charCodeAt(pos++);
            ch2=src.charCodeAt(pos++);
            ch3=src.charCodeAt(pos++);
            str.push(this.enKey.charAt(ch1>>2), this.enKey.charAt(((ch1<<4)+(ch2>>4))&0x3f));
            str.push(this.enKey.charAt(((ch2<<2)+(ch3>>6))&0x3f), this.enKey.charAt(ch3&0x3f));
        }
        if(pos<src.length){
            ch1=src.charCodeAt(pos++);
            str.push(this.enKey.charAt(ch1>>2));
            if(pos<src.length){
                ch2=src.charCodeAt(pos);
                str.push(this.enKey.charAt(((ch1<<4)+(ch2>>4))&0x3f));
                str.push(this.enKey.charAt(ch2<<2&0x3f), '=');
            }else{
                str.push(this.enKey.charAt(ch1<<4&0x3f), '==');
            }
        }
        return str.join('');
    },
    decode: function(src){
        var str=new Array();
        var ch1, ch2, ch3, ch4;
        var pos=0;
        src=src.replace(/[^A-Za-z0-9\+\/]/g, '');
        while(pos+4<=src.length){
            ch1=this.deKey[src.charCodeAt(pos++)];
            ch2=this.deKey[src.charCodeAt(pos++)];
            ch3=this.deKey[src.charCodeAt(pos++)];
            ch4=this.deKey[src.charCodeAt(pos++)];
            str.push(String.fromCharCode(
                (ch1<<2&0xff)+(ch2>>4), (ch2<<4&0xff)+(ch3>>2), (ch3<<6&0xff)+ch4));
        }
        if(pos+1<src.length){
            ch1=this.deKey[src.charCodeAt(pos++)];
            ch2=this.deKey[src.charCodeAt(pos++)];
            if(pos<src.length){
                ch3=this.deKey[src.charCodeAt(pos)];
                str.push(String.fromCharCode((ch1<<2&0xff)+(ch2>>4), (ch2<<4&0xff)+(ch3>>2)));
            }else{
                str.push(String.fromCharCode((ch1<<2&0xff)+(ch2>>4)));
            }
        }
        return str.join('');
    }
};


// ---------- iframe 停留刷新
function _attachEvent(obj, evt, func) {
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, true);
	} else if(obj.attachEvent) {
		obj.attachEvent("on" + evt, func);
	} else {
		eval("var old" + func + "=" + obj + ".on" + evt + ";");
		eval(obj + ".on" + evt + "=" + func + ";");
	}
}
function refreshmain(e) {
	try{
		e = e ? e : window.event;
		actualCode = e.keyCode ? e.keyCode : e.charCode;
		if(actualCode == 116) {
			top.content.location.reload();
			if(document.all) {
				e.keyCode = 0;
				e.returnValue = false;
			} else {
				e.cancelBubble = true;
				//e.calcelable = true;
				e.preventDefault();
			}
		}
	}catch(e){}
}
_attachEvent(document.documentElement, "keydown", refreshmain);

//操作说明 高度，宽度等都要带'px',否则不显示
var move_div;
function display_operation(width, height, top, left, msg)
{
	var div = document.getElementById("divOperation");
	if(div != null)
	{
		 document.body.removeChild(div);		 
	}
	else
	{
	   	div = document.createElement("div");
	   	div.setAttribute("id", "divOperation");
	   	div.style.width = width;
	   	div.style.height = height;
	   	div.style.top = top;
	   	div.style.left = left;
	   	//div.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75)";
  		//div.style.opacity="0.9";
	   	div.style.background = "#DCDCDC";
	   	div.style.position="absolute";
	   	if (msg == '')
	   	{
		    msg = "<br />&nbsp;&nbsp;1. \"选中分支、取消分支、展开分支、收缩分支\"是对其当前已经选中节点的操作（该节点已<span style=\"color:#EB8A3D;font-weight:800;background:#B5D3F7\">加粗高亮</span>显示）。<br />";
			msg += "<br />&nbsp;&nbsp;2. 选中分支：选中当前选中节点及已请求子节点。<br />";
			msg += "<br />&nbsp;&nbsp;3. 取消分支：取消当前选中节点及子节点。<br />";
			msg += "<br />&nbsp;&nbsp;4. 展开分支：请求当前选中节点的下级子节点，点击一次动态请求一级。<br />";
			msg += "<br />&nbsp;&nbsp;5. 收缩分支：收缩当前选中节点及子节点。<br />";
	   	}
		div.innerHTML = msg;
//		div.onmouseup = function (){
//							move_div = 0;
//						};
//		div.onmousedown = function (divOperation){
//							move_div = divOperation;
//							x = event.clientX-move_div.style.pixelLeft;
//							y = event.clientY-move_div.style.pixelTop;
//						};
//		div.onmousemove = function (){
//							if(move_div == 0)return false
//							else{
//								move_div.style.pixelLeft = event.clientX-x;
//								move_div.style.pixelTop = event.clientY-y;
//							}
//						};
		//div.style.cursor = "move";				
		//top.status="鼠标X="+event.clientX + " sX=" + document.body.scrollLeft + " 鼠标Y=" + event.clientY+ " sY=" + document.body.scrollTop; 		
		document.body.appendChild(div);
	}
}



