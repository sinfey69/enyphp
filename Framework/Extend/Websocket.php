<?php

namespace NetWork;

/**
 * websocket服务器类
 * @author enychen
 */
class Websocket
{
	/**
	 * 服务器地址
	 * @var string
	 */
	private $host;

	/**
	 * 服务器端口
	 * @var int
	 */
	private $port;

	/**
	 * 调试模式
	 * @var boolean
	 */
	private $debug;

	/**
	 * 服务器资源
	 * @var resource
	 */
	private $master;

	/**
	 * 用户列表
	 * @var array
	 */
	private $users = array();

	/**
	 * 需要执行的回调函数
	 * @var array
	 */
	private $callback = array();

	/**
	 * 日志路径
	 * @var string
	 */
	private $path;

	/**
	 * 构造函数
	 * @param string 服务器地址
	 * @param string 端口号
	 * @param string 日志路径
	 * @param string 开启调试输出
	 */
	public function __construct($host, $port, $path='.', $debug=TRUE)
	{
		// 主机
		$this->host = $host;
		// 端口
		$this->port = $port;
		// 日志目录
		$this->path = $path;
		// 调试
		$this->debug = $debug;

		// 创建服务器
		$this->create();
	}

	/**
	 * 设置业务回调函数
	 * @param array 包含对象和对象方法的数组,目前只支持process字段
	 */
	public function callback($method)
	{
		$this->callback = array_merge($this->callback, $method);
	}

	/**
	 * 创建服务器,目前只支持TCP协议
	 */
	private function create()
	{
		// 创建服务器
		$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) OR die('socket_create failed: ' . socket_last_error());
		// 服务器设置
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, TRUE) OR die('socket_set_option failed: ' . socket_last_error());
		// 绑定端口
		socket_bind($this->master, $this->host, $this->port) OR die('socket_bind failed: ' . socket_last_error());
		// 监听端口
		socket_listen($this->master) OR die('socket_listen failed: ' . socket_last_error());

		// 加入资源列表
		$this->users[] = new User($this->master);
	}

	/**
	 * 监听通信
	 */
	public function listen()
	{
		// 无限循环等待消息
		while(TRUE)
		{
			// 获得主机资源列表
			$changed = $this->getSocket(NULL, TRUE);

			// 通信调试
			$this->debug($changed);

			// 自动选择来消息的主机
			@socket_select($changed, $write=NULL, $express=NULL, NULL);

			// 读取所有连接资源
			foreach($changed as $socket)
			{
				// 如果是主进程表示是新连接的资源
				if($socket == $this->master)
				{
					// 能够获得新连接的资源
					if($client = socket_accept($this->master))
					{
						// 加入列表
						$this->users[] = new User($client);
					}
				}
				else
				{
					// 获取资源对应的对象
					$user = $this->getUser($socket);

					// 读取信息, 返回字节数
					if($data = @socket_recv($user->socket, $origin, 1024, 0))
					{
						// php后台过来的内容
						$needDecode = $this->isBackstage($user, $origin);
				
						// 是否握手, 已经握手进行数据处理，否则进行握手
						$user->handShake ? $this->process($user, $origin, $needDecode) : $this->handShake($user, $origin);
					}
					else
					{
						// 关闭浏览器的时候的断开连接, 离开页面或者重新加载页面的时候需要在process函数内处理
						$this->disConnect($user->socket);
					}
				}
			}
		}
	}

	/**
	 * 后台没有三次握手的状态,所以依靠是否能否直接解析json作为判断来源
	 * @param object 用户
	 * @param string 原生数据
	 * @return bool 是否是php-client传递过来的
	 */
	private function isBackstage($user, $origin)
	{
		// 尝试解析json
		if($result = json_decode($origin, TRUE))
		{
			$user->handShake = TRUE;
			return FALSE;
		}
	
		return TRUE;
	}

	/**
	 * 进行握手
	 * @param <object> 当前连接的用户
	 * @param <string> 浏览器发送的信息
	 */
	private function handShake($user, $header)
	{
		// 头信息四个变量
		$url = $host = $origin = $key = NULL;

		// 固定拼接字符串
		$joinKey = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
		
		// 解析
		if(preg_match("/GET (.*) HTTP/", $header, $match))
		{
			$url = $match[1];
		}
		if(preg_match("/Host: (.*)\r\n/", $header, $match))
		{
			$host = $match[1];
		}
		if(preg_match("/Origin: (.*)\r\n/", $header, $match))
		{
			$origin = $match[1];
		}
		if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $header, $match))
		{
			$key = $match[1];
		}

		// 响应头信息
		$upgrade = "HTTP/1.1 101 Switching Protocol\r\n" 
			. "Upgrade: websocket\r\n" 
			. "Connection: Upgrade\r\n" 
			. "Sec-WebSocket-Accept: " 
			. base64_encode(sha1($key . $joinKey, TRUE)) 
		    . "\r\n\r\n";

		// 输出响应头
		@socket_write($user->socket, $upgrade, strlen($upgrade));

		// 用户握手确认
		$user->handShake = TRUE;
	}

	/**
	 * 解析数据
	 * @param resource 资源
	 * @param string   用户发送过来的数据
	 * @return string  解码后的数据
	 */
	private function decode($socket, $origin)
	{
		// 检查用户是否刷新了浏览器
		$opcode = ord(substr($origin, 0, 1)) & 0x0F;
		$ismask = (ord(substr($origin, 1, 1)) & 0x80) >> 7;
		if($ismask != 1 || $opcode == 0x8)
		{
			$this->disConnect($socket);
			return NULL;
		}		

		// 解析数据
		$len = $masks = $data = $decoded = null;

		$len = ord($origin[1]) & 127;

		if ($len === 126)
		{
			$masks = substr($origin, 4, 4);
			$data = substr($origin, 8);
		}
		else if($len === 127)
		{
			$masks = substr($origin, 10, 4);
			$data = substr($origin, 14);
		} 
		else
		{
			$masks = substr($origin, 2, 4);
			$data = substr($origin, 6);
		}

		for($i=0,$len=strlen($data); $i<$len; $i++)
		{
			$decoded .= $data[$i] ^ $masks[$i % 4];
		}

		return $decoded;
	}

	/**
	 * 编码数据
	 * @param string 原生数据
	 * @param int 头部编码
	 * @return string 编码后的字符串
	 */
	public function encode($msg, $opcode=0x1)
	{
		//头部信息
		$firstByte = 0x80 | $opcode;
		$encodedata = null;
		$len = strlen($msg);

		if (0 <= $len && $len <= 125)
		{
			$encodedata = chr(0x81) . chr($len) . $msg;
		}
		else if (126 <= $len && $len <= 0xFFFF)
		{
			$low = $len & 0x00FF;
			$high = ($len & 0xFF00) >> 8;
			$encodedata = chr($firstByte) . chr(0x7E) . chr($high) . chr($low) . $msg;
		}

		return $encodedata;
	}

	/**
	 * 处理用户发送过来的数据
	 * @param object 用户
	 * @param string 原生的数据
	 */
	private function process($user, $origin, $decode=TRUE)
	{
		// 解析数据
		$msg = $decode ? $this->decode($user->socket, $origin) : $origin;		

		// 回调函数的执行
		if($msg && isset($this->callback['process']))
		{
			$args = array();
			$args['user'] = $user;
			$args['data'] = $msg;
			call_user_func_array($this->callback['process'], $args);
		}
	}

	/**
	 * 输出调试
	 * @param mixed 可输出的内容
	 */
	private function debug($changed)
	{
		!$this->debug OR print_r($changed);
	}

	/**
	 * 日志记录
	 * @param mixed 内容
	 */
	private function log($content)
	{
		// 日志文件
		$file = rtrim($this->path, '/') . '/' . date('Y-m-d') . '.log';	

		// 格式化日志内容
		$content = sprintf("%s%s\r\n%s\r\n%s\r\n", date('H:i:s'), str_repeat('=', 100), print_r($content, TRUE), str_repeat('=', 100));

		// 文件夹不存在
		if(!is_dir(dirname($file)))
		{
			@mkdir(dirname($file), 0777, TRUE);
		}

		// 写入日志
		file_put_contents($filename, $content, FILE_APPEND);
	}

	/**
	 * 断开连接,删除用户
	 * @param resource 需要断开的资源
	 */
	public function disConnect($socket)
	{
		// 遍历删除用户资源
		foreach($this->users as $key=>$user)
		{
			if($user->socket == $socket)
			{
				// 删除资源对应的用户
				array_splice($this->users, $key, 1);
				break;
			}
		}

		// 关闭资源
		@socket_close($socket);
	}

	/**
	 * 发送数据给用户
	 * @param resource|array 资源或者资源数组
	 * @param string 发送内容
	 */
	public function send($sockets, $msg)
	{
		$sockets = is_array($sockets) ? $sockets : array($sockets);
	
		// 数据编码
		$msg = $this->encode($msg);

		// 遍历输出内容
		foreach($sockets as $key => $socket)
		{
			@socket_write($socket, $msg, strlen($msg));
		}
	}

	/**
	 * 获得指定的用户
	 * @param mixed 用户资源|用户id
	 * @param bool  只获取一个用户
	 * @return object|array 用户或者用户数组
	 */
	public function getUser($exp, $only=TRUE)
	{
		$list = array();

		// 遍历所有用户
		foreach($this->users as $user)
		{
			// 资源相等或者id相等
			if($user->socket == $exp || $user->id == $exp)
			{
				$list[] = $user;
			}
		}
		
		// 是否有用户 | 是否只要一个用户
		return ($list ? ($only ? $list[0] : $list) : FALSE);
	}

	/**
	 * 获取一个id对应的所有资源
	 * @param bool 用户的id
	 * @param bool 是否包含服务器资源
	 * @return array 资源数组
	 */
	public function getSocket($id=NULL, $includeMaster=FALSE)
	{
		// 资源列表
		$sockets = array();
		
		// 循环所有的连接用户
		foreach($this->users as $user)
		{
			// 如果是获取指定用户的全部资源
			if($id)
			{
				if($user->id != $id)
				{
					continue;
				}

				$sockets[] = $user->socket;
			}
			else
			{
				// 获取全部的资源
				if(!$includeMaster && $user->socket == $this->master)
				{
					// 不包含服务器资源
					continue;
				}
				$sockets[] = $user->socket;
			}
		}

		return $sockets;
	}
}

/**
 * 连接到服务器的用户
 * @author enychen
 */
class User
{
	/**
	 * 用户唯一标识符
	 * @var mixed
	 */
	public $id = NULL;
	
	/**
	 * 用户的socket资源
	 * @var resource
	 */
	public $socket = NULL;

	/**
	 * 握手状态
	 * @var boolean
	 */
	public $handShake = FALSE;

	/**
	 * 用户token信息
	 * @var string
	 */
	public $token = NULL;

	/**
	 * 用户的信息
	 * @var array
	 */
	public $data = array();

	/**
	 * 创建用户
	 * @param resource 连接资源
	 */
	public function __construct($socket=NULL)
	{
		$this->socket = $socket;
	}
}