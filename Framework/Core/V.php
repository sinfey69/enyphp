<?php

namespace Core;

/**
 * 数据检查类
 */
class V
{
	/**
	 * 数据xml文件目录
	 * @var string
	 */
	private $path;

	/**
	 * 合法数据存储数组
	 * @var array
	 */
	private static $valid = array(
		'_GET'=>array(),
		'_POST'=>array(),
		'_REQUEST'=>array(),
	);

	/**
	 * 数据合法性检查
	 * @return void
	 */
	public static function validity()
	{
		// 获取检查合法性的xml文件下的所有验证规则
		if($rules = self::getRule())
		{
			foreach($rules as $key=>$rule)
			{
				// 来源
				$rule->from = "_{$rule->from}";
				// 存在才检查
				if(self::isExists($rule))
				{
					// 数据检查
					self::check($rule);
				}
			}
		}
		// 影响全局变量
		self::initialize();
	}

	/**
	 * 根据文件获取规则
	 * @return array 所有规则数组
	 */
	private static function getRule()
	{
		// 文件名
		$filename = VALIDATE.REQUEST_FILE.".xml";
		if(!is_file($filename))
		{
			return FALSE;
		}
		// 读取xml文件
		$xml = simplexml_load_file($filename);
		// 将xml转成普通对象
		$rules = array();
		foreach($xml as $element)
		{
			$rule = new \stdClass();
			foreach($element->attributes() as $attr=>$value)
			{
				$rule->$attr = $value->__toString();
			}

			if($rule)
			{
				$rules[] = $rule;
			}
		}

		return $rules;
	}

	/**
	 * 检查值是否存在,并且是否必须
	 * @param array 规则数组
	 * @return boolean 
	 */
	private static function isExists($rule)
	{
		// 不存在判断是否必须
		if(!array_key_exists($rule->name, $GLOBALS["{$rule->from}"]))
		{
			// 是否必须项
			if(isset($rule->require))
			{
				throw new \Exception($rule->require);
			}
			// 不存在的内容设置为NULL
			$rule->value = isset($rule->default) ? $rule->default : NULL;
			self::set($rule);
			// 不用继续检查
			return FALSE;
		}
		// 继续检查
		return TRUE;
	}

	/**
	 * 检查数据
	 * @param array 验证规则原始数组
	 */
	private static function check($rule)
	{
		// 来源值
		$rule->value = $GLOBALS["{$rule->from}"][$rule->name];
		// 验证选项
		$rule->options = array('options'=>array());
		// 规则检查
		switch($rule->rule)
		{
			case "INT":
				// 过滤转义int类型
				return self::filterInt($rule);
			case "STRING":
				return self::filterString($rule);
			case "REGEXP":
				return self::filterRegexp($rule);
			case "EMAIL":
			case "URL":
			case "IP":
				return self::filterNetwork($rule);
			case "EQUALS":
				// 匹配值
				return self::filterIn($rule);
			case "CALLBACK":
				return self::filterCallback($rule);
		}
	}

	/**
	 * 过滤整数数据,可设置区间
	 * @param object 规则对象
	 * return void
	 */
	private static function filterInt($rule)
	{
		// 设置验证规则
		$rule->rule = FILTER_VALIDATE_INT;
		// 包含区间
		$range = isset($rule->range) ? explode(',', $rule->range) : array();
		// 区间最小值
		if(isset($range[0]))
		{
			$rule->options['options']['min_range'] = $range[0];
		}
		// 区间最大值
		if(isset($range[1]))
		{
			$rule->options['options']['max_range'] = $range[1];
		}
		// 验证
		self::filter($rule);
	}

	/**
	 * 正则过滤
	 * @param object 规则对象
	 * return void
	 */
	private static function filterRegexp($rule)
	{
		$rule->rule = FILTER_VALIDATE_REGEXP;
		$rule->options['options']['regexp'] = $rule->pattern;
		self::filter($rule);
	}

	/**
	 * 邮箱|网址|ip验证
	 * @param object 规则对象
	 * return void
	 */
	private static function filterNetwork($rule)
	{
		$rule->rule = constant('FILTER_VALIDATE_' . $rule->rule);
		self::filter($rule);
	}

	/**
	 * 过滤string数据
	 * @param object 规则对象
	 * return void
	 */
	private static function filterString($rule)
	{
		// 字符串xss检查
		if(preg_match('/(<script|<iframe|<link|<frameset|<vbscript|<form)/i', $rule->value))
		{
			throw new \Exception($rule->prompt);
		}
		// 字符串长度检查
		if(isset($rule->length))
		{
			$length = explode(',', $rule->length);
			// 最大长度
			if( (!empty($length[1])) && (strlen($rule->value) > $length[1]) )
			{
				throw new FormException($rule->prompt);
			}
			// 最小长度
			if( $length[0] && (strlen($rule->value) < $length[0]) )
			{
				throw new FormException($rule->prompt);
			}

		}
		// 转义字符串
		if(empty($rule->escape))
		{
			 $rule->value = htmlspecialchars($rule->value);
		}
		//过滤
		self::set($rule);
	}

	/**
	 * 字符串|数字匹配
	 * @param object 规则对象
	 * return void
	 */
	private static function filterIn($rule)
	{
		$in = explode(',', $rule->in);
		if(!in_array($rule->value, $in))
		{
			throw new \Exception($rule->prompt);
		}
		self::set($rule);
	}

	/**
	 * 回调方法进行验证
	 * @param object 规则对象
	 */
	private static function filterCallback($rule)
	{
		// 解析类和方法
		list($class, $method) = explode(':',$rule->action);
		// 创建对象
		$object = new $class();
		// 对象值
		$result = $object->$method($rule->value);
		if($result === FALSE)
		{
			throw new \Exception($rule->prompt);
		}
		// 设置值
		self::set($rule);
	}

	/**
	 * 通用过滤
	 * @param object 规则对象
	 * return void
	 */
	private static function filter($rule)
	{
		// 检查
		$result = filter_var($rule->value, $rule->rule, $rule->options);
		// 错误抛出
		if($result === FALSE)
		{
			throw new \Exception($rule->prompt);
		}
		// 设置值
		self::set($rule);
	}

	/**
	 * 过滤查询后设置值
	 * @param object 检查后的规则对象
	 * @return void
	 */
	private static function set($rule)
	{
		// 替换别名
		$name = isset($rule->alias) ? $rule->alias : $rule->name;
		// 设置值
		self::$valid[$rule->from][$name] = $rule->value;
	}

	/**
	 * 检查值设置到对应的全局变量中
	 * @return void
	 */
	private static function initialize()
	{
		foreach(self::$valid as $key=>$global)
		{
			$GLOBALS[$key] = $global;
		}
	}
}