<?php

namespace Controller;
use \Mvc\Controller;
use \Driver\Memcached;
/**
 * 前台入口控制器
 */
class Home extends Controller
{
	public function index()
	{
		if(Memcached::set($_SERVER['REQUEST_URI'], 'enychen', TRUE))
		{
			echo Memcached::get($_SERVER['REQUEST_URI']);
		}
	}
}