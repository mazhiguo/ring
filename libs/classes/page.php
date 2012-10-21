<?php
/**
 * Gk-Express管理端分页类
 * @name Pagination
 * @version  1.0
 * @author  章启涛
 * @copyright  2001-2007 北京点击科技有限公司    
 * 
 * 最后修改时间 2007-12-25 增加onclick事件处理
 * 
 * 1.默认支持url类型 
 * 	type = 1
 * 		http://......./x.php  ：输出后的url为 http://......./x.php?page=10
 * 		http://......./x.php? ：输出后的url为 http://......./x.php?page=10
 * 		http://......./x.php?a=b ：输出后的url为 http://......./x.php?a=b&page=10
 *  type = 2    
 *     http://....../user/list/ ：输出后的url为  http://....../user/list/page/10
 	<?php
		$o = new Pagination();	
		$o->total_recode = 10;
		$o->limit = 1;
		$o->page = $_GET['page'];
		$o->in_url = 'class_page.php';
		$o->page_name = 'p'; 
		$o->links_num = '5'; 
		$url =  $o->out_url();
		echo $url
	?>
 */

class Pagination
{	
	
	/**
	 * 定义$_GET传参的url规则类型
	 *
	 * @var int
	 * 
	 * 注：
	 * 1.type = 1 : url没有进行转发的规则,如： index.php?a=b&page=2;index.php?page=2
	 * 2.type = 2 : url进行转发后的规则(ci转发规则) 如： .../user/list/action/unactive/page/5  中get等价于 $_GET = array('action'=>'unactive', 'page'=>'5');
	 */
	var $type;
	
	/**
	 * 每页显示条数
	 *
	 * @var int  
	 */
	
	var $limit;
	/**
	 * 总条数记录
	 *
	 * @var int 
	 */
	
	var $total_recode; 
	
	/**
	 * 总页数
	 *
	 * @var int 
	 */
	
	var $total_pages; 
			
	/**
	 * 当前页面第几页
	 *
	 * @var int 
	 */
	
	var $page; 
	
	/**
	 * 要进行分页处理的url
	 *
	 * @var string  
	 */
	var $in_url;
	
	/**
	 * js定义的函数名,如果为js函数，输出则为onclick事件
	 *
	 * @var string
	 */
	var $js_func;
	/**
	 * 链接后的第一个标识符...$_GET方法开始标识符号
	 * 
	 * @var string  
	 * 
	 * 注：传统为 '?'或'&' 有些架构里url转发采用'/'或其他标识，可自行定义某个字符来替代 '?, &'
	 */
	var $inter_mark;

	/**
	 * 等于符号标识 
	 *
	 * @var string 
	 * 
	 * 注：传统为?page=1中的 '=' 可自行定义某个字符来替代'='
	 */
	var $equal;
	
	/**
	 * 生成数字链接显示的最大个数
	 *
	 * @var int 
	 */
	var $links_num;
	
	/**
	 * 生成数字链接显示的最大个数
	 *
	 * @var int 
	 */
	var $go_links;
		
	/**
	 * 使用的页面标识名称，如 users_list.php?page=4 中的page
	 *
	 * @var unknown_type
	 */
	var $page_name;

	/**
	 * 下一页
	 *
	 * @var string 
	 */
	var $next;
	
	/**
	 * 上一页
	 *
	 * @var string 
	 */
	var $previous;
	
	/**
	 * 最后页
	 *
	 * @var string 
	 */
	var $last;

	/**
	 * 最后链接页面数字前的那段url: 如： users_list.php?page=4 中的 users_list.php?page= 这部分
	 *
	 * @var string
	 */
	var $surl;
	
	function __construct()
	{
		$this->page = 1;
		$this->limit = 1;
		$this->total_recode = 100;
		$this->in_url = '';
	}

	/**
	 * 生成输出的链接
	 *
	 * @return  string out_url
	 * 
	 */
	function out_url()
	{
		/**
		 * 初始化数据
		 */
		$this->init_data();
		/**
		 * css:使用时将css不要封装在内       //测试字体：Arial，  Courier New
		 */
/*		$out_url = '<style>
		.pages {border:1px solid #CEDBEF;font-size:12px;padding:2px;font-family:Arial;width:auto}
		.pages a {margin:5px;text-decoration:none;color:#555555}
		.cur_page {background:#CEDBEF;padding:4px;margin:0px;color:#555555;font-weight:800}
		.total {border-right:1px solid #CEDBEF;padding:4px}
		.go_pages {border-left:1px solid #CEDBEF;padding:4px}
		.page_input {background:#ffffff;border:1px solid #CEDBEF;color:#555555;width:30px;margin:0px}
		.go_button {background:#CEDBEF;cursor:pointer;margin-right:2px;}
		</style>';*/
		/**
		 * 如果只有一页，不输出链接
		 */
		if ($this->total_pages == 1) 
			return "<span class='total' title='总页数/总记录数'><b>{$this->total_pages}页/{$this->total_recode}条</b></span>";
		
		/**
		 * 使用表格，一行显示
		 */
		$out_url = '';
		$out_url .= "<span class='pages'>";
		/**
		 * 最大记录数
		 */
		$out_url .= "<span class='total' title='总页数/总记录数'><b>{$this->total_pages}页/{$this->total_recode}条</b></span>";
		
		$out_url .= "<span>";
		/**
		 * 首页,上一页
		 */
		if ($this->js_func != '')
		{
			$out_url .= "<a href='#' onclick='$this->js_func(\"1\")' title='首页'><img src='/images/first.gif' style='border:none'/></a>";
			$out_url .= "<a href='#' onclick='$this->js_func(\"$this->previous\")'  title='上一页'><img src='/images/previous.gif' style='border:none'/></a>";		
		}
		else 
		{
			$out_url .= "<a href='{$this->surl}1' title='首页'><img src='/images/first.gif' style='border:none'/></a>";
			$out_url .= "<a href='{$this->surl}{$this->previous}' title='上一页'><img src='/images/previous.gif' style='border:none'/></a>";
		}
		$out_url .= "</span><span>";
		/**
		 * 数字链接
		 */
		$out_url .= $this->make_number_links();	
		$out_url .= "</span>";
		/**
		 * 下一页,最后页
		 */
		if ($this->js_func != '')
		{
			$out_url .= "<a href='#' onclick='$this->js_func(\"$this->next\")' title='下一页'><img src='/images/next.gif' style='border:none'/></a>";
			$out_url .= "<a href='#' onclick='$this->js_func(\"$this->last\")' title='最后页'><img src='/images/last.gif' style='border:none'/></a>";		
		}
		else 
		{
			$out_url .= "<a href='{$this->surl}{$this->next}' title='下一页'><img src='/images/next.gif' style='border:none'/></a>";
			$out_url .= "<a href='{$this->surl}{$this->last}' title='最后页'><img src='/images/last.gif' style='border:none'/></a>";
		}
		
		/**
		 * 跳转：非特殊需要此功能，可注释掉
		 */
		if ($this->go_links)
			$out_url .= $this->make_go_links();
		/**
		 * 总页数
		 */
		$out_url .= "</span>";
		return $out_url;
	}	
	/**
	 * 初始化数据
	 *
	 */
	function init_data()
	{
		/**
		 * 初始化默认$_GET传参的url规则类型
		 */
		$this->type = $this->type == '' ?  1 : $this->type;
		/**
		 * 是否为onclick默认为否
		 */
		$this->js_func = $this->js_func == '' ? '' : $this->js_func;
				
		/**
		 * 根据in_url和type来初始化 inter_mark 和equal 
		 */
		$this->init_inter_mark();
		
		/**
		 * 自定义page名称，默认'page'
		 */
		$this->page_name = $this->page_name == '' ? 'page' : $this->page_name;
		
		/**
		 * 链接当前页前的 url字符串
		 */
		$this->surl = $this->in_url.$this->inter_mark.$this->page_name.$this->equal;
		
		/**
		 * 数字链接最大显示的个数，默认6
		 */
		$this->links_num = $this->links_num == '' ? 6 : $this->links_num;	
		
		/**
		 * 是否显示go_links
		 */
		$this->go_links = $this->go_links == '' ? '0' : $this->go_links;				
		/**
		 * 计算total_pages;
		 */
		$this->total_pages = ceil($this->total_recode/$this->limit);	
		$this->total_pages = $this->total_pages == 0 ? 1 : $this->total_pages;
		/**
		 * 当前传入的当前page超出实际最大page处理
		 */
		$this->page = $this->page == 0 ? 1 : $this->page;
		$this->page = ($this->page > $this->total_pages) ? $this->total_pages : $this->page;
		
		/**
		 * 上页，下页，最后页
		 */
		$this->previous = $this->page - 1 > 0 ? $this->page - 1 : 1;
		$this->last = $this->total_pages;
		$this->next = ($this->page+1) > $this->total_pages ? $this->total_pages : ($this->page+1);		
	}
	
	function init_inter_mark()
	{
		if ($this->type == 1)
		{
			$this->equal = '=';
			if (preg_match("/\?{1}/", $this->in_url))
			{
				if (preg_match("/={1}/", $this->in_url))
					$this->inter_mark = '&';
				else 
					$this->inter_mark = '';
			}
			else 
			{
				$this->inter_mark = '?';
			}		
		}
		else if ($this->type == 2)
		{
			$this->equal = '/';
			if (substr($this->in_url, -1 ,1) == '/')
				$this->inter_mark = '';
			else 
				$this->inter_mark = '/';
		}
	}
		
	/**
	 * 生成显示 [1][2][3]的页面
	 * 
	 * @return  string  $links
	 * 
	 * 注：
	 * 1.如果只有一页不生成(在方法make_out_url中已控制)；
	 * 2.如果指定的显示数字链接数量大于总页数，全显示
	 * 3.如果指定的显示数字链接数量小于总页数，控制显示
	 * 
	 */
	function make_number_links()
	{
		$str = '';
		/**
		 * 如果总页数小于等于设置的数字链接数，全部显示。当前页面加粗显示
		 */
		if ($this->total_pages <= $this->links_num)
		{
			for ($i = 1; $i <= $this->total_pages; $i++)
			{
				if ($i == $this->page)
				{
					if ($this->js_func != '')
						$str .= "<span class='cur_page'><a href='#' onclick='$this->js_func(\"$i\")'>$i</a></span>";
					else 
						$str .= "<span class='cur_page'><a href='{$this->surl}{$i}'>$i</a></span>";
				}
				else 
				{
					if ($this->js_func != '')
						$str .= "<a href='#' onclick='$this->js_func(\"$i\")'>$i</a>";
					else
						$str .= "<a href='{$this->surl}{$i}'>$i</a>";
				}
			}
		}
		/**
		 * 如果总页数大于设置的数字链接数，显示以数字链接数为准。
		 * 当前页面在中间位置显示，如当前页面在最前或最后时,开始的链接数特殊处理。
		 * 
		 */
		else 
		{
			/**
			 * 取链接个数的中间值$average；计算开始位置，把当前page放在中间显示
			 */
			$average = intval($this->links_num/2); 
			
			/**
			 * $start 为开始循环显示的值链接
			 */
			
			$start = $this->page - intval($this->links_num/2);
			/**
			 * 如果start小于0，说明是从1开始
			 */
			$start = $start <= 0 ? 1 : $start;	
			/**
			 * 当结束的数字达到最大页面时,start处理
			 */
			$start = ($average + $this->page) > $this->total_pages ? $this->total_pages - $this->links_num + 1 : $start;
			
			/**
			 * 从开始循环链接字符串，当前加粗
			 */
			for ($i=$start; $i<=$this->links_num+$start-1; $i++)
			{
				if ($i == $this->page)
				{
					if ($this->js_func != '')
						$str .= "<span class='cur_page'><a href='#' onclick='$this->js_func(\"$i\")'>$i</a></span>";
					else
						$str .= "<span class='cur_page'><a href='{$this->surl}{$i}'>$i</a></span>";
				}
				else 
				{
					if ($this->js_func != '')
						$str .= "<a href='#' onclick='$this->js_func(\"$i\")'>$i</a>";		
					else
						$str .= "<a href='{$this->surl}{$i}'>$i</a>";		
				}		
			}
		}
		return $str;
	}
	
	/**
	 * 生成跳转输入到页面
	 *
	 */
	function make_go_links()
	{
		if ($this->js_func != '') return;
		/**
		 * 使用input hidden记录总页数
		 */
		$str = "<input type='hidden' id='total_pages' value='{$this->total_pages}'/>";
		/**
		 * js函数跳转到页面
		 */
		$str .= "<input type='button' value='跳转' onclick=\"go_page(this.nextSibling.value)\" class='go_button'/>";
		/**
		 * 要跳转到的页面
		 */
		$str .= "<input type='text' id='page_input' value='{$this->page}' size='30' onkeydown='if(event.keyCode==13){go_page(this.value)} return;' class='page_input'/>";
		$str .= $this->check_input();
		return "<span class='go_pages'>".$str."</span>";
	}
	
	/**
	 * js的函数连接成字符串输出
	 *
	 * @return string
	 */
	function check_input()
	{
		$str = "
		<script>
			function go_page(input_value)
			{
				var go_page = parseInt(input_value);
				//如果输入的值（要跳转的页面）不是数字或者大于最大页数，则置为1
				if (isNaN(input_value)) {
					document.getElementById('page_input').value = 1;
					window.location='{$this->surl}'+1;
					return;
				} else if (input_value > {$this->total_pages}) {
					document.getElementById('page_input').value = {$this->total_pages};
					window.location='{$this->surl}'+{$this->total_pages};
					return;
				}
				window.location='{$this->surl}'+go_page;
				return;
			}
		</script>";
		return $str;
	}
}
?>