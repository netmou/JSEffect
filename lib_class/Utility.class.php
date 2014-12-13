<?php
IN_MY_PHP||die(0);
/**
 * 一个有价值工具类
 * @author netmou <leiyanfo@sina.com>
 */
class Utility {

    const GPC=get_magic_quotes_gpc();

    /**
     * 返回地球上两个经纬坐标之间的的距离，算法基于椭圆，返回值单位：米（M）
     */
    public function getFlatternDistance($lat1, $lng1, $lat2, $lng2) {
        if ($lat1 == $lat2 && $lng1 == $lng2) {
            return 0;
        }
        $f = ($lat1 + $lat2) / 2 * pi() / 180.0;
        $g = ($lat1 - $lat2) / 2 * pi() / 180.0;
        $l = ($lng1 - $lng2) / 2 * pi() / 180.0;

        $sin_g = sin($g);
        $sin_l = sin($l);
        $sin_f = sin($f);

        $a = 6378137.0;
        $fl = 1 / 298.257;

        $sin_g_2 = $sin_g * $sin_g;
        $sin_l_2 = $sin_l * $sin_l;
        $sin_f_2 = $sin_f * $sin_f;

        $s = $sin_g_2 * (1 - $sin_l_2) + (1 - $sin_f_2) * $sin_l_2;
        $c = (1 - $sin_g_2) * (1 - $sin_l_2) + $sin_f_2 * $sin_l_2;

        $w = atan(sqrt($s / $c));
        $r = sqrt($s * $c) / $w;
        $d = 2 * $w * $a;
        $h1 = (3 * $r - 1) / 2 / $c;
        $h2 = (3 * $r + 1) / 2 / $s;

        return $d * (1 + $fl * ($h1 * $sin_f_2 * (1 - $sin_g_2) - $h2 * (1 - $sin_f_2) * $sin_g_2));
    }

    /**
     * 判断点是否在多边形内,算法基于多边形外的点与多边形的边相交，有偶数个交点
     */
    public function pointInPolygon($p, $points) {
        $cross = 0;
        $size = count($points);
        for ($i = 0; $i < $size; $i++) {
            $p1 = $points[$i];
            $p2 = $points[($i + 1) % $size];
            if ($p1['lat'] == $p2['lat']) {
                continue;
            }
            if ($p['lat'] < min($p1['lat'], $p2['lat'])) {
                continue;
            }
            if ($p['lat'] >= max($p1['lat'], $p2['lat'])) {
                continue;
            }
            $x = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];
            if ($x > $p['lng']) {
                ++$cross;
            }
        }
        return $cross % 2 == 1;
    }

    /**
     * 去除XSS（跨站脚本攻击）的函数
     * CR(0a) and LF(0b) and TAB(9) are allowed
     * */
    public function removeXSS($val) {
        $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);

        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
        }

        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true;
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                $val = preg_replace($pattern, $replacement, $val);
                if ($val_before == $val) {
                    $found = false;
                }
            }
        }
        return $val;
    }

    public function filterData($input){
        if(is_array($input)){
            foreach($input as $key=>$val){
                $input[$key]=$this->filterData($val);
            }
            return $input;
        }else{
            return $this->removeXSS($input);
        }
    }


    /**
     * 临时重置页面
     */

    public function redirect($url) {
        if (! headers_sent()) {
            header('HTTP/1.1 302 Temporarily Moved');
            header('Location: ' . $url);
            exit(0);
        }
        trigger_error("Header content has been sent!",E_USER_ERROR);
    }

    /**
     * 给出js-alert提示并跳转页面
     */
    public function alert($msg, $addr=null) {
        echo "<script>\n";
        echo "alert('{$msg}');\n";
        if ($addr !=null) {
            echo "location.href='{$addr}';\n";
        }
        echo "</script>";
        exit(0);
    }

    /**
     * 将PHP变量的值嵌入在js代码中，使其成为合法的js常量
     * 本函数针对外部的输入，不适用于内部输入
     */
    public function toJsVar($val,$quote='"', $slash = false) {
        if (is_scalar($val)) {
            if (is_numeric($val)) {
                return $val;
            } else if (is_string($val)) {
                if ($slash && !GPC) {
                    $val = str_replace("\\", '\\\\', $val);
                    $val = str_replace("\"", '\"', $val);
                    $val = str_replace('\'', '\\\'', $val);
                }
                $val = str_replace("\f", '\f', $val); //换页
                $val = str_replace("\v", '\v', $val); //垂直制表
                $val = str_replace("\t", '\t', $val); //水平制表
                $val = str_replace("\n", '\n', $val); //换行
                $val = str_replace("\r", '\r', $val); //回车
                return $quote . $val . $quote;
            } else if (is_bool($val)) {
                return $val ? 'true' : 'false';
            }
        }
        return 'null';
    }

    /**
     * 针对外部输入，将变量中特殊字符转义
     */
    public function addSlash($str) {
        if (GPC) {
            return $str;
        }
        return addslashes($str);
    }

    /**
     * 针对外部输入，将变量中经过转义的特殊字符反转义
     */
    public function stripSlash($str) {
        if (GPC) {
            return stripslashes($str);
        }
        return $str;
    }

    /**
    * 移除全局变量的影响 only for php5.2 -
    */
    public function clearGlobal(){
        if (!ini_get(register_globals)) {
            return null;
        }
        if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            trigger_error("GLOBALS overwrite attempt detected!!",E_USER_ERROR);
        }
        $except= array('GLOBALS','_GET','_POST','_COOKIE','_REQUEST','_SERVER','_ENV','_FILES');
        $_SESSION=isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array();
        $input=array_merge($_GET,$_POST,$_COOKIE,$_SERVER,$_ENV,$_FILES,$_SESSION);
        foreach ($input as $k=>$v) {
            if (!in_array($k,$except) && isset($GLOBALS[$k])) {
                unset($GLOBALS[$k]);
            }
        }
    }

    /**
     * 在utf-8的字符编码的字符串中截取部分
     */
    public function subUtf8($str, $len, $pad = null) {
        $offset = 0;
        $chars = 0;
        $rst = null;
        $flag = array(0x3F, 0x1F, 0xF, 0x7, 0x3, 0x0);
        while ($chars < $len && $offset < strlen($str)) {
            $high = ord(substr($str, $offset, 1));
            for ($i = 2; $i < 8; $i++) {
                if ($high >> $i == $flag[$i]) {
                    $rst.= substr($str, $offset, 8 - $i);
                    $offset = $offset + 8 - $i;
                    ++$chars;
                    break;
                }
            }
        }
        return $rst . $pad;
    }

    /**
     * 获取IP地址
     */
    public function getRealIPAddress() {
        if ($IP = $_SERVER['HTTP_CLIENT_IP']) {
            return $IP;
        } else if ($IP = $_SERVER['HTTP_X_FORWARDED_FOR']) {
            return $IP;
        } else if ($IP = $_SERVER['REMOTE_ADDR']) {
            return $IP;
        }
        return '0.0.0.0';
    }

    public function upload($field,$allow=array(),$maxSize=5242880){
        $origin=array('gif','jpeg','jpg','png','bmp','txt','pdf');
        $allow=array_unique(array_merge($origin,$allow));
        if($_FILES[$field]){
            if ($_FILES[$field]['error'] == 4){
                return array('error'=>'2','message'=>'没有上传文件，请重新上传！！');
            }
            if ($_FILES[$field]['error'] > 0){
                return array('error'=>'1','message'=>'上传失败，未知网络错误！！');
            }
            if($_FILES[$field]["size"] > $maxSize){
                return array('error'=>'1','message'=>"上传失败，文件大小限制'{$maxSize}'！！");
            }
            $originfile=$_FILES[$field]["name"];
            $ext = strtolower(substr(strrchr($originfile,"."),1));
            if(!in_array($ext,$allow)){
                return array('error'=>'1','message'=>'上传失败，未知文件格式！！');
            }
            $filename=date("ymd") . rand(10000, 99999) . '.' . $ext;
            $dir=RTPATH.'uploads'.DS.date("Y").DS.date("m").DS;
            $path=WBPATH.'uploads/'.date("Y/m/");
            $url=$path.$filename;
            $file=$dir.$filename;
            file_exists($dir) || mkdir($dir,0777,true);
            move_uploaded_file($_FILES[$field]["tmp_name"], $file);
            return array(
                'error'=>'0',
                'url'=>$url,
                'file'=>$file,
                'dir'=>$dir,
                'path'=>$path,
                'filename'=>$filename,
                'originfile'=>$originfile,
            );
        }
        return array('error'=>'1','message'=>'没有上传动作！！');
    }

    /**
    * 生成缩略图，本函数支持透明通道，等比例缩放，对小图片不作处理
    * @param width 目标最大宽度
    * @param height 目标最大高度
    * @param cut 是剪贴图像还是缩放
    */
    public function thumb($src, $dst, $width=1000, $height=800, $cut=false){
        if(!file_exists($src)){
            trigger_error('Invalid file path!!',E_USER_ERROR);
        }
        $ext = strtolower(substr(strrchr($src,"."),1));
        $origin=array('gif','jpeg','jpg','png','bmp');
        if(!in_array($ext,$origin)){
            trigger_error('Unsupported image file!!',E_USER_ERROR);
        }
        list($w, $h) = getimagesize($src);
        if($w < $width or $h < $height){
            return copy($src, $dst);
        }
        switch($ext){
            case 'bmp':
                $img = imagecreatefromwbmp($src);
                break;
            case 'gif':
                $img = imagecreatefromgif($src);
                break;
            case 'jpeg':
            case 'jpg':
                $img = imagecreatefromjpeg($src);
                break;
            case 'png':
                $img = imagecreatefrompng($src);
                break;
        }
        if($cut){//剪切图片（$w:$center,$h:$top）
            $ratio = max($width/$w, $height/$h);
            $h = $height / $ratio;
            $x = ($w - $width / $ratio) / 2;
            $w = $width / $ratio;
        }else{//缩放图片
            $ratio = min($width/$w, $height/$h);
            $width = $w * $ratio;
            $height = $h * $ratio;
            $x = 0;
        }
        $canvas = imagecreatetruecolor($width, $height);
        if($ext == "gif" or $ext == "png"){
            imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }
        imagecopyresampled($canvas, $img, 0, 0, $x, 0, $width, $height, $w, $h);
        switch($ext){
            case 'bmp':
                return imagewbmp($canvas, $dst);
            case 'gif':
                    return imagegif($canvas, $dst);
            case 'jpg':
                return imagejpeg($canvas, $dst);
            case 'png':
                return imagepng($canvas, $dst);
        }
        return false;
    }

    /**
    * 将从数据库中返回的多行数据按字段求和，返回一维数组,
    */
    public function multiSum($data){
        for($i=1;$i<count($data);$i++){
            foreach($data[$i] as $key=>$val){
                $data[0][$key]+=$val;
            }
        }
        return $data[0];
    }

    /**
    * 分组统计转换 eg. select count(xx) as num, xx from... group by xx;
    * 返回数组 eg. array(xx1=>count(xx1),xx2=>count(xx2),...)
    */
    public function groupConvert ($data,$key,$val){
        $tmp=array();
        for($i=0;$i<count($data);$i++){
            $index=$data[$i][$key];
            $tmp[$index]=$data[$i][$val];
        }
        return $tmp;
    }

    /**
    * 将数据转换成地址形式：key=val&key2=val2&...
    */
    public function dataToUrl($data,$data2=array()){
        $tmp=array_unique(array_merge($data,$data2));
        return http_build_query($tmp);
    }

    /**
    * 将一个url的quergString部分解析为键值对数组
    */
    public function parseQuery($url) {
        $info = parse_url($url);
        $tmp = array();
        parse_str($info['query'], $tmp);
        return $tmp;
    }

    /**
    * form 表单文单行本输入框
    */
    public function formTextInput($name,$cat,$value=null,$attr=null){
        return "<input type=\"{$cat}\" name=\"{$name}\" value=\"{$value}\" {$attr}/>\n";
    }

    /**
    * form 表单下拉列表
    */
    public function formSelect($name,$data,$sid=0,$attr=null){
        $formStr="<select name=\"{$name}\" {$attr}>\n";
        foreach($data as $key=>$val){
            $select=($key==$sid)?'selected':null;
            $formStr.="<option {$select} value=\"{$key}\">{$val}</option>\n";
        }
        return $formStr."</select>\n";
    }

    /**
    * form 表单单选按钮组
    */
    public function formRadio($name,$data,$sid=0,$attr=null){
        $formStr=null;
        foreach($data as $key=>$label){
            $checked=($key==$sid)?'checked':null;
            $formStr.="<label> <input name=\"{$name}\" type=\"radio\" {$checked} value=\"{$key}\" {$attr} /> {$label} </label>";
        }
        return $formStr;
    }

    /**
    * form 表单复选框
    */
    public function formCheckBox($name,$data,$set=array(),$attr=null){
        $formStr=null;
        foreach($data as $key=>$label){
            $checked=(in_array($key,$set))?'checked':null;
            $formStr.="<label> <input name=\"{$name}\" type=\"checkbox\" {$checked} value=\"{$key}\" {$attr} /> {$label} </label>";
        }
        return $formStr;
    }
    /**
    * form 表单多行文本输入区
    */
    public function formTextArea($name,$value=null,$attr=null){
        return "<textarea  name=\"{$name}\" {$attr}>{$value}</textarea>";
    }

    /**
     * 加密字符串
     */
    public function encrypt($encrypt, $key = "~QaZ`!1X2s3C4W5V6d7B8@9N0f-M=E,g") {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv);
        return base64_encode($passcrypt);
    }

    /**
     * 解密字符串
     */
    public function decrypt($decrypt, $key = "~QaZ`!1X2s3C4W5V6d7B8@9N0f-M=E,g") {
        $decoded = base64_decode($decrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB, $iv);
    }

}
?>
