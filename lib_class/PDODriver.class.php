<?php
defined('IN_MY_PHP')||die(0);
/**
 * A PDODriver for MySQL
 * Convert PDOException to E_USER_ERROR
 * Created by netmou(leiyanfo@sina.com).
 * Date: 2015/01/07
 */

class PDODriver extends PDO {

	public $dbms='mysql';
	public $host='127.0.0.1';
	public $dbName='hczz';
	public $port=3306;

	private $user="root";
	private $pass="123456";
	//假装执行(exec)
	private $pretend=false;
	//表结构缓存
	private $tableName=null;
	private $columnNames=null;

	public $options = array(
		parent::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;",
		parent::ATTR_PERSISTENT=>false,
		parent::ATTR_ERRMODE => parent::ERRMODE_EXCEPTION,
	);

	public function __construct() {
		$dsn="{$this->dbms}:host={$this->host};port={$this->port};dbname={$this->dbName}";
		parent::__construct($dsn,$this->user,$this->pass,$this->options);
		$this->exec("SET sql_mode='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,NO_ZERO_IN_DATE';");
	}

	/** 重写改变异常处理方式 */
	public function prepare($prepare,$option=null){
		try{
			return  parent::prepare($prepare,$option);
		}catch(PDOException $e){
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
	}

	/** 重写改变异常处理方式 */
	public function query($query){
		try{
			return  parent::query($query);
		}catch(PDOException $e){
			trigger_error($e->getMessage().'With the sql:'.$query,E_USER_ERROR);
		}
	}

	/** 重写改变异常处理方式 */
	public function exec($query){
		if($this->pretend){
			return $query;
		}
		try{
			return  parent::exec($query);
		}catch(PDOException $e){
			trigger_error($e->getMessage().'With the sql:'.$query,E_USER_ERROR);
		}
	}


	/** 设置PDO驱动的属性 */
	public function setManyAttr($params){
        foreach($params as $key=>$val){
			parent::setAttribute($key,$val);
        }
    }

    /**
     * 取得结果集中某个字段值
     * @param string $sql
     * @param int $field
     * @return mixed
     */
    public function fetchField($sql, $fetchIndex = 0) {
    	$stmt=$this->query($sql);
		$stmt->setFetchMode(parent::FETCH_NUM);
		return $stmt->fetchColumn($fetchIndex);
    }

    /**
     * 取得单条记录
     * @param string $sql
     * @return integer
     */
    public function fetchRow($sql,$fetchMode=parent::FETCH_ASSOC) {
        $stmt=$this->query($sql);
		return $stmt->fetch($fetchMode);
    }

    /**
     * 将结果集以键值对的形式储存到数组中
     * @param string $sql
     * @return array
     */
    public function fetchData($sql,$fetchMode=parent::FETCH_ASSOC) {
		$stmt=$this->query($sql);
		return $stmt->fetchAll($fetchMode);
    }

	/**
	 * 获取随机记录，本函数能避免因id不连续导致有数据却取不到(足)的情况
	 * 本函数采用的排序方式是通过给每一个记录生成一个随机数，然后进行排序以达到随机获取的目的
	 * 本函数的算法思想是通过缩小查范围，多次查询，从而降低排序消耗，所以适用于大数据(B)
	 * @param string $table	查询的数据表
	 * @param int $num	获取随机查询结果集的条数
	 * @param in $step;	小范围随机查询的次数
	 * @param string $fields;	返回的字段
	 * @param string $condition;	查询的条件
	 * @return array dataSet
	 */
	public function fetchRand($table,$num,$step=50,$fields='*',$condition='1'){
        $info=$this->fetchRow("SELECT COUNT(`id`) as `total`, MIN(`id`) as `min`, MAX(`id`) as `max` FROM `{$table}` WHERE {$condition} ");
        if($num >= $info['total']){
			$ret=$this->fetchData("SELECT {$fields} FROM `{$table}` WHERE {$condition}");
			shuffle($ret);
            return $ret;
        }
        $offset=max(floor($info['total']/$step),1);// 最小值 1
        $idSet=range($info['min'],$info['max'],$offset);
        $limit=ceil($num/$info['total']*$offset); // 上取整，保证满足取得的总数
        $ret=array();
        $counter=0;
		shuffle($idSet); // 打乱每次的起始值
        while(count($idSet)>0 && $counter<$num){
            $start=array_shift($idSet);
            $end=$start+$offset;
            $stmt=$this->query("SELECT {$fields} FROM `{$table}` WHERE {$condition} AND `id`>={$start} AND `id`<{$end} ORDER BY RAND() LIMIT {$limit}");
            while($row = $stmt->fetch()){
                if(++$counter>$num){
                    break;
                }
                $ret[]=$row;
            }
        }
        return $ret;
    }

    /**
     * 过滤并向数据库插入数据
     * @param string $table 数据表名
     * @param array $data 键值对的集合,通常来自表单
     * @return boolean
     */
    public function postData($table, $data, $slash = false) {
        $insert = $this->facade($table, $data);
        return $this->insert($table, $insert, $slash);
    }

    /**
     * 过滤并向数据库更新数据
     * @param string $table 数据表名
     * @param array $data 键值对的集合,通常来自表单
     * @param mixed $condition 更新条件，不能为空
     * @return boolean
     */
    public function updateData($table, $data, $condition, $slash = false) {
        $update = $this->facade($table, $data);
        if (is_array($condition)) {
            $condition = $this->facade($table, $condition);
        }
        return $this->update($table, $update, $condition, $slash);
    }

    /**
     * 过滤数据，剔除数据表中不包含的键值对,并缓存表结构
     * @param string $table 数据表名
     * @param array $data 键值对的集合
     * @return array 能够插入数据库的键值对子集
     */
    private function facade($table, $data) {
		if($table==$this->tableName && $this->columnNames!=null){
			$fields=$this->columnNames;
		}else{
			$columns = $this->fetchData("SHOW COLUMNS FROM `{$table}`");
	        //$fields=array_column($columns,'Field');//supported by php5.5+
	        $func = create_function('$data', 'return $data[\'Field\'];');
	        $this->columnNames = $fields = array_map($func, $columns);
			$this->tableName=$table;
		}
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
        $values = array();
		$fields = array();
        foreach ($data as $key => $val) {
            if (is_scalar($val) && $val !== '') {
                $values[] = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
            } else if($val === '' || $val === null) {
                $values[] = 'NULL';
            }else{
				trigger_error("数据集不合法，无法拼合成sql！",E_USER_ERROR);
				continue; // 屏蔽错误异常时将该跳过该字段
			}
            $fields[] = '`' . $key . '`';
        }
		$table = $slash ? addslashes($table) : $table;
        $sql = 'INSERT INTO ' . '`' . trim($table) . '`' . '(' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        return $this->exec($sql.';');
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
        if (empty($condition)) {
            trigger_error("没有指定条件，更新整个表很危险",E_USER_ERROR);
        }
		$table = $slash ? addslashes($table) : $table;
        $sql = 'UPDATE `' . trim($table) . '` SET ';
        foreach ($data as $key => $val) {
            if (is_scalar($val) && $val !== '') {
                $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
            } else {
                $val = 'NULL';
            }
            $sql .= '`' . $key . '`' . '=' . $val . ',';
        }
        $sql = substr($sql, 0, strlen($sql) - 1) . ' WHERE 1 ';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if (is_scalar($val) && $val !== null) {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
					$sql = $sql . ' AND `' . $key . '`=' . $val;
                }else{
					trigger_error("指定的条件不合法，无法拼合成sql！",E_USER_ERROR);
				}
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->exec($sql.';');
    }
	//将阻止sql语句执行
	public function setPretend($pretend=false){
		$this->pretend=$pretend;
	}


	/**
	* 通过正则表达式替换sql中的返回字段，
	* 来提高获取SELECT查询记录总数的速度
	*/
	public function resultCount($query) {
        $regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
		// preg_match 匹配一次结束，出错返回 false
        if (preg_match($regex, $query, $output) === 1) {
            $stmt = $this->query("SELECT COUNT(*) FROM {$output[1]}", PDO::FETCH_NUM);
            return (int)$stmt->fetchColumn();
        }
        return 0;
    }

    /**
     * 根据条件模糊查询数据，返回指定的结果
     * 因为依赖 MYSQL-CONCAT_WS，故不适用于字段比较多的表
     * @param string $table 数据表名
     * @param array $fields 返回的字段
     * @param array $likes 模糊匹配的字段
     * @param string $keyword 检索的关键字
     * @param mixed $condition 附加限定条件
     * @param boolean slash 是否对数据进行转义
     * @return array
     */
    public function likeAll($table, $fields, $likes, $keyword, $condition = null, $slash = false) {
        $keyword = $slash ? addslashes($keyword) : $keyword;
        $like = ' AND CONCAT_WS(\'-|-\',' . implode(',', $likes) . ') LIKE ' . "'%{$keyword}%' ";
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if (is_scalar($val) && $val !== '') {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
					$like = $like . ' AND `' . $key . '`=' . $val;
                }
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
    public function fetch($table, $fields='*', $condition = null , $slash = false) {
        $selected = null;
        if (is_array($fields)) {
            while ($field = array_pop($fields)) {
                $selected = $slash ? $selected . '`' . addslashes($field) . '`,' : $selected . '`' . $field . '`,';
            }
            $selected = substr($selected, 0, strlen($selected) - 1);
        } else {
            $selected = $slash ? addslashes($fields) : $fields;
        }
		$table = $slash ? addslashes($table) : $table;
        $sql = 'SELECT ' . $selected . ' FROM `' . trim($table) . '` WHERE 1 ';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if (is_scalar($val) && $val !== '') {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
					$sql = $sql . ' AND `' . $key . '`=' . $val;
                }
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->fetchData($sql);
    }

    /**
     * 删除数据表中的部分数据
	 * 删除大量数据时应执行optimize table tableName，来优化表结构
     * @param string $table 数据表名
     * @param mixed $condition 限定条件，不能为空
     * @param boolean slash 是否对数据进行转义
     * @return boolean
     */
    public function delete($table, $condition = null, $slash = false) {
        if (empty($condition)) {
            trigger_error('没有指定条件，清空表很危险',E_USER_ERROR);
        }
		$table = $slash ? addslashes($table) : $table;
        $sql = 'DELETE FROM `' . $table . '` WHERE 1';
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                if (is_scalar($val) && $val !== '') {
                    $val = $slash ? '"' . addslashes($val) . '"' : '"' . $val . '"';
					$sql = $sql . ' AND `' . $key . '`=' . $val;
                }
            }
        } else if ($condition) {
            $condition = $slash ? addslashes($condition) : $condition;
            $sql = $sql . ' ' . $condition;
        }
        return $this->exec($sql.';');
    }
}

# 初始化PDO并将可能出现的异常转换为错误输出

// try{
// 	$pdo= new PDODriver();
// }catch(PDOException $e){
// 	trigger_error($e->getMessage(),E_USER_ERROR);
// }
