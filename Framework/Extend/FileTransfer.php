<?php
/**
 * 文件上传下载类
 * @author enychen
 */

namespace Extend;

class FileTransfer
{
	/**
	 * 上传文件错误信息
	 * @var string
	 */
	private static $error;
	
	/**
	 * 文件上传
	 * @param string 上传文件key
	 * @param object 上传文件配置，包含ext,size,path
	 * @param string 目标文件名
	 */
	public static function upload($name, $config, $destination=NULL)
	{
		// 文件不存在
		if(empty($_FILES[$name]))
		{
			return array(FALSE, '文件不存在');
		}
		// 格式化上传文件
		$upload = self::format($name);
		// 错误检查
		if($error = self::check($upload,$config))
		{
			return array(FALSE, self::$error);
		}
		// 文件上传
		self::move($upload, $config->path,$destination);
	}
	
	/**
	 * 文件上传格式化
	 * @param string 上传名
	 * @return array
	 */
	private static function format($name)
	{
		if(count($_FILES[$name]) == count($_FILES[$name], COUNT_RECURSIVE))
		{
			$upload = array($_FILES[$name]);
		}
		else
		{
			for($i=0, $len=count($_FILES[$name]['name']); $i<$len; $i++)
			{
				$upload[$i]['name'] = $_FILES[$name]['name'][$i];
				$upload[$i]['type'] = $_FILES[$name]['type'][$i];
				$upload[$i]['tmp_name'] = $_FILES[$name]['tmp_name'][$i];
				$upload[$i]['error'] = $_FILES[$name]['error'][$i];
				$upload[$i]['size'] = $_FILES[$name]['size'][$i];
			}	
		}
		
		return $upload;
	}
	
	/**
	 * 文件上传检查
	 * @param string 错误信息
	 */
	private static function check($upload, $config)
	{
		foreach($upload as $file)
		{
			// 是否是post传递的
			if(!is_uploaded_file($file['tmp_name']))
			{
				return "非法上传";
			}
			// 文件自身错误检查
			switch($file['error'])
			{
				case 0:
					break;
				case 1:
					// 上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
				case 2:
					// 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
					return '文件超过php设置的大小';
				case 3:
					// 文件只有部分被上传
					return '文件不完整';
				case 4:
					return '文件没有被上传';
				case 5:
					// php设置错误，没有设置临时文件夹
					trigger_error('Missing a temporary folder', E_USER_WARNING);
				case 6:
					// 无法将临时文件写入磁盘
					trigger_error('Failed to write file to disk', E_USER_WARNING);
				case 8:
					// 不知道啥，总之先写着
					trigger_error('A PHP extension stopped the file upload', E_USER_WARNING);
			}
			// 检查文件类型
			$fileInfo = new \finfo(FILEINFO_MIME_TYPE);
			$mimeType = $fileInfo->file($file['tmp_name']);
			if(!in_array($mimeType, $config->ext))
			{
				return  '不合法的类型文件';
			}
			// 检查文件的大小
			if($file['type'] > $config->size)
			{
				return '文件太大';
			}
		}
		
		return NULL;
	}
	
	/**
	 * 移动文件
	 * @param array 文件数组
	 * @param string 保存目录
	 * @param boolean
	 */
	private static function move($upload, $path, $destination)
	{
		foreach($upload as $i=>$file)
		{
			move_uploaded_file($file['tmp_name'], $path.time().$i.'_'.$destination.strrchr($file['name'], '.'));
		}
	}

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