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
	 * 修改要显示的模版
	 * @param string 要显示的模版
	 * @return void
	 */
	public static function setTemplate($template)
	{
		self::$template = $template;
	}

	/**
	 * 响应输出
	 */
	public static function response($output)
	{
		IS_AJAX ? self::view($output) : self::json($output);
	}

	/**
	 * 模版输出
	 */
	private static function view()
	{
		$view = new \Mvc\View();		
		$view->display(self::$template, $output);
	}

	/**
	 * json输出
	 */
	private static function json($output, $status=1, $url="")
	{
		$output['status'] = $status;
		$output['url'] = $url;
		exit(json_encode($output));
	}
	
	public static function _403()
	{
		
	}
	
	public static function _404()
	{
		
	}
	
	/**
	 * 系统错误响应
	 * @return void
	 */
	public static function _50x()
	{
		if(IS_AJAX)
		{
			$error = array(msg=>"系统错误");
			self::json($error, 0);
		}
		else
		{
			// 抛出500服务器错误
			header("Location: /50x.html");
		}
	}
}