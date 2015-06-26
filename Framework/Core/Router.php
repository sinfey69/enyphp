<?php

namespace Core;

/**
 * 路由调度类
 * @author chenxb
 */
class Route
{
	/**
	 * 默认路由器
	 * @var <object>
	 */
	private static $routes = NULL;

	/**
	 * 路由分析
	 * @param <stdClass> 路由对象，包括class,function,suffix属性
	 * @return void
	 */
	public static function dispatch(\stdClass $routes)
	{
		// 初始化环境
		self::initialize();
		// 初始化配置
		self::setRoutes($routes);
		// 获取路由来源
		self::source();
		// 设置请求信息
		self::setRequest();
	}

	/**
	 * 初始化请求环境
	 * @return void
	 */
	private static function initialize()
	{
		define('IS_CLI', !strcasecmp(php_sapi_name(), 'cli'));// 命令行模式
		define('IS_GET', self::server('REQUEST_METHOD')=='GET');// get请求
		define('IS_POST',self::server('REQUEST_METHOD')=='POST');// post请求
		define('IS_PUT',self::server('REQUEST_METHOD')=='PUT');// put请求
		define('IS_DELETE',self::server('REQUEST_METHOD')=='DELETE');// delete请求
		define('IS_AJAX',strcasecmp(server('REQUEST_METHOD'),'xmlhttprequest'));//ajax请求
	}

	/**
	 * 初始化控制器信息
	 * @param array 剩下的路由信息
	 * @return void
	 */
	private static function setController($routes)
	{
		// 类名首字母大写
		self::$routes->class = ucfirst(self::$routes->class);
		// 通用文件名
		define('REQUEST_FILE', self::$routes->class."/".self::$routes->function);
		// 设置控制器全称
		self::$routes->class = '\\Controller\\'.self::$routes->class;
		// 判断文件是否存在
		if(!method_exists(self::$routes->class, self::$routes->function))
		{
			throw new \Exception('404 NOT FOUND', 404);
		}
	}

	/**
	 * 数据来源
	 */
	private static function source($routes)
	{
		if(IS_CLI)
		{
			$routes = $_SERVER['argv'];
			array_splice($routes, 0, 1);
		}
		else
		{
			$routes = trim(server('PATH_INFO'), '/');
			$routes = str_replace('.'.self::$routes->suffix, '', $routes);
			$routes = $routes ? explode('/', $routes) : array();
		}
	}

	private static function setRequest()
	{
		foreach(array('class', 'function') as $val)
		{
			if(empty($routes[0]) || is_numeric($routes[0]))
			{
				break;
			}
			if(!preg_match('/^([a-z])+$/', $routes[0])) 
			{
				throw new \Exception('403 FORBIDDEN', 403);
			}
			self::$routes->$val = $routes[0];
			array_splice($routes, 0, 1);
		}
		self::setRequest($routes);
	}

	/**
	 * 初始化路由对象
	 * @param <stdClass> 路由对象，包括class,function,suffix属性
	 * @return void
	 */
	private static function setRoutes($routes)
	{		
		self::$routes = $routes;
	}

	/**
	 * 设置请求的信息
	 * @param <array> pathinfo信息中附带的内容
	 * @return void
	 */
	private static function setRequest($arguments)
	{
		$_REQUEST = $arguments;
	}
}