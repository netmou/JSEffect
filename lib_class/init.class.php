<?php
/**
* 微框架入口，我们将系统抛出的部分异常转换为用户异常错误
* 这样可在生产环境中使用error_reporting控制调试信息的输出
* 注意：前、后台页面结构一致，但两者的系统路径常量的值不同,
* 会话的前缀（Session）可能不一样
*/


define('IN_MY_PHP', true); // 拒绝非法文件引用
define('APPID', 'forum'); // 设置Session前缀来区别不同的应用
define('DS', DIRECTORY_SEPARATOR);
define('WBHOST','http://'.$_SERVER['HTTP_HOST']); // http协议和域名及端口
define('DOCPATH',trim(preg_replace('/[\\\\\/]/',DS,$_SERVER["DOCUMENT_ROOT"]), DS) . DS); // 服务器根目录（文件协议）
define('RTPATH', dirname(dirname(__FILE__)) . DS); // 网站（项目）根目录（文件协议）
define('WBPATH',str_replace('\\','/',str_replace(DOCPATH,DS,RTPATH))); // URL的根路径（url,eg:/xx/.../xx/,和服务器根目录相对应）
define('APPURL',WBHOST.WBPATH); // 带协议和域名及端口的url，用于兼容linux的路径
define('CRPATH', substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'], '/')) . '/'); // 入口脚本的绝对路径（url）
define('LBPATH',dirname(__FILE__).DS); // 库文件所在目录（文件协议）
date_default_timezone_set('PRC');
error_reporting(E_ALL ^ E_NOTICE);
// error_reporting(0);
// 该类使用单例模式，为便于使用，实现数组访问接口
class Master implements ArrayAccess{
    private static $single=null;
    private $container = array();
    private function __construct($classes=null){
        $this->load($classes);
    }
    public static function create($classes=null){
        if(!isset(self::$single))
            return new self($classes);
        self::$single->load($classes);
        return self::$single;
    }
    public function load($classes,$init=true,$path=null){
        if($classes && is_array($classes)){
            foreach($classes as $key=> $class){
                if(true==$init){
                    $this->init($class,$key,$path);
                }else{
                    $this->contain($class,$path);
                }
            }
        }
    }
    public function init($class,$key=null,$path=null){
        if(!isset($this->container[$class])){
            $this->contain($class,$path);
            if($key==null || is_numeric($key)){
                $key=$class;
            }
            try{ // some classes maybe throw exceptions eg.pdodriver
                $this->container[$key]=new $class();
            }catch(Exception $e){
                trigger_error($e->getMessage(),E_USER_ERROR);
            }
            
        }
    }
    public function contain($class,$path=null){
        if($path!=null){
            $file=$path.$class.'.class.php';
        }else{
            $file=LBPATH.$class.'.class.php';
        }
        if(file_exists($file) && is_readable($file)){
            return include_once($file);
        }
        trigger_error("Class:'{$class}' is not found,With the path:".$file,E_USER_ERROR);
    }
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            trigger_error("Offset:'{$offset}' must be a valid label", E_USER_ERROR);
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        $this->init($offset);
        return $this->container[$offset];
    }
}