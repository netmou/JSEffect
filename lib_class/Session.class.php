<?php
class Session{
    const SESSION_NAME="mySession";
    const SESSION_EXPIRE=30;
    public $status=false;
    public $cache=false;
    public function start(){
        if(!$this->status){
            if($this->cache){
                session_cache_limiter('private');
                session_cache_expire(self::SESSION_EXPIRE);
            }
            session_name(self::SESSION_NAME);
            session_start();
            $this->status=true;
        }
    }
    public function commit(){
        if($this->status){
            session_write_close();
            $this->status=false;
        }
    }
    public function check($key,$url,$val=null){
        $this->start();
        if($val && $this->get($key)==$val){
            return null;
        }else if($this->has($key)){
            return null;
        }
        $this->commit();
        header("location: {$url}");
        exit(0);
    }
    public function has($key){
        $this->start();
        return isset($_SESSION[$key]);
    }
    public function set($key,$val){
        $this->start();
        $_SESSION[$key]=$val;
    }
    public function delete($key){
        $this->start();
        unset($_SESSION[$key]);
    }
    public function get($key){
        $this->start();
        return $_SESSION[$key];
    }
    public function getId(){
        $this->start();
        return session_id();
    }
    public function abort(){
        if($this->status){
            session_abort();
            $this->status=false;
        }
    }
    public function login(){
        $this->start();
        session_regenerate_id(true);
    }
    public function logout(){
        $this->start();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->status=false;
    }

}
$store=new Session();
?>