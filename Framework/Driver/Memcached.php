<?php
/**
 * memcached缓存类
 * @author enychen
 */

namespace Storge;

use Core\C;

class Memcached
{	
	/**
	 * 全局对象
	 * @var boolean
	 */
	private static $instance = NULL;

	/**
	 * 禁止创建对象
	 * @return void
	 */
	private final function __construct(){}

	/**
	 * 禁止创建对象
	 * @return void
	 */
	private final function __construct(){}

	/**
	 * 静态调用方式
	 * @param string 方法名
	 * @param array 参数
	 */
	public static function __callStatic($method, $args)
	{
		if(!self::$instance)
		{
			// 检查对象是否创建
			self::create();
		}
		// 执行回调函数
		return call_user_func_array(array(self::$instance, $method), $args);
	}
	
	/**
	 * 创建服务器对象
	 * @return void
	 */
	private static function create()
	{
		// 创建分布式对象
		$memcached = new Memcached();
		// 连接服务器
		$memcached->addServers(self::config());
		// 已经创建
		self::$instance = $memcached;
		// 删除临时对象
		unset($memcached);
	}

	/**
	 * 整理配置
	 * @return array
	 */
	private static function config()
	{
		$mcs = array();
		foreach(C::memcached() as $key=>$config)
		{
			$mc[$key][] = $config->host;
			$mc[$key][] = $config->port;
			if(isset($config->weight))
			{
				$mc[$key][] = $config->weight;
			}

			$mcs[] = $mc;
		}

		return $mcs;
	}
}