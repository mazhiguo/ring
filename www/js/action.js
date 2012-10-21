// 增加用户，显示弹出树
function show_user_ug_tree(active_ug_id, user_id)
{
	if (active_ug_id != '')
		var url = '/user/show_user_ug_tree?active_ug_id='+active_ug_id;
	else
		var url = '/user/show_user_ug_tree?user_id='+user_id;
	show_modal_win(url,'',700,430,200,200);
}

//弹出窗口，显示一个角色分配的部门
function show_role_depts(role_id)
{
	var url = '/role/show_role_depts/role_id/'+role_id;
	show_modal_win(url,'',700,430,200,200);
}

//弹出窗口，某个部门 分配多个角色
function show_roles_dept(ug_id)
{
	var url = '/role/show_roles_dept/ug_id/'+ug_id;
	show_modal_win(url,'',700,430,200,200);
}

//弹出窗口，显示角色分配的用户,把一个角色分配给多个用户
function show_role_users(role_id)
{
	var url = '/role/show_role_users/role_id/'+role_id;
	show_modal_win(url,'',850,500,100,100);
}

//弹出窗口，一个用户分配角色
function show_roles_user(user_id)
{
	var url = '/role/show_roles_user/user_id/'+user_id;
	show_modal_win(url,'',700,430,200,200);
}

//弹出窗口，通讯录新建自定义分组
function show_add_dcontacts(contact_id, contact_name)
{
	var url = '/contacts/on_show_dadd/contacts_id/'+contact_id+'/name/'+contact_name+'/';
	show_modal_win(url,'',850,500,100,100);
}

//给组织分配短信，弹出窗口
function set_sms_to_dept(ug_id)
{
	var url = '/sms/show_sms_to_dept/ug_id/'+ug_id;
	show_modal_win(url,'',700,430,200,200);
}
//管理员重置密码
function admin_reset_pwd(admin_id)
{
	var url = '/admin/reset_pwd/admin_id/'+admin_id;
	show_modal_win(url,'',700,430,200,200);
}

function show_set_monmsg_users()
{
	var url = '/monmsg/show_set_users';
	show_modal_win(url,'',850,500,100,100);
}

function check_login_account(id)
{
	if(!is_empty(id)) return;
	var url = '/admin/check_login_account?account='+encodeURIComponent(document.getElementById('account').value);
	Ajax.Updater({url:url,id:'check_adminaccount'});
}

function set_per_page(id, url)
{
	Ajax.Updater({url:url, id:id, evalscripts:'true'});
}

function get_unused_gid(page)
{
	var url = '/user/get_unused_gid/page/'+page;
	Ajax.Updater({url:url, id:'get_unused_gid', loadingid:'loadinggid'});
}

function check_role_is_rename(name)
{
	var url = '/role/check_role_is_rename/name/'+name+'/';
	Ajax.Updater({url:url, id:'check_role_is_rename'});	
}

function check_channel_is_rename(name)
{
	var url = '/channel/check_channel_is_rename/name/'+name+'/';
	Ajax.Updater({url:url, id:'check_channel_is_rename'});
}

//同级部门上下移动交换显示位置
function up_down(id,url)
{
	Ajax.Updater({id:id, url:url, evalscripts:'true'});
}

//通讯录：增加用户，接受用户，查询；
function request_search_users(action)
{
	if (document.getElementById('content').value != '')
	{
		if (!check_account('content'))
		{
			alert(' search content should be chinese characters or letter or number! ');
			document.getElementById('content').focus();
			return false;
		}
	}
	var content = document.getElementById('content').value;
	var params = 'content='+content;
	var url = '/contacts/request_search_users/action/'+action;
	Ajax.Updater({url:url, params:params, jsfunc:'set_select_option', evalscripts:'true'});
}



// -------------------------------------- for tree

// checkbox 选中分支
function check_branch(tree)
{
	try {
		tree.setSubChecked(tree.getSelectedItemId(),true);
	} catch (e) {
		alert("tree does not exist ? please select item");
	}
	return;
}

// checkbox 取消分支
function uncheck_branch(tree)
{
	tree = tree == '' ? 'tree' : tree; 
	try {
		tree.setSubChecked(tree.getSelectedItemId(),false);
	} catch (e) {
		alert("tree does not exist ? please select item");
	}	
	return;
}

// 收缩选中分支
function collapse_selected(tree)
{
	tree = tree == '' ? 'tree' : tree; 
	try {
		tree.closeAllItems(tree.getSelectedItemId());
	} catch (e) {
		alert("tree does not exist ? please select item");
	}	
	return;
}

// 动态展开选中分支
function expand_selected(tree)
{
	tree = tree == '' ? 'tree' : tree; 
	try {
		tree.openAllItems(tree.getSelectedItemId());
	} catch (e) {
		alert("tree does not exist ? please select item");
	}	
	return;	
}

// 登陆选择单位树
function load_login_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){select_corp(id);});
	tree.setXMLAutoLoading("/tree/printSelectCorpTree"); 
	tree.loadXML("/tree/printSelectCorpTree?id=root");
}

// 登陆单位选择
function select_corp(id) 
{
	permission = tree.getUserData(id,'permission');
	if (permission == 'true')
		window.location.href = "/admin/select_corp?current_corp_id="+id;
	else 
		alert('no permission');
}






// 用户管理树
function load_user_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){user_list(id);});
	tree.setXMLAutoLoading("/tree/printUgTree?tree_from=user"); 
	tree.loadXML("/tree/printUgTree?id=root&tree_from=user");
}

// 请求部门列表用户
function user_list(id) 
{
	var ug_name = encodeURIComponent(tree.getUserData(id,'ug_name'));
	parent.list.location.href = "/user/list?ug_id="+id+"&name="+ug_name;
}  

// 采集活动树
function load_col_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){col_list(id);});
	tree.setXMLAutoLoading("/tree/printCollectionTree?tree_from=col"); 
	tree.loadXML("/tree/printCollectionTree?tree_from=col&id=root");
}

// 采集活动列表
function col_list(id) 
{
	var parent_id = tree.getUserData(id,'parent_id');
	parent.list.location.href = "/col/col_list/?ug_id="+id+"&parent_id="+parent_id;
}

// 部门采集活动树
function load_ugcol_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){ugcol_list(id);});
	tree.setXMLAutoLoading("/tree/printCollectionTree?tree_from=ug"); 
	tree.loadXML("/tree/printCollectionTree?tree_from=ug&id=root");
}

// 部门采集活动列表
function ugcol_list(id) 
{
	var parent_id = tree.getUserData(id,'parent_id');
	parent.list.location.href = "/ugcol/col_list/?ug_id="+id+"&parent_id="+parent_id;
}

// 部门管理树
function load_ug_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){dept_list(id);});
	tree.setXMLAutoLoading("/tree/printUgTree?tree_from=ug");  
	tree.loadXML("/tree/printUgTree?id=root&tree_from=ug");
}

// 子部门列表
function dept_list(id) 
{
	var parent_id = tree.getUserData(id,'parent_id');
	parent.list.location.href = "/ug/ug_dept_list/?ug_id="+id+"&parent_id="+parent_id;
}

// 新建采集内容部门树
function load_corp_tree()
{
	//if (document.getElementById('treebox').innerHTML!='') return;
 	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
    tree.enableThreeStateCheckboxes(true);
	tree.setXMLAutoLoading("/tree/printUgTree"); 
	tree.loadXML("/tree/printUgTree?id=root");	
}

function load_up_corp_tree(id)
{
	if (document.getElementById('treebox').innerHTML!='') return;
 	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
    tree.enableThreeStateCheckboxes(1);
	tree.setXMLAutoLoading("/tree/printUgTree?col_id="+id); 
	tree.loadXML("/tree/printUgTree?id=root&col_id="+id);	
}

// 角色分配部门树
function load_role_depts_tree(role_id)
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
    tree.enableThreeStateCheckboxes(true);
	tree.setXMLAutoLoading("/tree/printUgTree?role_id="+role_id); 
	tree.loadXML("/tree/printUgTree?id=root&role_id="+role_id);
}

// 角色分配用户树
function load_role_users_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){request_role_users(id);});
	tree.setXMLAutoLoading("/tree/printUgTree"); 
	tree.loadXML("/tree/printUgTree?id=root");	
}

function request_role_users(id)
{
	var branch = document.getElementById('branch').value;
	request_dept_users(id, branch);
}

// 请求部门下用户
function request_dept_users(ug_id, branch)
{	
	if (branch == '1')
		var url = '/role/request_dept_users/ug_id/'+ug_id+'/branch/1';
	else
		var url = '/role/request_dept_users/ug_id/'+ug_id;	
	Ajax.Updater({url:url, jsfunc:'set_select_option', evalscripts:'true'});
}

// 加载汇总数据树
function load_package_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){set_data_package(id);});
	tree.setXMLAutoLoading("/tree/printCollectionTree"); 
	tree.loadXML("/tree/printCollectionTree?id=root");	
}

// 请求采集活动下采集内容
function set_data_package(col_id) 
{
    var tmpl_id = document.getElementById("tmpl_id").value;
    var branch = document.getElementById("branch").value;
    //alert("branch:"+branch+" tmpl_id:"+tmpl_id);
	var url = '/package/request_col_contents/col_id/'+col_id+'/tmpl_id/'+tmpl_id+'/branch/'+branch;
	Ajax.Updater({url:url, jsfunc:'set_select_option', evalscripts:'true'});
}






// 新增用户树
function load_user_add_tree(active_ug_id) {
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
	tree.setXMLAutoLoading("/tree/printCommonTree?dept_id="+active_ug_id); 
	tree.loadXML("/tree/printCommonTree?id=root&tree_no_session=nosession&dept_id="+active_ug_id);
}

//编辑用户树
function load_user_upd_tree(user_id) 
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
	tree.setXMLAutoLoading("/tree/printCommonTree?user_id="+user_id); 
	tree.loadXML("/tree/printCommonTree?user_id="+user_id+"&id=root&tree_no_session=nosession");
}




// 新建通讯录部门树
function load_contacts_tree()
{
	if (document.getElementById('treebox').innerHTML!='') return;
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
	tree.setXMLAutoLoading("/tree/printCommonTree/view_sub_corps/from_contacts"); 
	tree.loadXML("/tree/printCommonTree?id=root&tree_no_session=nosession&view_sub_corps=from_contacts");	
}

// 新建通讯录自定义分组 加载树
function load_dcontacts_tree(corp_id)
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){set_dcontacts_members(id);});
	tree.setXMLAutoLoading("/tree/printCommonTree/corp_id/"+corp_id); 
	tree.loadXML("/tree/printCommonTree/corp_id/"+corp_id+"?id=root&tree_no_session=nosession");	
}

// 自定义通讯录 设置部门成员
function set_dcontacts_members(id)
{
	var branch = document.getElementById('branch').value;
	request_dept_users(id, branch);
}

// 通讯录部门成员管理编辑树
function load_contacts_depts_tree(contacts_id)
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(true);
	tree.setXMLAutoLoading("/tree/printCommonTree/contacts_id/"+contacts_id+"/view_sub_corps/from_contacts"); 
	tree.loadXML("/tree/printCommonTree/contacts_id/"+contacts_id+"?id=root&tree_no_session=nosession&view_sub_corps=from_contacts");
}

// 通讯录接收人员选择树
function load_contacts_receivers_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){request_receivers(id);});
	tree.setXMLAutoLoading("/tree/printCommonTree/view_sub_corps/from_tribe_add"); 
	tree.loadXML("/tree/printCommonTree?id=root&tree_no_session=nosession&view_sub_corps=from_tribe_add");
}

// 通讯录接收人员请求用户
function request_receivers(id) 
{
	var branch = document.getElementById('branch').value;	
	request_dept_users(id, branch);
}    

// 协作区增加成员树
function load_tribe_add_member_tree()
{
	tree = new dhtmlXTreeObject("treebox","100%","100%",'root');
	tree.setImagePath("/js/tree/imgs/");
	tree.enableCheckBoxes(false);
	tree.setOnClickHandler(function(id){request_tribe_members(id);});
	tree.setXMLAutoLoading("/tree/printCommonTree/view_sub_corps/from_tribe_add_member"); 
	tree.loadXML("/tree/printCommonTree?id=root&tree_no_session=nosession&view_sub_corps=from_tribe_add_member");	
}

// 协作区增加成员 请求部门下用户
function request_tribe_members(id)
{
	var branch = document.getElementById('branch').value;
	request_dept_users(id, branch);
}





function goto_api_setting(obj)
{
	if (obj.value == 'user') window.location = '/sys/showapiuser';
	else if (obj.value == 'ip') window.location = '/sys/showip';
	return;
}

