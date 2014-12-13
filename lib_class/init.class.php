<?php
define('IN_MY_PHP', true);
define('DS', DIRECTORY_SEPARATOR);
define('RTPATH', dirname(dirname(__FILE__)) . DS);
define('LIBPATH', dirname(__FILE__) . DS);
define('WBPATH', substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'], '/')) . '/');
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');
//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);
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
            $this->container[$key]=new $class();
        }
    }
    public function contain($class,$path=null){
        if($path!=null){
            $file=LIBPATH.$path.DS.$class.'.class.php';
        }else{
            $file=LIBPATH.$class.'.class.php';
        }
        $file=LIBPATH.$class.'.class.php';
        if(file_exists($file) && is_readable($file)){
            return include_once($file);
        }
        trigger_error("Class:'{$class}' is not found!!",E_USER_ERROR);
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
?>