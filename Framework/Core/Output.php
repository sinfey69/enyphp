<?php
/**
 * Response类
 * @author enychen
 */

namespace Core;

class Output
{

	/**
	 * 模版名
	 * @var string
	 */
	private static $template = REQUEST_FILE;

	/**
	 * 禁止使用模版
	 * @var boolean
	 */
	private static $disable = FALSE;

	/**
	 * 修改要显示的模版
	 * @param string 要显示的模版
	 * @return void
	 */
	public static function setTemplate($template)
	{
		self::$template = $template;
	}

	/**
	 * 禁止使用模版
	 * @return void
	 */
	public static function disableView()
	{
		self::$disable = TRUE;
	}

	/**
	 * 响应输出
	 */
	public static function response()
	{
		IS_AJAX ? self::view() : self::json();
	}

	/**
	 * 系统错误响应
	 * @return void
	 */
	public static function serverError()
	{		
		// 抛出500服务器错误
		header("Location: /50x.html");
	}

	/**
	 * 模版输出
	 */
	private static function view()
	{

	}

	/**
	 * json输出
	 */
	private static function json()
	{

	}
}