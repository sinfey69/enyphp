<?php

/**
 * 正则过滤
 * @param object 规则对象
 * return void
 */
function filterRegexp($rule)
{
	return (bool)preg_match($rule->pattern, $rule->value);
}