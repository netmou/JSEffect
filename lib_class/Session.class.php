<?php
defined('IN_MY_PHP')||die(0);
/**
* Session管理类，修补了Sessoin固定攻击
* @author netmou <leiyanfo@sina.com>
*/
class Session {
	const SESSION_NAME = "PHPSESSID";
	const SESSION_EXPIRE = 30;
	public $status = false;
	public $cache = false;
	public $prefix = APPID;
	public function start() {
		if (!$this -> status) {
			if ($this -> cache) {
				session_cache_limiter('private');
				session_cache_expire(self::SESSION_EXPIRE);
			}
			if(headers_sent()){
				trigger_error("Header content has been sent!",E_USER_ERROR);
			}
			session_name(self::SESSION_NAME);
			session_start();
			$this -> status = true;
		}
	}

	public function commit() {
		if ($this -> status) {
			session_write_close();
			$this -> status = false;
		}
	}

	public function check($key, $url='login.php', $commit = false) {
		if(false==$this -> has($key)) {
			$this -> commit();
			header("location: {$url}");
			exit(0);
		}
		if($commit){
			$this -> commit();
		}
	}

	public function has($key) {
		$this -> start();
		return isset($_SESSION[$this->prefix.$key]);
	}

	public function set($key, $val) {
		$this -> start();
		$_SESSION[$this->prefix.$key] = $val;
	}

	public function delete($key) {
		$this -> start();
		unset($_SESSION[$this->prefix.$key]);
	}

	public function get($key,$def=null) {
		$this -> start();
		$val=$_SESSION[$this->prefix.$key];
		if($val===null){
			return $def;
		}
		return $val;
	}

	public function getId() {
		$this -> start();
		return session_id();
	}

	//supported php5.6
	public function abort() {
		if ($this -> status) {
			session_abort();
			$this -> status = false;
		}
	}
	public function token(){
		$this -> start();
		$token_set=$_SESSION['form_token'];
		$token_str=md5(uniqid('ftk',true));
		if(is_array($token_set)){
			array_push($token_set,$token_str);
			$_SESSION['form_token']=$token_set;
		}else{
			$_SESSION['form_token']=array($token_str);
		}
		return $token_str;
	}
	public function verify($token_str){
		$this -> start();
		$token_set=$_SESSION['form_token'];
		if(is_array($token_set)){
			foreach($token_set as $key=> $token){
				if($token === $token_str){
					unset($token_set[$key]);
					$_SESSION['form_token']=$token_set;
					return true;
				}
			}
		}
		return false;
	}
	
	// regenerate id
	public function login() {
		$this -> start();
		session_regenerate_id(true);
	}

	//注销并删除客户端cookie
	public function logout() {
		$this -> start();
		$_SESSION = array();
		$this->commit();
	}
}