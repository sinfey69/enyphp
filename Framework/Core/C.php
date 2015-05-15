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
		$global = glob(CONFIG."*.php");
		$debug = DEBUG ? array(CONFIG."Debug.php") : array();
		foreach(array_merge($global, $debug) as $file)
		{
			require($file);
			self::$_CFG = array_merge($config, self::$_CFG);
		}
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
		if(empty(self::$_CFG->$index))
		{
			return NULL;
		}
		
		$pref = self::$_CFG->$index;

		if(!is_null($item))
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

		return $pref;
	}
}