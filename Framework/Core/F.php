<?php
/**
 * 公用函数库
 * @author enychen
 */

namespace Core;

class F
{
	/**
	 * 错误跳转
	 * @param string
	 */
	const REDIRECT_ERROR = 'error';

	/**
	 * 警告跳转
	 * @param string
	 */
	const REDIRECT_WARNING = 'warning';

	/**
	 * 正确跳转
	 * @param string
	 */
	const REDIRECT_SUCCESS = 'success';

	private static $lang = 'zh-cn';

	/**
	 * 多语言
	 * @param string key键
	 * @return string
	 */
	public static function lang($key)
	{
		if(!self::$lang)
		{
			// 设置默认值
			self::$lang = 'zh-cn';
			// 遍历查询
			if($language = server('HTTP_ACCEPT_LANGUAGE'))
			{
				$types = array('zh-cn', 'zh', 'en', 'fr', 'de', 'jp', 'ko', 'es', 'sv');
				foreach($types as $type)
				{
					$pattern = "/{$type}/i";
					if(preg_match($pattern, $language))
					{
						self::$lang = $type;
						break;
					}
				}
			}
		}

		// 读取语言包信息
		$package = require(LANGUAGE.self::$lang.'.php');

		return isset($package[$key]) ? $package[$key] : NULL;
	}

	/**
	 * 获得$_SERVER中的内容
	 * @param  string 下标值
	 * @param  找不到值后的默认值
	 * @return mixed 找到返回内容,否则返回第二个参数的内容
	 */
	public static function server($index, $defualt=NULL)
	{
		return empty($_SERVER[$index]) ? $defualt : addslashes($_SERVER[$index]);
	}

	/**
	 * 获得文件的完整路径并创建目录
	 * @param string 文件名
	 * @param string 基准目录
	 * @return string 绝对路径文件名
	 */
	public static function absFile($path, $abs='./')
	{
		// 文件信息
		$path = trim($path, '/');
		$dirname = dirname($path);
		$basename = basename($path);
		// 完整目录
		$path = sprintf('%s%s/', $abs, $dirname);
		// 不存在目录则递归创建目录
		is_dir($path) OR mkdir($path, 0750, TRUE);
		// 返回文件的绝对路径
		return sprintf("%s[%s]%s", $path, date('Y-m-d'), $basename);
	}

	/**
	 * 把数组转成对象
	 * @param array
	 * @return object
	 */
	public static function toObject($array)
	{
		return json_decode(json_encode($array));
	}

	/**
	 * 操作提示跳转
	 * @param string 错误信息
	 * @param mixed 跳转地址,可以自定义成array('url'=>title)形式
	 */
	public static function redirect($type, $message, $url=NULL)
	{
		// 操作类型
		$data['type'] = $type;
		// 提示信息
		$data['message'] = $message;
		// 跳转地址
		switch(TRUE)
		{
			case is_array($url):
				$data['url'] = $url;
				$data['autoback'] = FALSE;
				break;
			case is_null($url):
				$url = F::server('HTTP_REFERER', self::server('HTTP_HOST'));
			default:
				$data['url'] = array($url=>'5秒后自动返回.如果没有成功,请点击此处');
				$data['autoback'] = TRUE;
		}

		// 加载模版
		$view = new \Mvc\View();
		$view->display("common/notice", $data, NULL, FALSE);
		exit;
	}
}