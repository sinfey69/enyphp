<?php

namespace Model;
use \Mvc\Model;

class Test extends Model
{
	protected $table = "t";

	public function testInsert()
	{
		echo $this->field(['id,name','sex','email'])
			 ->where(['id'=>8])
			 ->update(['sex'=>'1']);
	}
}