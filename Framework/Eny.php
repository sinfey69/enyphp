<?php
/**
 * 核心框架类
 * @author enychen
 */

use
\Core\Config,// 配置类
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
		// 加载文件
		self::loadfile();
		// 加载配置
		Config::configure(CONFIG);
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
	 * 通用文件加载
	 * @return void
	 */
	private static function loadfile()
	{
		// 全局函数
		require(FRAMEWORK.'Core/Function.php');
	}
	
	/**
	 * 初始化环境
	 * @return void
	 */
	private static function environment()
	{
		// 通用常量定义
		defined('DEBUG') OR define('DEBUG',FALSE);// 调试模式
		date_default_timezone_set();// 日期设置
		spl_autoload_register('self::appAutoload'); // 自动加载机制
		if(!DEBUG)
		{
			set_exception_handler('self::appException');// 自定义异常机制
			set_error_handler('self::appError');// 自定义错误机制
			register_shutdown_function('self::appShutdown');// 程序退出前回调机制
			error_reporting(0);// 关闭报错
			ini_set('display_errors','off');// 关闭报错
		}		
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
		// 错误记录
		Log::error($error[$errno], $errstr, $errfile, $errline);
		// 服务器错误
		Output::_500();
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

	/**
	 * 程序执行
	 * @return void
	 */
	private static function application()
	{		
		// 路由解析
		list($class, $function) = Request::dispatch();
		// 数据检查
		Input::validity();
		// session初始化
		Session::initialize();
		// 控制器运行前的钩子
		Hook::runHook('prevController');
		// 创建控制器
		$controller = new $class();
		// 控制器执行前
		call_user_func(array($controller, '_before'));
		call_user_func(array($controller, $function));
		call_user_func(array($controller, '_after'));
		// 最终输出
		Output::response($output);
 	}
}