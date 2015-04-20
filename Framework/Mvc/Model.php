<?php

/**
 * Mysql数据库模型基类 
 */
namespace Mvc;
use \Driver\Mysql;

class Model
{
	/**
	 * 释放所有
	 * @var string
	 */
	const FETCH_ALL = 'fetchAll';

	/**
	 * 释放一行
	 * @var string
	 */
	const FETCH_ROW = 'fetch';

	/**
	 * 释放一列
	 * @var string
	 */
	const FETCH_COLUMN = 'fetchColumn';

	/**
	 * 返回影响的行数
	 * @var string
	 */
	const FETCH_AFFECTROW = 'affectRow';

	/**
	 * 表名
	 * @var string
	 */
	protected $table = NULL;

	/**
	 * 数据库对象
	 * @var object
	 */
	protected $db = NULL;

	/**
	 * 数据库连贯操作
	 * @var array
	 */
	protected $condition = array(
		'field'=>NULL,
		'where'=>NULL,
		'order'=>NULL,
		'group'=>NULL,
		'having'=>NULL,
		'limit'=>NULL,
	);

	/**
	 * sql语句参数值
	 * @var array
	 */
	protected $values = array();

	/**
	 * 创建模型
	 * @param string 要使用的数据库配置
	 * @return void
	 */
	public function __construct($key='mysql')
	{
		// 读取配置
		$driver = \Core\Config::G($key);
		// 创建数据库
		$this->db = Mysql::instance($driver);
	}

	/**
	 * 条件回调方法
	 * @param string field|where|having|order|group|limit
	 * @param array 参数列表
	 * @return \Mvc\Model
	 */
	public final function __call($method, $args)
	{
		switch($method)
		{
			case "field":
				// 要查询的字段
				$this->condition['field'] = is_array($args[0]) ? implode(',', $args[0]) : $args[0];
				break;
			case "order":
			case "group":
				$this->condition[$method] = strtoupper($method)." BY {$args[0]}";
				break;
			case "limit":
				$this->limit($args[0]);
				break;
			case "where":
			case "having":
				$this->comCondition($args[0], $method);
				break;
			default:
				trigger_error("NOT FOUND FUNCTION MODEL::{$method}");
		}

		return $this;
	}

	/**
	 * 执行插入，可插入多行
	 * @param array 插入键值对数组
	 * @return int 上一次插入的id | 影响的行数
	 */
	public final function insert(array $insert=array(), $type='lastInsertId')
	{
		// 从$_POST中获取数据
		$insert = $insert ? : $_POST;
		// 二维数组化
		$insert = count($insert) != count($insert, COUNT_RECURSIVE) ? $insert : array($insert);

		// 所有key
		$keys = array_keys($insert[0]);
		// 所有value
		foreach($insert as $key=>$val)
		{
			$temp = array();
			foreach($keys as $field)
			{
				$temp[] = ":{$field}{$key}";
				$values[":{$field}{$key}"] = array_shift($val);
			}
			$placeholder[] = "(".implode(',', $temp).")";
		}
		// array转成string
		$keys = implode(',', $keys);
		$placeholder = implode(',', $placeholder);

		// sql语句
		$sql = "INSERT INTO {$this->table}({$keys}) VALUES {$placeholder}";
		// 执行sql语句
		$this->db->query($sql, $values);
		// 插入的id
		return $this->db->$type();
	}

	/**
	 * 执行删除
	 * @return int 删除的行数
	 */
	public final function delete()
	{
		// 释放所有变量
		extract($this->condition);
		// sql语句
		$sql = "DELETE FROM {$this->table} {$where} {$order} {$limit}";
		// 执行语句
		$this->db->query($sql, $this->values);
		// 清空条件子句
		$this->setNull();
		// 返回影响的行数
		return $this->db->affectRow();
	}

	/**
	 * 执行更新
	 * @param array 键值对数组
	 * @param boolean 是否输出调试语句
	 * @return int 影响行数
	 */
	public final function update(array $update)
	{
		foreach($update as $key=>$val)
		{
			// 自增等系列处理
			if(stripos($val, $key) !== FALSE)
			{
				foreach(array('+','-','*','/','^','&','|','!') as $opeartion)
				{
					if(strpos($val, $opeartion))
					{
						$temp = explode($opeartion, $val);
						break;
					}
				}
				$set[] = "{$key}={$temp[0]}{$opeartion}:UPDATE{$key}";
				$this->values[":UPDATE{$key}"] = $temp[1];
			}
			else
			{
				// 普通赋值
				$set[] = "{$key}=:UPDATE{$key}";
				$this->values[":UPDATE{$key}"] = $val;
			}
		}
		// set语句
		$set = implode(',', $set);

		// 释放变量
		extract($this->condition);
		// sql语句
		$sql = "UPDATE {$this->table} SET {$set} {$where} {$order} {$limit}";
		// 执行更新
		$this->db->query($sql, $this->values);
		// 清空条件子句
		$this->setNull();
		// 返回影响行数
		return $this->db->affectRow();
	}

	/**
	 * 执行查询
	 * @param string 释放结果集类型
	 * @return mixed
	 */
	public final function select($func='fetchAll')
	{
		// 释放变量
		extract($this->condition);
		// sql语句
		$sql = "SELECT {$field} FROM {$this->table} {$where} {$group} {$having} {$order} {$limit}";
		// 执行查询
		$this->db->query($sql, $this->values);
		// 清空条件子句
		$this->setNull();
		// 返回结果
		return $this->db->fetch($func);
	}

	/**
	 * 统计行数
	 * @param array where子句键值对数组
	 * @param string 要统计的字段
	 * @return int
	 */
	public final function count()
	{
		// 释放变量
		extract($this->condition);
		// sql语句
		$sql = "SELECT COUNT({$field}) FROM {$this->table} {$where} {$group} {$order} {$limit}";
		// 执行查询
		$this->db->query($sql, $values, $debug);
		// 返回结果
		return $this->db->fetch(self::FETCH_COLUMN);
	}

	/**
	 * 拼接条件子句
	 * @param array 键值对数组
	 * @return array 
	 */
	private final function comCondition($condition, $field, $return=FALSE)
	{
		$where = $data = array();
		foreach($condition as $key=>$option)
		{
			// false null array() "" 的时候全部过滤
			if(!$option && !is_int($option))
			{
				continue;
			}
			
			if(is_array($option))
			{
				if($lan = strpos($key, " b"))
				{
					// between...and...
					$key = trim(substr($key, 0, $lan));
					$where[] = "{$key} BETWEEN :{$field}_BEWTEEN_{$key}_1 AND :{$field}_BEWTEEN_{$key}_2";
					$this->values[":{$field}_BEWTEEN_{$key}_1"] = $option[0];
					$this->values[":{$field}_BEWTEEN_{$key}_2"] = $option[1];
				}
				elseif(is_array($option[0]))
				{
					// or
					$or = array();
					foreach($option as $k=>$o)
					{
						$o = array($o[0]=>$o[1]);
						list($or[], $data) = $this->where($o, $field, TRUE);
					}
					$where[]  = "(".implode(" OR ", $or).")";
				}
				else
				{
					// in not in
					$operation = strpos($key, " n") ? "NOT IN" : "IN";
					$key = strpos($key, " n") ? trim(substr($key, 0, count($key)+1)) : $key;
					foreach($option as $k=>$val)
					{
						$temp[] = ":{$field}_INI_{$key}_{$k}";
						$this->values[":{$field}_INI_{$key}_{$k}"] = $val;
					}
					$where[] = "{$key} {$operation}(".implode(',', $temp).")";
				}
			}
			else if($lan = strpos($key, " "))
			{
				// > >= < <= !=
				$subkey = substr($key, 0, $lan);
				$where[] = "{$key}:{$field}_COMPARE_{$subkey}";
				$this->values[":{$field}_COMPARE_{$subkey}"] = $option;
			}
			else if((strpos($option, "%") !== FALSE) || (strpos($option, '_') !== FALSE))
			{
				// like
				$where[] = "{$key} LIKE :{$field}_LIKE_{$key}";
				$this->values[":{$field}_LIKE_{$key}"] = $option;
			}
			else
			{
				// =
				$where[] = "{$key}=:{$field}_EQUALS_{$key}";
				$this->values[":{$field}_EQUALS_{$key}"] = $option;
			}
		}

		if($return)
		{
			return $where;
		}
		else
		{
			$this->condition[$field] = strtoupper($field)." ".implode(' AND ', $where);
		}
	}
	
	/**
	 * LIMIT
	 * @param string|array|int 分页信息
	 * @return void
	 */
	private function limit($limit)
	{
		// 格式化成数组
		$limit = is_array($limit) ? $limit : explode(',', $limit);
		
		// 偏移量
		$offset = NULL;
		if((count($limit) == 2))
		{
			$offset = ":LIMIT_offset,";
			$this->values[':LIMIT_offset'] = ($limit[0]-1)*$limit[1];
		}

		// 条数
		$number = ":LIMIT_number";
		$this->values[':LIMIT_number'] = (int)array_pop($limit);

		// 保存条件
		$this->condition["limit"] = "LIMIT {$offset}{$number}";
	}

	/**
	 * 所有查询条件全部置为空
	 * @return void
	 */
	public final function setNull()
	{
		$this->condition = array(
			'field'=>NULL,
			'where'=>NULL,
			'order'=>NULL,
			'group'=>NULL,
			'having'=>NULL,
			'limit'=>NULL,
		);

		$this->values = array();
	}
}