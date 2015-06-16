<?php
/**
 * 日志记录
 * @author enychen
 */

namespace Core;

class Log
{
	/**
	 * 服务器错误日志记录
	 * @param int 错误代码
	 * @param string 错误信息
	 * @param string 错误发生的文件
	 * @param int 错误发生信息
	 * @return void
	 */
	public static function error($code, $message, $file, $line)
	{
		// 错误符号
		$error = array(
			0 => 'E_EXCEPTION',
			1 => 'E_ERROR',
			2 => 'E_WARNING',
			4 => 'E_PARSE',
			8 => 'E_NOTICE',
			16 => 'E_CORE_ERROR',
			32 => 'E_CORE_WARNING',
			64 => 'E_COMPILE_ERROR',
			128 => 'E_COMPILE_WARNING',
			256 => 'E_USER_ERROR',
			512 => 'E_USER_WARNING',
			1024 => 'E_USER_NOTICE',
			2048 => 'E_STRICT',
			4096 => 'E_RECOVERABLE_ERROR',
			8192 => 'E_DEPRECATED',
			16384 => 'E_USER_DEPRECATED',
			32767 => 'E_ALL',
		);
		// 错误整理
		$error = array('TYPE'=>$error[$code], 'MESSAGE'=>$message, 'FILE'=>$file, 'LINE'=>$line);
		// 获得格式化后的日志
		$content = self::content($error);
		// 文件名
		$file = "error/runtime";
		// 写入日志
		self::write($file, $content);
	}

	/**
	 * 通用日志记录方法
	 * @param string 文件名，可包含路径
	 * @param mixed 信息
	 */
	public static function log()
	{
		// 获取文件信息
		$args = func_get_args();
		// 文件名
		$filename = array_splice($args, 0, 1)[0];
		// 内容
		$content = self::content($args);
		// 写入
		self::write($filename, $content);
	}

	/**
	 * 日志记录
	 * @param string 文件名
	 * @param string 内容
	 */
	private static function write($file, $content)
	{
		// 目录是否存在
		$file = F::absFile("{$file}.log", LOG);
		// 日志记录
		file_put_contents($file, $content, FILE_APPEND);
	}

	/**
	 * 格式化输出内容
	 * @param mixed 内容
	 * @return void
	 */
	private static function content($content)
	{
		$content = is_array($content) ? $content[0] : array($content);
		// 第一条内容
		$rule = "[%s]" . PHP_EOL;
		$args = array(date('H:i:s'));
		// 遍历要输出的内容
		foreach($content as $key=>$val)
		{
			// 拼写规则
			$rule .= is_numeric($key) ? "%s".PHP_EOL : $key . ": %s".PHP_EOL;
			// 要替换的字符串
			$args[] = $val;
		}
		$rule .= PHP_EOL;
		// 格式化规则
		array_unshift($args, $rule);

		return call_user_func_array('sprintf', $args);
	}
}