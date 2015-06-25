<?php

/**
 * 获取配置选项
 * @param mixed key
 * @param mixed key
 * @return mixed
 */
function C($index, $item=NULL)
{
	$pref = empty($GLOBALS['_CFG'][$index]) ? NULL : $GLOBALS['_CFG'][$index];

	if($pref && !is_null($item))
	{
		$pref = empty($pref[$item]) ? NULL : $pref[$item];
	}

	return $pref;
}

/**
 * 多语言
 * @param string key键
 * @return string
 */
function L($key)
{
	static $lang;
	static $package;

	if(!$lang)
	{	
		// 设置默认值
		$lang = 'zh-cn';
		// 遍历查询
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
		// 读取语言包信息
		$package = require(LANGUAGE."{$lang}.php");
	}

	return isset($package[$key]) ? $package[$key] : NULL;
}

/**
 * 获得$_SERVER中的内容
 * @param  string 下标值
 * @param  找不到值后的默认值
 * @return mixed 找到返回内容,否则返回第二个参数的内容
 */
function S($index, $defualt=NULL)
{
	
}

/**
 * 获取url地址
 * @param string pathinfo
 * @param string
 * @return
 */
function U($pathinfo, $args)
{

}

/**
 * 获得文件的完整路径并创建目录
 * @param string 文件名
 * @param string 基准目录
 * @return string 绝对路径文件名
 */
function F($path, $abs='./')
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
	return sprintf("%s[%s]%s", $path, date('Y-m-d'), $basename);
}

/**
 * 调试
 */
function debug($info)
{
	echo '<pre>';
	print_r($info);
	echo '</pre>';
	exit;
}

