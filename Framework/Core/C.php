<?php
/**
 * 配置信息类
 * @author enychen
 */

namespace Core;

class C
{
	/**
	 * 通用配置数组
	 * @var object
	 */
	private static $_CFG = array();
	
	/**
	 * 简单的获取配置
	 * @param string 键
	 * @param string 键
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		array_unshift($args, $method);
		return call_user_func_array('self::G', $args);
	}

	/**
	 * 加载配置
	 * @return void
	 */
	public static function initialize()
	{
		// 读取通用配置
		$global = glob(CONFIG."*.php");
		// 读取开发配置
		$debug = DEBUG ? glob(CONFIG."*.debug.php") : array();
		// 加载配置文件
		foreach(array_merge($global, $debug) as $file)
		{
			// 加载配置文件
			require($file);
			// 合并参数
			self::$_CFG = array_merge($config, self::$_CFG);
		}

		// 把数组转成对象
		self::$_CFG = F::toObject(self::$_CFG);
	}

	/**
	 * 获取配置
	 * @param string 键
	 * @param string 键
	 * @return mixed
	 */
	public static function G($index, $item=NULL)
	{
		// 取出一个内容
		$pref = isset(self::$_CFG->$index) ? self::$_CFG->$index : NULL;
		// 取出一个值
		if(!is_null($item) && $pref)
		{
			if(is_array($pref))
			{
			 	$pref = isset($pref[$item]) ? $pref[$item] : NULL;
			}
			else
			{
				$pref = isset($pref->$item) ? $pref->$item : NULL;
			}
		}	
		// 返回参数
		return $pref;
	}
}