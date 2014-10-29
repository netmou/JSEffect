<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
*/

// Define a destination
    $allow_exts=array('gif','jpeg','jpg','png','bmp');
	$name='filedata';
	$rst=null;

	if (empty($_FILES[$name])){
		$rst = array('status'=>'error','msg'=>'上传失败，没有找到上传字段');
		exit(json_encode($rst));
	}
    if ($_FILES[$name]["error"] > 0 && $_FILES[$name]["error"] != 4){
        $rst = array('status'=>'error','msg'=>'上传失败，请联系管理员');
		exit(json_encode($rst));
    }
    if ($_FILES[$name]["error"] ==4 ){
        $rst = array('status'=>'error','msg'=>'没有上传文件');
		exit(json_encode($rst));
    }
    $filename=$_FILES[$name]["name"];
    $ext=substr($filename,strrpos($filename,'.')+1);
    $ext=strtolower($ext);
    if(!in_array($ext,$allow_exts)){
        $rst = array('status'=>'error','msg'=>'上传失败，未知文件格式');
		exit(json_encode($rst));
    }
    if($_FILES[$name]["size"] > 1024*1024*5){
        $rst = array('status'=>'error','msg'=>'上传失败，文件大小限制5M');
		exit(json_encode($rst));
    }
    $dir='upload/'.date("Y/m/d").'/';
    if(!file_exists($dir)){
        mkdir($dir,0777,true);
    }
    $file=$dir. date("Ymd") . rand(10000, 99999) . '.' . $ext;
    move_uploaded_file($_FILES[$name]["tmp_name"], $file);
    $rst = array('status'=>'succeed','msg'=>'文件上传成功','file'=>$file, 'orignfile'=>$filename);
	exit(json_encode($rst));
?>
