<?php
/**
 * Created by tudou.
 * Date: 13-2-4
 * Time: 下午9:57
 */
/**

 */
class PDODriver extends PDO {
	public $options = array(
		parent::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET sql_mode='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,NO_ZERO_IN_DATE'",
		parent::ATTR_PERSISTENT=>false,
	);
	public function __construct($host, $user, $pass, $dbname) {
		$dsn = "mysql:host={$host};dbname={$dbname}";
		parent::__construct($dsn,$user,$pass,$this->options);
	}
	public function query($query){
		$args = func_get_args();
		array_shift($args);
		$reponse = parent::prepare($query);
		return $reponse->execute($args);
	}

}
class PDOStatement ex

