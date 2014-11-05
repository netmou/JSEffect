
<?php
/**
 * Created by tudou.
 * Date: 13-2-4
 * Time: 下午9:57
 */
/**

*/
class PDODriver extends PDO{
    public $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ",
    );
    public function __construct($host,$user="root",$pass="",$dbname="",$persistent=false,$charset="utf8"){
    }
}
