<?php

/**
 * 过滤string数据
 * @param object 规则对象
 * @return void
 */
function filterString($rule)
{
	// xss注入攻击
	$flag = !preg_match('/(<script|<iframe|<link|<frameset|<vbscript|<form)/i', $rule->value);
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