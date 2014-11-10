<?php
!defined(IN_MY_PHP) && die(0);
/**
 * 请尽量将slash参数开启，防止SQL注入
 * 在服务器上部署时不要忘记关闭调试模式
 * @author netmou <leiyanfo@sina.com>
 */
 class MysqlException extends Exception{

     protected $msg;
     protected $sql;

     public function __construct($msg,$sql=null){
         $this->msg=$msg;
         $this->sql=$sql;
         parent::__construct('the sql info:'.$sql."\n".'the error msg:'.$msg."\n");
     }
     public function __tostring(){
         if($this->sql){
             $tmp='the sql info:'.$sql."\n";
         }
         $tmp.='the error msg:'.$this->getMessage()."\n";
         return $tmp.'in'.$this->getFile().'on line'.$this->getLine();
     }
 }

class mysql {

    private $Host = 'localhost';
    private $dbName = 'ocean';
    private $UserName = 'root';
    private $Password = '123456';
    private $dbCharSet = 'utf8';
    private $debug = true;
    private $linkID = null;
    private $queryID = null;
    public $lastSql;

    public function __construct($connect = true) {
        if ($connect) {
            $this->connect($this->Host, $this->dbName, $this->UserName, $this->Password);
        }
    }

    /**
     * 连接数据库方法
     */
    public function connect($host, $dbName, $user, $pass) {
        if (!$this->linkID) {
            $this->linkID = mysql_connect($host, $user, $pass, true);
            if (!$this->linkID && $this->debug) {
                throw new MysqlException(mysql_error());
            }
        }
        if (!mysql_select_db($dbName, $this->linkID) && $this->debug) {
            throw new MysqlException(mysql_error($this->linkID));
        }
        mysql_query("SET NAMES '" . $this->dbCharSet . "'", $this->linkID);
        mysql_query("SET sql_mode='NO_ZERO_IN_DATE'", $this->linkID);
    }

    /**
     * 检查到服务器的连接是否正常。如果断开，则自动尝试连接
     */
    function reconnect() {
        if (mysql_ping($this->linkID) === false) {
            $this->linkID = null;
        }
    }

    /**
     * 执行SQL语句
     * @param string $sql
     * @return mixed
     */
    public function execute($sql) {
        $this->lastSql = $sql;
        $this->queryID = mysql_query($sql);
        if (false === $this->queryID && $this->debug) {
            throw new MysqlException(mysql_error($this->linkID), $this->lastSql);
        }
        return $this->queryID;
    }

    public function resultRowCount($sql = null) {
        if ($sql) {
            $this->execute($sql);
        }
        if (is_resource($this->queryID)) {
            return mysql_num_rows($this->queryID);
        }
        return 0;
    }

    /**
     * 取得单条记录
     * @param string $sql
     * @return integer
     */
    public function fetchRow($sql = null) {
        if ($sql) {
            $this->execute($sql);
        }
        if (is_resource($this->queryID)) {
            return mysql_fetch_array($this->queryID, MYSQL_ASSOC);
        }
        return null;
    }

    /**
     * 取得结果集中某个字段值
     * @param string $sql
     * @param mixed $field
     * @return mixed
     */
    public function fetchField($sql = null, $field = 0, $row = 0) {
        if ($sql) {
            $this->execute($sql);
        }
        if (is_resource($this->queryID) && $row < mysql_num_rows($this->queryID)) {
            return mysql_result($this->queryID, $row, $field);
        }
        return null;
    }

    /**
     * 将结果集以键值对的形式储存到数组中
     * @param string $sql
     * @return array
     */
    public function fetchData($sql = null) {
        if ($sql) {
            $this->execute($sql);
        }
        $tmp = array();
        if (is_resource($this->queryID)) {
            while ($info = mysql_fetch_assoc($this->queryID)) {
                $tmp[] = $info;
            }
        }
        return $tmp;
    }

    /**
     * 返回上次插入数据表记录的ID
     * @return integer
     */
    public function lastInsertId() {
        return mysql_insert_id($this->linkID);
    }

    /**
     * 过滤并向数据库插入数据
     * @param string $table 数据表名
     * @param array $data 键值对的集合,键值集必须是字段集的子集
     * @return boolean
     */
    public function postData($table, $data, $slash = false) {
        $data2 = $this->facade($table, $data);
        return $this->insert($table, $data2, $slash);
    }

    /**
     * 过滤并向数据库更新数据
     * @param string $table 数据表名
     * @param array $data 键值对的集合,键值集必须是字段集的子集
     * @param mixed $condition 更新条件，不能为空
     * @return boolean
     */
    public function updateData($table, $data, $condition, $slash = false) {
        $data2 = $this->facade($table, $data);
        if (is_array($condition)) {
            $condition = $this->facade($table, $condition);
        }
        return $this->update($table, $data2, $condition, $slash);
    }

    /**
     * 过滤数据，剔除数据中没有和数据表的字段相对应的键和值
     * @param string $table 数据表名
     * @param array $data 键值对的集合
     * @return array 能够插入数据库的键值对子集
     */
    private function facade($table, $data) {
        $columns = $this->fetchData("SHOW COLUMNS FROM `{$table}`");
        //$fields=array_column($columns,'Field');//only php5.5+
        $func = create_function('$arr', 'return $arr[\'Field\'];');
        $fields = array_map($func, $columns);
        $keys = array_keys($data);
        while ($key = array_pop($keys)) {
            if (!in_array($key, $fields)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * 将数据插入到数据表
     * @param string $table 数据表名
     * @param array $data 键名对应字段名，键值对应字段值
     * @param boolean slash 是否对数据进行转义
     * @return boolean
     */
    public function insert($table, $data, $slash = false) {
        $values = $fields = array();
        foreach ($data as $key => $val) {
            if ($val !== null && is_scalar($val)) {
                $value = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
            } else {
                $value = 'NULL';
            }
            $values[] = $value;
            $fields[] = '`' . $key . '`';
        }
        $table = '`' . trim($table) . '`';
        $sql = 'INSERT INTO ' . $table . '(' . implode(',', $fields) . ') VALUES(' . implode(',', $values) . ')';
        return $this->execute($sql);
    }

    /**
     * 更新数据表中的部分数据
     * @param string $table 数据表名
     * @param array $data 键名对应字段名，键值对应字段值
     * @param mixed $condition 更新条件，不能为空
     * @param boolean slash 是否对数据进行转义
     * @return boolean
     */
    public function update($table, $data, $condition, $slash = false) {
        if (empty($condition) && $this->debug) {
            throw new MysqlException("没有指定条件，更新整个表很危险");
        }
        $sql = 'UPDATE `' . trim($table) . '` SET ';
        foreach ($data as $key => $val) {
            if ($val !== null && is_scalar($val)) {
                $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
            } else {
                $val = 'NULL';
            }
            $sql .= '`' . $key . '`' . '=' . $val . ',';
        }
        $sql = substr($sql, 0, strlen($sql) - 1) . ' where 1=1 ';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if ($val !== null && is_scalar($val)) {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
                }
                $sql = $sql . ' and `' . $key . '`=' . $val;
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->execute($sql);
    }

    /**
     * 根据条件模糊查询数据，返回指定的结果
     * @param string $table 数据表名
     * @param array $fields 返回的字段
     * @param array $likes 模糊匹配的字段
     * @param string $keyword 检索的关键字
     * @param mixed $condition 附加限定条件
     * @param boolean slash 是否对数据进行转义
     * @return array
     */
    function likeAll($table, $fields, $likes, $keyword, $condition = null, $slash = false) {
        $keyword = $slash ? addslashes($keyword) : $keyword;
        $like = ' and concat_ws(\'-|-\',' . implode(',', $likes) . ') like ' . "'%{$keyword}%' ";
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if ($val !== null && is_scalar($val)) {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
                }
                $like = $like . ' and `' . $key . '`=' . $val;
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $like = $like . ' ' . $condition;
        }
        $this->fetch($table, $fields, $like, $slash);
    }

    /**
     * 根据条件查询数据，返回指定的结果
     * @param string $table 数据表名
     * @param mixed $condition 限定条件
     * @param boolean slash 是否对数据进行转义
     * @return array
     */
    function fetch($table, $fields, $condition, $slash = false) {
        $selected = null;
        if (is_array($fields)) {
            while ($field = array_pop($fields)) {
                $selected = $slash ? $selected . '`' . addslashes($field) . '`,' : $selected . '`' . $field . '`,';
            }
            $selected = substr($selected, 0, strlen($selected) - 1);
        } else {
            $selected = trim($fields);
        }
        $sql = 'select ' . $selected . ' from `' . trim($table) . '` where 1=1 ';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if ($val !== null && is_scalar($val)) {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
                }
                $sql = $sql . ' and `' . $key . '`=' . $val;
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->fetchData($sql);
    }

    /**
     * 删除数据表中的部分数据
     * @param string $table 数据表名
     * @param mixed $condition 限定条件，不能为空
     * @param boolean slash 是否对数据进行转义
     * @return boolean
     */
    public function delete($table, $condition = null, $slash = false) {
        if (empty($condition) && $this->debug) {
            throw new MysqlException('没有指定条件，清空表很危险');
        }
        $sql = 'delete from `' . $table . '` where 1=1';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if ($val !== null && is_scalar($val)) {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
                }
                $sql = $sql . ' and `' . $key . '`=' . $val;
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->execute($sql);
    }

    /**
     * 返回上次插入或更新或删除影响的条数
     * @return integer
     */
    public function getAffectedRows() {
        return mysql_affected_rows($this->linkID);
    }

    public function __destruct() {
        if (is_resource($this->linkID)) {
            mysql_close($this->linkID);
        }
    }

}
$mysql = new mysql();
?>
