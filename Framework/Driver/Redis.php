<?php

namespace Storge;

use Core\C;
use Extend\Consistent;

class Redis
{
	/**
	 * 是否创建对象
	 * @var boolean
	 */
	private static $create =fALSE;
	
	/**
	 * 禁止创建对象
	 */
	private final function __construct(){}

	/**
	 * 禁止创建对象
	 */
	private final function __construct(){}

	/**
	 * 静态调用方式
	 * @param string 方法名
	 * @param array 参数
	 */
	public static function __callStatic($method, $args)
	{
		// 检查对象是否创建
		self::create();
		// 计算哈希值
		$point = Consistent::hash($args[0]);
		// 选择某一台缓存服务器
		$object = Consistent::lookup($point);
		// 执行回调函数
		return call_user_func_array(array($object, $method), $args);
	}

	/**
	 * 如果redis没有创建则进行创建
	 * @throws \Exception
	 */
	private static function create()
	{
		// 对象已经创建
		if(!self::$create)
		{
			foreach(C::redis() as $key=>$node)
			{
				$redis = new \Redis();
				// redis连接
				if($redis->connect($node->ip, $node->port, $node->timeout))
				{
					// redis设置
					$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
					// 是否需要验证
					!$config->password OR $redis->auth($this->password);
					// 增加节点
					Consistent::addNode("redis{$key}", $redis);
					// 删除临时对象
					unset($redis);
				}
				else
				{
					throw new \RedisException("Redis Connection Error: {$node->ip}");
				}
			}
			// 已经创建
			self::$create = TRUE;
		}
	}
}