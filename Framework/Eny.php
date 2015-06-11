<?php
/**
 * 核心框架类
 * @author enychen
 */

use
\Core\Hook,// 钩子类
\Core\Input,// 输入类
\Core\Log,// 日志类
\Core\Output,// 输出类
\Core\Router,// 路由类
\Core\Session;// session类

class Eny
{
	/**
	 * 初始化
	 * @return void
	 */
	public static function boot()
	{
		// 目录定义
		self::structure();
		// 加载配置
		self::configure();
		// 初始化环境
		self::environment();
		// 程序运行
		self::application();
	}

	/**
	 * 目录常量定义
	 * @return void
	 */
	private static function structure()
	{
		define('FCPATH', dirname(__DIR__).'/');// 站点目录
		define('APPLICATION', FCPATH.'Application/');// 项目目录
		define('FRAMEWORK', FCPATH.'Framework/');// 框架目录	
		define('BOOTSTRAP', FCPATH.'Bootstrap/');// 公共目录
		define('CONFIG', APPLICATION.'Config/');// 配置目录
		define('VALIDATE', APPLICATION.'Validate/');// 验证目录
		define('LANGUAGE', APPLICATION.'Language/');// 语言包目录
		define('VIEW', APPLICATION.'View/');// 模板目录
		define('PLUGIN', APPLICATION.'Plugin/');// 插件目录
		define('DATA', APPLICATION.'Data/');// 数据目录
		define('LOG', DATA.'Log/');// 日志目录
		define('CACHE', DATA.'Cache/');// 缓存目录
		define('COMPILE', DATA.'Compile/');// 编译文件
		define('FONT', DATA.'Font/');// 字体目录
		define('LOCK', DATA.'Lock/');// 锁目录
		define('SESSION', DATA.'Session/');// SESSION目录
	}
	
	/**
	 * 初始化环境
	 * @return void
	 */
	private static function environment()
	{
		// 全局函数
		require(FRAMEWORK.'Core/Function.php');
		// 通用常量定义
		defined('DEBUG') OR define('DEBUG',FALSE);// 调试模式
		define('IS_CLI', !strcasecmp(php_sapi_name(), 'cli'));// 命令行模式
		define('IS_GET',server('REQUEST_METHOD')=='GET');// get请求
		define('IS_POST',server('REQUEST_METHOD')=='POST');// post请求
		define('IS_PUT',server('REQUEST_METHOD')=='PUT');// put请求
		define('IS_DELETE',server('REQUEST_METHOD')=='DELETE');// delete请求
		define('IS_AJAX',strcasecmp(server('REQUEST_METHOD'),'xmlhttprequest'));//ajax请求
		define('IS_MOBILE',isMoblie());//手机端请求
		define('CLIENT_IP',ip());//ip地址
		IS_CLI OR header('Content-Type:text/html;charset=UTF-8');// 字符集设置
		date_default_timezone_set(C('global', 'timezone'));// 日期设置
		spl_autoload_register('Eny::appAutoload'); // 自动加载机制
		if(!DEBUG)
		{
			set_exception_handler('Eny::appException');// 自定义异常机制
			set_error_handler('Eny::appError');// 自定义错误机制
			register_shutdown_function('Eny::appShutdown');// 程序退出前回调机制
			error_reporting(0);// 关闭报错
			ini_set('display_errors','off');// 关闭报错
		}		
	}

	/**
	 * 程序执行
	 * @return void
	 */
	private static function application()
	{		
		// 路由解析
		list($class, $function) = Router::dispatch();
		// 数据检查
		Input::validity();
		// session初始化
		Session::initialize();
		// 控制器运行前的钩子
		Hook::runHook('prevController');
		// 创建控制器
		$controller = new $class();
		// 控制器执行前
		!method_exists($controller, '_init') OR $controller->_init();
		// 调用控制器函数
		$controller->$function();
		// 控制器执行后
		!method_exists($controller, '_end') OR $controller->_end();
 	}

 	/**
 	 * 加载配置
 	 * @return void
 	 */
 	private static function configure()
 	{
 		// 加载所有配置文件
		$master = glob(CONFIG .'*.php');
		$dev = DEBUG ? glob(CONFIG."dev/*.php") : array();
		// 加载配置
		foreach(array_merge($master, $dev) as $file)
		{
			require($file);
			$temp = array_merge($config, $temp);
		}
		$GLOBALS['_CFG'] = $temp;
 	}

	/**
	 * 自动加载机制
	 * @param string 类名
	 * @return void
	 */
	public static function appAutoload($class)
	{
		// 文件行号转换
		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		// 遍历目录
		foreach(array(APPLICATION, FRAMEWORK) as $dir)
		{
			// 完整文件名
			$file = "{$dir}{$class}.php";
			//是否加载
			if(is_file($file))
			{
				require($file);
				break;
			}
		}
	}

	/**
	 * 自定义错误处理机制
	 * @param int 错误代码
	 * @param string 错误信息
	 * @param string 错误文件
	 * @param int 错误行号
	 * @return void
	 */
	public static function appError($errno, $errstr, $errfile, $errline)
	{
		// 错误转向
		Log::error($error[$errno], $errstr, $errfile, $errline);
		// 服务器错误
		header('Location: /50x.html');
	}	

	/**
	 * 自定义异常处理机制
	 * @param  object 异常对象
	 * @return void
	 */
	public static function appException($e)
	{
		self::appError(0, $e->getMessage(), $e->getFile(), $e->getLine());
	}

	/**
	 * 程序退出前的回调机制
	 * @return void
	 */
	public static function appShutdown()
	{
		if($e = error_get_last())
		{
			switch($e['type'])
			{
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:  
					ob_end_clean();
					self::appError($e['type'], $e['message'], $e['file'], $e['line']);
					break;
			}
		}
	}
}