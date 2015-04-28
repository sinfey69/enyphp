<?php
/**
 * memcached缓存类
 * @author enychen
 */

namespace Storge;

use Core\C;
use Extend\Consistent;

class Memcached
{	
	/**
	 * 是否创建对象
	 * @var boolean
	 */
	private static $create =fALSE;

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
	 * 增加分布式节点对象
	 * @return void
	 */
	private static function create()
	{
		if(!self::$create)
		{
			// 创建分布式memcached对象
			foreach(C::memcached() as $key=>$node)
			{
				// 创建分布式对象
				$memcached = new Memcached();
				// 连接服务器
				$memcached->addServer($node->host, $node->port, $node->weight);
				// 增加节点
				Consistent::addNode("memcached{$key}", $memcached);
				// 删除临时对象
				unset($memcached);
			}
			// 已经创建
			self::$create = TRUE;
		}
	}
}