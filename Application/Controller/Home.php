<?php

namespace Controller;
use \Mvc\Controller;
use \Core\L;

/**
 * 前台入口控制器
 */
class Home extends Controller
{
	public function _init()
	{
		$this->view->isCache(REQUEST_FILE);
	}

	public function index()
	{
		$a = new \stdClass();
		$a->name = '我草啊';
		$data = array(
			'if'=>2,
			'list'=>array(1,2,3,4,5,6,7,8,9),
			'website'=>array('name'=>'e.enychen.com','t.enychen.com'),
			'author'=>'enychen',
			'object'=>$a
		);

		$this->view->display(REQUEST_FILE, $data);
	}
}