<?php

/**
* 字符串|数字相等匹配
* @param object 规则对象
* @return int|string
*/
function filterIn($rule)
{
	return in_array($rule->value, explode(',', $rule->range));
}