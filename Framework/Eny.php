<?php

/**
 * 核心框架类
 * @author Eny
 */

use \Core\C;
use \Core\D;
use \Core\L;
use \Core\V;
use \Controller\_Empty;

class Eny
{
	/**
	 * 初始化
	 * @return void
	 */
	public static function boot()
	{
		// 目录定义
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

		// 通用常量定义
		defined('DEBUG') OR define('DEBUG',FALSE);// 调试模式
        define('IS_CLI',!strcasecmp(php_sapi_name(), 'cli'));// 命令行模式

        // 系统环境设置
		date_default_timezone_set('PRC');// 日期设置
		set_exception_handler('Eny::appException');// 自定义异常机制
		spl_autoload_register('Eny::appAutoload'); // 自动加载机制
		DEBUG OR set_error_handler('Eny::appError');// 自定义错误机制
		DEBUG OR register_shutdown_function('Eny::appShutdown');// 程序退出前回调机制
		DEBUG OR error_reporting(0);// 关闭报错
		DEBUG OR ini_set('display_errors','off');// 关闭报错
		IS_CLI OR header('Content-Type:text/html;charset=UTF-8');// 字符集设置

		// 程序运行
		self::run();
	}

	/**
	 * 程序执行
	 * @return void
	 */
	private static function run()
	{
		// 加载通用配置
		C::load();
		// 路由解析
		list($class, $function) = D::parseUrl();
		// 数据检查
		V::validity();
		// 创建控制器
		$controller = new $class();
		// 调用控制器函数
		$controller->$function();
	}

	/**
	 * 自动加载机制
	 * @param string 类名
	 * @return void
	 */
	public static function appAutoload($class)
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

	/**
	 * 自定义错误处理机制
	 * @param int 错误代码
	 * @param string 错误信息
	 * @param string 错误文件
	 * @param int 错误行号
	 * @return void
	 */
	public static function appError($errno, $errstr, $errfile, $errline)
	{
		// 转换错误到为常量
		$const = array_search($errno, get_defined_constants());
		$const = $const ? : $errno;
		// 错误转向
		L::systemError($errno, $errstr, $errfile, $errline);
		// 抛出500服务器错误
		_Empty::_500();
		// 结束程序
		exit;
	}	

	/**
	 * 自定义异常处理机制
	 * @param  object 异常对象
	 * @return void
	 */
	public static function appException($e)
	{
		switch(get_class($e))
		{
			case "Exception":
				// 正常的错误提示
				switch($e->getMessage())
				{
					case 403:
						// 禁止操作
						_Empty::_403();
						break;
					case 404:
						// 找不到页面
						_Empty::_404();
						break;
					default:
						_Empty::_E($e->getMessage(), $e->getCode());
				}
				break;
			default:
				if(DEBUG)
				{
					exit($e->getMessage().' on File <b>'.$e->getFile().'</b>, Line at <b>'.$e->getLine().'</b>');
				}
				else
				{
					self::appError('E_EXCEPTION', $e->getMessage(), $e->getFile(), $e->getLine());
				}
		}
	}

	/**
	 * 程序退出前的回调机制
	 * @return void
	 */
	public static function appShutdown()
	{
		if($e = error_get_last())
		{
			switch($e['type'])
			{
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:  
					ob_end_clean();
					// 日志记录
					self::appError($e['type'], $e['message'], $e['file'], $e['line']);
					break;
			}
		}
	}
}

