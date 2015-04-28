<?php
/**
 * 分布式类
 * @author enychen
 */

namespace Extend;

class Consistent
{
	/**
	 *  节点
	 * @var array
	 */
	protected static $nodes = array();
	
	/**
	 * 虚拟节点
	 * @var array
	 */
	protected static $position = array();
	
	/**
	 * 虚拟节点数量
	 * @var int
	 */
	protected static $virtual  = 64;
	
	/**
	 * 计算值落在哪个节点上
	 * @param string 键
	 * @return object 节点对象
	 */
	public static function lookup($point)
	{
		// 默认第一个节点
		$node = current(self::$nodes);
		// 获取区间节点
		foreach(self::$position as $range=>$node)
		{
			if($point <= $range) 
			{
				$node = $node;
				break;
			}
		}
		
		// 获取对应的对象
		return self::$nodes[$node];
	}
	
	/**
	 * 增加节点
	 * @param string 节点key
	 * @param object 节点对象
	 * @return void
	 */
	public static function addNode($node, $object)
	{
		// 引入虚拟节点降低服务器压力
		for($i=0; $i<self::$virtual; $i++)
		{
			if(array_key_exists(self::hash("{$node}_$i"), self::$position))
			{
				return;
			}
			self::$position[self::hash("{$node}_$i")] = $node;
		}
		// 当前节点保存服务器对象self::$position
		self::$nodes[$node] = $object;
		// 排序节点
		ksort(self::$position, SORT_REGULAR);
	}

	/**
	 * 节点失效后删除所有虚拟节点
	 * @param string 节点哈希值
	 * @return void
	 */
	public static function delNode($node)
	{
		for($i=0; $i<self::$virtual; $i++)
		{
			unset(self::$position[self::hash("{$node}_$i")]);
		}
	}

	/**
	 * 计算哈希值
	 * @param string 
	 * @return int
	 */
	private static function hash($value)
	{
		return sprintf("%u", crc32($value));
	}
}