<?php
/**
 * memcached缓存类
 * @author enychen
 */

namespace Driver;

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
	 * 禁止克隆对象
	 * @return void
	 */
	private final function __clone(){}

	/**
	 * 静态调用方式
	 * @param string 方法名
	 * @param array 参数
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{ 
		// 检查对象是否创建
		self::$instance OR self::create();
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
		$memcached = new \Memcached();
		// 整理配置
		list($servers, $much) = self::config();
		// 连接服务器
		$memcached->addServers();
		// 设置分布式
		$much <= 1 OR $memcached->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
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
		foreach(C('memcached') as $key=>$config)
		{
			$mcs[$key][] = $config['host'];
			$mcs[$key][] = $config['port'];
			empty($config->weight) OR ($mcs[$key][] = $config['weight']);
		}

		return array($mcs, count($mcs));
	}
}