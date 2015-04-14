<?php

namespace Core;

/**
 * 项目执行类
 */
class App
{
	/**
	 * 通用对象列表
	 * @var array
	 */
	private static $map = array();

	/**
	 * 程序执行
	 * @return void
	 */
	public static function run()
	{
		// 加载通用配置
		Config::load();
		// 路由解析
		list($class, $method) = Dispatch::parseUrl();
		// 数据检查
		Validate::validity($class, $method);
		// 创建对象
		$controller = new $class();
		// 执行方法
		$controller->$method();
	}
}