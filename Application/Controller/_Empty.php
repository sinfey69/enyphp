<?php

namespace Controller;

use \Mvc\Controller;

/**
 * 空控制器,用于处理各种错误,无法直接调用
 */
class _Empty extends Controller
{
	/**
	 * 403跳转
	 */
	public static function _403()
	{
		exit("<style>h1,div{text-align:center}</style><h1>403 FORBIDDEN</h1><hr/><div>Email:chenxiaobo_901021@yeah.net</div>");
	}


	/**
	 * 404跳转
	 */
	public static function _404()
	{
		exit("<style>h1,div{text-align:center}</style><h1>404 NOT FOUND</h1><hr/><div>Email:chenxiaobo_901021@yeah.net</div>");
	}

	/**
	 * 500跳转
	 */
	public static function _500()
	{
		exit("<style>h1,div{text-align:center}</style><h1>500 SERVER ERROR</h1><hr/><div>Email:chenxiaobo_901021@yeah.net</div>");
	}

	/**
	 * 操作错误提示
	 * @param string 错误提示
	 * @param int 错误代码
	 * @param string 错误的跳转路径
	 * @return void
	 */
	public static function _E($message, $code=NULL, $url=NULL)
	{

	}

	/**
	 * 操作警告提示
	 * @param string 错误提示
	 * @param int 错误代码
	 * @param string 错误的跳转路径
	 * @return void
	 */
	public static function _W($message, $code=NULL, $url=NULL)
	{

	}

	/**
	 * 操作成功提示
	 * @param string 错误提示
	 * @param int 错误代码
	 * @param string 错误的跳转路径
	 * @return void
	 */
	public static function _S($message, $code=NULL, $url=NULL)
	{

	}
}