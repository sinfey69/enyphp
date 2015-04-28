<?php
/**
 * mysql数据库类
 * @author enychen
 */

namespace Driver;

class Mysql
{
	/**
	 * pdo池对象
	 * @var object
	 */
	private static $instance = NULL;

	/**
	 * 当前的pdo对象
	 * @var object
	 */
	private $pdo;

	/**
	 * 预处理对象
	 * @var object
	 */
	private $stmt;

	/**
	 * pdo实例
	 * @var array
	 */
	private $map = array();

	/**
	 * 配置信息对象数组
	 * @param array
	 */
	private $drivers = array();

	/**
	 * 构造函数
	 * @param array 配置对象数组
	 * @return void
	 */
	private final function __construct($drivers)
	{
		// 保存配置信息
		$this->drivers = $drivers;
		// 默认创建第一个数据库连接
		$this->changeDb();
	}

	/**
	 * 禁止执行克隆
	 * @return void
	 */
	private final function __clone(){}

	/**
	 * 创建数据库连接池对象
	 * @param array 配置对象数组	
	 * @return \Driver\Mysql
	 */
	public static function instance(array $drivers)
	{
		if(!self::$instance)
		{
			self::$instance = new self($drivers);
		}

		return self::$instance;
	}

	/**
	 * 切换数据库
	 * @param int 数据库编号
	 * @return void
	 */
	public function changeDb($key=0)
	{
		// 已经创建则切换
		if(isset($this->map[$key]))
		{
			$this->pdo = $this->map[$key];
		}
		else
		{
			// 获取配置对象
			$driver = $this->drivers[$key];
			// 数据库连接信息
			$dsn = "mysql:host={$driver->host};port={$driver->port};dbname={$driver->dbname};charset={$driver->charset}";
			// 驱动选项
			$option = array(
					\PDO::ATTR_CASE=>\PDO::CASE_LOWER,// 所有字段小写
					\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,// 如果出现错误抛出错误警告
					\PDO::ATTR_ORACLE_NULLS=>\PDO::NULL_TO_STRING,// 把所有的NULL改成""
					\PDO::ATTR_TIMEOUT=>30// 超时时间
			);			
			// 创建数据库驱动对象
			$this->pdo = $this->map[$key] = new \Pdo($dsn, $driver->user, $driver->password, $option);
		}
	}

	/**
	 * 执行sql查询
	 * @param string sql语句
	 * @param array 参数数组
	 * @return void
	 */
	public function query($sql, $params=array())
	{
		// 预处理绑定语句
		$this->stmt = $this->pdo->prepare($sql);
		// 参数绑定
		!$params OR $this->bindValue($params);
		// 输出调试
		!defined('SQL_ECHO') OR $this->debug($sql, $params);
		// 执行一条sql语句
		if($this->stmt->execute())
		{
			// 设置解析模式
			$this->stmt->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');	
		}
		else
		{
			// 抛出一个pdo错误
			throw new \PDOException($this->stmt->errorInfo()[2]);
		}
	}

	/**
	 * 参数与数据类型绑定
	 * @param array 值绑定
	 * @return void
	 */
	private function bindValue($params)
	{
		foreach($params as $key=>$value)
		{
			// 数据类型选择
			switch(TRUE)
			{
				case is_int($value):
					$type = \PDO::PARAM_INT; 
					break;
				case is_bool($value):
					$type = \PDO::PARAM_BOOL; 
					break;
				case is_null($value):
					$type = \PDO::PARAM_NULL; 
					break;
				default:
					$type = \PDO::PARAM_STR; 
			}
			// 参数绑定
			$this->stmt->bindValue($key, $value, $type);
		}
	}

	/**
	 * 简单回调pdo对象方法
	 * @param string 函数名
	 * @param array 参数数组
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		switch($method)
		{
			case "lastInsertId":
				$result = $this->pdo->lastInsertId();
				break;
			case "rowCount":
			case "fetchAll":
			case "fetch":
			case "fetchRow":
				$result = $this->stmt->$method();
				break;
			default:
				trigger_error("NOT FOUND Mysql::{$method}");
		}

		// 删除结果集
		unset($this->stmt);
		// 返回结果
		return $result;	
	}

	/**
	 * 输出预绑定sql和参数列表
	 * @param string 预处理sql语句
	 * @param array 数据
	 * @return void
	 */
	public function debug($sql, $data)
	{
		foreach($data as $key=>$placeholder)
		{
			// 字符串加上引号
			!is_string($placeholder) OR ($placeholder = "'{$placeholder}'");
			// 替换
			$start = strpos($sql, $key);
			$end = strlen($key);
			$sql = substr_replace($sql, $placeholder, $start, $end);
		}
		
		exit($sql);
	}
}