<?php

// 常量定义
define('FCPATH',dirname(__DIR__).'/');// 站点目录
define('APPLICATION',FCPATH.'Application/');//项目目录
define('FRAMEWORK',FCPATH.'Framework/');//框架文件目录
define('CONFIG',APPLICATION.'Config/');// 配置文件目录
define('VALIDATE',APPLICATION.'Validate/');// 数据xml文件目录
define('LANGUAGE',APPLICATION.'Language/');// 语言包目录
define('VIEW',APPLICATION.'View/');// 模板目录
define('PLUGIN',APPLICATION.'Plugin/');//模块目录
define('DATA',APPLICATION.'Data/');// 数据目录
define('LOG',DATA.'Log/');// 日志目录
define('CACHE',DATA.'Cache/');// 缓存目录
define('COMPILE',DATA.'Compile/');// 模版编译文件
define('FONT',DATA.'Font/');// 字体目录
define('FILE',DATA.'File/');// 文件目录
define('LOCK',DATA.'Lock/');// 锁机制目录
defined('DEBUG') OR define('DEBUG',FALSE);// 调试模式
define('IS_CLI',!strcasecmp(php_sapi_name(), 'cli'));// 命令行模式

// 自动加载
function __autoload($class)
{
	// 文件行号转换
	$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	// 遍历目录
	foreach(array(APPLICATION, FRAMEWORK) as $dir)
	{
		// 完整文件名
		$file = "{$dir}{$class}.php";
		//是否加载
		if(is_file($file))
		{
			require($file);
			break;
		}
	}
}

// 读取配置
\Core\C::load();