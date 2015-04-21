<?php

use \Core\C;

class CTest extends PHPUnit_Framework_TestCase
{	
	public function testInit()
	{
		$this->assertObjectHasAttribute('class', C::routes(), '没有这个class属性');
		$this->assertObjectHasAttribute('function', C::routes(), '没有这个function属性');
		$this->assertObjectHasAttribute('a', C::routes(), '没有这个a属性');
	}
}
