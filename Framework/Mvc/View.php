<?php

namespace Mvc;

use \Core\C;

/**
 * 模板解析类
 * @author Eny
 */
class View
{
	/**
	 * 模板目录
	 * @var string
	 */
	private $templateDir;

	/**
	 * 编译目录
	 * @var string
	 */
	private $complieDir;

	/**
	 * 缓存目录
	 */
	private $cacheDir;

	/**
	 * 插件目录
	 * @var string
	 */
	private $pluginDir;

	/**
	 * 过期时间
	 * @var int
	 */
	private $expire;

	/**
	 * 真缓存
	 * @var bool
	 */
	private $cache;

	/**
	 * 模板变量
	 * @var array
	 */
	private $tplVals;

	/**
	 * 模板语法规则
	 * @var object
	 */
	private $rules = array(
		'/<!--{include\s+(.+)}-->/iU' => '<?php $this->subTemplate("$1"); ?>',
		'/<!--{if\s+(.+)}-->/iU' => '<?php if($1) { ?>',
		'/<!--{elseif\s+(.+)}-->/' => '<?php } elseif($1) { ?>',
		'/<!--{else}-->/iU' => '<?php } else { ?>',
		'/<!--{\/if}-->/iU' => '<?php } ?>',
		'/<!--{loop\s+(.+)\s+(.+)}-->/iU' => '<?php if(is_array($1)) {foreach($1 as $index=>$2) { ?>',
		'/<!--{\/loop}-->/iU' => '<?php }} ?>',
		'/<!--{break}-->/iU' => '<?php break; ?>',
		'/<!--{continue}-->/iU' => '<?php continue; ?>',
		'/<!--{plugin\s+(.+)}-->/iU' => '<?php echo $this->plugin("$1"); ?>',
		'/<!--{([A-Z_][A-Z0-9_]*)}-->/iU'	=> '<?php echo $1; ?>',
		'/<!--{\$([a-zA-Z0-9_].*)\s+"(.+)"}-->/iU' => '<?php echo empty($$1) ? "$2" : $$1; ?>',
		'/<!--{\$([a-zA-Z0-9_].*)}-->/iU' => '<?php echo $$1 ?>',
	);

	/**
	 * 构造函数
	 * @param string 模板目录
	 * @param string 缓存目录
	 * @param string 插件目录
	 * @param int 过期时间
	 * @param boolean 真缓存
	 */
	public function __construct()
	{
		// 获取配置
		$view = C::view();
		// 模版文件
		$this->templateDir = VIEW.trim($view->theme)."/";
		// 编译文件
		$this->compileDir = COMPILE;
		// 缓存文件
		$this->cacheDir = CACHE;
		// 插件文件
		$this->pluginDir = PLUGIN."/View/";
		// 视图过期时间
		$this->expire = $view->expire;
		// 是否真缓存
		$this->cache = $view->cache;
	}

	/**
	 * 是否真缓存
	 * @param string 模版名
	 * @param id 单页面page值
	 * @return boolean
	 */
	public function isCache($tpl, $id=NULL)
	{
		if($this->cache)
		{
			// 模板|编译|缓存文件的绝对路径
			$files = $this->absFiles($tpl, $id);
			// 缓存检查
			if(!$this->isExpire($files[0], $files[2]))
			{
				exit($this->display($tpl));
			}
		}
	}

	/**
	 * 显示模板
	 * @param string 模板名
	 * @param array 模板数据
	 * @param string 单页面多缓存id
	 * @param boolean 真假缓存
	 * @return void
	 */
	public function display($tpl, $tplVals=array(), $id=NULL, $cache=NULL)
	{
		// 绑定变量
		$this->tplVals = $tplVals;
		// 是否缓存
		!is_bool($cache) OR ($this->cache = $cache);
		// 模板|编译|缓存文件的绝对路径
		$files = $this->absFiles($tpl, $id);
		// 检查是否过期
		if($this->isExpire($files[0], $files[2]))
		{
			// 编译文件
			$content=$this->complie($files);
			// 缓存文件
			$this->write($files, $content, $this->cache);
		}
		// 加载最终文件
		$this->includeCache($files[2]);
	}

	/**
	 * 模板|编译|缓存文件的绝对路径
	 * @param  string 模板文件
	 * @param  int 单页面id
	 * @return array
	 */
	public function absFiles($tpl, $id=NULL)
	{
		// 模板文件
		$files[] = "{$this->templateDir}{$tpl}.html";
		// 编译文件
		$files[] = $this->compileDir.md5($tpl).".php";
		// 缓存文件
		$files[] = $this->cacheDir.md5(strtolower("{$tpl}_{$id}")).".html";

		return $files;
	}

	/**
	 * 判断缓存是否过期
	 * @param  string 模板文件
	 * @param  string 缓存文件
	 * @return boolean
	 */
	private function isExpire($tpl, $cache)
	{
		$isExpire = TRUE;
		// 编译文件是否存在
		if(file_exists($cache))
		{
			// 模板文件上次修改时间是否大于缓存文件的上次修改时间
			$isExpire = filemtime($tpl) >= filemtime($cache) ? TRUE : FALSE;

			// 真缓存过期时间
			if(!$isExpire && $this->cache && (filemtime($cache) > (time()+$this->expire)))
			{
				$isExpire = TRUE;
			}
		}

		return $isExpire;
	}

	/**
	 * 模板编译
	 * @param  string 模板文件
	 * @param  string 编译文件
	 * @return void
	 */
	private function complie($files)
	{
		// 读取模板内容
		$content = file_get_contents($files[0]);

		// 模板语法修改
		foreach($this->rules as $search=>$replace)
		{
			$content = preg_replace($search, $replace, $content);
		}

		return $content;
	}

	/**
	 * 写入到文件
	 * @param  string 缓存文件
	 * @param  string 内容
	 * @return void
	 */
	private function write($files, $content, $cache)
	{
		// 编译文件写入
		$cContent = '<?php extract($this->tplVals); ?>'.PHP_EOL.$content;

		// 编译文件是否存在
		if($this->isExpire($files[0], $files[1]))
		{
			file_put_contents($files[1], $cContent);
		}		

		// 是否要真缓存
		if($cache)
		{
			// 开启缓存
			ob_start();
			// 执行代码
			include($files[1]);
			// 读取内容
			$cContent = ob_get_clean();
		}

		// 缓存文件写入
		file_put_contents($files[2], $cContent);
	}

	/**
	 * 内部加载文件
	 * @param string 模版文件
	 */
	private function subTemplate($subTpl)
	{
		// 子模板文件
		$files = $this->absFiles($subTpl);
		// 模版类
		if($this->isExpire($files[0], $files[2]))
		{
			// 编译文件
			$content=$this->complie($files);
			// 缓存文件
			$this->write($files, $content, FALSE);
		}

		$this->includeCache($files[2]);
	}

	/**
	 * 缓存文件加载
	 * @param 缓存文件名
	 * @return void
	 */
	private function includeCache($cache)
	{
		ob_start();
		include($cache);
		$content = ob_get_clean();

		// 模版是否有误
		if($e=error_get_last())
		{
			trigger_error($e['message']);
		}
		else
		{
			echo $content;
		}
	}

	/**
	 * 插件机制
	 * @param string 插件
	 */
	private function plugin($source)
	{
		
	}
}