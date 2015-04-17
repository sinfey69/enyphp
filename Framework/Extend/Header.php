<?php

namespace Common;

class Header
{
	/**
	 * 允许跨域设置cookie
	 */
	public static function P3P()
	{
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	}

	/**
	 * 设置字符集
	 */
	public static function contentType()
	{
		header('Content-Type: text/html;charset=UTF-8');
	}

	/**
	 * 页面跳转
	 * @param string 跳转地址
	 */
	public static function location($url)
	{
		header("Location: {$url}");
	}

	/**
	 * 缓存控制
	 */
	public function cacheControl()
	{
		header('Cache-Control:private, max-age=0, no-cache, must-revalidate, no-cache=Set-Cookie, proxy-revalidate');
	}
}

