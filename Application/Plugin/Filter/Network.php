<?php

/**
 * 邮箱|网址|ip验证
 * @param object 规则对象
 * return void
 */
function filterNetwork($rule)
{
	$rule->condition = constant('FILTER_VALIDATE_' . $rule->rule);
	return (bool)filter_var($rule->value, $rule->condition);
}