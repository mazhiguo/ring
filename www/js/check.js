function check_empty_return(id, back_id) {
	var e_back = document.getElementById(back_id);
	
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
		return false;
	} else {
		e_back.innerHTML = "<img src='/images/check_right.gif'>";
		return true;
	}
}
function check_account_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_account(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许汉字、字母、数字、上划线、下划线及小括号";
			return false;
		}
	}
}
function check_pwd_return(id, back_id, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 密码不能为空";
			return false;
	} else {
		if (check_pwd(id) ) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许6-12位英文字母或数字";
			return false;
		}
	}
}
function check_repwd_return(id,re_id, back_id, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(re_id)) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 密码不能为空";
			return false;
	} else { 
		if (jsTrim(document.getElementById(id).value) != jsTrim(document.getElementById(re_id).value)) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 重复密码和密码必须保持一致";
			return false;
		} else {
			if (check_pwd(re_id)) {
				e_back.innerHTML = "<img src='/images/check_right.gif'>";
				return true;
			} else {
				e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许6-12位英文字母或数字";
				return false;
			}
		}
	}
}

function check_number_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	var s=canempty?'':'或保持为空';
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (isNumber(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许输入自然数"+ s;
			return false;
		}
	}
}

function check_channelsize_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	var s=canempty?'':'或保持为空';
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if(isNumber(id) && (document.getElementById(id).value<10))
		{
			e_back.innerHTML = "<img src='/images/check_error.gif'> "+normalinfo;
			return false;
		}
		if (isNumber(id) && (document.getElementById(id).value.length<=4)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许输入不超过4位数值"+ s;
			return false;
		}
	}
}

function check_post_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_post(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 邮编不正确";
			return false;
		}
	}
}
function check_mobile_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_mobile(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 格式不正确";
			return false;
		}
	}
}
function check_phone_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_phone(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 格式不正确";
			return false;
		}
	}
}
function check_email_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_email(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 格式不正确";
			return false;
		}
	}
}
function check_url_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_url(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 网址不正确";
			return false;
		}
	}
}

function check_api_account_return(id, back_id, canempty, normalinfo) {
	var e_back = document.getElementById(back_id);
	if (!e_back) {return true;}
	if (!is_empty(id)) {
		if (!canempty) {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 不能为空";
			return false;
		} else {
			e_back.innerHTML = normalinfo;
			return true;
		}
	} else {
		if (check_api_account(id)) {
			e_back.innerHTML = "<img src='/images/check_right.gif'>";
			return true;
		} else {
			e_back.innerHTML = "<img src='/images/check_error.gif'> 只允许大小写英文字母、数字、上划线、下划线";
			return false;
		}
	}
}

function isEmpty(s) 
{
	if((s==null)||(s.replace(/\s*/,'') == ''))
	{
		return true;
	}
	return false;
}

// 去掉数据的首尾空字符
function jsTrim(value){
  return value.replace(/(^\s*)|(\s$)/g, "");
}
function trim(value){
  return value.replace(/\s*/g, "");
}

// 检查数字的合法性
function isNumber(id) {
	var obj = document.getElementById(id);
	var p=/^\d*$/;
	if (obj.value.match(p) == null)
		return false;
	return true;
}

//检查密码,只能由a-z,A-Z,0-9组成, 6-12位
function check_pwd(id)
{
	var obj = document.getElementById(id);
	var re =/^[a-zA-Z0-9]{6,12}$/; 
	if(!re.test(obj.value))
		return  false;
	return  true;  
}

//检查邮政编码
function check_post(id)
{
	var obj = document.getElementById(id)
	var p=/^[-()0-9]{1,20}$/;  
  	if(!p.test(obj.value))  
		return false;
	return true;
} 
//检查email
function check_email(id){
	var obj = document.getElementById(id);
	var p = /^([-_A-Za-z0-9\.]+)@([-_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/;
    if(p.test(obj.value)) 
    	return true;     
    return false;     
}    
//检查手机
function check_mobile(id){  
	var obj = document.getElementById(id);     
    //var p =/(^[1][3,5][0-9]{9}$)|(^0[1][3][0-9]{9}$)/; 
	var p =/^[-_()0-9]{1,20}$/; 
    if (p.test(obj.value)) 
      return true;    
    return false;       
}    


//检查电话号码
function check_phone(id)     
{    
	var obj = document.getElementById(id);
		//var p =/(^x\s*[0-9]{5}$)|(^(\([1-9][0-9]{2}\)\s)?[1-9][0-9]{2}-[0-9]{4}(\sx\s*[0-9]{5})?$)/;
    //var p =/(^([0][1-9]{2,3}[-])?\d{3,8}(-\d{1,6})?$)|(^\([0][1-9]{2,3}\)\d{3,8}(\(\d{1,6}\))?$)|(^\d{3,8}$)/;     
   // var p=/(^[0-9]{3,4}\-[0-9]{7,8}$)|(^[0-9]{7,11}$)|(^\([0-9]{3,4}\)[0-9]{7,8}$)|(^\([0-9]{3,4}\)[0-9]{1,5}\-[0-9]{1,5}$)/;
    var p =/^[-_()0-9]{1,20}$/;
	if (p.test(obj.value))
      return true;    
    return false;    
} 
//检查名称，只能由汉字、字母、数字组成
function check_account(id){
	var obj = document.getElementById(id);
    var p = /^[-_()（）0-9a-zA-Z\u4e00-\u9fa5]+$/;       
    if (p.test( trim(obj.value) ))
      return true;    
    return false;    
} 
//检查url格式
function _old_check_url( id ) {
	var obj = document.getElementById(id);
	var re=/^([hH][tT]{2}[pP]:\/\/|[hH][tT]{2}[pP][sS]:\/\/|\S)(\S+\.\S+)$/;
	var	chars = jsTrim(obj.value);
	if (chars.match(re) == null)
		return false;
	return true;
}
function check_url(id)
{      
	var obj = document.getElementById(id);
//    var p = /^((http:[/][/])?\w+(-[.]\w+|[/]\w*)*)?$/;     
   // var p = /^[-_=?%&:./A-Za-z0-9]+$/;  
    var p = /^[^\'\\]+$/;
    if(p.test(obj.value)) 
    	return true;  
    return false;     
} 
//创建差是否为自然数
function check_naturalnumber(id)    
{           
	var s = document.getElementById(id).value;    
    if (/^[0-9]+$/.test( s ) && (s > 0))    
       return true;    
    return false;    
}  

//创建差是否为自然数
function check_naturalnumber_num(id, num)    
{           
	var s = document.getElementById(id).value;    
    if (/^[0-9]+$/.test( s ) && (s >= num))    
       return true;    
    return false;    
}

//检查协作区搜索关键字
function check_keywords(id){  
	var obj = jsTrim(id); 
	var p = /(^[A-Za-z0-9\u4e00-\u9fa5]{1,64}$)|(^[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}$)|(^[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}$)|(^[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}$)|(^[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}[\，\,]{1}[A-Za-z0-9\u4e00-\u9fa5]{1,64}$)/; 
		if(obj=='') return true;
		if(!p.test( obj ))  return false;     
	return true;
} 

//授权访问用户：用户名
function check_api_account(id)
{      
	var obj = document.getElementById(id);  
    var p = /^[\w\-]+$/;     
    if(p.test(obj.value)) 
    	return true;  
    return false;     
}

function ajax_change_name(contacts_id)
{
	var name = document.getElementById(contacts_id+'_new_name').value;
	if (trim(name) == '') 
	{
		alert("通讯录名称不能为空。");
		$(contacts_id+'_new_name').focus();
		return;
	}
	$(contacts_id+'_name').innerHTML = jsTrim($(contacts_id+'_new_name').value);
	$(contacts_id+'_new_name').value = jsTrim($(contacts_id+'_name').innerHTML);
	if (trim(name)!='' && !check_account(contacts_id+'_new_name'))
	{
		alert('通讯录名称只能由汉字、字母、数字、上划线、下划线及小括号组成。');
		$(contacts_id+'_new_name').select();			
		return ;
	}
	$(contacts_id+'_new_name_show').style.display='none';
	$(contacts_id+'_name').style.display='';	
	params = 'contacts_id='+contacts_id+'&name='+name;
	Ajax.Updater({url:'/contacts/re_contacts_name', id:contacts_id+'_name', params:params, evalscripts:'true', loadingid:'loading'});
}

function ajax_change_remark(contacts_id)
{
	var remark = $(contacts_id+'_new_remark').value;
	if (trim(remark)=='' && !window.confirm('确定要设置内容为空。'))
	{
		return false;
	}
	$(contacts_id+'_remark').innerHTML = jsTrim($(contacts_id+'_new_remark').value);
	$(contacts_id+'_new_remark').value = jsTrim($(contacts_id+'_remark').innerHTML);
	if (trim(remark)!='' && !check_account(contacts_id+'_new_remark'))
	{
		alert('备注只能由汉字、字母、数字、上划线、下划线及小括号组成。');
		$(contacts_id+'_new_remark').select();		
		return ;
	}
	$(contacts_id+'_new_remark_show').style.display='none';
	$(contacts_id+'_remark').style.display='';	
	params = 'contacts_id='+contacts_id+'&remark='+remark;
	Ajax.Updater({url:'/contacts/re_contacts_remark', id:contacts_id+'_remark', params:params, evalscripts:'true', loadingid:'loading'});
}