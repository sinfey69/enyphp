<?php

namespace Controller;
use \Mvc\Controller;
use \Core\L;

/**
 * 前台入口控制器
 */
class Home extends Controller
{
	public function index()
	{
		L::log('test/chenxiaobo', array('name'=>'chenxiaobo', 'age'=>25, 'sex'=>'男'));
		echo '<pre>';
		print_r($_REQUEST);
		exit;
		/*$model = new \Model\Test();

		$model->testInsert();*/
	}
}