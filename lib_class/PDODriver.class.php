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
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET sql_mode='NO_ZERO_IN_DATE'",
		PDO::ATTR_PERSISTENT=>false,
	);
	public function __construct($host, $user, $pass, $dbname) {
		$dsn = "mysql:host={$host};dbname={$dbname}";
		parent::__construct($dsn,$user,$pass,$this->options);
	}

}

