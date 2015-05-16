<?php

namespace Controller;
use \Mvc\Controller;
use \Driver\Memcached;
use \Driver\Redis;
/**
 * 前台入口控制器
 */
class Home extends Controller
{
	public function index()
	{
		echo "hello world";
	}

	// memcached测试
	public function memcached()
	{
		if(Memcached::set($_SERVER['REQUEST_URI'], 'enychen', TRUE))
		{
			echo Memcached::get($_SERVER['REQUEST_URI']);
		}
	}

	// redis测试
	public function redis()
	{
		var_dump(redis::set('name','enychen'));
		var_dump(redis::get('name'));
	}

	// 画图测试
	public function image()
	{
		// 验证码
		\Extend\Image::captcha(100,30);
	}
}