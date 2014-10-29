<?php
class CommonFunc{

    /**
    * 返回地球上两个经纬坐标之间的的距离，算法基于椭圆，返回值单位：米（M）
    */
    function getFlatternDistance($lat1,$lng1,$lat2,$lng2){
        if($lat1==$lat2 && $lng1==$lng2){
            return 0;
        }
        $f = ($lat1 + $lat2)/2 * pi() / 180.0;
        $g = ($lat1 - $lat2)/2 * pi() / 180.0;
        $l = ($lng1 - $lng2)/2 * pi() / 180.0;

        $sin_g = sin($g);
        $sin_l = sin($l);
        $sin_f = sin($f);

        $a = 6378137.0;
        $fl = 1/298.257;

        $sin_g_2 = $sin_g*$sin_g;
        $sin_l_2 = $sin_l*$sin_l;
        $sin_f_2 = $sin_f*$sin_f;

        $s = $sin_g_2*(1-$sin_l_2) + (1-$sin_f_2)*$sin_l_2;
        $c = (1-$sin_g_2)*(1-$sin_l_2) + $sin_f_2*$sin_l_2;

        $w = atan(sqrt($s/$c));
        $r = sqrt($s*$c)/$w;
        $d = 2*$w*$a;
        $h1 = (3*$r -1)/2/$c;
        $h2 = (3*$r +1)/2/$s;

        return $d*(1 + $fl*($h1*$sin_f_2*(1-$sin_g_2) - $h2*(1-$sin_f_2)*$sin_g_2));
    }

    /**
    * 判断点是否在多边形内,算法基于多边形外的点与多边形相交，有偶数个交点
    */
    function pointInPolygon($p, $points){
        $cross = 0;
        $size=count($points);
        for($i = 0; $i < $size; $i++){
            $p1 = $points[$i];
            $p2 = $points[($i + 1) % $size];
            if($p1['lat'] == $p2['lat'] ){
                continue;
            }
            if( $p['lat'] < min($p1['lat'], $p2['lat'])){
                continue;
            }
            if ($p['lat'] >= max($p1['lat'], $p2['lat'])){
                continue;
            }
            $x = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];
            if ($x > $p['lng']){
                ++$cross;
            }
        }
        return $cross % 2 == 1;
    }

    /**
    * 去除XSS（跨站脚本攻击）的函数
    * CR(0a) and LF(0b) and TAB(9) are allowed
    **/
    function removeXSS($val) {
       $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);

       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';
       for ($i = 0; $i < strlen($search); $i++) {
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
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
             $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
             $val = preg_replace($pattern, $replacement, $val);
             if ($val_before == $val) {
                $found = false;
             }
          }
       }
       return $val;
    }

    /**
    * 将一个url的quergString部分解析为键值对数组
    */
    function parseQuery($url){
        $info=parse_url($url);
        $tmp=array();
        parse_str($info['query'],$tmp);
        return  $tmp;
    }

    /**
    * 给出js-alert提示并跳转页面
    */
    function alert($msg,$addr=null){
        echo "<script>\n";
        echo "alert('{$msg}');\n";
        if($addr!=""){
            echo "location.href='{$addr}';\n";
        }else{
            echo "history.go(-1);\n";
        }
        echo "</script>";
        exit(0);
    }

    /**
    * 将PHP变量的值嵌入在js代码中，使其成为合法的js常量
    * 本函数针对外部的输入，不适用于内部输入
    */
    function toJsVar($val,$slash=false){
        if(is_scalar($val)){
            if(is_numeric($val)){
                return $val;
            }else if(is_string($val)){
                $val=str_replace("\n",'\n',$val);
                $val=str_replace("\r",'\r',$val);
                if($slash && !get_magic_quotes_gpc()){
                    return '"' . addslashes($val) . '"';
                }
                return '"' . $val . '"';
            }else if(is_bool($val)){
                return $val?'true':'false';
            }
        }
        return 'null';
    }

    /**
    * 针对外部输入，将变量中特殊字符转义
    */
    function addSlash($str){
        if(get_magic_quotes_gpc()){
            return $str;
        }
        return addslashes($str);
    }

    /**
    * 针对外部输入，将变量中经过转义的特殊字符反转义
    */
    function stripSlash($str){
        if(get_magic_quotes_gpc()){
            return stripslashes($str);
        }
        return $str;
    }

    /**
    * 在utf-8的字符编码的字符串中截取部分
    */
    function subUtf8($str,$len,$pad=null){
        $offset = 0;
        $chars = 0;
        $rst = null;
        $flag=array(0x3F,0x1F,0xF,0x7,0x3,0x0);
        while($chars < $len && $offset < strlen($str)){
            $high = ord(substr($str,$offset,1));
            for($i=2;$i<8;$i++){
                if($high>>$i==$flag[$i]){
                    $rst.= substr($str,$offset,8-$i);
                    $offset = $offset +8-$i;
                    ++$chars;
                    break;
                }
            }
        }
        return $rst.$pad;
    }
}

?>
