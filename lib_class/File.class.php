<?php
IN_MY_PHP || die(0);
/**
 * 一个简单的文件处理类
 * @author netmou <leiyanfo@sina.com>
 */
class File {

    public function mkdir($path,$p=0640) {
        if (!file_exists($path)) {
            mkdir($path, $p,true);
        }
    }

    public function fetchdir($path) {
        $path = rtrim($path, '/') . '/';
        if (!is_dir($path)){
            return null;
        }
        $retval = array();
        $handler = opendir($path);
        while ($dir = readdir($handler)) {
            if($dir != '.' && $dir != '..') {
                $file = $path . $dir;
                if (is_dir($file)){
                    $retval['folder'][] = $dir;
                }else if(is_file($file)) {
                    $retval['file'][] = $dir;
                }
            }
        }
        closedir($handler);
        return $retval;
    }

    public function unlink($path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function rmdir($path) {
        $path = rtrim($path, '/') . '/';
        if (!is_dir($path)){
            return null;
        }
        $handler = opendir($path);
        while ($dir = readdir($handler)) {
            if ($dir != '.' && $dir != '..') {
                $file = $path . $dir;
                if (is_dir($file)){
                    $this->rmdir($file);
                }elseif (is_file($file)) {
                    unlink($file);
                }
            }
        }
        closedir($handler);
        rmdir($path);
    }

    /**
     * 复制目录
     *
     * @param string $src  要复制的目录地址
     * @param string $dst  目标目录地址
     * @param int $child  是否复制子目录
     * @return bool
     */
    public static function XCopy($src, $dst, $r = true) {
        if (!is_dir($src)) {
            return false;
        }
        if (!is_dir($dst)) {
            $this->mkdir($dst);
        }
        $handle = dir($src);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                $file=$src . "/" . $entry;
                if (is_dir($file) && $r) {
                    $this->xCopy($file, $dst . "/" . $entry, $r);
                }else {
                    copy($file, $dst . "/" . $entry);
                }
            }
        }
    }

    public static function touch($file, $cont = '') {
        $path = dirname($file);
        if (!is_dir($path)) {
            $this->mkdir($path);
        }
        return file_put_contents($file, $cont);
    }
}

?>