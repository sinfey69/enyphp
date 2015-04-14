<?php

/**
 * 钩子配置 $config['hook']['point']
 * $config['hook']['preSystem'] = array();
 * prevSystem | prevController | nextController
 *
 * $config['hook']['prevSystem'][] = array(
 * 	'filename'=>'Test.class.php',
 *  	'class'=>'\Test',
 *   	'function'=>'index',
 *    	'param'=>array(),
 * );
 */

$config['hook']['beforeController'][] = array(
	'class'=>'\Plugin'
);