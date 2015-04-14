<?php

namespace Controller;
use \Mvc\Controller;

class Index extends Controller
{
	/**
	 *
	 */
	public function enter()
	{
		$model = new \Model\Test();

		$model->testInsert();
	}
}