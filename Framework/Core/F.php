<?php

namespace Core;

/**
 * 公用函数库
 */
class F
{


	/**
	 * 多语言
	 * @param string key键
	 * @return string
	 */
	public static function lang($key)
	{
		static $lang;

		if(!$lang)
		{
			if($language = server('HTTP_ACCEPT_LANGUAGE'))
			{
				$types = array('zh-cn', 'zh', 'en', 'fr', 'de', 'jp', 'ko', 'es', 'sv');
				foreach($types as $type)
				{
					$pattern = "/{$type}/i";
					if(preg_match($pattern, $language))
					{
						$lang = $type;
						break;
					}
				}
			}
		}

		// 读取语言包信息
		$package = require(LANGUAGE.$lang.'.php');

		return isset($package[$key]) ? $package[$key] : NULL;
	}

	/**
	 * 获得$_SERVER中的内容
	 * @param  string 下标值
	 * @param  找不到值后的默认值
	 * @return mixed 找到返回内容,否则返回第二个参数的内容
	 */
	public static function server($index, $defualt=NULL)
	{
		return empty($_SERVER[$index]) ? $defualt : addslashes($_SERVER[$index]);
	}

	/**
	 * 获得文件的完整路径
	 * @param string 文件名
	 * @param string 基准目录
	 * @return string 绝对路径文件名
	 */
	public static function absFile($path, $abs='./')
	{
		// 文件信息
		$path = trim($path, '/');
		$dirname = dirname($path);
		$basename = basename($path);
		// 完整目录
		$path = sprintf('%s%s/', $abs, $dirname);
		// 不存在目录则递归创建目录
		is_dir($path) OR mkdir($path, 0750, TRUE);
		// 返回文件的绝对路径
		return sprintf("%s%s-%s", $path, $basename, date('Y-m-d'));
	}

	/**
	 * 把数组转成对象
	 * @param array
	 * @return object
	 */
	public static function toObject($array)
	{
		return json_decode(json_encode($array));
	}
}