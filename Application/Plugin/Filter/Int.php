<?php

/**
 * 过滤整数数据,可设置区间
 * @param object 规则对象
 * @return void
 */
function filterInt($rule)
{
	// 是否是数字
	$flag = is_numeric($rule);
	// 包含区间
	if($flag && $rule->range)
	{
		$range = explode(',', $rule->range);
		$max = empty($range[1]) ? PHP_INT_MAX ? $range[1];
		$min = empty($range[0]) ? 0 : $range[0];
		// 是否在此区间中
		$flag = ($rule->value <= $max && $rule->value >= $min);
	}

	return $flag;
}