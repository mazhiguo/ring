//背景层
function lock_screen(obj)
 {
   if(obj.document.getElementById("bgDiv") != null) obj.document.body.removeChild(obj.document.getElementById("bgDiv"));
   var sWidth,sHeight;
   sWidth=screen.width;
   sHeight=screen.height;
   
   var bgObj=obj.document.createElement("div");
   bgObj.setAttribute('id','bgDiv');
   bgObj.style.position="absolute";
   bgObj.style.top="0";
   bgObj.style.background="#777";
   bgObj.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75)";
   bgObj.style.opacity="0.6";
   bgObj.style.left="0";
   bgObj.style.width=sWidth + "px";
   bgObj.style.height=sHeight + "px";
   bgObj.style.zIndex = "10000";
   obj.document.body.appendChild(bgObj);//在body内添加该div对象
}
//清除所有的层
function clear_div(obj)
{		
	try{
		if (obj.document.getElementById("bgDiv") != null) obj.document.body.removeChild(obj.document.getElementById("bgDiv"));
		if (obj.document.getElementById("msgDiv") != null) obj.document.body.removeChild(obj.document.getElementById("msgDiv"));
		//隐藏span标签 显示ug下拉框
		if (top.document.getElementById('nav') != null) show(top.document.getElementById('nav').contentWindow.document.getElementById('ug'), top.document.getElementById('nav').contentWindow.document.getElementById('span_ug'));
	}catch(err){}
}

//提示框
function lock_screen_show_message(obj, msg)
{
	if(obj.document.getElementById("msgDiv") != null) obj.document.body.removeChild(obj.document.getElementById("msgDiv"));
	var msgw, msgh, bordercolor, titleheight;	
	titleheight=25           //提示窗口标题高度
	bordercolor="#336699";   //提示窗口的边框颜色
	titlecolor="#99CCFF";    //提示窗口的标题颜色
	msgw=350;                //提示窗口的宽度
	msgh=150;                //提示窗口的高度
	
	var msgObj=obj.document.createElement("div")//创建一个div对象（提示框层）
	msgObj.setAttribute("id","msgDiv");
	msgObj.setAttribute("align","center");
	msgObj.setAttribute("valign","middle");
	msgObj.style.background="white";
	msgObj.style.border="1px solid " + bordercolor;
	msgObj.style.position = "absolute";
	msgObj.style.left = "50%";
	msgObj.style.top = "50%";
	msgObj.style.font="12px/1.6em Verdana, Geneva, Arial, Helvetica, sans-serif";
	msgObj.style.marginLeft = "-225px" ;
	msgObj.style.marginTop = -75+obj.document.documentElement.scrollTop+"px";
	msgObj.style.width = msgw + "px";
	msgObj.style.height =msgh + "px";
	msgObj.style.textAlign = "center";
	msgObj.style.lineHeight ="25px";
	msgObj.style.zIndex = "10001";
	obj.document.body.appendChild(msgObj);//在body内添加提示框div对象msgObj
	
	var title=obj.document.createElement("h4");//创建一个h4对象（提示框标题栏）
	title.setAttribute("id","msgTitle");
	title.setAttribute("align","left");
	title.style.margin="0";
	title.style.padding="3px";
	title.style.background=bordercolor;
	title.style.filter="progid:DXImageTransform.Microsoft.Alpha(startX=20, startY=20, finishX=100, finishY=100,style=1,opacity=75,finishOpacity=100);";
	title.style.opacity="0.75";
	title.style.border="1px solid " + bordercolor;
	title.style.height="18px";
	title.style.font="12px Verdana, Geneva, Arial, Helvetica, sans-serif";
	title.style.color="white";
	title.style.cursor="pointer";
	title.innerHTML="提示：";
	obj.document.getElementById("msgDiv").appendChild(title);//在提示框div中添加标题栏对象title
	
	var txt=obj.document.createElement("p");//创建一个p对象（提示框提示信息）
	txt.style.margin="1em 0";
	txt.setAttribute("id","msgTxt");
	txt.innerHTML="<img src='/images/loading.gif'/> "+msg;//来源于函数调用时的参数值
	txt.innerHTML=txt.innerHTML+"<br><br><img src='/images/process.gif' />";
	obj.document.getElementById("msgDiv").appendChild(txt);//在提示框div中添加提示信息对象txt 
}

//在ie中div无法覆盖下拉框
//隐藏select下拉框  显示span标签
function display(obj1, obj2)
{
	try{
		if (obj1!=null && obj2!=null){
			obj1.style.display = 'none';
			obj2.style.display = '';
			obj2.innerHTML = obj1.options[obj1.selectedIndex].text;
		}
	}catch(err){}
}
//隐藏span标签 显示select下拉框
function show(obj1, obj2)
{
	try{
		if (obj1!=null && obj2!=null){
			obj1.style.display = '';
			obj2.style.display = 'none';
			obj2.innerHTML = '';
		}
	}catch(err){}
}
// 在ie中div无法覆盖下拉框 解决办法：隐藏下拉框 显示下拉框备选中的值
function hidden_select()
{
	display(top.document.getElementById('nav').contentWindow.document.getElementById('ug'), top.document.getElementById('nav').contentWindow.document.getElementById('span_ug'));
}

//统一操作
function lock_failed_func(msg, url)
{
	clear_div(top);
	if (typeof msg != 'undefined') alert(msg);
	if (typeof url != 'undefined') window.location = '/license/import';
	return ;
}