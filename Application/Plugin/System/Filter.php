<?php

namespace Plugin\System;

class Filter
{
	/**
	 * 字符串|数字相等匹配
	 * @param object 规则对象
	 * @return int|string
	 */
	public static function in($rule)
	{
		return in_array($rule->value, explode(',', $rule->range));
	}

	/**
	 * 过滤整数数据,可设置区间
	 * @param object 规则对象
	 * @return void
	 */
	public static function int($rule)
	{
		// 是否是数字
		$flag = is_numeric($rule);
		// 包含区间
		if($flag && $rule->range)
		{
			$range = explode(',', $rule->range);
			$max = empty($range[1]) ? PHP_INT_MAX : $range[1];
			$min = empty($range[0]) ? 0 : $range[0];
			// 是否在此区间中
			$flag = ($rule->value <= $max && $rule->value >= $min);
		}

		return $flag;
	}

	/**
	 * 验证Email
	 */
	public static function email($rule)
	{
		return filter_var($rule->value, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * 验证url地址
	 */
	public static function url($rule)
	{
		return filter_var($rule->value, FILTER_VALIDATE_URL);
	}

	/**
	 * 验证ip
	 */
	public static function ip($rule)
	{
		return filter_var($rule->value, FILTER_VALIDATE_IP);
	}

	/**
	 * 验证正则表达式
	 */
	public static function regexp()
	{
		return preg_match($rule->pattern, $rule->value);
	}

	/**
	 * 过滤string数据
	 * @param object 规则对象
	 * @return void
	 */
	public static function string($rule)
	{
		// xss注入攻击
		$flag = !preg_match('/(<script|<iframe|<link|<frameset|<vbscript|<form|<\?php)/i', $rule->value);
		// 字符串长度
		if($flag && isset($rule->range))
		{
			$length = strlen($rule->value);
			$range = explode(',', $rule->range);
			$max = empty($range[1]) ? PHP_INT_MAX ? $range[1];
			$min = empty($range[0]) ? 0 : $range[0];
			// 是否在此区间中
			$flag = ($length <= $max && $length >= $min);
		}

		return $flag;
	}

	/**
	 * 验证手机|电话号码
	 */
	public static function phone($rule)
	{
		foreach(["/(\d{3}-)(\d{8})$|(\d{4}-)(\d{7,8})$/", "/^1(3|4|5|7|8)[0-9]{9}$/"] as $pattern)
		{
			if($flag = preg_match($pattern, $rule->value))
			{
				break;
			}
		}
		return $flag;
	}
}