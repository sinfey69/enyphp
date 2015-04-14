<?php

namespace Core;

/**
 * 路由分析类
 * @author Eny
 */
class Dispatch
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
		// 初始化请求环境
		self::init();
		// 获取默认的路由配置
		self::$routes = Config::G('routes');
		// 获取请求的路径
		$routes = self::getRouter();
		// 设置路由信息
		self::setRouter($routes);
		// 路由结果返回
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
	 * @param object 请求对象
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
			$routes = empty($_SERVER['PATH_INFO']) ? NULL : trim($_SERVER['PATH_INFO'], '/');
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
			if(!preg_match('/^([a-zA-Z])+$/', $routes[0])) 
			{
				// 禁止操作
				self::location(403);
			}
			// 路由设置
			self::$routes[$val] = $routes[0];
			array_splice($routes, 0, 1);
		}

		// 定义通用文件名
		define('COMMON_FILE', self::$routes['class']."/".self::$routes['function']);

		// 控制器信息
		self::$routes['class'] = '\\Controller\\' . ucfirst(self::$routes['class']);
		self::$routes['function'] = strtolower(self::$routes['function']);
		
		// 控制器是否存在
		method_exists(self::$routes['class'], self::$routes['function']) OR self::location(404);

		// url请求参数设置到$_REQUEST;
		$_REQUEST = $routes;
	}

	/**
	 * 获得$_SERVER中的内容
	 * @param  string 下标值
	 * @param  找不到值后的默认值
	 * @return mixed 找到返回内容,否则返回第二个参数的内容
	 */
	public static function server($index, $defualt=FALSE)
	{
		return empty($_SERVER[$index]) ? $defualt : addslashes($_SERVER[$index]);
	}

	/**
	 * 是否手机端请求
	 * @return boolean
	 */
	private static function isMoblie()
	{
		$isMoblie = FALSE;
		if($userAgent = self::server('HTTP_USER_AGENT'))
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
	 * 获取客户端的ip地址
	 * @return mixed 找到返回ip地址,否则返回FALSE
	 */
	public static function ip()
	{
		if(IS_CLI)
		{
			return '0.0.0.0';
		}

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
	 * 获取浏览器语言,目前只识别中英法德日韩西瑞
	 * @param string 默认语言
	 * @return string
	 */
	public function language($default='zh-cn')
	{
		if($language = self::server('HTTP_ACCEPT_LANGUAGE'))
		{
			$types = array('zh-cn', 'zh', 'en', 'fr', 'de', 'jp', 'ko', 'es', 'sv');
			foreach($types as $type)
			{
				$pattern = "/{$type}/i";
				if(preg_match($pattern, $language))
				{
					return $type;
				}
			}
		}
		return $default;
	}

	/**
	 * 页面跳转
	 * @param int 错误代码 
	 * @return void
	 */
	public static function location($code)
	{
		header("Location: /{$code}.html");
	}
}