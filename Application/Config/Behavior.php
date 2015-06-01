<?php

/**
 * 全局设置
 */
$config['global']['timezone'] = 'Asia/Shanghai';	//时区

/**
 * 路由默认信息
 */
$config['routes']['class'] = 'Home';	// 默认入口控制器
$config['routes']['function'] = 'index';	// 默认方法
$config['routes']['suffix'] = 'html';	// 伪静态后缀


/**
 * 视图配置信息
 */
$config['view']['theme'] = '';	// 主题文件夹
$config['view']['cache'] = FALSE;  // 是否真缓存
$config['view']['expire'] = 1440;	// 真缓存过期时间,0表示不过期


/**
 * session环境信息
 */
$config['session']['handler'] = 'files';	// 存储方式, 可选：files | redis | memcached
$config['session']['path'] = SESSION;	// 存储位置, 如果是缓存请写具体的 ip:port
$config['session']['name'] = 'enyphp';	// SESSION名称
$config['session']['expire'] = 0;		// 过期时间, 0表示不过期


/**
 * 文件上传
 */
$config['upload']['ext'] = array('jpg', 'jpeg', 'png', 'gif'); 	// 文件类型
$config['upload']['size'] = 5096;				// 文件大小，按kb计算
$config['upload']['path'] = BOOTSTRAP."files/";		// 上传位置
