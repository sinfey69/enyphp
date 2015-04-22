<?php

use \Core\C;

class CTest extends PHPUnit_Framework_TestCase
{	
	public function testCallStatic()
	{
		$this->assertObjectHasAttribute('class', C::routes(), '从routes配置找不到class属性');
		$this->assertObjectHasAttribute('function', C::routes(), '从routes配置找不到function属性');
		$this->assertObjectHasAttribute('suffix', C::routes(), '从routes配置找不到suffix属性');
	}
}
