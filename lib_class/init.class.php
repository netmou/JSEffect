<?php
define('IN_MY_PHP', true);
define('DS', DIRECTORY_SEPARATOR);
define('RTPATH', dirname(dirname(__FILE__)) . DS);
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');
//error_reporting(E_ALL & ~E_NOTICE);
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
    public function load($classes){
        if($classes&&is_array($classes)){
            foreach($classes as $key=> $class){
                $this->init($class,$key);
            }
        }
    }
    public function init($class,$key=null){
        if(!isset($this->container[$class])){
            $file=$class.'.class.php';
            include_once($file);
            if(!$key || is_numeric($key)){
                $key=$class;
            }
            $this->container[$key]=new $class();
        }
    }
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            throw new Exception("Offset must be a valid label", 0);
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