<?php
define('IN_MY_PHP', true);
define('DS', DIRECTORY_SEPARATOR);
define('RTPATH', dirname(dirname(__FILE__)) . DS);
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');
include "Guard.class.php";
include "MysqlDiver.class.php";
include "Session.class.php";
include "Utility.class.php";
?>