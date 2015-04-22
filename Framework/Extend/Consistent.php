<?php

/**
 * 分布式计算hash值
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
	protected static $nodeNumber  = 64;
	
	/**
	 * 计算哈希值
	 * @param string 
	 * @return int
	 */
	public static function hash($value)
	{
		return sprintf("%u", crc32($value));
	}
	
	/**
	 * 计算值落在哪个节点上
	 * @param string 键
	 * @return object 节点对象
	 */
	public static function lookup($key)
	{
		// 计算节点数
		$point = self::hash($key);
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
		return $this->nodes[$node];
	}
	
	/**
	 * 节点失效后删除所有虚拟节点
	 * @param string 节点哈希值
	 * @return void
	 */
	public static function delNode($node)
	{
		for($i=0; $i<self::$nodeNumber; $i++)
		{
			unset(self::$position[self::hash("{$node}_$i")]);
		}
	}
	
	/**
	 * 增加节点
	 * @param string 节点key
	 * @param object 节点对象
	 * @reutrn void
	 */
	public static function addNode($node, $object)
	{
		for($i=0; $i<self::$nodeNumberi; $i++)
		{
			self::$position[self::hash("{$node}_$i")] = $node;
		}
		
		self::$nodes[$node] = $object;
		
		self::sortNode();
	}
	
	/**
	 * 节点排序
	 * @return void
	 */
	protected static function sortNode()
	{
		ksort(self::$position, SORT_REGULAR);
	}
}