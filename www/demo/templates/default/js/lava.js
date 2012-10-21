// ?base web site url
// var lavaurl="http://202.152.181.134/";

var url = window.location.href;
var lavaurl = url.substring(0, url.indexOf('/', 7)+1);

// call ajax update
function ajax_update(target, surl, params)
{
    var url = lavaurl + surl;
	
    var myAjax = new Ajax.Updater(target, url, {method: 'post', parameters: params, evalScripts: true});
}

// call ajax form update
function ajax_form_update(fname, target, surl)
{
	//var f = $(fname);
	//if(!f) f = document.forms[fname];
    var url = lavaurl + surl;
	var params = Form.serialize(fname);	
    var myAjax = new Ajax.Updater(target, url, {method: 'post', parameters: params, evalScripts: true});
}

// ??????
function DoRequestAndUpdate(target, surl, params)
{
    var url = lavaurl + surl;
    var myAjax = new Ajax.Updater(target, url, {method: 'post', parameters: params, evalScripts: true});
}

// ??????mμ??
function DoFormSubmitAndUpdate(fname, target, surl)
{
	var f = document.forms[fname];
	if(!f) f = $(fname);
	var params = Form.serialize(f);	
    var url = lavaurl + surl;
    var myAjax = new Ajax.Updater(target, url, {method: 'post', parameters: params, evalScripts: true});
}
