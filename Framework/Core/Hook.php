<?php

namespace Core;

/**
 * 钩子插件类
 */
class Hook
{
	/**
	 * 钩子列表
	 * @var array
	 */
	private $hooks = array();	

	/**
	 * 钩子插件目录
	 * @var string
	 */
	private $plugin;

	/**
	 * 构造函数,读取钩子配置文件
	 * @param array 钩子配置
	 * @param string 钩子插件文件目录
	 */
	public function __construct($hooks, $plugin)
	{
		// 配置文件信息
		$this->hooks = $hooks;

		// 插件文件目录
		$this->plugin = $plugin;
	}

	/**
	 * 运行钩子
	 * @param string 要执行的钩子名
	 */
	public function run($name)
	{
		// 读取钩子
		foreach($this->getHook($name) as $hook)
		{
			// 加载类文件
			require_once(sprintf('%s%s', $this->plugin, $hook['filename']));
			// 创建对象
			$object = new $hook['class']();
			// 执行方法
			$object->$hook['function']($hook['param']);
		}
	}

	/**
	 * 读取对应钩子配置信息,并格式化为一个二维数组
	 * @param string 钩子名
	 * @return mixed 钩子存在则返回钩子数组,不存在则返回array()
	 */
	private function getHook($name)
	{
		// 钩子不存在
		if(empty($this->hooks[$name]))
		{
			return array();
		}

		// 获得要执行的钩子列表
		$hooks = $this->hooks[$name];
		
		// 是否是一维数组
		if(count($hooks) == count($hooks, COUNT_RECURSIVE))
		{
			$hooks = array($hooks);
		}

		return $hooks;
	}
}