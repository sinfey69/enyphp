<?php

/**
 * 钩子选项
 * 可使用的钩点
 	* prevSystem      - 程序最初
 	* prevController  - 创建控制器之前
 	* initController  - 创建控制器后调用方法之前
 	* afterController - 控制器执行结束后 
 * 如果要写在一个钩点挂载多个钩子,写成$config['hook']['钩点'][] = array()
 */

/*$config['hook']['prevSystem'] = array(
	'class'=>'hookTest',
	'function'=>'index',
	'params'=>array(),
);*/