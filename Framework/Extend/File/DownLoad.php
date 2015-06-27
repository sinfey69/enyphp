<?php
/**
 * 文件上传下载类
 * @author enychen
 */

namespace Extend;

class DownLoad
{
	/**
	 * 文件下载，支持直接输出或读取文件输出
	 * @param string 文件名
	 * @param string 数据
	 * @return void
	 */
	public static function download($filename, $data=NULL)
	{
		header('Content-Description: File Transfer');
		header("Accept-Ranges:bytes");
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		if(is_file($filename) && !$data)
		{
			ob_start();
			readfile($filename);
			$data = ob_get_clean();
		}
		// 微软ms header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Content-Length: ' . strlen($data));
		
		echo $data;
	}
}