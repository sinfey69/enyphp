<?php

namespace Storge;

use Core\C;

class Memcached
{
	/**
	 * redis对象
	 * @var object
	 */
	private static $memcached;
	
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
		return call_user_func_array(array(self::$memcached, $method), $args);
	}

	/**
	 * 如果redis没有创建则进行创建
	 * @throws \Exception
	 */
	private static function create()
	{
		// 对象已经创建
		if(self::$memcached instanceof \Memcached)
		{
			return;
		}

		$memcached = new \Memcached();
	}
}