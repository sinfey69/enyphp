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
		$data = array(
			'list'=>array(1,2,3,4,5,6,7,8,9),
			'website'=>array('e.enychen.com','t.enychen.com'),
			'author'=>'enychen',
		);

		$this->view->display(REQUEST_FILE, $data, $_REQUEST[0]);
		/*$model = new \Model\Test();

		$model->testInsert();*/
	}
}