<?php 
class CFileUploader{
	var $saveName;// 保存名
	var $savePath;// 保存路径
	var $savePathOri;// 用户设置原始保存路径
	var $savePathFunc;// 保存路径的方法
	var $fileFormat = array('gif','jpg','doc','application/octet-stream');// 文件格式&MIME限定
	var $overwrite = 0;// 覆盖模式
	var $maxSize = 0;// 文件最大字节
	var $ext;// 文件扩展名
	var $thumb = 0;// 是否生成缩略图
	var $thumbWidth = 130;// 缩略图宽
	var $thumbHeight = 130;// 缩略图高
	var $thumbPrefix = "_thumb_";// 缩略图前缀
	var $errno;// 错误代号
	var $returnArray= array();// 所有文件的返回信息
	var $returninfo= array();// 每个文件返回信息
	

// 构造函数
// @param $savePath 文件保存路径
// @param $fileFormat 文件格式限制数组
// @param $maxSize 文件最大尺寸
// @param $overwriet 是否覆盖 1 允许覆盖 0 禁止覆盖
	function CFileUploader($savePath, $fileFormat='',$maxSize = 0, $overwrite = 0) {
		$this->setSavepath($savePath);
		$this->setFileformat($fileFormat);
		$this->setMaxsize($maxSize);
		$this->setOverwrite($overwrite);
		$this->setThumb($this->thumb, $this->thumbWidth,$this->thumbHeight);
		$this->errno = 0;
	}

	//建目录函数，其中参数$directoryName最后没有"/"，
	//要是有的话，以'/'打散为数组的时候，最后将会出现一个空值
	function makeDirectory($directoryName) 
	{
		$directoryName = str_replace("\\","/",$directoryName);
		$dirNames = explode('/', $directoryName);
		$total = count($dirNames) ;
		$temp = '';
		for($i=0; $i<$total; $i++) {
			$temp .= $dirNames[$i].'/';
			if (!is_dir($temp)) {
				$oldmask = umask(0);
				if (!mkdir($temp, 0777)) exit("不能建立目录 $temp"); 
				umask($oldmask);
			}
		}
		return true;
	}

	//按照文件名称，建立多级目录?
	function gen_rand_path($filename)
	{
		$spath=$filename;
		$pathlevel1 = substr($spath,0,2);
		$pathlevel2 = substr($spath,2,2);
		$pathlevel3 = substr($spath,4,2);
		$pathlevel4 = substr($spath,6,2);
		$newpath= $pathlevel1."/".$pathlevel2."/".$pathlevel3."/".$pathlevel4."/";	
		return $newpath;
	}
	
// 上传
// @param $fileInput 网页Form(表单)中input的名称
// @param $changeName 是否更改文件名
	function run($fileInput,$randName = 1,$customnname=''){
		if(isset($_FILES[$fileInput])){
			$fileArr = $_FILES[$fileInput];
			if(is_array($fileArr['name'])){//上传同文件域名称多个文件
				for($i = 0; $i < count($fileArr['name']); $i++){
					$ar['tmp_name'] = $fileArr['tmp_name'][$i];
					$ar['name'] = $fileArr['name'][$i];
					$ar['type'] = $fileArr['type'][$i];
					$ar['size'] = $fileArr['size'][$i];
					$ar['error'] = $fileArr['error'][$i];
					$this->getExt($ar['name']);//取得扩展名，赋给$this->ext，下次循环会更新
					$this->setSavename($randName == 1 ? '' : $ar['name']);//设置保存文件名
					if($this->copyfile($ar, $randName)){
						$this->returnArray[] =  $this->returninfo;
					}else{
						$this->returninfo['error'] = $this->errmsg();
						$this->returnArray[] =  $this->returninfo;
					}
				}
				return $this->errno ?  false :  true;
			}else{//上传单个文件
				$this->getExt($fileArr['name']);//取得扩展名
				//echo $this->ext;
				if (!$randName)
				{
					$filename = $customnname == '' ? $fileArr['name'] : $customnname.'.'.$this->ext;
				}
				else 
				{
					$filename = '';
				}
				$this->setSavename($filename);//设置保存文件名
				if($this->copyfile($fileArr, $randName)){//这里copy失败
					$this->returnArray[] =  $this->returninfo;
				}else{
					$this->returninfo['error'] = $this->errmsg();
					$this->returnArray[] =  $this->returninfo;
				}
				return $this->errno ?  false :  true;
			}
			return false;
		}else{
			$this->errno = 10;
			return false;
		}
	}

// 单个文件上传
// @param $fileArray 文件信息数组
	function copyfile($fileArray, $randName){
		$this->returninfo = array();
		// 返回信息
		$this->returninfo['name'] = $fileArray['name'];
		$this->returninfo['saveName'] = $this->saveName;
		$this->returninfo['size'] = $fileArray['size']; //number_format( ($fileArray['size'])/1024 , 0, '.', ' ');//以KB为单位
		$this->returninfo['type'] = $fileArray['type'];
		// 检查文件格式
		if (!$this->validateFormat()){
			$this->errno = 11;
			return false;
		}
		// if(!method_exists($this,"getSavepath"))
		if ($this->savePathFunc) 
		{
			$savePathFunc = $this->savePathFunc;
			$this->savePath = $savePathFunc($this->saveName);
			$this->returninfo['path'] = $this->savePath;
		}else
		if ($randName)
		{
			$randpath = $this->gen_rand_path($this->saveName);
			$this->savePath = $this->savePathOri.$randpath;
			$this->returninfo['path'] = $this->relativePath.$randpath;
		}
		$this->makeDirectory($this->savePath);

/*		echo "<br>-------------------------------------<br>";
		echo $this->savePath."<br>";
		echo $this->savePath.$this->saveName."<br>";
		echo "<br>-------------------------------------<br>";*/
		
		// 检查目录是否可写
		if(!@is_writable($this->savePath)){
			$this->errno = 12;
			return false;
		}
		// 如果不允许覆盖，检查文件是否已经存在
		if($this->overwrite == 0 && @file_exists($this->savePath.$fileArray['name'])){
			$this->errno = 13;
			return false;
		}
		// 如果有大小限制，检查文件是否超过限制
		if ($this->maxSize != 0 ){
			if ($fileArray["size"] > $this->maxSize){
				$this->errno = 14;
				return false;
			}
		}
		// 文件上传
		if(!@copy($fileArray["tmp_name"], $this->savePath.$this->saveName)){
			$this->errno = $fileArray["error"];
			return false;
		}elseif( $this->thumb ){// 创建缩略图
			$CreateFunction = "imagecreatefrom".($this->ext == 'jpg' ? 'jpeg' : $this->ext);
			$SaveFunction = "image".($this->ext == 'jpg' ? 'jpeg' : $this->ext);
			if (strtolower($CreateFunction) == "imagecreatefromgif" 
				&& !function_exists("imagecreatefromgif")) {
				$this->errno = 16;
				return false;
			} elseif (strtolower($CreateFunction) == "imagecreatefromjpeg" 
				&& !function_exists("imagecreatefromjpeg")) {
				$this->errno = 17;
				return false;
			} elseif (!function_exists($CreateFunction)) {
				$this->errno = 18;
				return false;
			}
				
			$Original = @$CreateFunction($this->savePath.$this->saveName);
			if (!$Original) {$this->errno = 19; return false;}
			$originalHeight = ImageSY($Original);
			$originalWidth = ImageSX($Original);
			$this->returninfo['originalHeight'] = $originalHeight;
			$this->returninfo['originalWidth'] = $originalWidth;
			if (($originalHeight < $this->thumbHeight 
				&& $originalWidth < $this->thumbWidth)) {
				// 如果比期望的缩略图小，那只Copy
				copy($this->savePath.$this->saveName, 
					$this->savePath.$this->thumbPrefix.$this->saveName);
			} else {
				if( $originalWidth > $this->thumbWidth ){// 宽 > 设定宽度
					$thumbWidth = $this->thumbWidth ;
					$thumbHeight = $this->thumbWidth * ( $originalHeight / $originalWidth );
					if($thumbHeight > $this->thumbHeight){// 高 > 设定高度
						$thumbWidth = $this->thumbHeight * ( $thumbWidth / $thumbHeight );
						$thumbHeight = $this->thumbHeight ;
					}
				}elseif( $originalHeight > $this->thumbHeight ){// 高 > 设定高度
					$thumbHeight = $this->thumbHeight ;
					$thumbWidth = $this->thumbHeight * ( $originalWidth / $originalHeight );
					if($thumbWidth > $this->thumbWidth){// 宽 > 设定宽度
						$thumbHeight = $this->thumbWidth * ( $thumbHeight / $thumbWidth );
						$thumbWidth = $this->thumbWidth ;
					}
				}
				if ($thumbWidth == 0) $thumbWidth = 1;
				if ($thumbHeight == 0) $thumbHeight = 1;
				$createdThumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
				if ( !$createdThumb ) {$this->errno = 20; return false;}
				if ( !imagecopyresampled($createdThumb, $Original, 0, 0, 0, 0, 
					$thumbWidth, $thumbHeight, $originalWidth, $originalHeight) )
					{$this->errno = 21; return false;}
				if ( !$SaveFunction($createdThumb, 
					$this->savePath.$this->thumbPrefix.$this->saveName) )
					{$this->errno = 22; return false;}
			}
		}
		// 删除临时文件
		if(!@$this->del($fileArray["tmp_name"])){
			return false;
		}
		return true;
	}

// 文件格式检查,MIME检测
	function validateFormat(){
		if(!is_array($this->fileFormat) 
			|| in_array(strtolower($this->ext), $this->fileFormat) 
			|| in_array(strtolower($this->returninfo['type']), $this->fileFormat) )
			return true;
		else
			return false;
	}
// 获取文件扩展名
// @param $fileName 上传文件的原文件名
	function getExt($fileName){
		$ext = explode(".", $fileName);
		$ext = $ext[count($ext) - 1];
		$this->ext = strtolower($ext);
	}

// 设置上传文件的最大字节限制
// @param $maxSize 文件大小(bytes) 0:表示无限制
	function setMaxsize($maxSize){
		$this->maxSize = $maxSize;
	}
// 设置文件格式限定
// @param $fileFormat 文件格式数组
	function setFileformat($fileFormat){
		if(is_array($fileFormat)){$this->fileFormat = $fileFormat ;}
	}

// 设置覆盖模式
// @param overwrite 覆盖模式 1:允许覆盖 0:禁止覆盖
	function setOverwrite($overwrite){
		$this->overwrite = $overwrite;
	}


// 设置保存路径
// @param $savePath 文件保存路径：以 "/" 结尾，若没有 "/"，则补上
// @param $relPath 相对路径
	function setSavepath($savePath, $relPath=''){
		$this->savePath = substr( str_replace("\\","/", $savePath) , -1) == "/" ? $savePath : $savePath."/";
		$this->savePathOri = $this->savePath;
		if ($relPath != '')
			$this->relativePath = substr( str_replace("\\","/", $relPath) , -1) == "/" ? $relPath : $relPath."/";
		else
			$this->relativePath =$this->savePath;
	}

	function getSavepath(){
		return $this->savePath;
	}

//  设置计算路径的函数
	function setSavePathFunc($func)
	{
		$this->savePathFunc = $func;
	}

// 设置缩略图
// @param $thumb = 1 产生缩略图 $thumbWidth,$thumbHeight 是缩略图的宽和高
	function setThumb($thumb, $thumbWidth = 0,$thumbHeight = 0){
		$this->thumb = $thumb;
		if($thumbWidth) $this->thumbWidth = $thumbWidth;
		if($thumbHeight) $this->thumbHeight = $thumbHeight;
	}

// 设置文件保存名
// @param $saveName 保存名，如果为空，则系统自动生成一个随机的文件名
	function setSavename($saveName){
		if ($saveName == ''){  // 如果未设置文件名，则生成一个随机文件名
			$uniqid = uniqid(rand(),true);
			$name = $uniqid.'.'.$this->ext;
		} else {
			$name = $saveName;
		}
		$this->saveName = $name;
	}

// 删除文件
// @param $fileName 所要删除的文件名
	function del($fileName){
		if(!@unlink($fileName)){
			$this->errno = 15;
			return false;
		}
		return true;
	}

// 返回上传文件的信息
	function getInfo(){
		return $this->returnArray;
	}

// 遍历处理文件
	function foreachfile()
	{
		$n = count($this->returnArray);
		for($i = 0; $i < n; $i++)
		{
			if($this->returnArray[$i]['size'] != 0)
			{
				$info = $this->returnArray[$i];
			}
		}
	}
	
// 得到错误信息
	function errmsg(){
		$uploadClassError = array(
			0	=>'There is no error, the file uploaded with success. ',
			1	=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			2	=>'The uploaded file exceeds the MAX_FILE_SIZE that was specified in the HTML form.',
			3	=>'The uploaded file was only partially uploaded. ',
			4	=>'No file was uploaded. ',
			6	=>'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. ',
			7	=>'Failed to write file to disk. Introduced in PHP 5.1.0. ',
			10	=>'Input name is not unavailable!',
			11	=>'The uploaded file is Unallowable!',
			12	=>'Directory unwritable!',
			13	=>'File exist already!',
			14	=>'File is too big!',
			15	=>'Delete file unsuccessfully!',
			16	=>'Your version of PHP does not appear to have GIF thumbnailing support.',
			17	=>'Your version of PHP does not appear to have JPEG thumbnailing support.',
			18	=>'Your version of PHP does not appear to have pictures thumbnailing support.',
			19	=>'An error occurred while attempting to copy the source image . 
					Your version of php ('.phpversion().') may not have this image type support.',
			20	=>'An error occurred while attempting to create a new image.',
			21	=>'An error occurred while copying the source image to the thumbnail image.',
			22	=>'An error occurred while saving the thumbnail image to the filesystem. 
					Are you sure that PHP has been configured with both read and write access on this folder?',
			);
		if ($this->errno == 0)
			return false;
		else
			return $uploadClassError[$this->errno];
	}
}

class CLavaUploader extends CFileUploader
{
	function CLavaUploader() 
	{
		//允许的文件类型
		$fileFormat = array('gif','jpg','jpge','png', 'bmp','lsp','emo','wink','xml','dat', 'csv');
		
		//文件大小限制，单位: Byte，1KB = 1000 Byte
		//0 表示无限制，但受php.ini中upload_max_filesize设置影响
		$maxSize = 0;
		
		//覆盖原有文件吗？ 0 不允许  1 允许 
		$overwrite = 1;

		$this->CFileUploader('', $fileFormat, $maxSize, $overwrite);
	}
	
	// just for DIY only!!!!
	function save_upload_files_from($savePath,$form_input_id,$customnname='' )
	{	
		// 如果FILEs中没有指定的ID, 就返回
		//如果收到表单传来的参数，则进行上传处理，否则显示表单
		if( ! isset( $_FILES[ $form_input_id ] ) )
		{
			return -1;
		}
		if($_FILES[$form_input_id]['name'] == "")
		{
			return -2;
		}
		//创建目录
		$this->makeDirectory($savePath);
		//设置文件上传目录
		$this->setSavepath($savePath);
		//echo '>>>>>>>>>>>>>>>>> '.  $savePath.'=============='.$form_input_id;
		//print_r($_FILES);
		//参数中的uploadinput是表单中上传文件输入框input的名字
		//后面的0表示不更改文件名，若为1，则由系统生成随机文件名
		if (!$this->run($form_input_id, 0, $customnname))
		{
			//通过$f->errmsg()只能得到最后一个出错的信息，
			//详细的信息在$this->getInfo()中可以得到。
			return -3;
		}
		//上传结果保存在数组returnArray中。
		//print_r($this->getInfo());
		return count($this->getInfo());
	}
	
	
	//
	// new recommond!!!
	//	
	function save_upload_files($form_input_id ,$randName=1)
	{	
		// 如果FILEs中没有指定的ID, 就返回
		//如果收到表单传来的参数，则进行上传处理，否则显示表单
		if( ! isset( $_FILES[ $form_input_id ] ) )
		{
			return -1;
		}
		if($_FILES[$form_input_id]['name'] == "")
		{
			return -2;
		}
		//创建目录
		$this->makeDirectory($this->savePath);
		//参数中的uploadinput是表单中上传文件输入框input的名字
		//后面的0表示不更改文件名，若为1，则由系统生成随机文件名
		if (!$this->run($form_input_id, $randName))
		{
			//通过$f->errmsg()只能得到最后一个出错的信息，
			//详细的信息在$this->getInfo()中可以得到。
			return -3;
		}
		//上传结果保存在数组returnArray中。
		//print_r($this->getInfo());
		return count($this->getInfo());
	}
}
?>