<?php

namespace Core;

class Dispatcher
{
    /**
     * 初始化控制器信息
     * @param array 剩下的路由信息
     * @return void
     */
    private static function dispatch($routes)
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
     * 关闭加载视图
     */
    public function disableView()
    {
        
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