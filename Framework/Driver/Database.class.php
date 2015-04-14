<?php

namespace Driver;

/**
 * 数据库公共接口
 */
interface Database
{
	/**
	 * 获得数据库对象
	 * @param  array  数据库配置
	 * @return object 数据库对象
	 */
	static function instance($drivers);

	function query($query, $params, $debug);

	function fetch($type);

	function lastInsertId();

	function affectRow();
}