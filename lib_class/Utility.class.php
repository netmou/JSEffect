<?php

defined('IN_MY_PHP') || die(0);
/**
 * 一个有价值工具类.
 *
 * @author netmou <leiyanfo@sina.com>
 */
class Utility
{
    /**
     * 将Excel的一个页面读取为一个二维数组
     * 本函数需引入PHPExcel类
     * 自动修复部分日期格式的数据导入后变成数字问题
     * 没有时间的转换为日期，有时间的转换为日期加时间
     * 自动将公式转换成值
     */
    public function readExcelFile($fileName, $row = 1, $sheetIndex = 0)
    {
        $result = array();
        try {
            $readType = PHPExcel_IOFactory::identify($fileName);  //在不知道文档类型的情况下获取
            $excelReader = PHPExcel_IOFactory::createReader($readType);
            $objPHPExcel = $excelReader->load($fileName);
        } catch (Exception $e) {
            trigger_error('PHPExcel File can\'nt be parsed!', E_USER_ERROR);
            return $result;
        }
        $objWorksheet = $objPHPExcel->getSheet($sheetIndex);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        for ($row; $row <= $highestRow; ++$row) {
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                $value = $cell->getValue();
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                    $formatcode = $objWorksheet->getParent()->getCellXfByIndex($cell->getXfIndex())
                        ->getNumberFormat()->getFormatCode();
                    if (preg_match('/(?=.*?(h{1,2}|s{1,2}))(?=.*?m{1,2})/i', $formatcode)) {
                        $value = gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($value));
                    } elseif (preg_match('/(?=.*?((yy){1,2}|d{1,2}))(?=.*?m{1,2})/i', $formatcode)) {
                        $value = gmdate('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($value));
                    } else {
                        $value = PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                    }
                }elseif($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_FORMULA){
                    $value=$cell->getCalculatedValue();
                }
                $result[$row - 1][$col] = $value;
            }
        }

        return $result;
    }

    /**
     * 返回地球上两个经纬坐标之间的的距离，算法基于椭圆，返回值单位：米（M）.
     */
    public function getFlatternDistance($lat1, $lng1, $lat2, $lng2)
    {
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
     * 判断点是否在多边形内,算法基于多边形外的点与多边形的边相交，有偶数个交点.
     */
    public function pointInPolygon($p, $points)
    {
        $cross = 0;
        $size = count($points);
        for ($i = 0; $i < $size; ++$i) {
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
    
    /***/
    public function fetchAgeByIdCard($id_card){
        $birth=@strtotime(substr($id_card,6,8));
        if($birth==false){
            return '';
        }
        return ceil((time()-$birth)/86400/365);
    }

    /**
     * 去除XSS（跨站脚本攻击）的函数
     * CR(0a) and LF(0b) and TAB(9) are allowed.
     * */
    public function removeXSS($val)
    {
        $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);

        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); ++$i) {
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
        }

        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true;
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); ++$i) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); ++$j) {
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
     * 本函数针对外部输入数据，过滤XSS攻击，并进行SQL特殊字符转义
     * 本函数应用在数据保存时，主要防止sql注入和XSS攻击.
     */
    public function filterData($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $val) {
                $input[$key] = $this->{__FUNCTION__}($val);
            }
            return $input;
        }
        $rxss = $this->removeXSS($input);
        return $this->addSlash($rxss);
    }
    /**
     * 本函数不同于XSS攻击过滤，本函数仅作HTML转义处理，同样能防止过滤XSS攻击
     * 本函数应用在数据输出时，主要防止数据影响页面代码.
     */
    public function outputData($output)
    {
        if (is_array($output)) {
            foreach ($output as $key => $val) {
                $output[$key] = $this->{__FUNCTION__}($val);
            }
            return $output;
        }

        return htmlspecialchars($output);
    }

    /**
     * 本函数检查指定的URL是否可访问，需要PHP开启CURL.
     */
    public function curlAvailable($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true); // 不取回数据
        $rst = curl_exec($curl); // 发送请求
        if ($rst !== false && curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            return true;
        }
        curl_close($curl);

        return false;
    }

    /**
     * 本函数检查指定的URL是否可访问，.
     */
    public function urlAvailable($url)
    {
        $heads = @get_headers($url);
        if (stristr($heads[0], '200') && stristr($heads[0], 'OK')) {
            return true;
        }

        return false;
    }

    /**
     * 临时重置页面，建议应用此函数时开启输出缓冲.
     */
    public function redirect($url)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 302 Temporarily Moved');
            header('location: '.$url);
            exit(0);
        }
        trigger_error('Header content has been sent!', E_USER_ERROR);
    }

    /**
     * 给出js-Alert提示并跳转页面.
     */
    public function jsAlert($msg, $go = null)
    {
        if (!headers_sent()) {
            header('Content-type: text/html; charset=utf-8');
        }
        echo "<script charset=\"utf-8\">\n";
        echo "alert('{$msg}');\n";
        if (is_numeric($go)) {
            echo "window.history.go({$go});\n";
        } elseif (is_string($go)) {
            echo 'window.location="'.$go."\";\n";
        } else {
            echo "window.location=document.referrer;\n";
        }
        echo '</script>';
        exit(0);
    }

    /**
     * 设置浏览器缓存当前页面的时效.
     */
    public function cache($seconds)
    {
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
        header('Cache-Control: max-age='.$seconds);
    }

    /**
     * 禁止浏览器缓存当前页面.
     */
    public function noCache()
    {
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pramga: no-cache');
    }

    /**
     * 将PHP变量的值嵌入在js代码中，使其成为合法的js常量.
     */
    public function toJsVar($val, $quote = '"', $slash = false)
    {
        if (is_scalar($val)) {
            if (is_numeric($val)) {
                return $val;
            } elseif (is_string($val)) {
                if ($slash && !get_magic_quotes_gpc()) {
                    $val = str_replace('\\', '\\\\', $val);
                    $val = str_replace('"', '\"', $val);
                    $val = str_replace('\'', '\\\'', $val);
                }
                $val = str_replace("\f", '\f', $val); //换页
                $val = str_replace("\v", '\v', $val); //垂直制表
                $val = str_replace("\t", '\t', $val); //水平制表
                $val = str_replace("\n", '\n', $val); //换行
                $val = str_replace("\r", '\r', $val); //回车
                return $quote.$val.$quote;
            } elseif (is_bool($val)) {
                return $val ? 'true' : 'false';
            }
        }

        return 'null';
    }

    /**
     * 针对外部输入，将变量中特殊字符转义.
     */
    public function addSlash($data)
    {
        if (get_magic_quotes_gpc()) {
            return $data;
        }
        if ($data && is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->{__FUNCTION__}($item);
            }

            return $data;
        }

        return addslashes($data);
    }

    /**
     * 针对外部输入，将变量中经过转义的特殊字符反转义.
     */
    public function stripSlash($data)
    {
        if (get_magic_quotes_gpc()) {
            if ($data && is_array($data)) {
                foreach ($data as $key => $item) {
                    $data[$key] = $this->{__FUNCTION__}($item);
                }

                return $data;
            }

            return stripslashes($data);
        }

        return $data;
    }

    /**
     * 移除全局变量的影响 only for php5.2 -.
     */
    public function clearGlobal()
    {
        if (!ini_get('register_globals')) {
            return;
        }
        if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            trigger_error('GLOBALS overwrite attempt detected!!', E_USER_ERROR);
        }
        $except = array('GLOBALS','_GET','_POST','_COOKIE','_REQUEST','_SERVER','_ENV','_FILES');
        $_SESSION = isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array();
        $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, $_SESSION);
        foreach ($input as $k => $v) {
            if (!in_array($k, $except) && isset($GLOBALS[$k])) {
                unset($GLOBALS[$k]);
            }
        }
    }

    /**
     * 在utf-8的字符编码的字符串中截取部分，本函数适用于全部字符.
     */
    public function subUtf8($str, $len, $pad = null)
    {
        $offset = 0;
        $chars = 0;
        $rst = null;
        $flag = array(0x3F, 0x1F, 0xF, 0x7, 0x3, 0x0);
        while ($chars < $len && $offset < strlen($str)) {
            $high = ord(substr($str, $offset, 1));
            $last = $offset;
            for ($i = 2; $i < 8; ++$i) {
                if ($high >> $i == $flag[$i - 2]) {
                    $rst .= substr($str, $offset, 8 - $i);
                    $offset = $offset + 8 - $i;
                    ++$chars;
                    break;
                }
            }
            //防止非UTF-8编码导致死循环
            if ($last === $offset) {
                ++$offset;
            }
        }

        return $rst == $str ? $rst : $rst.$pad;
    }

    /**
     * 将Excel中的日期进行转换.
     */
    public function GregorianToUnix($val)
    {
        $jd = GregorianToJD(1, 1, 1970);
        $gregorian = JDToGregorian($jd + intval($val) - 25569);

        return strtotime($gregorian);
    }

    /**
     * 隐藏字符串的部分，如电话，身份证等，适用于ASCII字符组成的字符串.
     */
    public function textPartHide($text, $head = 3, $rear = 2, $d = '*')
    {
        $len = strlen($text);
        $replace = '';
        for ($i = 0;$i < $len - $head - $rear;++$i) {
            $replace .= $d;
        }

        return preg_replace('/^(.{'.$head.'})(?:.+?)(.{'.$rear.'})$/', '${1}'.$replace.'${2}', $text);
    }

    /**
     * 获取客户端浏览器的IP地址.
     */
    public function getIPAddr()
    {
        if ($IP = $_SERVER['HTTP_CLIENT_IP']) {
            return $IP;
        } elseif ($IP = $_SERVER['HTTP_X_FORWARDED_FOR']) {
            return $IP;
        } elseif ($IP = $_SERVER['REMOTE_ADDR']) {
            return $IP;
        }

        return '0.0.0.0';
    }

    /**
     * 生成指定长度的随机字符串（由ASCII字符构成），通常用作干扰盐值.
     */
    public function randStr($length)
    {
        $ret = null;
        for ($i = 0; $i < $length; ++$i) {
            $ret .= chr(rand(33, 126));
        }

        return addslashes($ret);
    }
    /**
     * money frome digit convert to cn.
     */
    public function rmbFormat($money = 0, $is_round = true, $int_unit = '元')
    {
        $chs = array(0, '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        $uni = array('', '拾', '佰', '仟');
        $dec_uni = array('角', '分');
        $exp = array('','万','亿');
        $res = '';
        $parts = explode('.', $money, 2);
        $int = isset($parts [0]) ? strval($parts [0]) : 0;
        $dec = isset($parts [1]) ? strval($parts [1]) : '';
        $dec_len = strlen($dec);
        if (isset($parts [1]) && $dec_len > 2) {
            $dec = $is_round ? substr(strrchr(strval(round(floatval('0.'.$dec), 2)), '.'), 1) : substr($parts [1], 0, 2);
        }
        if (empty($int) && empty($dec)) {
            return '零';
        }
        for ($i = strlen($int) - 1, $t = 0; $i >= 0; ++$t) {
            $str = '';
            for ($j = 0; $j < 4 && $i >= 0; $j++, $i--) {
                $u = $int{$i}
                > 0 ? $uni [$j] : '';
                $str = $chs [$int {$i}].$u.$str;
            }
            $str = rtrim($str, '0');
            $str = preg_replace('/0+/', '零', $str);
            $u2 = $str != '' ? $exp [$t] : '';
            $res = $str.$u2.$res;
        }
        $dec = rtrim($dec, '0');
        if (!empty($dec)) {
            $res .= $int_unit;
            $cnt = strlen($dec);
            for ($i = 0; $i < $cnt; ++$i) {
                $u = $dec {$i}
                > 0 ? $dec_uni [$i] : '';
                $res .= $chs [$dec {$i}].$u;
            }
            if ($cnt == 1) {
                $res .= '整';
            }
            $res = rtrim($res, '0');
            $res = preg_replace('/0+/', '零', $res);
        } else {
            $res .= $int_unit.'整';
        }

        return $res;
    }

    /**
     * 获取pathinfo模式下的get变量，形式如：/key1/val1/key2/val2/...
     */
    public function urlRewrite($pathinfo = null)
    {
        $uri = $pathinfo ? $pathinfo : $_SERVER['PATH_INFO'];
        $params = explode('/', trim($uri, '/'));
        $ret = array();
        for ($i = 0;$i < count($params);$i += 2) {
            $ret[$params[$i]] = $params[$i + 1];
        }

        return $ret;
    }
    /**
     * 模拟http数据包获取远程的某个页面的html代码
     * 受Session影响，访问系统自身，本函数可能会发生阻塞，请避免Session死锁.
     */
    public function fetchHTMLPage($url, $postData = array(), $cookieData = array())
    {
        $cookie = null;
        foreach ($cookieData as $key => $val) {
            $cookie .= "{$key}={$val};";
        }
        $header = "User-Agent: Mozilla/5.0+ \r\n";
        if ($cookie != null) {
            $header = rtrim($cookie, ';')."\r\n";
        }
        $cookie =rtrim($cookie,';');
        $streamopt = array(
            'http' => array(
                'method' => empty($postData) ? 'GET' : 'POST',
                'header' => $header."Accept: */* \r\n",
                'content' => http_build_query($postData, '', '&'),
                'timeout' => 30,
            ),
        );

        return @file_get_contents($url, false, stream_context_create($streamopt));
    }

    public function httpMutilPost($url, $post, $files = array(), $cookies = array())
    {
        $content = null;
        $cookie = null;
        $boundary = $this->randStr(32);
        //post-data
        foreach ($post as $key => $val) {
            $content .= "--{$boundary}\n";
            $content .= "Content-Disposition: form-data; name=\"{$key}\"\n\n{$val}\n";
        }
        $content .= "--{$boundary}\n";
        //file-data
        foreach ($files as $key => $file) {
            $fileContents = file_get_contents($file);
            $content .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$file}\"\n";
            $content .= "Content-Type: application/octet-stream\n";
            $content .= "Content-Transfer-Encoding: binary\n\n";
            $content .= $fileContents."\n";
            $content .= "--$boundary--\n";
        }
        foreach ($cookies as $key => $val) {
            $cookie .= "{$key}={$val};";
        }
        $header = "User-Agent: Mozilla/5.0+ \r\n";
        if ($cookie != null) {
            $header = rtrim($cookie, ';')."\r\n";
        }
        $header .= "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
        $streamContext = stream_context_create(array('http' => array(
            'method' => 'POST',
            'header' => $header,
            'content' => $content,
        )));
        $handler = @fopen($url, 'rb', false, $streamContext);
        if (false === $handler) {
            throw new Exception("there is a problem with '{$url}'");
        }
        $response = @stream_get_contents($handler);
        if (false === $response) {
            throw new Exception("Problem reading data from {$url}");
        }
        if($handler!==false){
            fclose($handler);
        }
        return $response;
    }

    /**
     * 获取远程图片、文件等并保存，本函数依赖RTPATH（文件根路径），WBPATH（URL根）等常量.
     */
    private function pullImage($url, $allow = array(), $maxSize = 5242880)
    {
        preg_match('/(http\:\/\/)?(.+?)[\/]([^\/]+?)\.([^\/\.\?\#]+?)([\/\?\#].+)?$/', $url, $match);
        if (empty($match) || $match[3] == '' || $match[4] == '') {
            return array('error' => '1','message' => '无法获取文件信息，请检查网址是否合法！');
        }
        if (!$this->urlAvailable($url)) {
            return array('error' => '1','message' => '网址无法访问，请检查网址是否正确！');
        }
        $origin = array('gif','jpeg','jpg','png','bmp','txt','pdf','doc','docx','xls','xlsx');
        $allow = array_unique(array_merge($origin, $allow));
        $ext = strtolower($match[4]);
        if (!in_array($ext, $allow)) {
            return array('error' => '1','message' => '获取失败，未知文件格式！');
        }
        $context = stream_context_create(array(
            'http' => array('follow_location' => false),
        ));
        $content = @file_get_contents($url, false, $context);
        if (strlen($content) > $maxSize) {
            return array('error' => '1','message' => "获取失败，文件大小限制'{$maxSize}'Byte！");
        }
        $originfile = $match[3].'.'.$ext;
        $filename = date('YmdHis').rand(1000, 9999).'.'.$ext;
        $dir = RTPATH.'uploads'.DS.'utility'.DS.date('Y').DS.date('m').DS;
        $path = WBPATH.'uploads/utility/'.date('Y/m/');
        $url = $path.$filename;
        $file = $dir.$filename;
        if (!file_exists($dir) && !mkdir($dir, 0640, true)) {
            return array('error' => '1','message' => '上传失败，系统权限不足，无法写入！');
        }
        if (file_exists($file) && !file_put_contents($file, $content)) {
            return array('error' => '1','message' => '获取失败，文件已存在或文件写入出错！');
        }

        return array(
            'error' => '0',
            'url' => $url,
            'file' => $file,
            'dir' => $dir,
            'path' => $path,
            'filename' => $filename,
            'originfile' => $originfile,
        );
    }

    /**
     * 上传单文件函数，本函数依赖RTPATH（文件根路径），WBPATH（URL根）等常量
     * 返回的错误代码（1:自定义错误, 2:没有上传文件, 3:NET & I/O错误, 4:不存在的上传动作）.
     */
    public function upload($field, $allow = array(), $maxSize = 5242880)
    {
        $origin = array('gif','jpg','png','bmp','txt','pdf','doc','docx','xls','xlsx');
        $allow = array_unique(array_merge($origin, $allow));
        if ($_FILES[$field]) {
            if ($_FILES[$field]['error'] == 4) {
                return array('error' => '2','message' => '没有上传文件，请选择文件再上传！');
            } elseif ($_FILES[$field]['error'] > 0) {
                return array('error' => '3','message' => '上传失败，错误未知，请联系管理员！');
            }
            if ($_FILES[$field]['size'] > $maxSize) {
                return array('error' => '1','message' => "上传失败，文件大小限制'{$maxSize}'Byte！");
            }
            if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
                return array('error' => '1','message' => '上传失败，非法的临时文件，谢绝攻击！');
            }
            $originfile = $_FILES[$field]['name'];
            $ext = strtolower(substr(strrchr($originfile, '.'), 1));
            if (!in_array($ext, $allow)) {
                return array('error' => '1','message' => '上传失败，未知文件格式！');
            }
            $filename = date('ymdHis').rand(1000, 9999).'.'.$ext;
            $dir = RTPATH.'uploads'.DS.'utility'.DS.date('Y').DS.date('m').DS;
            $path = WBPATH.'uploads/utility/'.date('Y/m/');
            $url = $path.$filename;
            $file = $dir.$filename;
            if (!file_exists($dir) && !mkdir($dir, 0640, true)) {
                return array('error' => '1','message' => '上传失败，系统权限不足，无法创建目录！');
            }
            if (file_exists($file) || !move_uploaded_file($_FILES[$field]['tmp_name'], $file)) {
                return array('error' => '1','message' => '上传失败，文件名已存在或文件写入出错！');
            };

            return array(
                'error' => '0',
                'url' => $url,
                'file' => $file,
                'dir' => $dir,
                'path' => $path,
                'filename' => $filename,
                'originfile' => $originfile,
            );
        }
        return array('error' => '4','message' => '不存在的上传动作！');
    }

    /**
     * 上传动作不确定上传数目的情况下，直接上传全部或有前缀的部分.
     * 本函数不支持数组形式的表单如photo[]等形式
     */
    public function uploadAll($prefix = '', $allow = array(), $maxSize = 5242880)
    {
        $ret = array();
        foreach ($_FILES as $key => $val) {
            if ($prefix == '' || ($prefix != '' && strpos($key, $prefix) === 0)) {
                $ret[$key] = $this->upload($key, $allow, $maxSize);
            }
        }

        return $ret;
    }

    /**
     * 生成缩略图，本函数支持透明通道，等比例缩放，对小图片不作处理.
     *
     * @param width 目标最大宽度
     * @param height 目标最大高度
     * @param cut 是剪裁图像（顶部中心）还是缩放图像
     */
    public function thumb($src, $dst, $width = 1024, $height = 768, $cut = false)
    {
        if (!file_exists($src)) {
            trigger_error('Invalid file path!!', E_USER_ERROR);
        }
        $ext = strtolower(substr(strrchr($src, '.'), 1));
        $origin = array('gif','jpeg','jpg','png','bmp');
        if (!in_array($ext, $origin)) {
            trigger_error('Unsupported image file!!', E_USER_ERROR);
        }
        list($w, $h) = getimagesize($src);
        if ($w < $width or $h < $height) {
            return copy($src, $dst);
        }
        switch ($ext) {
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
        if ($cut) {
            //剪切图片（$w:$center,$h:$top）
            $ratio = max($width / $w, $height / $h);
            $h = $height / $ratio;
            $x = ($w - $width / $ratio) / 2;
            $w = $width / $ratio;
        } else {
            //缩放图片
            $ratio = min($width / $w, $height / $h);
            $width = $w * $ratio;
            $height = $h * $ratio;
            $x = 0;
        }
        $canvas = imagecreatetruecolor($width, $height);
        if ($ext == 'gif' or $ext == 'png') {
            imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }
        imagecopyresampled($canvas, $img, 0, 0, $x, 0, $width, $height, $w, $h);
        switch ($ext) {
            case 'bmp':
                return imagewbmp($canvas, $dst);
            case 'gif':
                    return imagegif($canvas, $dst);
            case 'jpeg':
            case 'jpg':
                return imagejpeg($canvas, $dst);
            case 'png':
                return imagepng($canvas, $dst);
        }

        return false;
    }

    /**
     * 返回包含指定键值集合的部分数组，$reverse反选.
     */
    public function subSet($array, $keySet, $reverse = false)
    {
        $rtn = array();
        foreach ($keySet as $key) {
            $rtn[$key] = $array[$key];
        }
        if ($reverse) {
            return array_diff_key($array, $rtn);
        }

        return $rtn;
    }

    /**
     * 将从数据库中返回的多行数据(二维数组)按字段（列）求和，返回一维数组.
     */
    public function multiSum($data)
    {
        for ($i = 1;$i < count($data);++$i) {
            foreach ($data[$i] as $key => $val) {
                $data[0][$key] += $val;
            }
        }

        return $data[0];
    }

    /**
     * 将多次数据查询的结果集（已合并）去掉重复记录,使用主键ID进行判断.
     */
    public function multiUnique($data, $primaryKey = 'id')
    {
        $keySet = array();
        foreach ($data as $index => $record) {
            if (in_array($record[$primaryKey], $keySet)) {
                unset($data[$index]);
                continue;
            }
            array_push($keySet, $record[$primaryKey]);
        }

        return $data;
    }

    /**
     * 使用array_multisort对二维数组排序，适用于多次数据查询合并的结果集
     * 在使用时应确保记录无重复，建议使用$this->multiUnique过滤.
     */
    public function multiSort($data, $key, $order = SORT_ASC, $type = SORT_NUMERIC)
    {
        $keySet = array();
        foreach ($data as $record) {
            $keySet[] = $record[$key];
        }
        array_multisort($keySet, $order, $type, $data);

        return $data;
    }

    /**
     * 数据的分组统计转换,适用于将数据记录的某个字段（表达式）同主键关联成键值对
     * 目的在于将‘主键->字段（表达式）’的多行记录转换成一维数组的键值对形式
     * eg. data: select count(xx) as num, xx from... group by xx;
     * return: array(xx1=>count(xx1),xx2=>count(xx2),...).
     */
    public function groupConvert($data, $key, $express)
    {
        $tmp = array();
        for ($i = 0;$i < count($data);++$i) {
            $index = $data[$i][$key];
            $tmp[$index] = $data[$i][$express];
        }

        return $tmp;
    }

    /**
     * 将二维数组的的一列剥离出来，并进行矩阵转置.
     * 对于简单应用，本函数可用<mysql>group_concat替代
     */
    public function columnConvert($array, $field)
    {
        $rst = array();
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $rst[$key] = $item[$field];
            }
        }

        return $rst;
    }

    /**
     * 矩阵转置操作.
     */
    public function reverseMatrix($matrix)
    {
        $ret = array();
        for ($i = 0;$i < count($matrix);++$i) {
            for ($j = 0;$j < count($matrix[$i]);++$j) {
                $ret[$j][$i] = $matrix[$i][$j];
            }
        }

        return $ret;
    }

    /**
     * 解析一个不规范的日期,支持yyyymmdd,yyyy mm dd,yyyy-mm-dd,yyyy/mm/dd等
     * 年月日的索引值为1..3，0为期望的日期格式.
     */
    public function parseDate($str, $separator = '-')
    {
        $date = array();
        preg_match('/(\d{4})[\s\-\/]?(\d{2})?[\s\-\/]?(\d{2})?/', $str, $date);
        $section = array_filter(array_slice($date, 1));
        $date[0] = implode($separator, $section);

        return $date;
    }

    /**
     * 解析一个时间字符串,支持hhiiss,hh:ii:ss,hh ii ss
     * 字符串中的秒数可以被省略
     * 时分秒的索引值为1..3，0为期望的时间格式.
     */
    public function parseTime($str, $separator = ':')
    {
        $time = array();
        preg_match('/(\d{2})[\:\s](\d{2})[\:\s](\d{2})?/', $str, $time);
        $section = array_filter(array_slice($time, 1));
        $time[0] = implode($separator, $section);

        return $time;
    }

    /**
     * 将键值对形式的数据转换成地址形式：key=val&key2=val2&...
     */
    public function dataToUrl($data, $data2 = array())
    {
        $tmp = array_unique(array_merge($data, $data2));

        return http_build_query($tmp);
    }

    /**
     * 将一个url的quergString部分解析为键值对数组.
     */
    public function parseQuery($url)
    {
        $tmp = array();
        $info = parse_url($url);
        parse_str($info['query'], $tmp);

        return $tmp;
    }

    /**
     * form 表单单行本输入框.
     */
    public function formTextInput($name, $cat, $value = null, $attr = null)
    {
        return "<input type=\"{$cat}\" name=\"{$name}\" value=\"{$value}\" {$attr} />";
    }

    /**
     * form 表单多行文本输入区.
     */
    public function formTextArea($name, $value = null, $attr = null)
    {
        return "<textarea  name=\"{$name}\" {$attr}>{$value}</textarea>";
    }

    /**
     * form 表单下拉列表.
     */
    public function formSelect($name, $data, $sid = 0, $selectAttr = null)
    {
        $formStr = "<select name=\"{$name}\" {$selectAttr}>\n";
        foreach ($data as $key => $val) {
            $select = ($key == $sid) ? 'selected' : null;
            $formStr .= "<option {$select} value=\"{$key}\">{$val}</option>\n";
        }

        return $formStr.'</select>';
    }

    /**
     * form 表单单选按钮组.
     */
    public function formRadio($name, $data, $sid = 0, $commonAttr = null)
    {
        $formStr = null;
        foreach ($data as $key => $label) {
            $checked = ($key == $sid) ? 'checked' : null;
            $formStr .= "<input name=\"{$name}\" id=\"{$name}_{$key}\" type=\"radio\" {$checked} value=\"{$key}\" {$commonAttr} /><label for=\"{$name}_{$key}\"> {$label} </label>&nbsp;&nbsp;";
        }

        return preg_replace('/^(.+?)(?:&nbsp;)*$/', '${1}', $formStr);
    }

    /**
     * form 表单复选框组.
     */
    public function formCheckBox($name, $data, $set = array(), $commonAttr = null)
    {
        $formStr = null;
        foreach ($data as $key => $label) {
            $checked = (in_array($key, $set)) ? 'checked' : null;
            $formStr .= "<input name=\"{$name}[]\" id=\"{$name}_{$key}\" type=\"checkbox\" {$checked} value=\"{$key}\" {$commonAttr} /><label for=\"{$name}_{$key}\"> {$label} </label>&nbsp;&nbsp;";
        }

        return preg_replace('/^(.+?)(?:&nbsp;)*$/', '${1}', $formStr);
    }

    /**
     * 像一颗树一样，使表单列表Select元素有层次感
     * array(array(id=>xx,pid=>xx,title=>xx),...).
     */
    public function selectTree($data, $root, $sid, $parent = array(), $end = false)
    {
        $set = array();
        $decorate = null;
        $retStr = null;
        $current = null;
        foreach ($data as $item) {
            if ($item['id'] == $root) {
                $current = $item;
            }
            if ($item['pid'] == $root) {
                $set[] = $item;
            }
        }
        if (is_array($current)) {
            for ($i = 0;$i < count($parent) - 1;++$i) {
                if ($parent[$i] > 0) {
                    $decorate .= '│&nbsp;';
                } else {
                    $decorate .= '&nbsp;&nbsp;';
                }
            }
            if (count($parent) > 0) {
                $decorate .= $end ? '└─' : '├─';
            }
            $selected = $current['id'] == $sid ? 'selected' : null;
            $retStr .= "<option {$selected} value=\"{$current['id']}\">{$decorate}{$current['title']}</option>\n";
        }
        $size = count($set);
        array_push($parent, $size);
        for ($i = 0;$i < $size;++$i) {
            $end = ($i == $size - 1) ? true : false;
            if (true == $end) {
                $parent[count($parent) - 1] = 0;
            }
            $retStr .= $this->{__FUNCTION__}($data, $set[$i]['id'], $sid, $parent, $end);
        }

        return $retStr;
    }

    /**
     * 像一颗树一样，使分类列表项有层次感，不支持分页
     * array(array(id=>xx,pid=>xx,title=>xx),...).
     */
    public function catTree($data, $root, $parent = array(), $end = false)
    {
        $set = array();
        $decorate = null;
        $ret = array();
        $current = null;
        foreach ($data as $item) {
            if ($item['id'] == $root) {
                $current = $item;
            }
            if ($item['pid'] == $root) {
                $set[] = $item;
            }
        }
        if (is_array($current)) {
            for ($i = 0;$i < count($parent) - 1;++$i) {
                if ($parent[$i] > 0) {
                    $decorate .= '│&nbsp;&nbsp;&nbsp;';
                } else {
                    $decorate .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            }
            if (count($parent) > 0) {
                $decorate .= $end ? '└─' : '├─';
            }
            $current['decorate'] = $decorate;
            $ret[] = array_merge($ret, $current);
        }
        $size = count($set);
        array_push($parent, $size);
        for ($i = 0;$i < $size;++$i) {
            $end = ($i == $size - 1) ? true : false;
            if (true == $end) {
                $parent[count($parent) - 1] = 0;
            }
            $tmp = $this->{__FUNCTION__}($data, $set[$i]['id'], $parent, $end);
            $ret = array_merge($ret, $tmp);
        }

        return $ret;
    }

    /**
     * 加密字符串，密钥32位加密，仅libmcrypt 2.2.x可用
     * 密钥可由$this->randStr()随机生成并存放至数据库.
     */
    public function encrypt($encrypt, $key)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $pwdcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv);

        return base64_encode($pwdcrypt);
    }

    /**
     * 解密字符串，密钥32位解密，仅libmcrypt 2.2.x可用.
     */
    public function decrypt($decrypt, $key)
    {
        $decoded = base64_decode($decrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB, $iv);
    }
}
