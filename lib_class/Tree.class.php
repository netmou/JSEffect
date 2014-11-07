<?php

/**
 * 通用的树型类，可以生成任何树型结构
 */
class Tree {

	/**
	 * @ $arr     array    生成树型结构所需要的2维数组
	 * @ $icon    array    生成树型结构所需修饰符号，可以换成图片
	 * @ $ret     min      初始化变量
	 */
	private $arr = array();
	//初始化数组
	private $icon = array('&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─');
	//修饰符号
	private $ret = '';

	/**
	 * 构造函数，初始化类
	 * @param array 2维数组
	 */
	function __construct($arr = array()) {//初始化array
		$this -> arr = $arr;
		$this -> ret = '';
	}

	/**
	 * 得到父级数组
	 * @param int
	 * @return array
	 */
	function getParent($myid) {
		$newarr = array();
		//初始化数组
		if (!isset($this -> arr[$myid]))
			return false;
		//如果arry[$myid]不为真则返回假
		$pid = $this -> arr[$myid]['pid'];
		$pid = $this -> arr[$pid]['pid'];
		if (is_array($this -> arr)) {//如果arr为数组
			foreach ($this->arr as $id => $a) {//遍历数组
				if ($a['pid'] == $pid)
					$newarr[$id] = $a;
				//如果父ID等于 ID 将$a返回给newarr
			}
		}
		return $newarr;
		//返回newarr
	}

	/**
	 * 得到子级数组
	 * @param int
	 * @return array
	 */
	function getChild($myid) {//得到子集数组
		$a = $newarr = array();
		//初始化数组
		if (is_array($this -> arr)) {
			foreach ($this->arr as $id => $a) {//循环遍历arr
				if ($a['pid'] == $myid)
					$newarr[$id] = $a;
				//如果父ID等于myid返回newsarr
			}
		}
		return $newarr ? $newarr : false;
		//如果newarr为真则返回newarr
	}

	/**
	 * 得到当前位置数组
	 * @param int
	 * @return array
	 */
	function getPos($myid, &$newarr) {
		$a = array();
		//初始化数组
		if (!isset($this -> arr[$myid]))
			return false;
		//如果arr为假返回false
		$newarr[] = $this -> arr[$myid];
		$pid = $this -> arr[$myid]['pid'];
		if (isset($this -> arr[$pid])) {
			$this -> getPos($pid, $newarr);
		}
		if (is_array($newarr)) {
			krsort($newarr);
			foreach ($newarr as $v) {
				$a[$v['id']] = $v;
			}
		}
		return $a;
	}

	/**
	 * 得到树型结构
	 * @param $myid int ID，表示获得这个ID下的所有子级
	 * @param $str string 生成树型结构的基本代码，例如："<option value=\$id \$select>\$spacer\$name</option>"
	 * @param $sid int 被选中的ID，比如在做树型下拉框的时候需要用到
	 * @return string
	 */
	function getTree($myid, $str, $sid = 0, $adds = '') {
		$number = 1;
		$child = $this -> getChild($myid);
		if (is_array($child)) {
			$total = count($child);
			foreach ($child as $id => $a) {
				$j = $k = '';
				if ($number == $total) {
					$j .= $this -> icon[2];
					$k = $adds ? $this -> icon[0] : '';
				} else {
					$j .= $this -> icon[1];
					$k = $adds ? $this -> icon[0] : '';
				}
				$spacer = $adds ? $adds . $j : '';
				$select = $a['id'] == $sid ? 'selected="selected"' : '';
				extract($a);
				eval("\$nstr = \"$str\";");
				$this -> ret .= $nstr;
				$this -> getTree($id, $str, $sid, $adds . $k . ' ');
				$number++;
			}
		}
		return $this -> ret;
	}
	//data数据形式 $id=>array($id,$pid,$name);
	function tree2($data, $root, $sid = 0, $deep = 0, $end = false, $pend = false) {
		$info = $data[$root];
		for ($i = 0; $i < $deep - 1; $i++) {
			if (!$pend)
				$decorate = '&nbsp;&nbsp;│';
			else
				$decorate = '&nbsp;&nbsp;&nbsp;';
		}
		if ($deep > 0) {
			$decorate .= $end ? '&nbsp;&nbsp;└─' : '&nbsp;&nbsp;├─';
		}
		$selected = $info['id'] == $sid ? 'selected="selected"' : null;
		$retStr = "<option {$selected} value=\"{$info['id']}\">{$decorate}{$info['name']}</option>\n";
		$children = array();
		foreach ($data as $id => $child) {
			if ($child['pid'] == $root) {
				$children[] = $child;
			}
		}
		$size = count($children);
		for ($i = 0; $i < $size; $i++) {
			$send = ($i == $size - 1) ? true : false;
			$retStr .= tree2($data, $children[$i]['id'], $sid, $deep + 1, $send, $end);
		}
		return $retStr;
	}

}
?>
