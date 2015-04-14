<?php

namespace Core;

/**
 * 日志记录
 */
class Log
{
	/**
	 * 日志目录
	 * @var string
	 */
	private $path;

	/**
	 * 创建对象
	 * @param string 日志目录
	 */
	public function __construct($path='./')
	{
		$this->path = $path;
	}

	/**
	 * 服务器错误日志记录
	 * @param int 错误代码
	 * @param string 错误信息
	 * @param string 错误发生的文件
	 * @param int 错误发生信息
	 */
	public function systemError($code, $message, $file, $line)
	{
		// 将错误数字转成对应的错误常量名
		$error = get_defined_constants();
		$error = array_splice($error, 0, 16);
		$code  = array_search($code, $error);
		$error = array('TYPE: '=>$code, 'MESSAGE: '=>$message, 'FILE: '=>$file, 'LINE: '=>$line);
		
		// 获得格式化后的日志
		$content = $this->content($error);
		// 文件名
		$file = sprintf("syserror/%s", date('Y-m-d'));

		// 写入日志
		$this->write($file, $content);
	}

	/**
	 * 通用日志记录方法
	 */
	public function record()
	{
		// 获取文件信息
		$args = func_get_args();

		// 文件名
		$filename = array_splice($args, 0, 1)[0];
		// 内容
		$content = $this->content($args);
		// 写入
		$this->write($filename, $content);
	}

	/**
	 * 日志记录
	 * @param string 文件名
	 * @param string 内容
	 */
	private function write($file, $content)
	{
		// 目录是否存在
		$file = $this->filename($file);

		// 日志记录
		file_put_contents($file, $content, FILE_APPEND);
	}

	/**
	 * 获得文件的完整路径
	 * @param 文件名
	 * @return string
	 */
	private function filename($path)
	{
		// 文件信息
		$path = trim($path, '/');
		$dirname = dirname($path);
		$basename = basename($path);

		// 日志完整目录
		$path =  sprintf('%s%s/',  $this->path, $dirname);

		// 不存在目录则递归创建目录
		is_dir($path) OR mkdir($path, 0755, TRUE);

		// 返回文件的绝对路径
		return sprintf("%s%s.log", $path, $basename);
	}

	/**
	 * 格式化输出内容
	 * @param mixed 内容
	 */
	private function content($content)
	{
		$content = is_array($content) ? $content : array($content);

		// 第一条内容
		$rule = "[%s]" . PHP_EOL;
		$args = array(date('Y-m-d H:i:s'));

		// 遍历要输出的内容
		foreach($content as $key=>$val)
		{
			// 拼写规则
			$rule .= is_numeric($key) ? "%s\r\n" : $key . "%s\r\n";

			// 要替换的字符串
			$args[] = $val;
		}
		$rule .= PHP_EOL;

		// 格式化规则
		array_unshift($args, $rule);

		return call_user_func_array('sprintf', $args);
	}
}