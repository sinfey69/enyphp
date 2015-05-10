<?php
/**
 * 邮件类
 * @author enychen
 */

namespace Extend;

class Mail
{
	private static $fs;
	
	private static function init()
	{
		self::$fs = fsockopen($name);
	}
	
	/**
	 * 发送邮件
	 */
	public static function send()
	{

	}

	/**
	 * 接收邮件
	 */
	public static function get()
	{

	}
}