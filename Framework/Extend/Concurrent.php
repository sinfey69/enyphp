<?php
/**
 * 文件锁机制类
 * @author enychen
 */

namespace Extend;

class Concurrent
{	
	/**
	 * 加锁的文件
	 * @var string
	 */
	private static $filename;
	
	/**
	 * 加锁文件数组
	 * @var string
	 */
	private static $fp;

	/**
	 * 加锁
	 * @param string 文件名
	 * @return boolean
	 */
	public static function lock($filename)
	{
		// 完整文件名
		self::$filename = \Core\F::absFile("{$filename}.lock", LOCK);
		// 打开文件,不存在的时候则尝试创建
		self::$fp = fopen(self::$filename, 'w+');
		// 文件加锁
		return flock(self::$fp, LOCK_EX);
	}

	/**
	 * 解锁
	 * @param boolean 是否删除锁文件
	 * @return void
	 */
	public static function unlock($delete=FALSE)
	{
		// 解锁
		flock(self::$fp, LOCK_UN);
		// 关闭文件
		fclose(self::$fp);
		// 是否删除文件
		!$delete OR unlink(self::$filename);
		// 返回结果
		return TRUE;
	}
}