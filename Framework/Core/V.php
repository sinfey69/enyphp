<?php
/**
 * 数据检查类
 * @author enychen
 */

namespace Core;

class V
{
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
	 * @param string 文件名
	 * @return void
	 */
	public static function validity()
	{
		// 获取检查合法性的xml文件下的所有验证规则
		if($rules = self::getRule())
		{
			foreach($rules as $key=>$rule)
			{
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
	 * @return array 所有规则对象数组
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
				// 来源
				$rule->from = "_{$rule->from}";
				// 验证选项
				$rule->options = array('options'=>array());
				// 保存
				$rules[] = $rule;
			}
		}

		return $rules;
	}

	/**
	 * 检查值是否存在,并且是否必须
	 * @param object 规则对象
	 * @return boolean 
	 */
	private static function isExists($rule)
	{
		// 不存在判断是否必须
		if(!array_key_exists($rule->name, $GLOBALS[$rule->from]))
		{
			// 是否必须项
			if(isset($rule->require))
			{
				self::throwError($rule->require);
			}

			// 不存在的内容设置为个NULL
			$rule->value = isset($rule->default) ? self::setDefault($rule->default) : NULL;
			// 保存规则
			self::set($rule);

			// 不用继续检查
			return FALSE;
		}

		// 继续检查
		return TRUE;
	}

	/**
	 * 检查数据
	 * @param object 规则对象
	 * @return void
	 */
	private static function check($rule)
	{
		// 来源值
		$rule->origin = explode(',', $GLOBALS[$rule->from][$rule->name]);

		// 暂存值
		$save = array();
		// 循环遍历检查数组
		for($i=0,$len=count($rule->origin); $i<$len; $i++)
		{
			// 设置当前要检查的值
			$rule->value = $rule->origin[$i];
			// 规则检查
			switch($rule->rule)
			{
				case "INT":
					// 过滤转义int类型
					$save[$i] = self::filterInt($rule);
					break;
				case "STRING":
					// 字符串检查
					$save[$i] = self::filterString($rule);
					break;
				case "REGEXP":
					// 正则匹配
					$save[$i] = self::filterRegexp($rule);
					break;
				case "EMAIL":
				case "URL":
				case "IP":
					// 网络格式检查
					$save[$i] = self::filterNetwork($rule);
					break;
				case "EQUALS":
					// 匹配值
					$save[$i] = self::filterEquals($rule);
					break;
				case "CALLBACK":
					$save[$i] = self::filterCallback($rule);
					break;
			}

			// 去掉空格
			$save[$i] = trim($save[$i]);
		}

		// 单个值还是一整个数组
		$rule->value = $i > 1 ? $save : reset($save);

		// 设置值
		self::set($rule);
	}

	/**
	 * 过滤整数数据,可设置区间
	 * @param object 规则对象
	 * return void
	 */
	private static function filterInt($rule)
	{
		// 设置验证规则
		$rule->condition = FILTER_VALIDATE_INT;
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
		return self::filter($rule);
	}

	/**
	 * 正则过滤
	 * @param object 规则对象
	 * return void
	 */
	private static function filterRegexp($rule)
	{
		$rule->condition = FILTER_VALIDATE_REGEXP;
		$rule->options['options']['regexp'] = $rule->pattern;
		return self::filter($rule);
	}

	/**
	 * 邮箱|网址|ip验证
	 * @param object 规则对象
	 * return void
	 */
	private static function filterNetwork($rule)
	{
		$rule->condition = constant('FILTER_VALIDATE_' . $rule->rule);
		return self::filter($rule);
	}

	/**
	 * 过滤string数据
	 * @param object 规则对象
	 * @return void
	 */
	private static function filterString($rule)
	{
		// 字符串xss检查
		if(preg_match('/(<script|<iframe|<link|<frameset|<vbscript|<form)/i', $rule->value))
		{
			self::throwError($rule->prompt);
		}
		// 字符串长度检查
		if(isset($rule->length))
		{
			$length = explode(',', $rule->length);
			// 最大长度
			if((!empty($length[1])) && (strlen($rule->value) > $length[1]))
			{
				self::throwError($rule->prompt);
			}
			// 最小长度
			if($length[0] && (strlen($rule->value) < $length[0]))
			{
				self::throwError($rule->prompt);
			}
		}
		// 转义字符串
		if(empty($rule->escape))
		{
			 $rule->value = htmlspecialchars($rule->value);
		}
		return $rule->value;
	}

	/**
	 * 字符串|数字匹配
	 * @param object 规则对象
	 * @return int|string
	 */
	private static function filterEquals($rule)
	{
		$range = explode(',', $rule->range);
		if(!in_array($rule->value, $range))
		{
			self::throwError($rule->prompt);
		}
		return $rule->value;
	}

	/**
	 * 回调方法进行验证
	 * @param object 规则对象
	 * @return int|string
	 */
	private static function filterCallback($rule)
	{
		require_once(PLUGIN."Validate/{$rule->method}.php");
		$result = call_user_func_array($rule->method, array($rule->value));
		if($result === FALSE)
		{
			self::throwError($rule->prompt);
		}
		// 设置值
		return $result;
	}

	/**
	 * 通用过滤
	 * @param object 规则对象
	 * return void
	 */
	private static function filter($rule)
	{
		// 检查
		$result = filter_var($rule->value, $rule->condition, $rule->options);
		// 错误抛出
		if($result === FALSE)
		{
			self::throwError($rule->prompt);
		}
		// 设置值
		return $result;
	}

	/**
	 * 设置默认值
	 */
	private static function setDefault($default)
	{
		switch($default) 
		{
			case 'time':
				return time();
			case 'date':
				return date('Y-m-d H:i:s');
			default:
				return $default;
		}
	}

	/**
	 * 过滤查询后设置值
	 * @param object 检查后的规则对象
	 * @return void
	 */
	private static function set($rule)
	{
		// 替换别名, xml无法解析<,不知道为何
		$name = isset($rule->alias) ? str_replace(array(' lte',' lt'), array(' <=',' <'), $rule->alias) : $rule->name;
		// 替换来源
		$from = isset($rule->move) ? "_{$rule->move}" : $rule->from;
		// 格式化数据
		$value = isset($rule->format) ? str_replace(":{$name}", $rule->value, $rule->format) : $rule->value;

		// 设置值
		if(isset($rule->aggregate))
		{
		 	foreach(explode(',', $rule->aggregate) as $key)
		 	{
		 		self::$valid[$from][$key][$name] = $value;
		 	}
		}
		else
		{
			self::$valid[$from][$name] = $value;
		}
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

	/**
	 * 错误抛出
	 * @param string 错误信息
	 * @return void
	 */
	private static function throwError($message)
	{
		F::redirect(F::REDIRECT_ERROR, $message);
	}
}