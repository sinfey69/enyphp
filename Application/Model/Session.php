<?php

namespace Model;
use \Mvc\Model;

/**
 * Session模型
 */
class Session extends Model
{
	protected $table = "session";

	/**
	 * 开启session
	 * @param string 目录
	 * @param string sessionname
	 * @return int 1或者false
	 */
	public function open($path, $name)
	{
		if(session_id())
		{
			// 执行插入或替换
			$sql = "REPLACE INTO {$this->table} SET session_id=:id, ip=:ip, lastDate=:date";
			// sql语句执行
			$this->db->query($sql, array(':id'=>session_id(), 'ip'=>CLIENT_IP, ':date'=>time()));
			// 返回影响行数
			return $this->db->rowCount();
		}
	}

	/**
	 * 关闭session
	 * @return void
	 */
	public function close()
	{
		return TRUE;
	}

	/**
	 * 读取配置
	 * @param string sessionId
	 */
	public function read($id)
	{
		if($id)
		{
			return $this->field('data')->where(array('session_id'=>$id))->select(self::FETCH_ROW);
		}
	}

	/**
	 * 写入session
	 * @param string sessionid
	 * @param 
	 */
	public function write($id, $data)
	{
		if($id)
		{
			return $this->where(array('session_id'=>$id))->update(array('data'=>$data));
		}
	}

	/**
	 * 删除session信息
	 * @param string sessionid
	 * @return int
	 */
	public function destroy($id)
	{
		return $this->where(array('session_id'=>$id))->limit(1)->delete();
	}

	/**
	 * 而外的回收机制
	 * @return void
	 */
	public function gc()
	{
		return $this->where(array('session_id'=>$id))->limit(1)->delete();
	}

	/**
	 * 删除过期的key
	 * @param int 比较的时间点
	 * @return int
	 */
	public function expire($time)
	{
		return $this->where(array('lastDate <'=>$time))->delete();
	}
}