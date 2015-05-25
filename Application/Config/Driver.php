<?php

/**
 * Mysql配置
 * 可以配置多个数据库,具体如下
 * $config['mysql'][编号][配置选项]
 */
$config['mysql'][0]['identify'] = 'master';		// 主库
$config['mysql'][0]['host'] = '192.168.10.216';	// 主机
$config['mysql'][0]['port'] = '3306';			// 端口
$config['mysql'][0]['user'] = 'php';			// 用户
$config['mysql'][0]['password'] = 'enamephp';	// 密码
$config['mysql'][0]['charset'] = 'utf8';		// 字符集
$config['mysql'][0]['dbname'] = "test";			// 数据库

/**
 * redis配置
 * $config['redis'][编号][配置选项]
 */
$config['redis'][0]['host'] = "127.0.0.1";	// 主机
$config['redis'][0]['port'] = 6379;			// 端口
$config['redis'][0]['timeout'] = 3;			// 超时时间
$config['redis'][0]['password'] = NULL;		// 密码，没有设置为空

/**
 * memcached配置
 * $config['memcached'][编号][配置选项]
 */
$config['memcached'][0]['host'] = "127.0.0.1"; 	// 主机地址
$config['memcached'][0]['port'] = 11211;		// 端口
$config['memcached'][0]['weight'] = NULL;	// 权限比，不设置为NULL
