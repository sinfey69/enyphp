<?php

/**
 * 钩子插件类(Hook)
 * @author enychen
 */
class Hook
{
	/**
	 * 运行钩子
	 * @param string 要执行的钩子名
	 * @return void
	 */
	public static function runHook($name)
	{
		// 读取钩子
		foreach(self::getHook($name) as $hook)
		{
			$class = "\\Plugin\\Hook\\{$hook->class}";
			$method = $hook->function;
			// 创建对象
			$object = new $class();
			// 执行方法
			$object->$method($hook->params);
		}
	}

	/**
	 * 读取对应钩子配置信息,并格式化为一个二维数组
	 * @param string 钩子名
	 * @return mixed 钩子存在则返回钩子数组,不存在则返回array()
	 */
	private static function getHook($name)
	{
		// 钩子不存在
		if(!C::hook($name))
		{
			return array();
		}
		// 获得要执行的钩子列表
		$hooks = C::hook($name);
		// 是否是一维数组
		if(is_object($hooks))
		{
			$hooks = array($hooks);
		}		
		return $hooks;
	}
}