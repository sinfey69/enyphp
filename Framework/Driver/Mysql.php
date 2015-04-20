<?php

/**
 * mysql数据库类
 */
namespace Driver;

class Mysql
{
	/**
	 * 当前对象
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
	 * 数据库实例 
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
	 */
	private final function __construct($drivers)
	{
		// 保存配置信息
		$this->drivers = $drivers;
	}

	/**
	 * 禁止执行克隆
	 */
	private final function __clone(){}

	/**
	 * 创建数据库实例
	 * @param array 配置对象信息
	 * @param int 当前要创建的数据库编号
	 * @return object 当前对象
	 */
	public static function instance($drivers, $key=0)
	{
		if(!self::$instance)
		{
			self::$instance = new self($drivers);
		}

		return self::$instance;
	}

	/**
	 * 执行sql查询
	 * @param string sql语句
	 * @param array 参数数组
	 * @param boolean 是否输出调试语句
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
		$this->stmt->execute();
		// 设置解析模式
		$this->stmt->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
	}

	/**
	 * 切换数据库
	 * @param string 主从key
	 * @return void
	 */
	public function changeDb($key)
	{
		extract($this->drivers[$key]);
		// key键
		$key = md5("{$host}:{$port}:{$dbname}");

		// 已经创建则切换
		if(isset($this->map[$key]))
		{
			$this->pdo = $this->map[$key];
			return;
		}

		// 数据库连接信息
		$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
		// 驱动选项
		$option = array(
				\PDO::ATTR_CASE=>\PDO::CASE_LOWER,// 所有字段小写
				\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_WARNING,// 如果出现错误抛出错误警告
				\PDO::ATTR_ORACLE_NULLS=>\PDO::NULL_TO_STRING,// 把所有的NULL改成""
				\PDO::ATTR_TIMEOUT=>30
		);
		// 创建数据库驱动对象
		$this->pdo = $this->map[$key] = new \Pdo($dsn, $user, $password, $option);
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
	 * 解析数据库查询资源
	 * @param string  fetchAll | fetch | fetchColumn
	 * @return mixed  查询得到返回数组或字符串,否则返回false或空数组
	 */
	public function fetch($func='fetchAll')
	{
		// 执行释放
		$result = $this->stmt->$func();
		unset($this->stmt);
		return $result;
	}

	/**
	 * 获取上次插入的id
	 * @return int 
	 */
	public function lastInsertId()
	{
		$result = $this->pdo->lastInsertId();
		unset($this->stmt);
		return $result;
	}

	/**
	 * 返回插入|更新|删除影响的行数
	 * @return int 
	 */
	public function affectRow()
	{
		$result = $this->stmt->rowCount();
		unset($this->stmt);
		return $result;
	}

	/**
	 * 返回结果集中的行数
	 * @return int
	 */
	public function resourceCount()
	{
		$result = $this->stmt->columnCount();
		unset($this->stmt);
		return $result;	
	}

	/**
	 * 输出预绑定sql和参数列表
	 * @return void
	 */
	public function debug($sql, $data)
	{
		foreach($data as $key=>$d)
		{
			if(is_string($d))
			{
				$d = "\\{$d}\\";
			}

			$sql = str_replace($key, $d, $sql);
		}
		
		echo $sql;
	}
}