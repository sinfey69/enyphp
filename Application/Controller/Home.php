<?php

namespace Controller;
use \Mvc\Controller;
use \Extend\Page;
/**
 * 前台入口控制器
 */
class Home extends Controller
{
	public function index()
	{
		echo "<style>.page a{padding:10px;text-decoration:none;display:inline-block;border:1px solid #333}.page .page-selected{background:blue}</style>";
		echo "<div class='page'>".Page::build($_GET['page'], 100, 10)."</div>";
	}
}