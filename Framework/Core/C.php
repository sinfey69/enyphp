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
		// 绑定键
		array_unshift($args, $method);
		// 回调获取参数
		return call_user_func_array('self::G', $args);
	}

	/**
	 * 加载配置
	 * @return void
	 */
	public static function loadConfig()
	{
		$global = glob(CONFIG."*.php");
		$debug = DEBUG ? array(CONFIG."Debug.php") : array();
		foreach(array_merge($global, $debug) as $file)
		{
			require($file);
			self::$_CFG = array_merge($config, self::$_CFG);
		}
	}

	/**
	 * 获取配置
	 * @param string 键
	 * @param string 键
	 * @return mixed
	 */
	public static function G($index, $item=NULL)
	{
		$pref = isset(self::$_CFG[$index]) ? self::$_CFG[$index] : NULL;
		if(!is_null($item))
		{
			$pref = isset($pref[$item]) ? $pref[$item] : NULL;
		}
		return $pref;
	}
}