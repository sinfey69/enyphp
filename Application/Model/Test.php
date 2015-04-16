<?php

namespace Model;
use \Mvc\Model;

class Test extends Model
{
	protected $table = "t";

	/**
	 * 
	 */
	public function testInsert()
	{
		//define('SQL_ECHO', TRUE);
		echo '<pre>';
		print_r($this->field(['id,name','sex','email'])
			->where(['id'=>8])
			->update(['sex'=>'1']));

		/*for($i=0;$i<1; $i++) {
			$name = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, rand(3,6));

			$data[] = array('name'=>$name, 'email'=>$name."@yeah.net");
		}

		echo $this->insert($data);*/
	}
}