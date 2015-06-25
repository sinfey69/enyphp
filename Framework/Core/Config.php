<?php
/**
 * 配置文件类
 * @author enychen
 */

namespace Core;

class Config
{
	/**
 	 * 加载配置
 	 * @param string 目录
 	 * @return void
 	 */
 	public static function configure($path)
 	{
 		// 加载所有配置文件
		$master = glob($path . '*.php');
		$dev = DEBUG ? glob($path . "dev/*.php") : array();
		// 加载配置
		foreach(array_merge($master, $dev) as $file)
		{
			require($file);
			$configure = array_merge($config, $configure);
		}
		// 封装成对象
		$configure = json_decode(json_encode($configure));
		$GLOBALS['_CFG'] = $configure;
 	}
}