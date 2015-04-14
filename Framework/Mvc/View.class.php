<?php

namespace Core;

/**
 * 模板解析类
 * @author Eny
 */
class Template
{
	/**
	 * 模板目录
	 * @var string
	 */
	private $tPath;

	/**
	 * 缓存目录
	 * @var string
	 */
	private $cPath;

	/**
	 * 插件目录
	 * @var string
	 */
	private $pPath;

	/**
	 * 过期时间
	 * @var int
	 */
	private $expire;

	/**
	 * 真缓存
	 * @var bool
	 */
	private $real;

	/**
	 * 模板变量
	 * @var string
	 */
	private $vars;

	/**
	 * 模板语法规则
	 */
	private $rules = array(
		'/\{include=(.+)\}/iU' 		     	=> '<?php $this->display("$1", $this->vars); ?>',
		'/\{if=(.+)\}/iU'	      		     	=> '<?php if($1) { ?>',
		'/\{elseif=(.+)\}/' 	      		=> '<?php } elseif($1) { ?>',
		'/\{else\}/iU'	      		     	=> '<?php } else { ?>',
		'/\{\/if\}/iU'	      		     	=> '<?php } ?>',
		'/\{loop=(.+)\|(.+)\}/iU'  		     	=> '<?php if(is_array($1)) {foreach($1 as $index=>$2) { ?>',
		'/\{\/loop\}/iU'	      		     	=> '<?php }} ?>',
		'/\{break\}/iU'	      		     	=> '<?php break; ?>',
		'/\{continue\}/iU'	      	     	=> '<?php continue; ?>',
		'/\{plugin=\$(.+)\}/iU'               		=> '<?php echo $this->plugin("$1"); ?>',
		'/\{const=([A-Za-z_][A-Za-z0-9_]*)\]/' 	=> '<?php echo $1; ?>',
		'/\{var=\$([a-zA-Z0-9_].*)\}/iU'	    	=> '<?php echo $$1; ?>',
	);

	/**
	 * 构造函数
	 * @param string 模板目录
	 * @param string 缓存目录
	 * @param string 插件目录
	 * @param int 过期时间
	 * @param boolean 真缓存
	 */
	public function __construct($tPath, $cPath, $pPath='./', $expire=1440, $real=FALSE)
	{
		$this->tPath  = $tPath;
		$this->cPath  = $cPath;
		$this->pPath  = $pPath;
		$this->expire = $expire;
		$this->real   = $real;
	}

	public function really($template)
	{
		return false;
	}

	/**
	 * 显示模板
	 * @param string 模板名
	 * @param array 模板数据
	 * @return void
	 */
	public function display($tpl, $vars=array(), $id=NULL, $real=NULL)
	{
		// 绑定变量
		$this->vars = $vars;
		// 是否缓存
		if(is_bool($real))
		{
			$this->real = $real;
		}
		// 获取模板的绝对路径
		list($tpl, $cpl) = $this->filename($tpl);
		// 检查是否过期
		if($this->isExpire($tpl, $cpl))
		{
			$this->complie($tpl, $cpl);
		}
		// 释放变量
		$this->real OR extract($this->vars);
		// 加载模板
		include($cpl);
	}

	/**
	 * 获得模板文件和编译文件的绝对路径
	 * @param  string 模板文件
	 * @param  int 单页面id
	 * @return array
	 */
	private function filename($tpl, $id=NULL)
	{
		// 单页面模板
		$id = $id ?  '_' . $id : "";

		// 模板的绝对路径
		$template = sprintf("%s%s.html", $this->tPath, $tpl);

		// 编译文件的绝对路径
		$complie = sprintf("%s%s%s.html", $this->cPath, $tpl, $id);

		is_dir(dirname($complie)) OR mkdir(dirname($complie), 0755, TRUE);

		return array($template, $complie);
	}

	/**
	 * 判断缓存是否过期
	 * @param  string 模板文件
	 * @param  string 缓存文件
	 * @return boolean
	 */
	private function isExpire($tpl, $cpl)
	{
		$isExpire = TRUE;
		// 编译文件是否存在
		if(file_exists($cpl))
		{
			// 模板文件上次修改时间是否大于缓存文件的上次修改时间
			$isExpire = filemtime($tpl) >= filemtime($cpl) ? TRUE : FALSE;

			// 真缓存过期时间
			if(!$isExpire && $this->real && (filemtime($cpl)  > (time()+$this->expire)))
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
	private function complie($tpl, $cpl)
	{
		// 读取模板内容
		$content = file_get_contents($tpl);

		// 模板语法修改
		foreach($this->rules as $search=>$replace)
		{
			$content = preg_replace($search, $replace, $content);
		}

		// 是否真缓存
		$this->write($cpl, $content);
	}

	/**
	 * 写入到文件
	 * @param  string 缓存文件
	 * @param  string 内容
	 * @return void
	 */
	private function write($cpl, $content)
	{
		// 是否要真缓存
		if($this->real)
		{
			extract($this->vars);
			// 开启缓存
			ob_start();
			// 执行代码
			eval('?>' . $content);
			// 读取内容
			$content = ob_get_clean();
		}

		// 写入到文件
		file_put_contents($cpl, $content);
	}

	/**
	 * 插件机制
	 * @param string 插件名成
	 * @return string
	 */
	private function plugin($args)
	{
		// 格式化插件
		if($plugins = explode("|", $args))
		{
			// 获取执行的参数方法
			$inner = array_splice($plugins, 0, 1)[0];
			// 格式化的参数
			$inner = $this->vars[$inner];
			// 附加参数
			$params = explode(':', $plugins[0]);
			// 执行方法
			$method = array_splice($params, 0, 1)[0];
			// 整合数据
			array_unshift($params, $inner);

			// 文件名
			$filename = sprintf("%s%s.plugin.php", $this->pPath, $method);
			// 加载文件
			require_once($filename);

			// 执行方法进行输出
			return call_user_func_array($method, $params);
		}
	}

	/**
     * 判断是否是公共目录,并设置为不真缓存
     * @param 模版名
     */
	private function isReal($template)
	{
		if(strripos($template, 'common') !== FALSE)
		{
			$this->real = FALSE;
		}
	}
}