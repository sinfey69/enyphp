<?php
/**
 * HTTP请求类
 * @author enychen
 */

namespace Extend;

class Http
{
	/**
	 * GET请求
	 * @var string
	 */
	const GET = "GET";

	/**
	 * POST请求
	 * @var string
	 */
	const POST = "POST";

	/**
	 * 使用file_get_contents执行请求，目前只支持GET和POST请求
	 * @param string 请求地址
	 * @param array  附加参数
	 * @param string 执行方法
	 * @param array  附加头信息,需要自己写完整的头部信息
	 * @return string 获得的信息
	 */
	public static function fgcRequest($url, $data=array(), $method='GET', array $header=array())
	{
		// 选项设置
		$options = array(
			'http'=>array(
				'method'=>strtoupper($method),
			),
		);
		// 选项设置
		switch(TRUE)
		{
			case !empty($header):
				// 头信息设置
				$options['http']['header'] = implode("\r\n", $header);
			case !strcasecmp($method, 'POST'):
				// POST参数设置
				$options['http']['content'] = $data;
				break;
			case !strcasecmp($method, 'GET'):
				// GET完整路径拼接
				$url = $data ? "{$url}?".http_build_query($data) : $url;
				break;
		}
		// 创建上下文
		$context = stream_context_create($options);

		// 执行请求
		return file_get_contents("http://{$url}",false, $context);
	}

	/**
	 * 使用curl执行请求
	 * @param string 请求地址
	 * @param array  附加参数
	 * @param string 执行方法
	 * @param array  附加头信息,参照curl_setopt进行键值对设置
	 * @return string 获得的信息
	 */
	public static function curlRequest($url, $data=array(), $method='GET', array $header=array())
	{
		// curl创建
		$ch = curl_init();
		// 选项设置
		switch(TRUE)
		{
			case !strcasecmp($method, 'POST'):
				// POST参数设置
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case !strcasecmp($method, 'GET'):
				// GET完整路径拼接
				$url = $data ? "{$url}?".http_build_query($data) : $url;
				break;
		}
		// 默认设置头信息
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 自定义头信息
		foreach($header as $key=>$val)
		{
			curl_setopt($ch, constant($key), $val);
		}
		// curl执行
		$result = curl_exec($ch);
		// 关闭curl
		curl_close($ch);

		return $result;
	}
}