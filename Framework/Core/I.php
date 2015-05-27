<?php
/**
 * input类
 * @author enychen
 */

namespace Core;

class I
{
	/**
	 * 合法数据存储数组
	 * @var array
	 */
	private static $valid = [
		'_GET'=>[],
		'_POST'=>[],
		'_REQUEST'=>[],
	];

	/**
	 * 数据检查
	 * @return void
	 */
	public static function validity()
	{
		// 载入验证规则
		if($rules = self::getRule())
		{
			// 数据检查
			$rules = self::validate($rules);
			// 格式化数据
			$rules = self::format($rules);
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
		if(is_file(VALIDATE.REQUEST_FILE.".xml"))
		{
			foreach(simplexml_load_file(VALIDATE.REQUEST_FILE.".xml") as $element)
			{
				$rule = new \stdClass();
				foreach($element->attributes() as $attr=>$value)
				{
					$rule->$attr = (string)$value;
				}
				$rules[] = $rule;
			}
			// 初始化验证规则
			return self::initRules($rules);
		}
		return FALSE;
	}

	/**
	 * 初始化检查规则
	 * @param array 规则对象数组
	 * @return array 格式化后的规则数组
	 */
	private static function initRules($rules)
	{
		foreach($rules as $key=>$rule)
		{
			// 来源
			$from = strtoupper("_{$rule->from}");
			// 值
			$value = isset($GLOBALS[$from][$rule->name]) ? $GLOBALS[$from][$rule->name] : NULL;
			// 规则
			$rule->rule = ucfirst($rule->rule);
			// 原始值
			$rule->origin = is_array($value) ? $value : explode(',', $value);
			// 别名
			if(isset($rule->alias))
			{
				$rules[$key]->name = str_replace([' lte',' lt'], [' <=',' <'], $rule->alias);
			}
			// 移动
			if(isset($rule->move))
			{
				$rules[$key]->from = "_{$rule->move}";
			}
			// 聚合
			if(isset($rule->aggregate))
			{
				$rules[$key]->aggregate = explode(',', $rule->aggregate);
			}
		}
		return $rules;
	}

	/**
	 * 检查数据
	 * @param object 规则对象
	 * @return void
	 */
	private static function validate($rules)
	{
		foreach($rules as $rule) 
		{
			// 是否允许为空
			if(is_null($rule->origin) && isset($rule->require))
			{
				self::throwError($rule->require);
			}
			else if(!is_null($rule->origin))
			{
				// 循环遍历检查数组
				for($i=0,$len=count($rule->origin); $i<$len; $i++)
				{
					// 设置当前要检查的值
					$rule->value = $rule->origin[$i];
					// 检查值
					Plugin\System::$rule->rule($rule) OR self::throwError($rule->prompt);
					// 去掉空格
					$rule->origin[$i] = trim($rule->value);
				}
			}

			// 单个值还是一整个数组
			$rule->value = $i > 1 ? $rule->origin : $rule->origin[0];
		}

		return $rules;
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
		// 数据格式化
		$rule = $this->dataFormat($rule);
		// 设置值
		if(isset($rule->aggregate))
		{
		 	foreach(explode(',', $rule->aggregate) as $key)
		 	{
		 		self::$valid[$rule->from][$key][$rule->name] = $rule->value;
		 	}
		}
		else
		{
			self::$valid[$rule->from][$rule->name] = $rule->value;
		}
	}

	/**
	 * 数据格式化
	 * @param object
	 */
	private static function dataFormat($rule)
	{
		if(isset($rule->format))
		{
			$rule->value = str_replace(":{$rule->name}", $rule->format, $rule->value);
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