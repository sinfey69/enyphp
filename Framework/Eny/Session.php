<?php

namespace Core;

class Session
{
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
			case 'files':
				ini_set('session.save_path', SESSION);
				break;
			case 'redis':
			case 'memcached':
				ini_set('session.save_handler', $config->handler);
				$driver = C::G($config->handler, $config->path);
				$config->path = "tcp://{$driver->host}:{$driver->port}";
				break;
		}
		// 设置过期时间
		ini_set('session.gc_maxlifetime', $config->expire);
		// 开启session
		session_start();
	}
}