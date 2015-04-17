<?php

namespace Core;

class C
{
	/**
	 * 通用配置数组
	 * @var array
	 */
	private static $_CFG = array();

	/**
	 * 禁止创建对象
	 */
	private final function __construct(){}

	/**
	 * 禁止克隆对象
	 */
	private final function __clone(){}

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
	public static function load()
	{
		// 读取通用配置
		$global = glob(CONFIG."*.php");
		$debug = DEBUG ? glob(CONFIG."*.debug.php") : array();
		// 加载配置文件
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
		// 取出一个对象
		$pref = isset(self::$_CFG[$index]) ? self::$_CFG[$index] : FALSE;

		if($item && $pref)
		{
			// 取出第二个参数
			$pref =  isset($pref[$item]) ? $pref[$item] : FALSE;
		}
				
		// 返回参数
		return $pref;
	}
}