<?php

/**
 * 路由默认信息
 * - class 		默认控制器
 * - function 	默认方法
 * - suffix		伪静态后缀
 */
$config['routes']['class'] = 'Home';
$config['routes']['function'] = 'index';
$config['routes']['suffix'] = 'html';

/**
 * 视图配置信息
 * - theme 	主题包
 * - expire 过期时间
 * - cache	是否真缓存
 */
$config['view']['theme'] = '';
$config['view']['expire'] = '1440';
$config['view']['cache'] = FALSE;

/**
 * session环境
 * - handler 使用session的方式, 可选：files | mysql | redis | memcached
 * - path    session存放地, files写一个文件目录, memcached|redis|mysql 写要使用的编号,具体编号在driver.php配置文件中
 * - name    session名称
 * - expire  过期时间,0表示永不过期
 */
$config['session']['handler'] = 'redis';
$config['session']['path'] = 0;
$config['session']['name'] = 'enyphp';
$config['session']['expire'] = 0;