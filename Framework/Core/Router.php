<?php

namespace Core;

/**
 * 路由调度类
 * @author chenxb
 */
class Route
{
	/**
	 * 默认路由器
	 * @var <object>
	 */
	private static $routes = NULL;

	/**
	 * 路由分析
	 * @param <stdClass> 路由对象，包括class,function,suffix属性
	 * @return void
	 */
	public static function dispatch(\stdClass $routes)
	{
		// 初始化环境
		self::initialize();
		// 初始化配置
		self::setRoutes($routes);
		// 获取路由来源
		self::source();
		// 设置请求信息
		self::setRequest();
	}

	/**
	 * 初始化请求环境
	 * @return void
	 */
	private static function initialize()
	{
		define('IS_CLI', !strcasecmp(php_sapi_name(), 'cli'));// 命令行模式
		define('IS_GET', self::server('REQUEST_METHOD')=='GET');// get请求
		define('IS_POST',self::server('REQUEST_METHOD')=='POST');// post请求
		define('IS_PUT',self::server('REQUEST_METHOD')=='PUT');// put请求
		define('IS_DELETE',self::server('REQUEST_METHOD')=='DELETE');// delete请求
		define('IS_AJAX',strcasecmp(server('REQUEST_METHOD'),'xmlhttprequest'));//ajax请求
	}

	/**
	 * 初始化控制器信息
	 * @param array 剩下的路由信息
	 * @return void
	 */
	private static function setController($routes)
	{
		// 类名首字母大写
		self::$routes->class = ucfirst(self::$routes->class);
		// 通用文件名
		define('REQUEST_FILE', self::$routes->class."/".self::$routes->function);
		// 设置控制器全称
		self::$routes->class = '\\Controller\\'.self::$routes->class;
		// 判断文件是否存在
		if(!method_exists(self::$routes->class, self::$routes->function))
		{
			throw new \Exception('404 NOT FOUND', 404);
		}
	}

	/**
	 * 数据来源
	 */
	private static function source(routes)
	{
		if(IS_CLI)
		{
			$routes = $_SERVER['argv'];
			array_splice($routes, 0, 1);
		}
		else
		{
			$routes = trim(server('PATH_INFO'), '/');
			$routes = str_replace('.'.self::$routes->suffix, '', $routes);
			$routes = $routes ? explode('/', $routes) : array();
		}
	}

	private static function setRequest()
	{
		foreach(array('class', 'function') as $val)
		{
			if(empty($routes[0]) || is_numeric($routes[0]))
			{
				break;
			}
			if(!preg_match('/^([a-z])+$/', $routes[0])) 
			{
				throw new \Exception('403 FORBIDDEN', 403);
			}
			self::$routes->$val = $routes[0];
			array_splice($routes, 0, 1);
		}
		self::setRequest($routes);
	}

	/**
	 * 初始化路由对象
	 * @param <stdClass> 路由对象，包括class,function,suffix属性
	 * @return void
	 */
	private static function setRoutes($routes)
	{		
		self::$routes = $routes;
	}

	/**
	 * 设置请求的信息
	 * @param <array> pathinfo信息中附带的内容
	 * @return void
	 */
	private static function setRequest($arguments)
	{
		$_REQUEST = $arguments;
	}
}

/**
 * 请求信息
 */
class Request
{
	/**
	 * 获取$_SERVER信息
	 * @param <string> 下标值
	 * @param <mixed> 不存在返回的值
	 * @return <string>
	 */
	public static function server($index, $default=NULL)
	{
		return empty($_SERVER[$index]) ? $defualt : addslashes($_SERVER[$index]);
	}

	/**
 	 * 获取ip地址
	 */
	public static function ip()
	{		
		if(IS_CLI)
		{
			return '127.0.0.1'; 
		}

		$ip = NULL;
		$froms = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'REMOTE_ADDR'
		);
		foreach($froms as $from)
		{
			if($ip = getenv($from))
			{
				break;
			}
		}

		return $ip;
	}

	/**
	 * 是否手机端请求
	 * @return boolean
	 */
	public static function isMoblie()
	{
		if($userAgent = server('HTTP_USER_AGENT'))
		{
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
			foreach($mobileType as $device)
			{
				if(stristr($userAgent, $device))
				{
					return TRUE;
				}
			}
		}		
		return FALSE;
	}
}