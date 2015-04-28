<?php
/**
 * 头信息输出
 * @author enychen
 */

namespace Extend;

class Header
{
	/**
	 * 允许跨域设置cookie
	 * @return void
	 */
	public static function P3P()
	{
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	}

	/**
	 * 页面跳转
	 * @param string 跳转地址
	 * @return void
	 */
	public static function location($url)
	{
		header("Location: /{$url}");
	}

	/**
	 * 缓存控制
	 * @return void
	 */
	public static function cacheControl()
	{
		header('Cache-Control:private, max-age=0, no-cache, must-revalidate, no-cache=Set-Cookie, proxy-revalidate');
	}
}

