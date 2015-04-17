<?php

use \core\ModelBase;

class DomainAuction extends ModelBase
{
	protected $table = "domain_auction";

	/**
	 * 这是一个函数
	 * @param string 参数说明1
	 * @param int    参数说明2
	 * @return void
	 * @return array 包含啥key的值
	 */
	public function funcName($domain, $num)
	{
		$sql = "SELECT * FROM {$this->table} WHERE domainname=:domainname ORDER BY FinishDate DESC limit 0,{$num}";
		$this->db->query($sql, array(":domainname"=>$domain));
		return $this->db->fetch(self::FETCH_ROW); // FETCH_ALL FETCH_COLUMN;
	}
}