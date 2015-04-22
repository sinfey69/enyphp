<?php

namespace Storge;

use \Core\C;

class Session
{
	/**
	 * 数据库session操作模型
	 * @var object
	 */
	private static $model;

	/**
	 * 初始化Session存储方式
	 * @return void
	 */
	public static function start()
	{
		$config = C::session();

		// 设置session名
		session_name($config->name);
		// 设置保存方式
		switch($config->handler)
		{
			case 'redis':
			case 'memcached':
				ini_set('session.save_handler', $config->handler);
				$config->path = "tcp://{$config->path}";
			case 'memcache':
				ini_set('session.save_path', $config->path);
				break;
			case 'mysql':
				ini_set('session.save_path', 'user');
				self::$model = new \Model\Session();
				session_set_save_handler(
					array(self::$model,'open'),
					array(self::$model,'close'),
					array(self::$model,'read'),
					array(self::$model,'write'),
					array(self::$model,'destroy'),
					array(self::$model,'gc')	
				);
				break;
		}
		// 设置过期时间
		ini_set('session.gc_maxlifetime', $config->expire);

		// 开启session
		session_start();
	}
}