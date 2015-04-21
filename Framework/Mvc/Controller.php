<?php

namespace Mvc;

/**
 * 控制基类
 * @author Eny
 */
abstract class Controller
{
	/**
	 * 模版对象
	 * @var object
	 */
	protected $view;

	/**
	 * 构造函数
	 * @return void
	 */
	public final function __construct()
	{
		// 创造视图对象
		$this->view = new \Mvc\View();	
	}
}