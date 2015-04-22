<?php

namespace Storge;

use Core\C;

class Redis
{
	/**
	 * redis对象
	 * @var object
	 */
	private static $redis;
	
	/**
	 * 禁止创建对象
	 */
	private final function __construct(){}

	/**
	 * 禁止创建对象
	 */
	private final function __construct(){}
	
	/**
	 * 静态方法执行redis的函数
	 * @param unknown $method
	 */
	public static function __callStatic($method, $args)
	{
		// 检查对象是否创建
		self::create();
		// 执行redis的方法
		return call_user_func_array(array(self::$redis, $method), $args);
	}

	/**
	 * 如果redis没有创建则进行创建
	 * @throws \Exception
	 */
	private static function create()
	{
		// 对象已经创建
		if(self::$redis instanceof \Redis)
		{
			return;
		}
		// 读取配置
		$config = C::redis();
		// 创建redis对象
		$redis = new \Redis();
		// redis连接
		if($redis->connect($config->ip, $config->port, $config->timeout))
		{
			// redis设置
			$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
			// 是否需要验证
			!$config->password OR $redis->auth($this->password);
			// 类静态保存
			self::$redis = $redis;
			// 删除临时对象
			unset($redis);
		}
		else
		{
			throw new \Exception("Redis Connection Error");
		}
	}
}