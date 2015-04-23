<?php

/**
 * 路由默认信息
 */
$config['routes']['class'] = 'Home';
$config['routes']['function'] = 'index';
$config['routes']['suffix'] = 'html';

/**
 * 视图配置信息
 */
$config['view']['theme'] = '';
$config['view']['expire'] = '1440';
$config['view']['cache'] = FALSE;

/**
 * session环境
 */
$config['session']['handler'] = 'mysql';  # files | user | redis | memcached | memcache
$config['session']['path'] = SESSION;     # 如果设置为mysql|redis|memcached,此处不设置则表示使用通用的mysql配置
$config['session']['name'] = 'enyphp';
$config['session']['expire'] = 10;