<?php
/**
 * 核心框架类
 * @author enychen
 */

use 
\Core\C,// 配置类
\Core\D,// 路由类
\Core\F,// 全局方法类
\Core\H,// 钩子类
\Core\I,// 输入类
\Core\L,// 日志类
\Core\R,// 输出类
\Core\S;// session类

class Eny
{
	/**
	 * 初始化
	 * @return void
	 */
	public static function boot()
	{
		// 目录定义
		define('FCPATH',dirname(__DIR__).'/');// 站点目录
		define('APPLICATION',FCPATH.'Application/');// 项目目录
		define('FRAMEWORK',FCPATH.'Framework/');// 框架目录		
		define('BOOTSTRAP',FCPATH.'Bootstrap/');// 公共目录
		define('CONFIG',APPLICATION.'Config/');// 配置目录
		define('VALIDATE',APPLICATION.'Validate/');// 验证目录
		define('LANGUAGE',APPLICATION.'Language/');// 语言包目录
		define('VIEW',APPLICATION.'View/');// 模板目录
		define('PLUGIN',APPLICATION.'Plugin/');// 插件目录
		define('DATA',APPLICATION.'Data/');// 数据目录
		define('LOG',DATA.'Log/');// 日志目录
		define('CACHE',DATA.'Cache/');// 缓存目录
		define('COMPILE',DATA.'Compile/');// 编译文件
		define('FONT',DATA.'Font/');// 字体目录
		define('LOCK',DATA.'Lock/');// 锁目录
		define('SESSION',DATA.'Session/');// SESSION目录
		// 通用常量定义
		defined('DEBUG') OR define('DEBUG',FALSE);// 调试模式
		define('IS_CLI', !strcasecmp(php_sapi_name(), 'cli'));// 命令行模式
		// 系统环境设置
		IS_CLI OR header('Content-Type:text/html;charset=UTF-8');// 字符集设置
		date_default_timezone_set('PRC');// 日期设置
		spl_autoload_register('Eny::appAutoload'); // 自动加载机制
		if(!DEBUG)
		{
			set_exception_handler('Eny::appException');// 自定义异常机制
			set_error_handler('Eny::appError');// 自定义错误机制
			register_shutdown_function('Eny::appShutdown');// 程序退出前回调机制
			error_reporting(0);// 关闭报错
			ini_set('display_errors','off');// 关闭报错
		}

		// 程序运行
		self::run();
	}

	/**
	 * 程序执行
	 * @return void
	 */
	private static function run()
	{
		// 加载配置
		C::loadConfig();
		// 路由解析
		list($class, $function) = D::router();
		// 数据检查
		I::validity();
		// session初始化
		S::initialize();
		// 创建控制器
		$controller = new $class();
		// 控制器执行前
		!method_exists($controller, '_init') OR $controller->_init();
		// 调用控制器函数
		$controller->$function();
		// 控制器执行后
		!method_exists($controller, '_end') OR $controller->_end();
		// 浏览器输出
		R::response();
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
		L::error($error[$errno], $errstr, $errfile, $errline);
		// 服务器错误
		R::serverError();
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