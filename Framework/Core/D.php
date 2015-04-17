<?php

namespace Core;

use \Mvc\View;

/**
 * 路由分析类
 * @author Eny
 */
class D
{
	/**
	 * 默认路由器
	 * @param object
	 */
	private static $routes = NULL;

	/**
	 * 分析获得控制器和路由
	 * @return void 
	 */
	public static function parseUrl()
	{
		// 初始化路由环境
		self::init();
		// 初始化文件
		self::$routes = C::routes();
		// 获取请求的路径
		$routes = self::getRouter();
		// 设置路由信息
		$routes = self::setRouter($routes);
		// 设置调用信息
		self::setController($routes);
		// 真缓存检查
		!C::view('cache') OR self::really();
		// 返回信息
		return array(self::$routes['class'], self::$routes['function']);
	}

	/**
	 * 初始化请求环境
	 * @return void
	 */
	private static function init()
	{
        define('IS_GET',$_SERVER['REQUEST_METHOD']=='GET');// GET请求
        define('IS_POST',$_SERVER['REQUEST_METHOD']=='POST');// POST请求
       	//define('IS_PUT',$_SERVER['REQUEST_METHOD']=='PUT');// PUT请求
        //define('IS_DELETE',$_SERVER['REQUEST_METHOD']=='DELETE');// DELETE请求
        define('IS_AJAX',strcasecmp($_SERVER['REQUEST_METHOD'],'xmlhttprequest'));// AJAX请求
        define('IS_MOBILE', self::isMoblie());// 是否是手机端
	}

	/**
	 * 获得请求的路由
	 * @return array
	 */
	private static function getRouter()
	{
		if(IS_CLI)
		{
			$routes =$_SERVER['argv'];;
			array_splice($routes, 0, 1);
		}
		else
		{
			$routes = trim(F::server('PATH_INFO', NULL), '/');
			str_replace('.'.self::$routes['suffix'], '',  $routes);
			$routes = explode('/', $routes);
		}

		return $routes;
	}

	/**
	 * 设置路由的信息
	 * @param array path_info的信息数组
	 * @return void
	 */
	private static function setRouter($routes)
	{
		// 设定请求的内容
		foreach(array('class', 'function') as $val)
		{
			// 如果是数组
			if(empty($routes[0]) || is_numeric($routes[0]))
			{
				break;
			}
			// 非法请求参数
			if(!preg_match('/^([a-z])+$/', $routes[0])) 
			{
				// 禁止操作
				throw new \Exception(403);
			}
			// 路由设置
			self::$routes[$val] = $routes[0];
			array_splice($routes, 0, 1);
		}

		return $routes;
	}

	/**
	 * 初始化控制器信息
	 * @param array 剩下的路由信息
	 * @return void
	 */
	private static function setController($routes)
	{
		// 定义通用文件名
		define('REQUEST_FILE', self::$routes['class']."/".self::$routes['function']);
		// 文件名首字母大写
		self::$routes['class'] = '\\Controller\\'.ucfirst(self::$routes['class']);
		// 判断文件是否存在
		if(!method_exists(self::$routes['class'], self::$routes['function']))
		{
			throw new \Exception(404);
		}
		// url请求参数设置到$_REQUEST;
		$_REQUEST = $routes;
	}

	/**
	 * 是否手机端请求
	 * @return boolean
	 */
	private static function isMoblie()
	{
		$isMoblie = FALSE;
		if($userAgent = F::server('HTTP_USER_AGENT'))
		{
			// 手机代理信息
			$mobileType = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi",
			"android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio",
			"au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-",
			"compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_",
			"fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc",
			"huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo",
			"lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator",
			"meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront",
			"newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm",
			"panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover",
			"sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens",
			"sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit",
			"tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda",
			"voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
			// 头信息匹配
			foreach ($mobileType as $device)
			{
				if (stristr($userAgent, $device))
				{
					$isMoblie = TRUE;
					break;
				}
			}
		}
		
		return $isMoblie;
	}

	/**
	 * 真缓存直接加载文件
	 * @return void
	 */
	public static function really()
	{
		$view = new View();
		if($view->isCache(REQUEST_FILE, (isset($_REQUEST[0]) ? "{$_REQUEST[0]}" : NULL)))
		{
			exit;
		}
	}
}