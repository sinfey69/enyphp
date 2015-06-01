<?php

namespace Core;

/**
 * 路由调度
 * @author chenxb
 */
class Router
{
	/**
	 * 默认路由器
	 * @param object
	 */
	private static $routes = NULL;

	/**
	 * 路由分析
	 * @return void
	 */
	public static function dispatch()
	{
		// 初始化配置
		self::$routes = C('routes');
		// 获得请求路由
		$routes = self::getRoutes();
		// 设置路由信息
		$routes = self::setRoutes($routes);
		// 检查控制器
		self::setController($routes);
		// 返回信息
		return array(self::$routes['class'], self::$routes['function']);
	}

	/**
	 * 获得请求路由
	 * @return array
	 */
	private static function getRoutes()
	{
		if(IS_CLI)
		{
			$routes = server('argv');
			array_splice($routes, 0, 1);
		}
		else
		{
			$routes = trim(server('PATH_INFO'), '/');
			$routes = str_replace('.'.self::$routes['suffix'], '', $routes);
			$routes = $routes ? explode('/', $routes) : array();
		}
		return $routes;
	}

	/**
	 * 设置路由信息
	 * @param array 来源信息数组
	 * @return void
	 */
	private static function setRoutes($routes)
	{
		foreach(array('class', 'function') as $val)
		{
			if(empty($routes[0]) || is_numeric($routes[0]))
			{
				break;
			}
			if(!preg_match('/^([a-z])+$/', $routes[0])) 
			{
				Output::_403();
			}
			self::$routes[$val] = $routes[0];
			array_splice($routes, 0, 1);
		}
		return $routes;
	}

	/**
	 * 初始化控制器信息
	 * @param array 剩下的路由信息
	 * @return void
	 */
	private static function setController($routes)
	{
		// 类名首字母大写
		self::$routes['class'] = ucfirst(self::$routes['class']);
		// 通用文件名
		define('REQUEST_FILE', self::$routes['class']."/".self::$routes['function']);
		// 设置控制器全称
		self::$routes['class'] = '\\Controller\\'.self::$routes['class'];
		// 判断文件是否存在
		if(!method_exists(self::$routes['class'], self::$routes['function']))
		{
			Output::_404();
		}
		// url请求参数设置到$_REQUEST;
		$_REQUEST = $routes;
	}
}