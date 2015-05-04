<?php

namespace Core;
use \Mvc\Model;

class S
{
	/**
	 * 数据库session操作模型
	 * @var object
	 */
	private static $model;

	/**
	 * 初始化Session存储方式
	 * @return void
	 */
	public static function initialize()
	{
		$config = C::session();

		// 设置session名
		session_name($config->name);
		// 设置保存方式
		switch($config->handler)
		{
			case 'files':
				ini_set('session.save_path', SESSION);
				break;
			case 'redis':
			case 'memcached':
				ini_set('session.save_handler', $config->handler);
				$driver = C::G($config->handler, $config->path);
				$config->path = "tcp://{$driver->host}:{$driver->port}";
			case 'mysql':
				ini_set('session.save_path', 'user');
				self::$model = new Session();
				session_set_save_handler(
					array(self::$model,'open'),
					array(self::$model,'close'),
					array(self::$model,'read'),
					array(self::$model,'write'),
					array(self::$model,'destroy'),
					array(self::$model,'gc')	
				);
				// 过期session删除
				self::$model->expire(time()-$config->expire);
				break;
		}
		// 设置过期时间
		ini_set('session.gc_maxlifetime', $config->expire);

		// 开启session
		session_start();
	}
}


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