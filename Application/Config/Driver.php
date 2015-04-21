<?php

/**
 * Mysql配置
 * 可以配置多个数据库,具体如下
 * $config['database'][数据库编号][数据库配置选项]
 */
$config['mysql'][0]['host'] = '192.168.10.216';
$config['mysql'][0]['port'] = '3306';
$config['mysql'][0]['user'] = 'php';
$config['mysql'][0]['password'] = 'enamephp';
$config['mysql'][0]['charset'] = 'utf8';
$config['mysql'][0]['dbname'] = "test";


/**
 * redis配置
 * 可以配置多个redis,3.0之前得手动集群,暂不实现
 * $config['redis'][redis编号][redis配置选项]
 */
$config['reids'][0]['host'] = "192.168.200.92";
$config['redis'][0]['port'] = 6379;
$config['redis'][0]['db'] = 0;
$config['redis'][0]['password'] = "";